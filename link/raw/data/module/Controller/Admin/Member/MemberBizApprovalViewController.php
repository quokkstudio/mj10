<?php

namespace Controller\Admin\Member;

use Component\Member\BizApprovalService;

class MemberBizApprovalViewController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'member', 'biz_approval');
        $request = \App::getInstance('request');
        $memNo = (int)$request->get()->get('memNo');

        if ($memNo <= 0) {
            throw new \Exception(__('회원번호가 없습니다.'));
        }

        /** @var \Component\Member\Member $memberService */
        $memberService = \App::load('\Component\\Member\\Member');
        $memberInfo = $memberService->getMember($memNo, 'memNo', 'memNo, memId, memNm, email, company, zonecode, address, addressSub, entryDt, appFl', false);
        if (empty($memberInfo) || !is_array($memberInfo)) {
            throw new \Exception(__('회원정보를 찾을 수 없습니다.'));
        }

        $bizService = new BizApprovalService();
        $profile = $bizService->getProfile($memNo);
        $photos = $bizService->getPhotos($memNo);

        $certification = [];
        try {
            $companyCertification = \App::getInstance(\Component\Member\Company\CompanyCertification::class);
            $certification = $companyCertification->getCertification($memNo);
        } catch (\Throwable $e) {
            $certification = [];
        }

        $this->setData('member', $memberInfo);
        $this->setData('profile', $profile);
        $this->setData('photos', $photos);
        $this->setData('certification', $certification);
    }
}
