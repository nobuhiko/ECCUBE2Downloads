<?php

namespace Plugin\ECCUBE2Downloads\Tests\Web;

use Eccube\Entity\Customer;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Tests\Web\AbstractWebTestCase;

class MypageHistoryTest extends AbstractWebTestCase
{
    /** @var Customer */
    protected $Customer;

    /** @var Order */
    protected $Order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->Customer = $this->createCustomer();
        $this->Order = $this->createOrder($this->Customer);

        // 新規受付ステータスに設定
        $OrderStatus = $this->entityManager->find(OrderStatus::class, OrderStatus::NEW);
        $this->Order->setOrderStatus($OrderStatus);
        $this->entityManager->flush();
    }

    public function testHistoryPageAccessible()
    {
        $this->loginTo($this->Customer);

        $this->client->request('GET', $this->generateUrl('mypage_history', [
            'order_no' => $this->Order->getOrderNo(),
        ]));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testHistoryShowsDownloadLink()
    {
        $this->loginTo($this->Customer);

        // ダウンロード商品情報を設定
        foreach ($this->Order->getOrderItems() as $OrderItem) {
            if ($OrderItem->isProduct() && $OrderItem->getProductClass()) {
                $ProductClass = $OrderItem->getProductClass();
                $ProductClass->down_filename = 'テスト.pdf';
                $ProductClass->down_realfilename = 'test.pdf';
                break;
            }
        }

        $this->Order->setPaymentDate(new \DateTime());
        $OrderStatus = $this->entityManager->find(OrderStatus::class, OrderStatus::PAID);
        $this->Order->setOrderStatus($OrderStatus);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $this->generateUrl('mypage_history', [
            'order_no' => $this->Order->getOrderNo(),
        ]));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        // ダウンロード商品セクションが表示されていること
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('ダウンロード商品', $content);
        self::assertStringContainsString('ダウンロード', $content);
    }

    public function testHistoryShowsPaymentPending()
    {
        $this->loginTo($this->Customer);

        // ダウンロード商品情報を設定（未入金）
        foreach ($this->Order->getOrderItems() as $OrderItem) {
            if ($OrderItem->isProduct() && $OrderItem->getProductClass()) {
                $ProductClass = $OrderItem->getProductClass();
                $ProductClass->down_filename = 'テスト.pdf';
                $ProductClass->down_realfilename = 'test.pdf';
                break;
            }
        }
        $this->Order->setPaymentDate(null);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $this->generateUrl('mypage_history', [
            'order_no' => $this->Order->getOrderNo(),
        ]));

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('入金確認中', $content);
    }
}
