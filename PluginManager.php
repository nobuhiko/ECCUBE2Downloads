<?php

namespace Plugin\ECCUBE2Downloads;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryFee;
use Eccube\Entity\Master\Pref;
use Eccube\Entity\Master\SaleType;
use Eccube\Entity\Member;
use Eccube\Entity\Payment;
use Eccube\Entity\PaymentOption;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\ECCUBE2Downloads\Entity\Config;
use Psr\Container\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    public const SALE_TYPE_ID = 222;
    public const SALE_TYPE_NAME = 'ダウンロード';

    public function enable(array $meta, ContainerInterface $container)
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $this->createSaleType($em);
        $this->createDelivery($em);
        $this->createConfig($em);

        $em->flush();
    }

    public function disable(array $meta, ContainerInterface $container)
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $Delivery = $this->findDownloadDelivery($em);
        if ($Delivery) {
            $Delivery->setVisible(false);
            $em->flush();
        }
    }

    public function uninstall(array $meta, ContainerInterface $container)
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        // 配送方法の削除
        $Delivery = $this->findDownloadDelivery($em);
        if ($Delivery) {
            $em->remove($Delivery);
        }

        // SaleTypeの削除
        $SaleType = $em->find(SaleType::class, self::SALE_TYPE_ID);
        if ($SaleType) {
            $em->remove($SaleType);
        }

        // 設定の削除
        $Config = $em->getRepository(Config::class)->findOneBy([]);
        if ($Config) {
            $em->remove($Config);
        }

        $em->flush();
    }

    private function createSaleType(EntityManagerInterface $em)
    {
        $SaleType = $em->find(SaleType::class, self::SALE_TYPE_ID);
        if ($SaleType) {
            return;
        }

        $SaleType = new SaleType();
        $SaleType->setId(self::SALE_TYPE_ID);
        $SaleType->setName(self::SALE_TYPE_NAME);
        $SaleType->setSortNo(self::SALE_TYPE_ID);

        $em->persist($SaleType);
    }

    private function createDelivery(EntityManagerInterface $em)
    {
        $Delivery = $this->findDownloadDelivery($em);
        if ($Delivery) {
            $Delivery->setVisible(true);

            return;
        }

        $SaleType = $em->find(SaleType::class, self::SALE_TYPE_ID);
        $Member = $em->getRepository(Member::class)->find(1);

        $Delivery = new Delivery();
        $Delivery->setName('ダウンロード商品送料');
        $Delivery->setServiceName('ダウンロード');
        $Delivery->setSaleType($SaleType);
        $Delivery->setCreator($Member);
        $Delivery->setSortNo(0);
        $Delivery->setVisible(true);
        $Delivery->setCreateDate(new \DateTime());
        $Delivery->setUpdateDate(new \DateTime());
        $em->persist($Delivery);
        $em->flush();

        // 全都道府県の送料を0で設定
        $Prefs = $em->getRepository(Pref::class)->findAll();
        foreach ($Prefs as $Pref) {
            $DeliveryFee = new DeliveryFee();
            $DeliveryFee->setDelivery($Delivery);
            $DeliveryFee->setPref($Pref);
            $DeliveryFee->setFee(0);
            $em->persist($DeliveryFee);
        }

        // 全支払方法と紐付け
        $Payments = $em->getRepository(Payment::class)->findAll();
        foreach ($Payments as $Payment) {
            $PaymentOption = new PaymentOption();
            $PaymentOption->setDeliveryId($Delivery->getId());
            $PaymentOption->setPaymentId($Payment->getId());
            $PaymentOption->setDelivery($Delivery);
            $PaymentOption->setPayment($Payment);
            $em->persist($PaymentOption);
        }
    }

    private function createConfig(EntityManagerInterface $em)
    {
        $Config = $em->getRepository(Config::class)->findOneBy([]);
        if ($Config) {
            return;
        }

        $Config = new Config();
        $Config->setDownloadableDays(30);
        $Config->setDownloadableDaysUnlimited(false);
        $em->persist($Config);
    }

    /**
     * @return Delivery|null
     */
    private function findDownloadDelivery(EntityManagerInterface $em)
    {
        $SaleType = $em->find(SaleType::class, self::SALE_TYPE_ID);
        if (!$SaleType) {
            return null;
        }

        return $em->getRepository(Delivery::class)->findOneBy(['SaleType' => $SaleType]);
    }
}
