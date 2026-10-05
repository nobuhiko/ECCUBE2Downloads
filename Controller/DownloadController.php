<?php

namespace Plugin\ECCUBE2Downloads44\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Plugin\ECCUBE2Downloads44\Repository\ConfigRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DownloadController extends AbstractController
{
    public function __construct(private readonly ConfigRepository $configRepository)
    {
    }

    #[Route(path: '/mypage/download/{order_no}/{order_item_id}', name: 'eccube2downloads44_mypage_download', requirements: ['order_item_id' => '\d+'], methods: ['GET'])]
    public function download(string $order_no, int $order_item_id): BinaryFileResponse
    {
        $Customer = $this->getUser();
        if (!$Customer instanceof Customer) {
            throw new AccessDeniedHttpException();
        }

        // 注文が本人のものか確認
        $this->entityManager->getFilters()->enable('incomplete_order_status_hidden');
        /** @var Order|null $Order */
        $Order = $this->entityManager->getRepository(Order::class)->findOneBy([
            'order_no' => $order_no,
            'Customer' => $Customer,
        ]);
        if (!$Order) {
            throw new NotFoundHttpException();
        }

        // 対象OrderItemを取得
        /** @var OrderItem|null $OrderItem */
        $OrderItem = $this->entityManager->getRepository(OrderItem::class)->find($order_item_id);
        if (!$OrderItem || !$OrderItem->getOrder() || $OrderItem->getOrder()->getId() !== $Order->getId()) {
            throw new NotFoundHttpException();
        }

        // ダウンロード商品か確認
        $ProductClass = $OrderItem->getProductClass();
        if (!$ProductClass || empty($ProductClass->down_realfilename)) {
            throw new NotFoundHttpException();
        }

        // 入金確認済みか確認
        $paymentDate = $Order->getPaymentDate();
        if (!$paymentDate) {
            throw new AccessDeniedHttpException('入金が確認されていません。');
        }

        // ダウンロード期限チェック
        $Config = $this->configRepository->get();
        if ($Config && !$Config->isDownloadableDaysUnlimited()) {
            $deadline = clone $paymentDate;
            $deadline->modify('+'.(int) $Config->getDownloadableDays().' days');
            if (new \DateTime() > $deadline) {
                throw new AccessDeniedHttpException('ダウンロード期限が過ぎています。');
            }
        }

        // ファイル名のバリデーション（ディレクトリトラバーサル防止）
        $realfilename = (string) $ProductClass->down_realfilename;
        if (basename($realfilename) !== $realfilename || str_contains($realfilename, "\0")) {
            throw new NotFoundHttpException();
        }

        $downloadDir = $this->eccubeConfig->get('kernel.project_dir').'/var/downloads';
        $realDownloadDir = realpath($downloadDir);
        if ($realDownloadDir === false) {
            throw new NotFoundHttpException();
        }

        $filePath = $realDownloadDir.'/'.$realfilename;
        $realPath = realpath($filePath);

        if ($realPath === false || !is_file($realPath) || !str_starts_with($realPath, $realDownloadDir.DIRECTORY_SEPARATOR)) {
            throw new NotFoundHttpException();
        }

        // ファイル配信
        $displayName = $ProductClass->down_filename ?: $realfilename;
        $displayName = basename((string) $displayName);
        $displayName = (string) preg_replace('/[\x00-\x1F\x7F]/', '', $displayName);
        $response = new BinaryFileResponse($realPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $displayName
        );

        return $response;
    }
}
