<?php

namespace Controller\Admin\Member;

use Component\Member\BizApprovalService;
use Component\Member\MemberAdmin;
use Component\Member\Manager;

class MemberBizApprovalPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $post = $request->post()->all();
        $mode = gd_isset($post['mode']);
        if ($mode === '') {
            $mode = gd_isset($request->get()->get('mode'));
        }
        $memNos = (array)gd_isset($post['memNo'], []);
        $returnUrl = gd_isset($post['returnUrl'], $request->getReferer());
        $returnUrl = urldecode($returnUrl);

        $session = \App::getInstance('session');
        $manager = $session->get(Manager::SESSION_MANAGER_LOGIN);
        $managerId = gd_isset($manager['managerId'], 'admin');

        $bizService = new BizApprovalService();
        $memberAdmin = \App::load(MemberAdmin::class);

        if ($mode === '' && gd_isset($post['adminMemo']) !== '') {
            $mode = 'save_memo';
        }
        if ($mode === '' && gd_isset($post['rejectReason']) !== '') {
            $mode = 'reject';
        }

        switch ($mode) {
            case 'approve':
                \DB::begin_tran();
                try {
                    $bizService->updateApprovalStatus($memNos, BizApprovalService::STATUS_APPROVED, $managerId);
                    $memberAdmin->approvalJoinByMemberNo($memNos);
                    \DB::commit();
                } catch (\Throwable $e) {
                    \DB::rollback();
                    throw $e;
                }
                $this->redirect($returnUrl);
                break;
            case 'reject':
                $rejectReason = gd_isset($post['rejectReason']);
                \DB::begin_tran();
                try {
                    $bizService->updateApprovalStatus($memNos, BizApprovalService::STATUS_REJECTED, $managerId, $rejectReason);
                    $memberAdmin->disapprovalJoinByMemberNo($memNos);
                    \DB::commit();
                } catch (\Throwable $e) {
                    \DB::rollback();
                    throw $e;
                }
                $this->redirect($returnUrl);
                break;
            case 'save_memo':
                $memNo = (int)($memNos[0] ?? 0);
                $memo = gd_isset($post['adminMemo']);
                if ($memNo > 0) {
                    $bizService->updateAdminMemo($memNo, $memo);
                }
                $this->redirect($returnUrl);
                break;
            default:
                throw new \Exception(__('요청을 찾을 수 없습니다.'));
        }
    }
}
