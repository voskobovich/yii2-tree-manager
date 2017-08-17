# Tree Manager for Yii2

Виджет для управления деревом.

Внимание!
-----
Виджет рассчитан на работу с поведениями Павла Зимакова:

[Yii2 Adjacency List Behavior](https://github.com/paulzi/yii2-adjacency-list)  
[Yii2 Nested Sets Behavior](https://github.com/paulzi/yii2-nested-sets)  
[Yii2 Nested Intervals Behavior](https://github.com/paulzi/yii2-nested-intervals)  
[Yii2 Materialized Path Behavior](https://github.com/paulzi/yii2-materialized-path)  

Отличная статья на [Хабре](http://habrahabr.ru/post/266155/).


Installation
-------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist voskobovich/yii2-tree-manager "~1.0"
```

or add

```
"voskobovich/yii2-tree-manager": "~1.0"
```

to the require section of your `composer.json` file.


Usage
-----
 
  1. Подключите к вашей модели любое из указанных выше поведений
  
  2. Подключите в контроллер дополнительные actions

```
public function actions()
{
    $modelClass = 'namespace\ModelName';

    return [
        'moveNode' => [
            'class' => 'voskobovich\tree\manager\actions\MoveNodeAction',
            'modelClass' => $modelClass,
        ],
        'deleteNode' => [
            'class' => 'voskobovich\tree\manager\actions\DeleteNodeAction',
            'modelClass' => $modelClass,
        ],
        'updateNode' => [
            'class' => 'voskobovich\tree\manager\actions\UpdateNodeAction',
            'modelClass' => $modelClass,
        ],
        'createNode' => [
            'class' => 'voskobovich\tree\manager\actions\CreateNodeAction',
            'modelClass' => $modelClass,
        ],
    ];
}
```  

3. Выведите виджет в удобном месте

```
use \voskobovich\tree\manager\widgets\nestable\Nestable;

<?= Nestable::widget([
    'modelClass' => 'models\ModelName',
]) ?>
```

Пример того, как выглядит виджет:
-------------

![Screenshot](http://s019.radikal.ru/i644/1708/64/5f8e8e986d3c.png)
