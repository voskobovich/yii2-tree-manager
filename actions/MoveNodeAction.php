<?php

namespace voskobovich\tree\manager\actions;

use voskobovich\tree\manager\forms\MoveNodeForm;
use voskobovich\tree\manager\interfaces\TreeInterface;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\web\HttpException;

/**
 * Class MoveNodeAction
 * @package \tree\manager\\actions
 */
class MoveNodeAction extends BaseAction
{
    /**
     * Move a node (model) below the parent and in between left and right
     *
     * @param integer $id the primaryKey of the moved node
     * @return array
     * @throws HttpException
     */
    public function run($id)
    {
        /** @var ActiveRecord|TreeInterface $model */
        $model = $this->findModel($id);

        $form = new MoveNodeForm();

        $params = Yii::$app->getRequest()->getBodyParams();
        $form->load($params, '');

        if (!$form->validate()) {
            return $form;
        }

        try {
            if ($form->prev_id > 0) {
                $parentModel = $this->findModel($form->prev_id);
                if ($parentModel->isRoot()) {
                    return $model->appendTo($parentModel)->save();
                } else {
                    return $model->insertAfter($parentModel)->save();
                }
            } elseif ($form->next_id > 0) {
                $parentModel = $this->findModel($form->next_id);
                return $model->insertBefore($parentModel)->save();
            } elseif ($form->parent_id > 0) {
                $parentModel = $this->findModel($form->parent_id);
                return $model->appendTo($parentModel)->save();
            }
        } catch (Exception $ex) {
        }

        return false;
    }
}