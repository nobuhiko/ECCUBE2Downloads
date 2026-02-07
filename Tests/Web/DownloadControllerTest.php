<?php

namespace Plugin\ECCUBE2Downloads\Tests\Web;

use Eccube\Entity\Customer;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Tests\Web\AbstractWebTestCase;
use Plugin\ECCUBE2Downloads\Entity\Config;

class DownloadControllerTest extends AbstractWebTestCase
{
    /** @var Customer */
    protected $Customer;

    /** @var Order */
    protected $Order;

    /** @var string */
    protected $downloadDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->Customer = $this->createCustomer();

        // テスト用ダウンロードファイルを作成
        $this->downloadDir = $this->eccubeConfig->get('kernel.project_dir') . '/var/downloads';
        if (!file_exists($this->downloadDir)) {
            mkdir($this->downloadDir, 0777, true);
        }
        file_put_contents($this->downloadDir . '/test_file.pdf', 'dummy pdf content');

        // 注文を作成
        $this->Order = $this->createOrder($this->Customer);
        $this->Order->setPaymentDate(new \DateTime());
        $OrderStatus = $this->entityManager->find(OrderStatus::class, OrderStatus::PAID);
        $this->Order->setOrderStatus($OrderStatus);
        $this->entityManager->flush();

        // 最初のOrderItemのProductClassにダウンロード情報を設定
        foreach ($this->Order->getOrderItems() as $OrderItem) {
            if ($OrderItem->isProduct()) {
                $ProductClass = $OrderItem->getProductClass();
                if ($ProductClass) {
                    $ProductClass->down_filename = 'テスト商品.pdf';
                    $ProductClass->down_realfilename = 'test_file.pdf';
                    $this->entityManager->flush();
                    break;
                }
            }
        }
    }

    protected function tearDown(): void
    {
        if ($this->downloadDir && file_exists($this->downloadDir . '/test_file.pdf')) {
            unlink($this->downloadDir . '/test_file.pdf');
        }
        parent::tearDown();
    }

    /**
     * ダウンロード可能なOrderItemのIDを取得
     */
    private function getDownloadableOrderItemId(): ?int
    {
        foreach ($this->Order->getOrderItems() as $OrderItem) {
            if ($OrderItem->isProduct() && $OrderItem->getProductClass() && $OrderItem->getProductClass()->down_realfilename) {
                return $OrderItem->getId();
            }
        }

        return null;
    }

    public function testDownloadSuccess()
    {
        $this->loginTo($this->Customer);

        $orderItemId = $this->getDownloadableOrderItemId();
        self::assertNotNull($orderItemId);

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => $this->Order->getOrderNo(),
            'order_item_id' => $orderItemId,
        ]));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertStringContainsString('attachment', $this->client->getResponse()->headers->get('Content-Disposition'));
    }

    public function testDownloadNotLoggedIn()
    {
        $orderItemId = $this->getDownloadableOrderItemId();

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => $this->Order->getOrderNo(),
            'order_item_id' => $orderItemId,
        ]));

        // ログインしていないのでリダイレクト or 403
        $status = $this->client->getResponse()->getStatusCode();
        self::assertTrue($status === 302 || $status === 403);
    }

    public function testDownloadOtherCustomer()
    {
        $OtherCustomer = $this->createCustomer();
        $this->loginTo($OtherCustomer);

        $orderItemId = $this->getDownloadableOrderItemId();

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => $this->Order->getOrderNo(),
            'order_item_id' => $orderItemId,
        ]));

        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDownloadNotPaid()
    {
        $this->loginTo($this->Customer);

        // 入金日をnullに設定
        $this->Order->setPaymentDate(null);
        $this->entityManager->flush();

        $orderItemId = $this->getDownloadableOrderItemId();

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => $this->Order->getOrderNo(),
            'order_item_id' => $orderItemId,
        ]));

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDownloadExpired()
    {
        $this->loginTo($this->Customer);

        // 入金日を31日前に設定（デフォルト30日制限）
        $this->Order->setPaymentDate(new \DateTime('-31 days'));
        $this->entityManager->flush();

        $orderItemId = $this->getDownloadableOrderItemId();

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => $this->Order->getOrderNo(),
            'order_item_id' => $orderItemId,
        ]));

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDownloadUnlimited()
    {
        $this->loginTo($this->Customer);

        // 無制限に設定
        $Config = $this->entityManager->getRepository(Config::class)->findOneBy([]);
        $Config->setDownloadableDaysUnlimited(true);
        $this->entityManager->flush();

        // 入金日を1年前に設定
        $this->Order->setPaymentDate(new \DateTime('-365 days'));
        $this->entityManager->flush();

        $orderItemId = $this->getDownloadableOrderItemId();

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => $this->Order->getOrderNo(),
            'order_item_id' => $orderItemId,
        ]));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDownloadInvalidOrderNo()
    {
        $this->loginTo($this->Customer);

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => 'INVALID-ORDER-NO',
            'order_item_id' => 999999,
        ]));

        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDownloadNonDownloadProduct()
    {
        $this->loginTo($this->Customer);

        // down_realfilenameのないOrderItemのIDを取得
        $nonDownloadItemId = null;
        foreach ($this->Order->getOrderItems() as $OrderItem) {
            if ($OrderItem->isProduct() && $OrderItem->getProductClass() && !$OrderItem->getProductClass()->down_realfilename) {
                $nonDownloadItemId = $OrderItem->getId();
                break;
            }
        }

        if ($nonDownloadItemId === null) {
            $this->markTestSkipped('Non-download OrderItem not found');
        }

        $this->client->request('GET', $this->generateUrl('eccube2downloads_mypage_download', [
            'order_no' => $this->Order->getOrderNo(),
            'order_item_id' => $nonDownloadItemId,
        ]));

        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
