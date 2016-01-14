<?php

namespace voskobovich\tree\manager\interfaces;

/**
 * Interface TreeInterface
 * @package voskobovich\tree\manager\interfaces
 */
interface TreeInterface
{
    /**
     * @param int|null $depth
     * @return \yii\db\ActiveQuery
     */
    public function getParents($depth = null);

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent();

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoot();

    /**
     * @param int|null $depth
     * @param bool $andSelf
     * @return \yii\db\ActiveQuery
     */
    public function getDescendants($depth = null, $andSelf = false);

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren();

    /**
     * @param int|null $depth
     * @return \yii\db\ActiveQuery
     */
    public function getLeaves($depth = null);

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\NotSupportedException
     */
    public function getPrev();

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\NotSupportedException
     */
    public function getNext();

    /**
     * Populate children relations for self and all descendants
     * @param int $depth = null
     * @return self
     */
    public function populateTree($depth = null);

    /**
     * @return bool
     */
    public function isRoot();

    /**
     * @param \yii\db\BaseActiveRecord $node
     * @return bool
     */
    public function isChildOf($node);

    /**
     * @return bool
     */
    public function isLeaf();

    /**
     * @return $this
     */
    public function makeRoot();

    /**
     * @param \yii\db\BaseActiveRecord $node
     * @return $this
     */
    public function prependTo($node);

    /**
     * @param \yii\db\BaseActiveRecord $node
     * @return $this
     */
    public function appendTo($node);

    /**
     * @param \yii\db\BaseActiveRecord $node
     * @return $this
     */
    public function insertBefore($node);

    /**
     * @param \yii\db\BaseActiveRecord $node
     * @return $this
     */
    public function insertAfter($node);

    /**
     * @return bool|int
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function deleteWithChildren();

    /**
     * @return TreeQueryInterface
     */
    public function find();
}