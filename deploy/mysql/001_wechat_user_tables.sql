-- ============================================================
-- CO-GOAL 小程序 · 用户与患者档案表
-- 目标库：按实际库名导入（建议库默认字符集 utf8mb4 / utf8mb4_general_ci）
-- 要求：MySQL >= 5.7.8（使用了 JSON 字段类型）
-- 特点：可重复执行（先删后建，数据不保留，勿在生产库使用）
-- 导入：mysql -u<user> -p <数据库名> < 001_wechat_user_tables.sql
-- ============================================================

SET NAMES utf8mb4;
-- 忽略历史外键约束的干扰（本脚本不建外键，关系由应用层维护）
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. 用户账号表
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `user_account`;
CREATE TABLE `user_account` (
  `user_id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `nickname`      VARCHAR(64)     NOT NULL DEFAULT ''                COMMENT '昵称',
  `avatar`        VARCHAR(255)    NOT NULL DEFAULT ''                COMMENT '头像',
  `gender`        TINYINT         NOT NULL DEFAULT 0                 COMMENT '性别：0未知 1男 2女',
  `phone`         VARCHAR(20)     NOT NULL DEFAULT ''                COMMENT '常用手机号',
  `api_token`        VARCHAR(64)  NOT NULL DEFAULT ''                COMMENT '登录态token（64位随机串）',
  `token_expired_at` INT UNSIGNED  NOT NULL DEFAULT 0                 COMMENT 'token 过期时间戳',
  `session_key`      VARCHAR(64)  NOT NULL DEFAULT ''                COMMENT '微信会话密钥（解密手机号用，注意保密）',
  `last_login_at` INT UNSIGNED    NOT NULL DEFAULT 0                 COMMENT '最近登录时间',
  `login_count`   INT UNSIGNED    NOT NULL DEFAULT 0                 COMMENT '登录次数',
  `status`        TINYINT         NOT NULL DEFAULT 1                 COMMENT '状态：1正常 0禁用',
  `created_at`    INT UNSIGNED    NOT NULL DEFAULT 0                 COMMENT '创建时间',
  `updated_at`    INT UNSIGNED    NOT NULL DEFAULT 0                 COMMENT '更新时间',
  PRIMARY KEY (`user_id`),
  KEY `idx_phone` (`phone`),
  KEY `idx_token` (`api_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户账号表';

-- ------------------------------------------------------------
-- 2. 登录凭证表（微信 openid / 手机号 / 邮箱 / 用户名）
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `user_auth`;
CREATE TABLE `user_auth` (
  `auth_id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '凭证ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '用户ID → user_account.user_id',
  `identity_type` VARCHAR(20)     NOT NULL                COMMENT '登录方式：wechat/phone/email/username',
  `identifier`    VARCHAR(128)    NOT NULL                COMMENT '标识：openid/手机号/邮箱/用户名',
  `credential`    VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '密码哈希；微信登录为空',
  `created_at`    INT UNSIGNED    NOT NULL DEFAULT 0      COMMENT '创建时间',
  `updated_at`    INT UNSIGNED    NOT NULL DEFAULT 0      COMMENT '更新时间',
  PRIMARY KEY (`auth_id`),
  UNIQUE KEY `uk_type_identifier` (`identity_type`, `identifier`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='登录凭证表';

-- ------------------------------------------------------------
-- 3. 患者档案表（与 user_account 1:1）
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `patient_profile`;
CREATE TABLE `patient_profile` (
  `profile_id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '档案ID',
  `user_id`          BIGINT UNSIGNED NOT NULL                COMMENT '用户ID → user_account.user_id',
  `age`              TINYINT UNSIGNED NOT NULL DEFAULT 0     COMMENT '年龄',
  `dx_status`        VARCHAR(16)  NOT NULL DEFAULT ''        COMMENT '诊断状态：初诊/复发',
  `ecog`             TINYINT      NOT NULL DEFAULT 0         COMMENT 'ECOG 评分 0-4',
  `hctci`            JSON              DEFAULT NULL          COMMENT 'HCT-CI 合并症数组，如 ["糖尿病","房颤"]',
  `mutations`        JSON              DEFAULT NULL          COMMENT '基因变异数组，如 ["NPM1","FLT3-ITD"]',
  `fit_status`       VARCHAR(16)  NOT NULL DEFAULT ''        COMMENT '体能初判：fit/unfit',
  `eln_risk`         VARCHAR(16)  NOT NULL DEFAULT ''        COMMENT 'ELN 2022 分层：低危/中危/高危',
  `prognosis_expect` TINYINT UNSIGNED NOT NULL DEFAULT 0     COMMENT '「治好」预期 %',
  `created_at`       INT UNSIGNED NOT NULL DEFAULT 0         COMMENT '创建时间',
  `updated_at`       INT UNSIGNED NOT NULL DEFAULT 0         COMMENT '更新时间',
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `uk_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='患者档案表';

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 注：不使用数据库外键约束（应用层保证一致性），关联关系由人工维护：
--   user_auth.user_id        → user_account.user_id
--   patient_profile.user_id  → user_account.user_id
-- ------------------------------------------------------------
