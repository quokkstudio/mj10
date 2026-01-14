<?php

namespace Controller\Admin\Member;

use Component\Member\BizApprovalService;
class MemberBizApprovalListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'member', 'biz_approval');
        $request = \App::getInstance('request');

        if (!$request->get()->has('page')) {
            $request->get()->set('page', 1);
        }
        if (!$request->get()->has('pageNum')) {
            $request->get()->set('pageNum', 10);
        }
        if ($request->get()->has('entryDt') === false) {
            $request->get()->set('entryDt', ['', '']);
        }

        if ($request->get()->has('searchFl') === false) {
            $request->get()->set('searchFl', 'y');
        }

        $page = (int)$request->get()->get('page');
        $pageNum = (int)$request->get()->get('pageNum');
        $search = $request->get()->all();

        $service = new BizApprovalService();
        $listData = ['data' => [], 'total' => 0];
        if ($request->get()->get('searchFl') === 'y') {
            $listData = $service->getList($search, $page, $pageNum);
        }

        $pageObject = new \Component\Page\Page($page, 0, 0, $pageNum);
        $pageObject->setTotal((int)$listData['total']);
        $pageObject->setAmount((int)$listData['total']);
        $pageObject->setUrl($request->getQueryString());
        $pageObject->setPage();

        $this->setData('page', $pageObject);
        $this->setData('data', $listData['data']);
        $this->setData('search', $search);
        $this->setData('searchKeys', [
            'companyName' => __('업체명'),
            'memId' => __('아이디'),
            'email' => __('이메일'),
            'orderManagerName' => __('발주담당자명'),
            'orderManagerPhone' => __('발주담당자 연락처'),
            'payManagerName' => __('결제담당자명'),
            'payManagerPhone' => __('결제담당자 연락처'),
        ]);
        $this->setData('approvalStatusOptions', [
            '' => __('전체'),
            BizApprovalService::STATUS_PENDING => __('승인대기'),
            BizApprovalService::STATUS_APPROVED => __('승인'),
            BizApprovalService::STATUS_REJECTED => __('반려'),
        ]);
        $this->setData('certOptions', [
            '' => __('전체'),
            'y' => __('등록'),
            'n' => __('미등록'),
        ]);
        $this->setData('photoOptions', [
            '' => __('전체'),
            'y' => __('있음'),
            'n' => __('없음'),
        ]);
        $this->setData('isPeriodBtn', true);
    }
}
