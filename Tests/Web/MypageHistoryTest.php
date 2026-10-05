<?php

declare(strict_types=1);

namespace Plugin\ECCUBE2Downloads44\Tests\Web;

use Symfony\Component\HttpFoundation\Response;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Tests\Web\AbstractWebTestCase;

final class MypageHistoryTest extends AbstractWebTestCase
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
        $this->assertInstanceOf(OrderStatus::class, $OrderStatus);
        $this->Order->setOrderStatus($OrderStatus);
        $this->entityManager->flush();
    }

    public function testHistoryPageAccessible(): void
    {
        $this->loginTo($this->Customer);

        $this->client->request('GET', $this->generateUrl('mypage_history', [
            'order_no' => $this->Order->getOrderNo(),
        ]));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testHistoryShowsDownloadLink(): void
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

        $this->client->request('GET', $this->generateUrl('mypage_history', [
            'order_no' => $this->Order->getOrderNo(),
        ]));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        // ダウンロード商品セクションが表示されていること
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('ダウンロード商品', (string) $content);
        $this->assertStringContainsString('ダウンロード', (string) $content);
    }

    public function testHistoryShowsPaymentPending(): void
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
        $this->Order->setPaymentDate();
        $this->entityManager->flush();

        $this->client->request('GET', $this->generateUrl('mypage_history', [
            'order_no' => $this->Order->getOrderNo(),
        ]));

        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('入金確認中', (string) $content);
    }
}
