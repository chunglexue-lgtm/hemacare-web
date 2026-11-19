<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "{{%user_account}}".
 *
 * @property int $user_id 用户ID
 * @property string $nickname 昵称
 * @property string $avatar 头像
 * @property int $gender 性别：0未知 1男 2女
 * @property string $phone 常用手机号
 * @property string $api_token 登录态token（64位随机串）
 * @property int $token_expired_at token 过期时间戳
 * @property string $session_key 微信会话密钥（解密手机号用，注意保密）
 * @property int $last_login_at 最近登录时间
 * @property int $login_count 登录次数
 * @property int $status 状态：1正常 0禁用
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class UserAccount extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['session_key'], 'default', 'value' => ''],
            [['updated_at'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['gender', 'token_expired_at', 'last_login_at', 'login_count', 'status', 'created_at', 'updated_at'], 'integer'],
            [['nickname', 'api_token', 'session_key'], 'string', 'max' => 64],
            [['avatar'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'nickname' => 'Nickname',
            'avatar' => 'Avatar',
            'gender' => 'Gender',
            'phone' => 'Phone',
            'api_token' => 'Api Token',
            'token_expired_at' => 'Token Expired At',
            'session_key' => 'Session Key',
            'last_login_at' => 'Last Login At',
            'login_count' => 'Login Count',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
