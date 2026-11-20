-- ============================================================
-- CO-GOAL 小程序 · 用户与患者档案表（含完整数据字典注释）
-- 目标库：按实际库名导入（建议库默认字符集 utf8mb4 / utf8mb4_general_ci）
-- 要求：MySQL >= 5.7.8（使用了 JSON 字段类型）
-- 特点：可重复执行（先 DROP 后 CREATE，数据不保留，勿在生产库使用）
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
  `user_id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
      COMMENT '用户ID(PK，自增)',
  `nickname`         VARCHAR(64)  NOT NULL DEFAULT ''
      COMMENT '微信昵称[个人资料区]：登录时为空，用户授权资料/手填后更新',
  `avatar`           VARCHAR(255) NOT NULL DEFAULT ''
      COMMENT '头像URL[个人资料区]：chooseAvatar选择后需上传存储，存正式URL',
  `gender`           TINYINT      NOT NULL DEFAULT 0
      COMMENT '性别[个人资料区]手选：0未知/1男/2女（微信已不再提供性别授权接口）',
  `phone`            VARCHAR(20)  NOT NULL DEFAULT ''
      COMMENT '常用手机号[个人资料区]：getPhoneNumber按钮授权→code→服务端换号写入；同步user_auth(phone)记录',
  `api_token`        VARCHAR(64)  NOT NULL DEFAULT ''
      COMMENT '登录态token(64位hex)：每次登录重新签发，旧token即刻失效(单点登录)；小程序请求头X-Api-Token携带',
  `token_expired_at` INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT 'token过期时间(Unix秒)：签发时=当前+30天；校验需大于当前时间',
  `session_key`      VARCHAR(64)  NOT NULL DEFAULT ''
      COMMENT '微信会话密钥：code2session返回，预留(解密手机号等)，属敏感信息注意保密',
  `last_login_at`    INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '最近登录时间(Unix秒)：每次登录自动更新',
  `login_count`      INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '累计登录次数：每次登录+1',
  `status`           TINYINT      NOT NULL DEFAULT 1
      COMMENT '账号状态：1正常/0禁用；禁用后token校验失败(即刻踢下线)',
  `created_at`       INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '创建时间(Unix秒)：TimestampBehavior自动填充',
  `updated_at`       INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '更新时间(Unix秒)：TimestampBehavior自动刷新',
  PRIMARY KEY (`user_id`),
  KEY `idx_phone` (`phone`),
  KEY `idx_token` (`api_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='用户账号表：登录主体(患者/医生共用)，1个账号对应user_auth多条登录凭证';

-- ------------------------------------------------------------
-- 2. 登录凭证表（微信 openid / 手机号 / 邮箱 / 用户名）
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `user_auth`;
CREATE TABLE `user_auth` (
  `auth_id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
      COMMENT '凭证ID(PK，自增)',
  `user_id`       BIGINT UNSIGNED NOT NULL
      COMMENT '所属账号→user_account.user_id；一个账号可绑定多种登录方式(1:n)',
  `identity_type` VARCHAR(20)  NOT NULL
      COMMENT '登录方式：wechat(微信openid)/phone(手机号)/email(邮箱)/username(用户名)；目前仅wechat启用，其余预留',
  `identifier`    VARCHAR(128) NOT NULL
      COMMENT '登录标识：wechat=openid(同一AppID下唯一)；phone=11位手机号；email=邮箱地址；username=登录名',
  `credential`    VARCHAR(255) NOT NULL DEFAULT ''
      COMMENT '密码哈希(bcrypt)：仅email/username方式使用；wechat/phone方式恒为空',
  `created_at`    INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '创建时间(Unix秒)：TimestampBehavior自动填充',
  `updated_at`    INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '更新时间(Unix秒)：TimestampBehavior自动刷新',
  PRIMARY KEY (`auth_id`),
  UNIQUE KEY `uk_type_identifier` (`identity_type`, `identifier`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='登录凭证表：账号与凭证分离，支持一个账号多种登录方式';

-- ------------------------------------------------------------
-- 3. 患者档案表（与 user_account 1:1）
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `patient_profile`;
CREATE TABLE `patient_profile` (
  `profile_id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT
      COMMENT '档案ID(PK，自增)',
  `user_id`          BIGINT UNSIGNED NOT NULL
      COMMENT '所属账号→user_account.user_id，唯一约束(1:1，一个账号一份档案)',
  `age`              TINYINT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '年龄[小程序-基础信息Tab-个人与诊断档案]数字输入框，单位:岁；≥75参与unfit判定',
  `dx_status`        VARCHAR(16)  NOT NULL DEFAULT ''
      COMMENT '诊断状态[基础信息-个人与诊断档案]单选：初诊/复发',
  `ecog`             TINYINT      NOT NULL DEFAULT 0
      COMMENT 'ECOG体能评分[基础信息-体能自评]单选：0正常/1轻/2卧床<50%/3卧床>50%/4全卧；≥3判unfit',
  `hctci`            JSON              DEFAULT NULL
      COMMENT 'HCT-CI合并症[基础信息-体能自评]多选chips：高血压/糖尿病/肾功能不全/房颤/心衰/慢阻肺/既往实体瘤(JSON数组)；选中≥3项判unfit',
  `mutations`        JSON              DEFAULT NULL
      COMMENT '基因变异[基础信息-分子与风险档案]多选chips：NPM1/FLT3-ITD/DNMT3A/IDH1/IDH2/TP53/RUNX1/ASXL1(JSON数组)；服务端按ELN2022重算eln_risk',
  `fit_status`       VARCHAR(16)  NOT NULL DEFAULT ''
      COMMENT '体能初判徽标：fit/unfit；服务端重算(ECOG≥3或HCT-CI≥3或年龄≥75→unfit)，不信任前端传入值',
  `eln_risk`         VARCHAR(16)  NOT NULL DEFAULT ''
      COMMENT 'ELN2022风险分层徽标：低危/中危/高危；服务端重算规则：TP53→高危；NPM1+FLT3-ITD→中危(2022版已取消比值区分)；NPM1单阳→低危；其他→中危',
  `prognosis_expect` TINYINT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '「治好」预期%[基础信息-预后校准]滑块0-100；与医生科普中位OS≈14.7月对比标注预期一致性',
  `created_at`       INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '创建时间(Unix秒)：TimestampBehavior自动填充',
  `updated_at`       INT UNSIGNED NOT NULL DEFAULT 0
      COMMENT '更新时间(Unix秒)：TimestampBehavior自动刷新',
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `uk_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='患者档案表：小程序基础信息Tab对应数据，服务端保存时重算fit_status/eln_risk';

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 注：不使用数据库外键约束（应用层保证一致性），关联关系由人工维护：
--   user_auth.user_id        → user_account.user_id   (1:n)
--   patient_profile.user_id  → user_account.user_id   (1:1)
-- ------------------------------------------------------------
