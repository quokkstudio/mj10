<?php

namespace Controller\Admin\Member;

use Component\Member\BizApprovalService;

class MemberBizApprovalExcelController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $search = $request->get()->all();

        $service = new BizApprovalService();
        $rows = $service->getExcelList($search);

        $this->streamedDownload('사업자가입승인관리.xls');

        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>' . __('가입일') . '</th>';
        echo '<th>' . __('업체명') . '</th>';
        echo '<th>' . __('아이디') . '</th>';
        echo '<th>' . __('이메일') . '</th>';
        echo '<th>' . __('발주담당자') . '</th>';
        echo '<th>' . __('발주담당자 연락처') . '</th>';
        echo '<th>' . __('결제담당자') . '</th>';
        echo '<th>' . __('결제담당자 연락처') . '</th>';
        echo '<th>' . __('등록증 여부') . '</th>';
        echo '<th>' . __('적재사진 개수') . '</th>';
        echo '<th>' . __('적재사진(미리보기)') . '</th>';
        echo '<th>' . __('승인상태') . '</th>';
        echo '<th>' . __('처리일시') . '</th>';
        echo '<th>' . __('처리자') . '</th>';
        echo '<th>' . __('반려사유') . '</th>';
        echo '<th>' . __('관리자메모') . '</th>';
        echo '</tr>';

        foreach ($rows as $row) {
            $companyName = gd_isset($row['companyName'], $row['memberCompany'] ?? '');
            $status = gd_isset($row['approvalStatus'], BizApprovalService::STATUS_PENDING);
            $statusLabel = $this->getStatusLabel($status);
            $treatAt = $row['approvedAt'] ?: $row['rejectedAt'];
            $treatBy = $row['approvedBy'] ?: $row['rejectedBy'];
            $certLabel = ($row['certExists'] ?? 'n') === 'y' ? __('등록') : __('미등록');
            $photoUrl = '';
            if (!empty($row['photoFirstPath']) || !empty($row['photoFirstSavedName'])) {
                $photoUrl = $service->buildPhotoViewUrl(
                    (string)gd_isset($row['photoFirstPath']),
                    (int)gd_isset($row['memNo']),
                    (string)gd_isset($row['photoFirstSavedName'])
                );
            }
            echo '<tr>';
            echo '<td>' . gd_isset($row['entryDt']) . '</td>';
            echo '<td>' . gd_htmlspecialchars($companyName) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['memId']) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['orderManagerName']) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['orderManagerPhone']) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['payManagerName']) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['payManagerPhone']) . '</td>';
            echo '<td>' . $certLabel . '</td>';
            echo '<td>' . (int)gd_isset($row['photoCount']) . '</td>';
            echo '<td>' . ($photoUrl !== '' ? ('<img src="' . gd_htmlspecialchars($photoUrl) . '" width="80" />') : '') . '</td>';
            echo '<td>' . $statusLabel . '</td>';
            echo '<td>' . gd_isset($treatAt) . '</td>';
            echo '<td>' . gd_htmlspecialchars($treatBy) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['rejectReason']) . '</td>';
            echo '<td>' . gd_htmlspecialchars($row['adminMemo']) . '</td>';
            echo '</tr>';
        }

        echo '</table>';
        exit;
    }

    private function getStatusLabel(string $status): string
    {
        switch ($status) {
            case BizApprovalService::STATUS_APPROVED:
                return __('승인');
            case BizApprovalService::STATUS_REJECTED:
                return __('반려');
            default:
                return __('승인대기');
        }
    }
}
