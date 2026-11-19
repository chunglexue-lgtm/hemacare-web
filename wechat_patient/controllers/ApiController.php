<?php

declare(strict_types=1);

namespace app\wechat_patient\controllers;

use app\wechat_patient\helpers\ApiResponse;
use app\wechat_patient\services\AuthService;
use app\models\UserAccount;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * API 基类：统一 JSON 响应 + X-Api-Token 鉴权
 * 子类把不需要登录的 action id 放进 $authExcept
 */
class ApiController extends Controller
{
    public $enableCsrfValidation = false;

    /** @var string[] 不需要鉴权的 action id 列表 */
    public $authExcept = [];

    /** @var UserAccount|null 当前登录用户 */
    public $currentUser;

    public function init()
    {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (in_array($action->id, $this->authExcept, true)) {
            return true;
        }
        $token = (string)Yii::$app->request->headers->get('X-Api-Token', '');
        $user = (new AuthService())->getUserByToken($token);
        if ($user === null) {
            Yii::$app->response->data = ApiResponse::error(401, '未登录或 token 已过期');
            return false;
        }
        $this->currentUser = $user;
        return true;
    }
}
