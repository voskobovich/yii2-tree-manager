<?php

namespace voskobovich\tree\manager\actions;

use Yii;
use voskobovich\tree\manager\interfaces\TreeInterface;
use yii\db\ActiveRecord;
use yii\web\HttpException;

/**
 * Class CreateNodeAction
 * @package voskobovich\tree\manager\actions
 */
class CreateNodeAction extends BaseAction
{
    /**
     * @return null
     * @throws HttpException
     */
    public function run()
    {
        /** @var TreeInterface|ActiveRecord $model */
        $model = Yii::createObject($this->modelClass);

        $params = Yii::$app->getRequest()->getBodyParams();
        $model->load($params);

        if (!$model->validate()) {
            return $model;
        }

        $roots = $model::find()->roots()->all();

        if (isset($roots[0])) {
            return $model->appendTo($roots[0])->save();
        } else {
            return $model->makeRoot()->save();
        }
    }
}