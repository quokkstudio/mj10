<?php

namespace Component\Member;

use Component\Validator\Validator;
use Framework\ObjectStorage\Service\ImageUploadService;

if (!class_exists('\\Bundle\\Component\\Member\\BizApprovalService')) {
    class_alias('\\Component\\AbstractComponent', '\\Bundle\\Component\\Member\\BizApprovalService');
}

class BizApprovalService extends \Bundle\Component\Member\BizApprovalService
{
    const TABLE_PROFILE = 'qks_member_biz_profile';
    const TABLE_PHOTO = 'qks_member_biz_photos';
    const PHOTO_UPLOAD_PATH = '/member/biz/loading/%s';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const MAX_PHOTO_COUNT = 10;
    const MAX_PHOTO_SIZE_MB = 10;
    const ALLOW_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /** @var \Framework\Database\DBTool */
    protected $db;

    /** @var ImageUploadService */
    protected $imageUploadService;

    public function __construct()
    {
        $this->db = \App::load('DB');
        $this->imageUploadService = new ImageUploadService();
    }

    public function getProfile(int $memNo): array
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $sql = 'SELECT * FROM ' . self::TABLE_PROFILE . ' WHERE memNo = ?';
        $rows = $this->db->query_fetch($sql, $arrBind);

        return $rows ? $rows[0] : [];
    }

    public function getPhotos(int $memNo): array
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $sql = 'SELECT * FROM ' . self::TABLE_PHOTO . ' WHERE memNo = ? ORDER BY sortNo ASC, sno ASC';
        $rows = $this->db->query_fetch($sql, $arrBind);

        if (!empty($rows)) {
            foreach ($rows as &$row) {
                $row['previewUrl'] = $this->buildPhotoViewUrl(
                    (string)gd_isset($row['storagePath']),
                    (int)gd_isset($row['memNo'], $memNo),
                    (string)gd_isset($row['savedName'])
                );
            }
            unset($row);
        }

        return $rows;
    }

    public function getPhoto(int $sno): array
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $sql = 'SELECT * FROM ' . self::TABLE_PHOTO . ' WHERE sno = ?';
        $rows = $this->db->query_fetch($sql, $arrBind);

        return $rows ? $rows[0] : [];
    }

    public function saveProfileFromJoin(int $memNo, array $params, array $memberData = [])
    {
        $companyName = gd_isset($params['company']);
        if ($companyName === '' && !empty($memberData)) {
            $companyName = gd_isset($memberData['company']);
        }

        $data = [
            'memNo' => $memNo,
            'companyName' => $companyName,
            'entryKeyYn' => $this->normalizeYn($params['entryKeyYn'] ?? ''),
            'entryGuide' => gd_isset($params['entryGuide']),
            'loadingPlaceText' => gd_isset($params['loadingPlaceText']),
            'specialNote' => gd_isset($params['specialNote']),
            'orderManagerName' => gd_isset($params['orderManagerName']),
            'orderManagerPhone' => gd_isset($params['orderManagerPhone']),
            'payManagerName' => gd_isset($params['payManagerName']),
            'payManagerPhone' => gd_isset($params['payManagerPhone']),
            'approvalStatus' => self::STATUS_PENDING,
            'approvedAt' => null,
            'approvedBy' => null,
            'rejectedAt' => null,
            'rejectedBy' => null,
            'rejectReason' => null,
            'adminMemo' => gd_isset($params['adminMemo']),
        ];

        $this->upsertProfile($data);
    }

    public function savePhotosFromJoin(int $memNo, array $files, bool $replace = true): array
    {
        $normalizedFiles = $this->normalizeFiles($files);
        if (empty($normalizedFiles)) {
            return [];
        }

        if (count($normalizedFiles) > self::MAX_PHOTO_COUNT) {
            throw new \Exception(__('적재장소 사진은 최대 ') . self::MAX_PHOTO_COUNT . __('장까지 업로드 가능합니다.'));
        }

        $existingPhotos = [];
        if ($replace) {
            $existingPhotos = $this->getPhotos($memNo);
        }

        $uploadedPaths = [];
        $uploadPath = sprintf(self::PHOTO_UPLOAD_PATH, $memNo);
        $this->imageUploadService->setAllowMimeTypes(['image/jpeg', 'image/png']);
        $sortNo = 1;

        try {
            foreach ($normalizedFiles as $file) {
                $this->validateUploadFile($file);

                $result = $this->imageUploadService->uploadImage(
                    $file,
                    $uploadPath,
                    false,
                    self::MAX_PHOTO_SIZE_MB
                );

                if (empty($result['result'])) {
                    throw new \Exception(__('적재장소 사진 업로드에 실패했습니다.'));
                }

                $uploadedPaths[] = $result['saveFileNm'];

                $this->insertPhotoRow($memNo, $sortNo, $file, $result);
                $sortNo++;
            }
        } catch (\Throwable $e) {
            $this->deleteFiles($uploadedPaths);
            throw $e;
        }

        if ($replace && !empty($existingPhotos)) {
            $this->deletePhotoRows($existingPhotos);
        }

        return $uploadedPaths;
    }

    public function deletePhotosByMemNo(int $memNo)
    {
        $photos = $this->getPhotos($memNo);
        if (!empty($photos)) {
            $this->deletePhotoRows($photos);
        }
    }

    public function deleteFiles(array $paths)
    {
        foreach ($paths as $path) {
            if (!empty($path)) {
                $this->imageUploadService->deleteImage($path);
            }
        }
    }

    protected function deletePhotoRows(array $photos)
    {
        foreach ($photos as $photo) {
            if (!empty($photo['storagePath'])) {
                $this->imageUploadService->deleteImage($photo['storagePath']);
            }
            if (!empty($photo['sno'])) {
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 'i', (int)$photo['sno']);
                $this->db->set_delete_db(self::TABLE_PHOTO, 'sno = ?', $arrBind);
            }
        }
    }

    public function updateApprovalStatus(array $memNos, string $status, string $managerId, string $reason = '')
    {
        $now = date('Y-m-d H:i:s');
        foreach ($memNos as $memNo) {
            $memNo = (int)$memNo;
            if ($memNo <= 0) {
                continue;
            }

            $arrUpdate = [
                'approvalStatus = ?',
                'updatedAt = ?',
            ];
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $status);
            $this->db->bind_param_push($arrBind, 's', $now);

            if ($status === self::STATUS_APPROVED) {
                $arrUpdate[] = 'approvedAt = ?';
                $arrUpdate[] = 'approvedBy = ?';
                $arrUpdate[] = 'rejectedAt = NULL';
                $arrUpdate[] = 'rejectedBy = NULL';
                $arrUpdate[] = 'rejectReason = NULL';
                $this->db->bind_param_push($arrBind, 's', $now);
                $this->db->bind_param_push($arrBind, 's', $managerId);
            } elseif ($status === self::STATUS_REJECTED) {
                $arrUpdate[] = 'rejectedAt = ?';
                $arrUpdate[] = 'rejectedBy = ?';
                $arrUpdate[] = 'rejectReason = ?';
                $arrUpdate[] = 'approvedAt = NULL';
                $arrUpdate[] = 'approvedBy = NULL';
                $this->db->bind_param_push($arrBind, 's', $now);
                $this->db->bind_param_push($arrBind, 's', $managerId);
                $this->db->bind_param_push($arrBind, 's', $reason);
            }

            $this->db->bind_param_push($arrBind, 'i', $memNo);
            try {
                $this->db->set_update_db(self::TABLE_PROFILE, $arrUpdate, 'memNo = ?', $arrBind, false);
            } catch (\Throwable $e) {
                $this->logDbError('updateApprovalStatus.update', ['memNo' => $memNo, 'status' => $status], $e);
                throw $e;
            }
        }
    }

    protected function upsertProfile(array $data)
    {
        $memNo = (int)$data['memNo'];
        $exists = $this->getProfile($memNo);
        $now = date('Y-m-d H:i:s');

        if (!empty($exists)) {
            $data['updatedAt'] = $now;
            $arrUpdate = [];
            $arrBind = [];
            foreach ($data as $key => $value) {
                if ($key === 'memNo') {
                    continue;
                }
                if ($value === null) {
                    $arrUpdate[] = $key . ' = NULL';
                    continue;
                }
                $arrUpdate[] = $key . ' = ?';
                $this->db->bind_param_push($arrBind, 's', $value);
            }
            $this->db->bind_param_push($arrBind, 'i', $memNo);
            try {
                $this->db->set_update_db(self::TABLE_PROFILE, $arrUpdate, 'memNo = ?', $arrBind, false);
            } catch (\Throwable $e) {
                $this->logDbError('upsertProfile.update', ['memNo' => $memNo, 'fields' => array_keys($data)], $e);
                throw $e;
            }
        } else {
            $data['createdAt'] = $now;
            $data['updatedAt'] = $now;
            $arrBind = ['param' => [], 'bind' => []];
            foreach ($data as $key => $value) {
                $arrBind['param'][] = $key;
                $this->db->bind_param_push($arrBind['bind'], $this->getBindType($value), $value);
            }
            try {
                $this->db->set_insert_db(self::TABLE_PROFILE, $arrBind['param'], $arrBind['bind'], 'y');
            } catch (\Throwable $e) {
                $this->logDbError('upsertProfile.insert', ['memNo' => $memNo, 'fields' => $arrBind['param']], $e);
                throw $e;
            }
        }
    }

    protected function insertPhotoRow(int $memNo, int $sortNo, array $file, array $result)
    {
        $saveFileNm = $result['saveFileNm'] ?? '';
        $uploadFileNm = $result['uploadFileNm'] ?? ($file['name'] ?? '');
        $mimeType = $file['type'] ?? '';
        $fileSize = (int)($file['size'] ?? 0);
        $now = date('Y-m-d H:i:s');

        $storagePath = '';
        $filePath = $result['filePath'] ?? '';
        if ($saveFileNm !== '' && preg_match('~^https?://~i', $saveFileNm)) {
            $storagePath = $saveFileNm;
        } elseif ($filePath !== '' && preg_match('~^https?://~i', $filePath)) {
            $storagePath = rtrim($filePath, '/') . '/' . ltrim(basename($saveFileNm), '/');
        } elseif ($filePath !== '') {
            $storagePath = rtrim($filePath, '/') . '/' . ltrim(basename($saveFileNm), '/');
        } else {
            $storagePath = $saveFileNm;
        }

        $arrBind = ['param' => [], 'bind' => []];
        $fields = [
            'memNo' => $memNo,
            'sortNo' => $sortNo,
            'storagePath' => $storagePath,
            'savedName' => basename(parse_url($saveFileNm, PHP_URL_PATH) ?: $saveFileNm),
            'originName' => $uploadFileNm,
            'mimeType' => $mimeType,
            'fileSize' => $fileSize,
            'createdAt' => $now,
        ];

        foreach ($fields as $key => $value) {
            $arrBind['param'][] = $key;
            $this->db->bind_param_push($arrBind['bind'], $this->getBindType($value), $value);
        }

        try {
            $this->db->set_insert_db(self::TABLE_PHOTO, $arrBind['param'], $arrBind['bind'], 'y');
        } catch (\Throwable $e) {
            $this->logDbError('insertPhotoRow.insert', ['memNo' => $memNo, 'fields' => $arrBind['param']], $e);
            throw $e;
        }
    }

    protected function logDbError(string $context, array $data, \Throwable $e)
    {
        \Logger::error('BizApprovalService DB error: ' . $context, [
            'message' => $e->getMessage(),
            'data' => $data,
        ]);

        // 로컬/운영에서 로그 경로가 없을 때를 대비한 파일 로그 (여러 경로에 시도)
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $context . ' | ' . $e->getMessage()
            . ' | ' . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $paths = [
            rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'biz_approval_debug.log',
            __DIR__ . DIRECTORY_SEPARATOR . 'biz_approval_debug.log',
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'biz_approval_debug.log',
        ];
        foreach ($paths as $logFile) {
            try {
                @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
            } catch (\Throwable $ignored) {
                // ignore
            }
        }
    }

    public function updateAdminMemo(int $memNo, string $memo)
    {
        $arrUpdate = [
            'adminMemo = ?',
            'updatedAt = ?',
        ];
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $memo);
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d H:i:s'));
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->set_update_db(self::TABLE_PROFILE, $arrUpdate, 'memNo = ?', $arrBind, false);
    }

    public function getList(array $search, int $page, int $pageNum): array
    {
        $data = $this->getListData($search, $page, $pageNum);
        $total = $this->getTotal($search);

        return [
            'data' => $data,
            'total' => $total,
        ];
    }

    public function getExcelList(array $search): array
    {
        return $this->getListData($search, 1, 0);
    }

    protected function getListData(array $search, int $page, int $pageNum): array
    {
        $arrBind = [];
        $whereSql = $this->buildWhereSql($search, $arrBind);
        $certTable = $this->getCompanyCertificationTable();

        $offsetSql = '';
        if ($pageNum > 0) {
            $offset = max(0, ($page - 1) * $pageNum);
            $offsetSql = ' LIMIT ' . (int)$offset . ', ' . (int)$pageNum;
        }

        $sql = 'SELECT '
            . 'm.memNo, m.memId, m.memNm, m.email, m.company AS memberCompany, m.entryDt, m.appFl, '
            . 'p.companyName, p.orderManagerName, p.orderManagerPhone, p.payManagerName, p.payManagerPhone, '
            . 'p.approvalStatus, p.approvedAt, p.approvedBy, p.rejectedAt, p.rejectedBy, p.rejectReason, p.adminMemo, '
            . '(SELECT COUNT(*) FROM ' . self::TABLE_PHOTO . ' ph WHERE ph.memNo = m.memNo) AS photoCount, '
            . '(SELECT storagePath FROM ' . self::TABLE_PHOTO . ' ph WHERE ph.memNo = m.memNo ORDER BY sortNo ASC, sno ASC LIMIT 1) AS photoFirstPath, '
            . '(SELECT savedName FROM ' . self::TABLE_PHOTO . ' ph WHERE ph.memNo = m.memNo ORDER BY sortNo ASC, sno ASC LIMIT 1) AS photoFirstSavedName, '
            . 'CASE WHEN cert.memNo IS NULL THEN "n" ELSE "y" END AS certExists '
            . 'FROM ' . DB_MEMBER . ' m '
            . 'LEFT JOIN ' . self::TABLE_PROFILE . ' p ON p.memNo = m.memNo '
            . 'LEFT JOIN ' . $certTable . ' cert ON cert.memNo = m.memNo '
            . 'WHERE ' . $whereSql
            . ' ORDER BY m.entryDt DESC' . $offsetSql;

        return $this->db->query_fetch($sql, $arrBind);
    }

    public function getTotal(array $search): int
    {
        $arrBind = [];
        $whereSql = $this->buildWhereSql($search, $arrBind);
        $certTable = $this->getCompanyCertificationTable();
        $sql = 'SELECT COUNT(DISTINCT m.memNo) AS cnt '
            . 'FROM ' . DB_MEMBER . ' m '
            . 'LEFT JOIN ' . self::TABLE_PROFILE . ' p ON p.memNo = m.memNo '
            . 'LEFT JOIN ' . $certTable . ' cert ON cert.memNo = m.memNo '
            . 'WHERE ' . $whereSql;
        $rows = $this->db->query_fetch($sql, $arrBind);

        return (int)($rows[0]['cnt'] ?? 0);
    }

    protected function buildWhereSql(array $search, array &$arrBind): string
    {
        $where = ['m.memberFl = "business"'];

        $entryDt = gd_isset($search['entryDt'], []);
        if (!empty($entryDt[0])) {
            $where[] = 'm.entryDt >= ?';
            $this->db->bind_param_push($arrBind, 's', $entryDt[0] . ' 00:00:00');
        }
        if (!empty($entryDt[1])) {
            $where[] = 'm.entryDt <= ?';
            $this->db->bind_param_push($arrBind, 's', $entryDt[1] . ' 23:59:59');
        }

        $approvalStatus = gd_isset($search['approvalStatus']);
        if ($approvalStatus !== '') {
            if ($approvalStatus === self::STATUS_PENDING) {
                $where[] = '(p.approvalStatus = ? OR p.approvalStatus IS NULL OR p.approvalStatus = "")';
                $this->db->bind_param_push($arrBind, 's', $approvalStatus);
            } else {
                $where[] = 'p.approvalStatus = ?';
                $this->db->bind_param_push($arrBind, 's', $approvalStatus);
            }
        }

        $keyword = trim(gd_isset($search['keyword']));
        $key = gd_isset($search['key']);
        if ($keyword !== '' && $key !== '') {
            $keyword = '%' . $keyword . '%';
            switch ($key) {
                case 'companyName':
                    $where[] = '(p.companyName LIKE ? OR m.company LIKE ?)';
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    break;
                case 'memId':
                    $where[] = 'm.memId LIKE ?';
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    break;
                case 'email':
                    $where[] = 'm.email LIKE ?';
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    break;
                case 'orderManagerName':
                    $where[] = 'p.orderManagerName LIKE ?';
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    break;
                case 'orderManagerPhone':
                    $where[] = 'p.orderManagerPhone LIKE ?';
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    break;
                case 'payManagerName':
                    $where[] = 'p.payManagerName LIKE ?';
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    break;
                case 'payManagerPhone':
                    $where[] = 'p.payManagerPhone LIKE ?';
                    $this->db->bind_param_push($arrBind, 's', $keyword);
                    break;
                default:
                    break;
            }
        }

        $certFl = gd_isset($search['certFl']);
        if ($certFl === 'y') {
            $where[] = 'cert.memNo IS NOT NULL';
        } elseif ($certFl === 'n') {
            $where[] = 'cert.memNo IS NULL';
        }

        $photoFl = gd_isset($search['photoFl']);
        if ($photoFl === 'y') {
            $where[] = 'EXISTS (SELECT 1 FROM ' . self::TABLE_PHOTO . ' ph WHERE ph.memNo = m.memNo)';
        } elseif ($photoFl === 'n') {
            $where[] = 'NOT EXISTS (SELECT 1 FROM ' . self::TABLE_PHOTO . ' ph WHERE ph.memNo = m.memNo)';
        }

        return gd_implode(' AND ', $where);
    }

    protected function normalizeFiles(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }

        $normalized = [];
        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if (empty($files['name'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i] ?? 0,
                ];
            }
        } else {
            if (!empty($files['name']) && $files['error'] !== UPLOAD_ERR_NO_FILE) {
                $normalized[] = $files;
            }
        }

        return $normalized;
    }

    protected function validateUploadFile(array $file)
    {
        if (!isset($file['tmp_name']) || Validator::validateIncludeEval($file['tmp_name']) === false) {
            throw new \Exception(__('업로드 할 수 없는 파일입니다.'));
        }

        if (!empty($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception(__('파일 업로드에 실패했습니다.'));
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext === '' || in_array($ext, self::ALLOW_EXTENSIONS, true) === false) {
            throw new \Exception(__('등록이 불가한 파일 형식입니다. (jpg, jpeg, png)'));
        }

        $sizeMb = ((int)($file['size'] ?? 0)) / 1024 / 1024;
        if ($sizeMb > self::MAX_PHOTO_SIZE_MB) {
            throw new \Exception(__('파일은 최대 ') . self::MAX_PHOTO_SIZE_MB . __('MB 이하로 등록 가능합니다.'));
        }
    }

    protected function normalizeYn(string $value): string
    {
        return ($value === 'y' || $value === '1' || $value === 'on') ? 'y' : 'n';
    }

    protected function getBindType($value): string
    {
        if (is_int($value)) {
            return 'i';
        }

        if (is_float($value)) {
            return 'd';
        }

        return 's';
    }

    protected function getCompanyCertificationTable(): string
    {
        if (class_exists('Origin\\Model\\Member\\CompanyCertification')) {
            $model = new \Origin\Model\Member\CompanyCertification();
            if (method_exists($model, 'getTable')) {
                return $model->getTable();
            }
        }

        return 'es_member_company_certification';
    }

    public function buildPhotoViewUrl(string $storagePath, int $memNo = 0, string $savedName = ''): string
    {
        $path = trim($storagePath);
        if ($path === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }
        if (preg_match('~https?://~i', $path)) {
            $pos = stripos($path, 'http');
            return substr($path, $pos);
        }

        $dataPos = strpos($path, '/data/');
        if ($dataPos !== false) {
            return substr($path, $dataPos);
        }

        if (strpos($path, '/') === 0) {
            if (strpos($path, '/data/') === 0) {
                return $path;
            }
            return '/data' . $path;
        }

        if ($memNo > 0 && $savedName !== '') {
            return '/data' . sprintf(self::PHOTO_UPLOAD_PATH, $memNo) . '/' . ltrim($savedName, '/');
        }

        return '/data/' . ltrim($path, '/');
    }
}
