<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "{{%patient_profile}}".
 *
 * @property int $profile_id 档案ID
 * @property int $user_id 用户ID → user_account.user_id
 * @property int $age 年龄
 * @property string $dx_status 诊断状态：初诊/复发
 * @property int $ecog ECOG 评分 0-4
 * @property string|null $hctci HCT-CI 合并症数组，如 ["糖尿病","房颤"]
 * @property string|null $mutations 基因变异数组，如 ["NPM1","FLT3-ITD"]
 * @property string $fit_status 体能初判：fit/unfit
 * @property string $eln_risk ELN 2022 分层：低危/中危/高危
 * @property int $prognosis_expect 「治好」预期 %
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class PatientProfile extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%patient_profile}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hctci', 'mutations'], 'default', 'value' => null],
            [['updated_at'], 'default', 'value' => 0],
            [['eln_risk'], 'default', 'value' => ''],
            [['user_id'], 'required'],
            [['user_id', 'age', 'ecog', 'prognosis_expect', 'created_at', 'updated_at'], 'integer'],
            [['hctci', 'mutations'], 'safe'],
            [['dx_status', 'fit_status', 'eln_risk'], 'string', 'max' => 16],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'profile_id' => 'Profile ID',
            'user_id' => 'User ID',
            'age' => 'Age',
            'dx_status' => 'Dx Status',
            'ecog' => 'Ecog',
            'hctci' => 'Hctci',
            'mutations' => 'Mutations',
            'fit_status' => 'Fit Status',
            'eln_risk' => 'Eln Risk',
            'prognosis_expect' => 'Prognosis Expect',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
