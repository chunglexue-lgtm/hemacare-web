<?php

declare(strict_types=1);

namespace app\wechat_patient\services;

use Yii;
use yii\base\Exception;

/**
 * 微信服务端 API 封装（仅服务端调用，AppSecret 不出服务端）
 */
class WechatService
{
    private string $appid;
    private string $secret;

    public function __construct()
    {
        $cfg = Yii::$app->params['wechat'] ?? [];
        $this->appid = (string)($cfg['appid'] ?? '');
        $this->secret = (string)($cfg['secret'] ?? '');
    }

    /**
     * wx.login 的 code 换取 openid + session_key
     *
     * @return array{openid: string, session_key?: string, unionid?: string}
     * @throws Exception 微信返回错误或网络失败时
     */
    public function code2session(string $code): array
    {
        if ($this->appid === '' || $this->secret === '') {
            throw new Exception('微信配置缺失：请设置环境变量 WECHAT_APPID / WECHAT_SECRET');
        }
        $url = 'https://api.weixin.qq.com/sns/jscode2session?' . http_build_query([
            'appid' => $this->appid,
            'secret' => $this->secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ]);
        $resp = $this->httpGet($url);
        $data = json_decode($resp, true);
        if (!is_array($data)) {
            throw new Exception('微信接口响应异常：' . mb_substr((string)$resp, 0, 200));
        }
        if (isset($data['errcode']) && (int)$data['errcode'] !== 0) {
            throw new Exception('微信登录失败：' . ($data['errmsg'] ?? 'unknown') . '（' . $data['errcode'] . '）');
        }
        if (empty($data['openid'])) {
            throw new Exception('微信接口未返回 openid');
        }
        return $data;
    }

    /** GET 请求（cURL） */
    protected function httpGet(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('请求微信接口失败：' . $err);
        }
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($status !== 200) {
            throw new Exception('微信接口 HTTP 状态异常：' . $status);
        }
        return (string)$resp;
    }
}
