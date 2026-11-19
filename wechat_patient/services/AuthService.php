<?php

declare(strict_types=1);

namespace app\wechat_patient\services;

use app\models\PatientProfile;
use app\models\UserAccount;
use app\models\UserAuth;
use Yii;

/**
 * 登录/注册/令牌（登录即注册：按 openid 查得到直接用，查不到自动建档）
 */
class AuthService
{
    /** token 有效期：30 天 */
    public const TOKEN_TTL = 2592000;

    /**
     * 微信 openid 登录
     *
     * @return array{token: string, isNewUser: bool, hasProfile: bool, user: array}
     */
    public function loginByOpenid(string $openid, string $sessionKey = ''): array
    {
        $auth = UserAuth::findByIdentifier(UserAuth::TYPE_WECHAT, $openid);
        $isNew = false;

        if ($auth === null) {
            [$user, $auth] = $this->createUserWithWechat($openid);
            $isNew = true;
        } else {
            $user = UserAccount::findOne($auth->user_id);
            if ($user === null || (int)$user->status !== UserAccount::STATUS_ACTIVE) {
                throw new \yii\db\Exception('用户不存在或已被禁用');
            }
        }

        // 签发 token + 更新登录信息
        $user->api_token = $this->generateToken();
        $user->token_expired_at = time() + self::TOKEN_TTL;
        $user->session_key = $sessionKey;
        $user->last_login_at = time();
        $user->login_count = (int)$user->login_count + 1;
        if (!$user->save()) {
            throw new \yii\db\Exception('登录信息保存失败：' . json_encode($user->getFirstErrors(), JSON_UNESCAPED_UNICODE));
        }

        return [
            'token' => $user->api_token,
            'isNewUser' => $isNew,
            'hasProfile' => PatientProfile::find()->where(['user_id' => $user->user_id])->exists(),
            'user' => [
                'user_id' => (int)$user->user_id,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'phone' => $user->phone,
            ],
        ];
    }

    /** 事务：创建 user_account + user_auth(wechat) 两表记录 */
    protected function createUserWithWechat(string $openid): array
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            $user = new UserAccount();
            $user->nickname = '';
            $user->avatar = '';
            $user->gender = 0;
            $user->phone = '';
            $user->status = UserAccount::STATUS_ACTIVE;
            if (!$user->save()) {
                throw new \yii\db\Exception('创建用户失败：' . json_encode($user->getFirstErrors(), JSON_UNESCAPED_UNICODE));
            }

            $auth = new UserAuth();
            $auth->user_id = $user->user_id;
            $auth->identity_type = UserAuth::TYPE_WECHAT;
            $auth->identifier = $openid;
            $auth->credential = '';
            if (!$auth->save()) {
                throw new \yii\db\Exception('创建登录凭证失败：' . json_encode($auth->getFirstErrors(), JSON_UNESCAPED_UNICODE));
            }

            $tx->commit();
            return [$user, $auth];
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    /** 按 token 取有效用户（ApiController 鉴权用） */
    public function getUserByToken(string $token): ?UserAccount
    {
        if ($token === '') {
            return null;
        }
        $user = UserAccount::findOne(['api_token' => $token]);
        if ($user === null || !$user->validateToken($token)) {
            return null;
        }
        return $user;
    }

    /** 64 位随机 token */
    protected function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
