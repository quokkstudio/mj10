-- 사업자 프로필 (1:1)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 적재장소 사진 (1:N)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
