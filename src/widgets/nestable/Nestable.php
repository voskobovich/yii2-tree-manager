<?php

namespace voskobovich\tree\manager\widgets\nestable;

use voskobovich\tree\manager\interfaces\TreeInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * Class Nestable
 * @package voskobovich\tree\manager\widgets
 */
class Nestable extends Widget
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var array
     */
    public $modelClass;

    /**
     * @var array
     */
    public $nameAttribute = 'name';

    /**
     * Behavior key in list all behaviors on model
     * @var string
     */
    public $behaviorName = 'nestedSetsBehavior';

    /**
     * @var array.
     */
    public $pluginOptions = [];

    /**
     * Url to MoveNodeAction
     * @var string
     */
    public $moveUrl;

    /**
     * Url to CreateNodeAction
     * @var string
     */
    public $createUrl;

    /**
     * Url to UpdateNodeAction
     * @var string
     */
    public $updateUrl;

    /**
     * Url to page additional update model
     * @var string
     */
    public $advancedUpdateRoute;

    /**
     * Url to DeleteNodeAction
     * @var string
     */
    public $deleteUrl;

    /**
     * Handler for render form fields on create new node
     * @var callable
     */
    public $formFieldsCallable;

    /**
     * Структура меню в php array формате
     * @var array
     */
    private $_items = [];

    /**
     * Инициализация плагина
     */
    public function init()
    {
        parent::init();

        if (empty($this->id)) {
            $this->id = $this->getId();
        }

        if ($this->modelClass == null) {
            throw new InvalidConfigException('Param "modelClass" must be contain model name');
        }

        if (null == $this->behaviorName) {
            throw new InvalidConfigException("No 'behaviorName' supplied on action initialization.");
        }

        if (null == $this->advancedUpdateRoute && ($controller = Yii::$app->controller)) {
            $this->advancedUpdateRoute = "{$controller->id}/update";
        }

        if ($this->formFieldsCallable == null) {
            $this->formFieldsCallable = function ($form, $model) {
                /** @var ActiveForm $form */
                echo $form->field($model, $this->nameAttribute);
            };
        }

        /** @var ActiveRecord|TreeInterface $model */
        $model = $this->modelClass;

        /** @var ActiveRecord[]|TreeInterface[] $rootNodes */
        $rootNodes = $model::find()->roots()->all();

        if (!empty($rootNodes[0])) {
            /** @var ActiveRecord|TreeInterface $items */
            $items = $rootNodes[0]->populateTree();
            $this->_items = $this->prepareItems($items);
        }
    }

    /**
     * @param ActiveRecord|TreeInterface $node
     * @return array
     */
    protected function getNode($node)
    {
        $items = [];
        /** @var ActiveRecord[]|TreeInterface[] $children */
        $children = $node->children;

        foreach ($children as $n => $node) {
            $items[$n]['id'] = $node->getPrimaryKey();
            $items[$n]['name'] = $node->getAttribute($this->nameAttribute);
            $items[$n]['children'] = $this->getNode($node);
            $items[$n]['update-url'] = Url::to([$this->advancedUpdateRoute, 'id' => $node->getPrimaryKey()]);
        }

        return $items;
    }

    /**
     * @param ActiveRecord|TreeInterface[] $node
     * @return array
     */
    private function prepareItems($node)
    {
        return $this->getNode($node);
    }

    /**
     * @param null $name
     * @return array
     */
    private function getPluginOptions($name = null)
    {
        $options = ArrayHelper::merge($this->getDefaultPluginOptions(), $this->pluginOptions);

        if (isset($options[$name])) {
            return $options[$name];
        }

        return $options;
    }

    /**
     * Работаем!
     */
    public function run()
    {
        $this->registerActionButtonsAssets();
        $this->actionButtons();

        Pjax::begin([
            'id' => $this->id . '-pjax'
        ]);
        $this->registerPluginAssets();
        $this->renderMenu();
        $this->renderForm();
        Pjax::end();

        $this->actionButtons();
    }

    /**
     * Register Asset manager
     */
    private function registerPluginAssets()
    {
        NestableAsset::register($this->getView());

        $view = $this->getView();

        $pluginOptions = $this->getPluginOptions();
        $pluginOptions = Json::encode($pluginOptions);
        $view->registerJs("$('#{$this->id}').nestable({$pluginOptions});");
        // language=JavaScript
        $view->registerJs("
			$('#{$this->id}-new-node-form').on('beforeSubmit', function(e){
                $.ajax({
                    url: '{$this->getPluginOptions('createUrl')}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(data, textStatus, jqXHR) {
	                    $('#{$this->id}-new-node-modal').modal('hide')
	                    $.pjax.reload({container: '#{$this->id}-pjax'});
	                    window.scrollTo(0, document.body.scrollHeight);
                    },
                }).fail(function (jqXHR) {
                    alert(jqXHR.responseText);
                });

                return false;
			});
		");
    }

    /**
     * Register Asset manager
     */
    private function registerActionButtonsAssets()
    {
        $view = $this->getView();
        $view->registerJs("
			$('.{$this->id}-nestable-menu [data-action]').on('click', function(e) {
                e.preventDefault();

				var target = $(e.target),
				    action = target.data('action');

				switch (action) {
					case 'expand-all':
					    $('#{$this->id}').nestable('expandAll');
					    $('.{$this->id}-nestable-menu [data-action=\"expand-all\"]').hide();
					    $('.{$this->id}-nestable-menu [data-action=\"collapse-all\"]').show();

						break;
					case 'collapse-all':
					    $('#{$this->id}').nestable('collapseAll');
					    $('.{$this->id}-nestable-menu [data-action=\"expand-all\"]').show();
					    $('.{$this->id}-nestable-menu [data-action=\"collapse-all\"]').hide();

						break;
				}
			});
		");
    }

    /**
     * Generate default plugin options
     * @return array
     */
    private function getDefaultPluginOptions()
    {
        $options = [
            'namePlaceholder' => $this->getPlaceholderForName(),
            'deleteAlert' => Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable',
                'The nobe will be removed together with the children. Are you sure?'),
            'newNodeTitle' => Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable',
                'Enter the new node name'),
        ];

        $controller = Yii::$app->controller;
        if ($controller) {
            $options['moveUrl'] = Url::to(["{$controller->id}/moveNode"]);
            $options['createUrl'] = Url::to(["{$controller->id}/createNode"]);
            $options['updateUrl'] = Url::to(["{$controller->id}/updateNode"]);
            $options['deleteUrl'] = Url::to(["{$controller->id}/deleteNode"]);
        }

        if ($this->moveUrl) {
            $this->pluginOptions['moveUrl'] = $this->moveUrl;
        }
        if ($this->createUrl) {
            $this->pluginOptions['createUrl'] = $this->createUrl;
        }
        if ($this->updateUrl) {
            $this->pluginOptions['updateUrl'] = $this->updateUrl;
        }
        if ($this->deleteUrl) {
            $this->pluginOptions['deleteUrl'] = $this->deleteUrl;
        }

        return $options;
    }

    /**
     * Get placeholder for Name input
     */
    public function getPlaceholderForName()
    {
        return Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable', 'Node name');
    }

    /**
     * Кнопки действий над виджетом
     */
    public function actionButtons()
    {
        echo Html::beginTag('div', ['class' => "{$this->id}-nestable-menu"]);

        echo Html::beginTag('div', ['class' => 'btn-group']);
        echo Html::button(Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable', 'Add node'), [
            'data-toggle' => 'modal',
            'data-target' => "#{$this->id}-new-node-modal",
            'class' => 'btn btn-success'
        ]);
        echo Html::button(Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable', 'Collapse all'), [
            'data-action' => 'collapse-all',
            'class' => 'btn btn-default'
        ]);
        echo Html::button(Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable', 'Expand all'), [
            'data-action' => 'expand-all',
            'class' => 'btn btn-default',
            'style' => 'display: none'
        ]);
        echo Html::endTag('div');

        echo Html::endTag('div');
    }

    /**
     * Вывод меню
     */
    private function renderMenu()
    {
        echo Html::beginTag('div', ['class' => 'dd-nestable', 'id' => $this->id]);

        $this->printLevel($this->_items);

        echo Html::endTag('div');
    }

    /**
     * Render form for new node
     */
    private function renderForm()
    {
        /** @var ActiveRecord $model */
        $model = new $this->modelClass;
        $labelNewNode = Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable','New node');
        $labelCloseButton = Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable','Close');
        $labelCreateNode = Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable','Create node');

        echo <<<HTML
<div class="modal" id="{$this->id}-new-node-modal" tabindex="-1" role="dialog" aria-labelledby="newNodeModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
HTML;
        /** @var ActiveForm $form */
        $form = ActiveForm::begin([
            'id' => $this->id . '-new-node-form'
        ]);

        echo <<<HTML
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="newNodeModalLabel">$labelNewNode</h4>
      </div>
      <div class="modal-body">
HTML;

        echo call_user_func($this->formFieldsCallable, $form, $model);

        echo <<<HTML
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">$labelCloseButton</button>
        <button type="submit" class="btn btn-primary">$labelCreateNode</button>
      </div>
HTML;
        $form->end();
        echo <<<HTML
    </div>
  </div>
</div>
HTML;
    }

    /**
     * Распечатка одного уровня
     * @param $level
     */
    private function printLevel($level)
    {
        echo Html::beginTag('ol', ['class' => 'dd-list']);

        foreach ($level as $item) {
            $this->printItem($item);
        }

        echo Html::endTag('ol');
    }

    /**
     * Распечатка одного пункта
     * @param $item
     */
    private function printItem($item)
    {
        $htmlOptions = ['class' => 'dd-item'];
        $htmlOptions['data-id'] = !empty($item['id']) ? $item['id'] : '';

        echo Html::beginTag('li', $htmlOptions);

        echo Html::tag('div', '', ['class' => 'dd-handle']);
        echo Html::tag('div', $item['name'], ['class' => 'dd-content']);

        echo Html::beginTag('div', ['class' => 'dd-edit-panel']);
        echo Html::input('text', null, $item['name'],
            ['class' => 'dd-input-name', 'placeholder' => $this->getPlaceholderForName()]);

        echo Html::beginTag('div', ['class' => 'btn-group']);
        echo Html::button(Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable', 'Save'), [
            'data-action' => 'save',
            'class' => 'btn btn-success btn-sm',
        ]);
        echo Html::a(Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable', 'Advanced editing'),
            $item['update-url'], [
                'data-action' => 'advanced-editing',
                'class' => 'btn btn-default btn-sm',
                'target' => '_blank'
            ]);
        echo Html::button(Yii::t('vendor/voskobovich/yii2-tree-manager/widgets/nestable', 'Delete'), [
            'data-action' => 'delete',
            'class' => 'btn btn-danger btn-sm'
        ]);
        echo Html::endTag('div');

        echo Html::endTag('div');

        if (isset($item['children']) && count($item['children'])) {
            $this->printLevel($item['children']);
        }

        echo Html::endTag('li');
    }
}