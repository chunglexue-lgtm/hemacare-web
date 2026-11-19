<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\PatientProfile as BasePatientProfile;

/**
 * 患者档案（业务类）
 * Gii 生成基类：app\models\base\PatientProfile（生成区，勿手改）
 *
 * @property UserAccount $user
 */
class PatientProfile extends BasePatientProfile
{
    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        return ['profile_id'];
    }

    /** 所属用户 */
    public function getUser()
    {
        return $this->hasOne(UserAccount::class, ['user_id' => 'user_id']);
    }

    /** JSON 列防御性解码：读取后统一为数组 */
    public function getListAttr(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
