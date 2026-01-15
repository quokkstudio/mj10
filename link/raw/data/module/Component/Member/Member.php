<?php

namespace Component\Member;

class Member extends \Bundle\Component\Member\Member
{
    // ✅ 부모 시그니처와 충돌 나지 않게 타입힌트 최소화
    public function join($params, $vo = null)
    {
        $isBusiness = (gd_isset($params['memberFl']) === 'business');

        // ✅ 부모 join 호출
        $memberVO = parent::join($params, $vo);

        if ($isBusiness === false) {
            return $memberVO;
        }

        $memNo = (int)$memberVO->getMemNo();

        // ✅ 같은 네임스페이스라면 이대로 OK (Component\Member\BizApprovalService)
        $bizService = new BizApprovalService();
        $uploadedPaths = [];

        try {
            // 주의: 파일 업로드가 $params가 아니라 $_FILES/Request 쪽에 있을 수 있음(환경별)
            if (!empty($params['loadingPlacePhotos'])) {
                $uploadedPaths = $bizService->savePhotosFromJoin($memNo, $params['loadingPlacePhotos'], true);
            }

            // memberVO->toArray()가 없다면 여기서도 Fatal이 날 수 있음(가입 실행 시점)
            $bizService->saveProfileFromJoin($memNo, $params, method_exists($memberVO, 'toArray') ? $memberVO->toArray() : []);
            $this->forceBusinessPending($memNo, $memberVO);
        } catch (\Throwable $e) {
            if (!empty($uploadedPaths)) {
                $bizService->deleteFiles($uploadedPaths);
            }
            throw $e;
        }

        return $memberVO;
    }

    // ✅ MemberVO 타입힌트 제거(네임스페이스 불일치/존재 문제 방지)
    protected function forceBusinessPending(int $memNo, $memberVO)
    {
        $arrUpdate = [
            'appFl = ?',
        ];
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', 'n');
        $this->db->bind_param_push($arrBind, 'i', (int)$memNo);

        $this->db->set_update_db(DB_MEMBER, $arrUpdate, 'memNo = ?', $arrBind, false);
    }
}
