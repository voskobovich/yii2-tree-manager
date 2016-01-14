<?php

namespace voskobovich\tree\manager\forms;

use yii\base\Model;
use Yii;

/**
 * Class MoveNodeForm
 * @package voskobovich\tree\manager\\forms
 */
class MoveNodeForm extends Model
{
    /**
     * @var integer
     */
    public $prev_id;

    /**
     * @var integer
     */
    public $next_id;

    /**
     * @var integer
     */
    public $parent_id;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['prev_id', 'next_id', 'parent_id'], 'integer']
        ];
    }
}