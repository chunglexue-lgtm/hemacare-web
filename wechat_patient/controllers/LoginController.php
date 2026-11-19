<?php

declare(strict_types=1);

namespace app\wechat_patient\controllers;

use app\wechat_patient\helpers\ApiResponse;
use app\wechat_patient\services\AuthService;
use app\wechat_patient\services\WechatService;
use Yii;

/**
 * 微信登录：POST {code} → openid → 登录即注册 → 签发 token
 */
class LoginController extends ApiController
{
    public $authExcept = ['index'];

    /**
     * POST /wechat-patient/login
     * body: code（wx.login 获得，一次性，5 分钟有效）
     */
    public function actionIndex()
    {
        $code = (string)Yii::$app->request->post('code', '');
        if ($code === '') {
            return ApiResponse::error(400, '缺少参数 code');
        }

        try {
            $wx = new WechatService();
            $session = $wx->code2session($code);

            $auth = new AuthService();
            $result = $auth->loginByOpenid($session['openid'], (string)($session['session_key'] ?? ''));

            return ApiResponse::ok($result, '登录成功');
        } catch (\Throwable $e) {
            Yii::error('登录失败：' . $e->getMessage(), __METHOD__);
            return ApiResponse::error(500, $e->getMessage());
        }
    }
}
