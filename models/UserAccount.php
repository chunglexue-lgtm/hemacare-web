<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\UserAccount as BaseUserAccount;

/**
 * 用户账号（业务类）
 * Gii 生成基类：app\models\base\UserAccount（生成区，勿手改）
 *
 * @property UserAuth[] $userAuths
 * @property PatientProfile $patientProfile
 */
class UserAccount extends BaseUserAccount
{
    public const STATUS_DISABLED = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        return ['user_id'];
    }

    /**
     * 自动填充 created_at / updated_at（Unix 秒级时间戳）
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            \yii\behaviors\TimestampBehavior::class => [
                'class' => \yii\behaviors\TimestampBehavior::class,
            ],
        ]);
    }

    /** token 是否有效（存在 + 时效 + 账号状态） */
    public function validateToken(string $token): bool
    {
        return $this->api_token !== ''
            && hash_equals($this->api_token, $token)
            && (int)$this->token_expired_at > time()
            && (int)$this->status === self::STATUS_ACTIVE;
    }

    /** 登录凭证 */
    public function getUserAuths()
    {
        return $this->hasMany(UserAuth::class, ['user_id' => 'user_id']);
    }

    /** 患者档案（1:1） */
    public function getPatientProfile()
    {
        return $this->hasOne(PatientProfile::class, ['user_id' => 'user_id']);
    }
}
