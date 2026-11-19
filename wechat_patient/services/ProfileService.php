<?php

declare(strict_types=1);

namespace app\wechat_patient\services;

use app\models\PatientProfile;
use yii\db\Exception;

/**
 * 患者档案：保存时服务端重算 fit_status / eln_risk，不信任前端结果
 */
class ProfileService
{
    public function getByUserId(int $userId): ?PatientProfile
    {
        return PatientProfile::findOne(['user_id' => $userId]);
    }

    /**
     * 保存档案（字段兼容小程序端命名：dx/mut/prog）
     *
     * @param array $data age, dx|dx_status, ecog, hctci, mut|mutations, prog|prognosis_expect
     */
    public function save(int $userId, array $data): PatientProfile
    {
        $profile = $this->getByUserId($userId);
        if ($profile === null) {
            $profile = new PatientProfile();
            $profile->user_id = $userId;
        }

        $profile->age = (int)($data['age'] ?? $profile->age ?? 0);
        $profile->dx_status = (string)($data['dx'] ?? $data['dx_status'] ?? $profile->dx_status ?? '');
        $profile->ecog = (int)($data['ecog'] ?? $profile->ecog ?? 0);

        $hctci = $data['hctci'] ?? $profile->hctci ?? [];
        $mut = $data['mut'] ?? $data['mutations'] ?? $profile->mutations ?? [];
        $profile->hctci = is_array($hctci) ? array_values($hctci) : [];
        $profile->mutations = is_array($mut) ? array_values($mut) : [];

        $profile->prognosis_expect = (int)($data['prog'] ?? $data['prognosis_expect'] ?? $profile->prognosis_expect ?? 0);

        // 服务端重算，不信任前端
        $profile->fit_status = $this->calcFit($profile->age, $profile->ecog, $profile->hctci);
        $profile->eln_risk = $this->calcEln($profile->mutations);

        if (!$profile->save()) {
            throw new Exception('档案保存失败：' . json_encode($profile->getFirstErrors(), JSON_UNESCAPED_UNICODE));
        }
        return $profile;
    }

    /** ECOG≥3 或 HCT-CI≥3 项 或 年龄≥75 → unfit */
    private function calcFit(int $age, int $ecog, mixed $hctci): string
    {
        $n = is_array($hctci) ? count($hctci) : 0;
        return ($ecog >= 3 || $n >= 3 || $age >= 75) ? 'unfit' : 'fit';
    }

    /** ELN 2022 简化分层 */
    private function calcEln(mixed $mut): string
    {
        $m = is_array($mut) ? $mut : [];
        if (in_array('TP53', $m, true)) {
            return '高危';
        }
        if (in_array('NPM1', $m, true) && in_array('FLT3-ITD', $m, true)) {
            return '中危';
        }
        if (in_array('NPM1', $m, true)) {
            return '低危';
        }
        return '中危';
    }
}
