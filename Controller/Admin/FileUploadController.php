<?php

namespace Plugin\ECCUBE2Downloads\Controller\Admin;

use Eccube\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController
{
    /**
     * @var string|null
     */
    private $downloadDir;

    private function getDownloadDir(): string
    {
        if ($this->downloadDir === null) {
            $this->downloadDir = $this->eccubeConfig->get('kernel.project_dir').'/var/downloads';
        }

        return $this->downloadDir;
    }

    /**
     * ダウンロードファイルのアップロード.
     *
     * @Route("/%eccube_admin_route%/eccube2downloads/file/upload", name="eccube2downloads_admin_file_upload", methods={"POST"})
     */
    public function upload(Request $request)
    {
        if (!$request->isXmlHttpRequest() && $this->isTokenValid()) {
            throw new BadRequestHttpException();
        }

        $file = $request->files->get('eccube2downloads_file');
        if ($file === null) {
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
     *
     * @Route("/%eccube_admin_route%/eccube2downloads/file/delete", name="eccube2downloads_admin_file_delete", methods={"POST"})
     */
    public function delete(Request $request)
    {
        if (!$request->isXmlHttpRequest() && $this->isTokenValid()) {
            throw new BadRequestHttpException();
        }

        $filename = $request->request->get('filename');
        if (!$filename || basename($filename) !== $filename || strpos($filename, "\0") !== false) {
            throw new BadRequestHttpException();
        }

        $downloadDir = $this->getDownloadDir();
        $filePath = $downloadDir.'/'.$filename;
        $realPath = realpath($filePath);
        $realDownloadDir = realpath($downloadDir);

        if ($realPath && $realDownloadDir && is_file($realPath) && strpos($realPath, $realDownloadDir.DIRECTORY_SEPARATOR) === 0) {
            $fs = new Filesystem();
            $fs->remove($realPath);
        }

        return $this->json(['status' => 'OK']);
    }
}
