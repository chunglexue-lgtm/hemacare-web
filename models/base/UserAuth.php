<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "{{%user_auth}}".
 *
 * @property int $auth_id 凭证ID
 * @property int $user_id 用户ID → user_account.user_id
 * @property string $identity_type 登录方式：wechat/phone/email/username
 * @property string $identifier 标识：openid/手机号/邮箱/用户名
 * @property string $credential 密码哈希；微信登录为空
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class UserAuth extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_auth}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['credential'], 'default', 'value' => ''],
            [['updated_at'], 'default', 'value' => 0],
            [['user_id', 'identity_type', 'identifier'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['identity_type'], 'string', 'max' => 20],
            [['identifier'], 'string', 'max' => 128],
            [['credential'], 'string', 'max' => 255],
            [['identity_type', 'identifier'], 'unique', 'targetAttribute' => ['identity_type', 'identifier']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'auth_id' => 'Auth ID',
            'user_id' => 'User ID',
            'identity_type' => 'Identity Type',
            'identifier' => 'Identifier',
            'credential' => 'Credential',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
