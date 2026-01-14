-- 사업자 가입 승인/관리: 필수 DB 작업 통합 스크립트
-- 실행 전: 반드시 대상 DB 선택 후 실행하세요.

-- 1) 커스텀 테이블 생성
CREATE TABLE IF NOT EXISTS `qks_member_biz_profile` (
    `sno` INT NOT NULL AUTO_INCREMENT,
    `memNo` INT NOT NULL,
    `companyName` VARCHAR(255) DEFAULT NULL,
    `entryKeyYn` CHAR(1) NOT NULL DEFAULT 'n',
    `entryGuide` TEXT,
    `loadingPlaceText` TEXT,
    `specialNote` TEXT,
    `orderManagerName` VARCHAR(50) DEFAULT NULL,
    `orderManagerPhone` VARCHAR(30) DEFAULT NULL,
    `payManagerName` VARCHAR(50) DEFAULT NULL,
    `payManagerPhone` VARCHAR(30) DEFAULT NULL,
    `approvalStatus` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `approvedAt` DATETIME DEFAULT NULL,
    `approvedBy` VARCHAR(50) DEFAULT NULL,
    `rejectedAt` DATETIME DEFAULT NULL,
    `rejectedBy` VARCHAR(50) DEFAULT NULL,
    `rejectReason` TEXT,
    `adminMemo` TEXT,
    `createdAt` DATETIME NOT NULL,
    `updatedAt` DATETIME NOT NULL,
    PRIMARY KEY (`sno`),
    UNIQUE KEY `uniq_memNo` (`memNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `qks_member_biz_photos` (
    `sno` INT NOT NULL AUTO_INCREMENT,
    `memNo` INT NOT NULL,
    `sortNo` INT NOT NULL DEFAULT 0,
    `storagePath` VARCHAR(255) NOT NULL,
    `savedName` VARCHAR(255) DEFAULT NULL,
    `originName` VARCHAR(255) DEFAULT NULL,
    `mimeType` VARCHAR(100) DEFAULT NULL,
    `fileSize` INT DEFAULT 0,
    `createdAt` DATETIME NOT NULL,
    PRIMARY KEY (`sno`),
    KEY `idx_memNo` (`memNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) 기존 테이블 문자셋 정리(이미 utf8mb4면 영향 없음)
ALTER TABLE `qks_member_biz_profile` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `qks_member_biz_photos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3) regDt/modDt 컬럼(필요 시) 추가
SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'qks_member_biz_profile'
      AND column_name = 'regDt'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `qks_member_biz_profile` ADD COLUMN `regDt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'qks_member_biz_profile'
      AND column_name = 'modDt'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `qks_member_biz_profile` ADD COLUMN `modDt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) es_adminMenu regDt/modDt 기본값(메뉴 등록 시 경고 방지)
SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'es_adminMenu'
      AND column_name = 'regDt'
);
SET @sql := IF(@col_exists = 1,
    'ALTER TABLE `es_adminMenu` MODIFY `regDt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE()
      AND table_name = 'es_adminMenu'
      AND column_name = 'modDt'
);
SET @sql := IF(@col_exists = 1,
    'ALTER TABLE `es_adminMenu` MODIFY `modDt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5) 관리자 메뉴 등록 (회원 > 회원관리 하위)
SET @prefix := _utf8mb4 'qks' COLLATE utf8mb4_unicode_ci;
SET @topMenuCode := _utf8mb4 'member' COLLATE utf8mb4_unicode_ci;
SET @midMenuCode := _utf8mb4 'member' COLLATE utf8mb4_unicode_ci;
SET @menuCode := _utf8mb4 'biz_approval' COLLATE utf8mb4_unicode_ci;
SET @menuName := _utf8mb4 '사업자 가입 승인/관리' COLLATE utf8mb4_unicode_ci;
SET @menuUrl := _utf8mb4 '/member/member_biz_approval_list.php' COLLATE utf8mb4_unicode_ci;

SELECT adminMenuNo
INTO @topMenuNo
FROM es_adminMenu
WHERE adminMenuDepth = 1
  AND adminMenuCode COLLATE utf8mb4_unicode_ci = @topMenuCode
  AND adminMenuType = 'd'
ORDER BY adminMenuNo
LIMIT 1;

SELECT adminMenuNo
INTO @midMenuNo
FROM es_adminMenu
WHERE adminMenuDepth = 2
  AND adminMenuCode COLLATE utf8mb4_unicode_ci = @midMenuCode
  AND adminMenuParentNo = @topMenuNo
  AND adminMenuType = 'd'
ORDER BY adminMenuNo
LIMIT 1;

SELECT adminMenuNo
INTO @menuNo
FROM es_adminMenu
WHERE adminMenuDepth = 3
  AND adminMenuType = 'd'
  AND adminMenuCode COLLATE utf8mb4_unicode_ci = @menuCode
  AND adminMenuParentNo = @midMenuNo
ORDER BY adminMenuNo
LIMIT 1;

SELECT LPAD(
  IFNULL(MAX(CAST(SUBSTRING(adminMenuNo, LENGTH(@prefix) + 1) AS UNSIGNED)), 0) + 1,
  5,
  '0'
)
INTO @nextNo
FROM es_adminMenu
WHERE adminMenuNo COLLATE utf8mb4_unicode_ci LIKE CONCAT(@prefix, '%');
SET @newMenuNo := CONCAT(@prefix, @nextNo);

SELECT IFNULL(MAX(adminMenuSort), 0)
INTO @maxSort
FROM es_adminMenu
WHERE adminMenuParentNo = @midMenuNo;
SET @newSort := (FLOOR(@maxSort / 100) * 100) + 100;

INSERT INTO es_adminMenu (
  adminMenuNo,
  adminMenuType,
  adminMenuProductCode,
  adminMenuPlusCode,
  adminMenuCode,
  adminMenuDepth,
  adminMenuParentNo,
  adminMenuSort,
  adminMenuName,
  adminMenuUrl,
  adminMenuDisplayType,
  adminMenuSettingType,
  adminMenuEcKind,
  adminMenuHideVersion,
  adminMenuDisplayEnv
)
SELECT
  @newMenuNo,
  'd',
  'godomall',
  NULL,
  @menuCode,
  3,
  @midMenuNo,
  @newSort,
  @menuName,
  @menuUrl,
  'y',
  'd',
  'a',
  '',
  'all'
FROM DUAL
WHERE @topMenuNo IS NOT NULL
  AND @midMenuNo IS NOT NULL
  AND @menuNo IS NULL
  AND NOT EXISTS (
  SELECT 1
  FROM es_adminMenu
  WHERE adminMenuDepth = 3
    AND adminMenuType = 'd'
    AND adminMenuCode COLLATE utf8mb4_unicode_ci = @menuCode
    AND adminMenuParentNo = @midMenuNo
);
