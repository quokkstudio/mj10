<?php

namespace Controller\Admin\Member;

use Component\Member\BizApprovalService;
use Framework\ObjectStorage\Service\ImageUploadService;

class MemberBizPhotoController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $mode = $request->get()->get('mode');
        $sno = (int)$request->get()->get('sno');

        if ($sno <= 0) {
            throw new \Exception(__('요청을 찾을 수 없습니다.'));
        }

        if (!in_array($mode, ['download', 'view'], true)) {
            throw new \Exception(__('요청을 찾을 수 없습니다.'));
        }

        $service = new BizApprovalService();
        $photo = $service->getPhoto($sno);

        if (empty($photo)) {
            throw new \Exception(__('파일을 찾을 수 없습니다.'));
        }

        if ($mode === 'download') {
            (new ImageUploadService())->download($photo['originName'], $photo['storagePath']);
            exit;
        }

        $storagePath = (string)gd_isset($photo['storagePath']);
        if (preg_match('~^https?://~i', $storagePath)) {
            header('Location: ' . $storagePath, true, 302);
            exit;
        }
        if (preg_match('~https?://~i', $storagePath)) {
            $pos = stripos($storagePath, 'http');
            header('Location: ' . substr($storagePath, $pos), true, 302);
            exit;
        }

        $publicPath = $storagePath;
        if ($publicPath === '' || strpos($publicPath, '/') === false) {
            $savedName = gd_isset($photo['savedName']);
            $memNo = (int)gd_isset($photo['memNo']);
            if ($savedName !== '' && $memNo > 0) {
                $publicPath = '/member/biz/loading/' . $memNo . '/' . ltrim($savedName, '/');
            }
        }
        if ($publicPath !== '' && strpos($publicPath, '/data/') !== 0) {
            $publicPath = (strpos($publicPath, '/') === 0 ? '/data' . $publicPath : '/data/' . $publicPath);
        }

        $realPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . $publicPath;
        if ($realPath === '' || !is_file($realPath)) {
            (new ImageUploadService())->download($photo['originName'], $photo['storagePath']);
            exit;
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($realPath) : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($realPath));
        header('Content-Disposition: inline; filename="' . basename($realPath) . '"');
        readfile($realPath);
        exit;
    }
}
