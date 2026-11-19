<?php

declare(strict_types=1);

namespace app\wechat_patient\helpers;

/**
 * 统一 API 返回格式：{ code, msg, data }
 */
class ApiResponse
{
    public static function ok($data = [], string $msg = 'ok'): array
    {
        return ['code' => 0, 'msg' => $msg, 'data' => $data];
    }

    public static function error(int $code, string $msg, $data = []): array
    {
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}
