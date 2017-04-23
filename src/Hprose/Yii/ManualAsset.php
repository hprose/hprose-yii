<?php
/**
 * Created by PhpStorm.
 * User: pavle
 * Date: 2017/4/23
 * Time: 下午5:33
 */

namespace Hprose\Yii;


use yii\web\AssetBundle;

class ManualAsset extends AssetBundle
{
    public $sourcePath = '@Hprose/Yii/assets';

    public $css = [
        'theme-base.css',
        'theme-medium.css',
    ];
}