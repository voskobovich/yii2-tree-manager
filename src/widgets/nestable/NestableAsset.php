<?php

namespace voskobovich\tree\manager\widgets\nestable;

use yii\web\AssetBundle;

/**
 * Class NestableAsset
 * @package voskobovich\tree\manager\widgets
 */
class NestableAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@vendor/voskobovich/yii2-tree-manager/src/widgets/nestable/assets';

    /**
     * @var array
     */
    public $css = [
        'jquery.nestable.css'
    ];

    /**
     * @var array
     */
    public $js = [
        'jquery.nestable.js'
    ];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\YiiAsset',
    ];
}