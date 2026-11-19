<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\UserAuth as BaseUserAuth;

/**
 * 登录凭证（业务类）
 * Gii 生成基类：app\models\base\UserAuth（生成区，勿手改）
 *
 * @property UserAccount $user
 */
class UserAuth extends BaseUserAuth
{
    public const TYPE_WECHAT = 'wechat';
    public const TYPE_PHONE = 'phone';
    public const TYPE_EMAIL = 'email';
    public const TYPE_USERNAME = 'username';

    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        return ['auth_id'];
    }

    /** 按登录方式 + 标识查凭证 */
    public static function findByIdentifier(string $type, string $identifier): ?self
    {
        return static::findOne(['identity_type' => $type, 'identifier' => $identifier]);
    }

    /** 所属用户 */
    public function getUser()
    {
        return $this->hasOne(UserAccount::class, ['user_id' => 'user_id']);
    }
}
