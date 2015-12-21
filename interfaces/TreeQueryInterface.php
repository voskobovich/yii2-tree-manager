<?php

namespace voskobovich\tree\manager\interfaces;

use yii\db\ActiveQuery;


/**
 * Interface TreeQueryInterface
 * @package voskobovich\tree\manager\interfaces
 */
interface TreeQueryInterface
{
    /**
     * @return ActiveQuery
     */
    public function roots();
}