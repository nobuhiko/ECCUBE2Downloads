<?php

namespace Plugin\ECCUBE2Downloads44\Controller\Admin;

use Eccube\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class FileUploadController extends AbstractController
{
    private ?string $downloadDir = null;

    private function getDownloadDir(): string
    {
        $this->downloadDir ??= $this->eccubeConfig->get('kernel.project_dir').'/var/downloads';

        return $this->downloadDir;
    }

    /**
     * ダウンロードファイルのアップロード.
     */
    #[Route(path: '/%eccube_admin_route%/eccube2downloads44/file/upload', name: 'eccube2downloads44_admin_file_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest() && $this->isTokenValid()) {
            throw new BadRequestHttpException();
        }

        $file = $request->files->get('eccube2downloads44_file');
        if (!$file instanceof UploadedFile) {
            return $this->json(['error' => 'ファイルが選択されていません。'], Response::HTTP_BAD_REQUEST);
        }

        if (!$file->isValid()) {
            return $this->json(['error' => $file->getErrorMessage()], Response::HTTP_BAD_REQUEST);
        }

        $downloadDir = $this->getDownloadDir();
        $fs = new Filesystem();
        if (!$fs->exists($downloadDir)) {
            $fs->mkdir($downloadDir, 0775);
        }

        if (!is_writable($downloadDir)) {
            return $this->json(['error' => 'ダウンロードディレクトリに書き込み権限がありません。'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $origName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();
        $savedName = date('mdHis').uniqid('_').($ext ? '.'.$ext : '');

        $file->move($downloadDir, $savedName);

        return $this->json([
            'filename' => $savedName,
            'original_name' => $origName,
        ]);
    }

    /**
     * ダウンロードファイルの削除.
     */
    #[Route(path: '/%eccube_admin_route%/eccube2downloads44/file/delete', name: 'eccube2downloads44_admin_file_delete', methods: ['POST'])]
    public function delete(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest() && $this->isTokenValid()) {
            throw new BadRequestHttpException();
        }

        $filename = (string) $request->request->get('filename', '');
        if ($filename === '' || basename($filename) !== $filename || str_contains($filename, "\0")) {
            throw new BadRequestHttpException();
        }

        $downloadDir = $this->getDownloadDir();
        $filePath = $downloadDir.'/'.$filename;
        $realPath = realpath($filePath);
        $realDownloadDir = realpath($downloadDir);

        if ($realPath && $realDownloadDir && is_file($realPath) && str_starts_with($realPath, $realDownloadDir.DIRECTORY_SEPARATOR)) {
            $fs = new Filesystem();
            $fs->remove($realPath);
        }

        return $this->json(['status' => 'OK']);
    }
}
