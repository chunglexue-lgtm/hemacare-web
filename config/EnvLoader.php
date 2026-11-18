<?php

declare(strict_types=1);

/**
 * 轻量 .env 加载器（无第三方依赖）。
 *
 * 用法：
 *   require __DIR__ . '/config/EnvLoader.php';
 *   EnvLoader::load(dirname(__DIR__));
 *
 * 规则：
 * - 本地开发：在项目根目录放 .env（KEY=VALUE，支持 # 注释和成对引号）
 * - Docker/云托管：镜像和运行环境不包含 .env，直接使用容器环境变量
 * - 已存在的真实环境变量优先，.env 中的同名变量不会覆盖（容器级配置最高）
 */
final class EnvLoader
{
    private static bool $loaded = false;

    public static function load(string $basePath): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $file = $basePath . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            if ($name === '') {
                continue;
            }

            // 去掉成对引号
            $len = strlen($value);
            if ($len >= 2) {
                $first = $value[0];
                $last = $value[$len - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (getenv($name) === false) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
