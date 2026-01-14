-- 사업자 가입 승인/관리 메뉴 등록 (회원 > 회원관리 하위)
-- 실행 전: es_adminMenu에 1차(member) / 2차(member) 메뉴가 존재해야 함

-- NOTE: collation 충돌 방지 위해 문자열 변수에 명시적 COLLATE 지정
SET @prefix := _utf8mb4 'qks' COLLATE utf8mb4_unicode_ci;
SET @topMenuCode := _utf8mb4 'member' COLLATE utf8mb4_unicode_ci;
SET @midMenuCode := _utf8mb4 'member' COLLATE utf8mb4_unicode_ci;
SET @menuCode := _utf8mb4 'biz_approval' COLLATE utf8mb4_unicode_ci;
SET @menuName := _utf8mb4 '사업자 가입 승인/관리' COLLATE utf8mb4_unicode_ci;
SET @menuUrl := _utf8mb4 '/member/member_biz_approval_list.php' COLLATE utf8mb4_unicode_ci;

-- 1차(회원) 메뉴 번호
SELECT adminMenuNo
INTO @topMenuNo
FROM es_adminMenu
WHERE adminMenuDepth = 1
  AND adminMenuCode COLLATE utf8mb4_unicode_ci = @topMenuCode
  AND adminMenuType = 'd'
ORDER BY adminMenuNo
LIMIT 1;

-- 2차(회원관리) 메뉴 번호
SELECT adminMenuNo
INTO @midMenuNo
FROM es_adminMenu
WHERE adminMenuDepth = 2
  AND adminMenuCode COLLATE utf8mb4_unicode_ci = @midMenuCode
  AND adminMenuParentNo = @topMenuNo
  AND adminMenuType = 'd'
ORDER BY adminMenuNo
LIMIT 1;

-- 3차 메뉴 번호 (페이지)
SELECT adminMenuNo
INTO @menuNo
FROM es_adminMenu
WHERE adminMenuDepth = 3
  AND adminMenuType = 'd'
  AND adminMenuCode COLLATE utf8mb4_unicode_ci = @menuCode
  AND adminMenuParentNo = @midMenuNo
ORDER BY adminMenuNo
LIMIT 1;

-- 업체 prefix 기반 신규 adminMenuNo 생성 (prefix + 5자리)
SELECT LPAD(
  IFNULL(MAX(CAST(SUBSTRING(adminMenuNo, LENGTH(@prefix) + 1) AS UNSIGNED)), 0) + 1,
  5,
  '0'
)
INTO @nextNo
FROM es_adminMenu
WHERE adminMenuNo COLLATE utf8mb4_unicode_ci LIKE CONCAT(@prefix, '%');
SET @newMenuNo := CONCAT(@prefix, @nextNo);

-- 3차 메뉴 정렬값 계산 (100 단위)
SELECT IFNULL(MAX(adminMenuSort), 0)
INTO @maxSort
FROM es_adminMenu
WHERE adminMenuParentNo = @midMenuNo;
SET @newSort := (FLOOR(@maxSort / 100) * 100) + 100;

-- 중복 방지 후 등록
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
