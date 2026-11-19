<?php

declare(strict_types=1);

namespace app\wechat_patient;

/**
 * 患者端小程序 API 模块
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\wechat_patient\controllers';

    public function init()
    {
        parent::init();
    }
}
