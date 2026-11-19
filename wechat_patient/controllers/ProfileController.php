<?php

declare(strict_types=1);

namespace app\wechat_patient\controllers;

use app\models\PatientProfile;
use app\wechat_patient\helpers\ApiResponse;
use app\wechat_patient\services\ProfileService;
use Yii;

/**
 * 患者档案：GET 读取 / POST 保存（需 X-Api-Token）
 */
class ProfileController extends ApiController
{
    /** GET /wechat-patient/profile */
    public function actionIndex()
    {
        $profile = (new ProfileService())->getByUserId((int)$this->currentUser->user_id);
        return ApiResponse::ok(['profile' => $profile === null ? null : $this->formatProfile($profile)]);
    }

    /** POST /wechat-patient/profile */
    public function actionSave()
    {
        try {
            $profile = (new ProfileService())->save(
                (int)$this->currentUser->user_id,
                Yii::$app->request->post()
            );
            return ApiResponse::ok(['profile' => $this->formatProfile($profile)], '保存成功');
        } catch (\Throwable $e) {
            Yii::error('档案保存失败：' . $e->getMessage(), __METHOD__);
            return ApiResponse::error(500, $e->getMessage());
        }
    }

    /** 输出字段与小程序端本地存储键一致（age/dx/ecog/hctci/mut/fit/eln/prog） */
    private function formatProfile(PatientProfile $profile): array
    {
        return [
            'age' => (int)$profile->age,
            'dx' => $profile->dx_status,
            'ecog' => (int)$profile->ecog,
            'hctci' => $profile->getListAttr($profile->hctci),
            'mut' => $profile->getListAttr($profile->mutations),
            'fit' => $profile->fit_status,
            'eln' => $profile->eln_risk,
            'prog' => (int)$profile->prognosis_expect,
            'updated_at' => (int)$profile->updated_at,
        ];
    }
}
