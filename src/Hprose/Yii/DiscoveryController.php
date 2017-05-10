<?php
/**
 * 发现项目中服务，发布的服务只需要继承Hprose\Yii\Controller即可
 * User: Pavle Lee <523260513@qq.com>
 * Date: 2017/4/23
 * Time: 下午6:46
 */

namespace Hprose\Yii;

use Yii;
use yii\caching\Dependency;
use yii\caching\FileDependency;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Application;
use yii\web\Controller;
use yii\web\Response;

/**
 * 服务发现地址
 * @package Hprose\Yii
 *
 * @url discovery/index 发现项目中的服务(使用缓存)
 * @url discovery/flush 清空缓存
 */
class DiscoveryController extends Controller
{
    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @var string
     */
    public $cacheKey = 'rpc-services';

    /**
     * @var string
     */
    public $cacheFilePath = '@runtime/hprose_cache';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $dependFile = Yii::getAlias($this->cacheFilePath);
        if (!file_exists($dependFile)) {
            touch($dependFile);
        }
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $dependency = $this->createDependency();

        return Yii::$app->cache->getOrSet($this->cacheKey, function () {
            Yii::trace('Dynamic Find');
            $services = [];
            $commands = $this->getCommandDescriptions();
            foreach ($commands as $controller => $actions) {
                foreach ($actions as $action) {
                    $services[$action] = Url::to(["{$controller}/{$action}"], true);
                }
            }

            return $services;
        }, null, $dependency);
    }

    /**
     * Returns an array of commands an their descriptions.
     * @return array all available commands as keys and their description as values.
     */
    protected function getCommandDescriptions()
    {
        $commands = [];
        foreach ($this->getCommands() as $command) {
            $actions = [];
            $result = Yii::$app->createController($command);
            if ($result !== false && $result[0] instanceof \Hprose\Yii\Controller) {
                list($controller) = $result;
                /** @var Controller $controller */
                $actions = $this->getActions($controller);
            }

            $commands[$command] = $actions;
        }

        return $commands;
    }

    /**
     * Returns all available actions of the specified controller.
     * @param Controller $controller the controller instance
     * @return array all available action IDs.
     */
    protected function getActions($controller)
    {
        $actions = array_keys($controller->actions());
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($name !== 'actions' && $method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0) {
                $actions[] = Inflector::camel2id(substr($name, 6), '-', true);
            }
        }
        sort($actions);

        return array_unique($actions);
    }

    /**
     * Returns all available command names.
     * @return array all available command names
     */
    protected function getCommands()
    {
        $commands = $this->getModuleCommands(Yii::$app);
        sort($commands);
        return array_unique($commands);
    }

    /**
     * Returns available commands of a specified module.
     * @param \yii\base\Module $module the module instance
     * @return array the available command names
     */
    protected function getModuleCommands($module)
    {
        $prefix = $module instanceof Application ? '' : $module->getUniqueId() . '/';

        $commands = [];
        foreach (array_keys($module->controllerMap) as $id) {
            $commands[] = $prefix . $id;
        }

        $controllerPath = $module->getControllerPath();
        if (is_dir($controllerPath)) {
            $files = scandir($controllerPath);
            foreach ($files as $file) {
                if (!empty($file) && substr_compare($file, 'Controller.php', -14, 14) === 0) {
                    $controllerClass = $module->controllerNamespace . '\\' . substr(basename($file), 0, -4);
                    if ($this->validateControllerClass($controllerClass)) {
                        $commands[] = $prefix . Inflector::camel2id(substr(basename($file), 0, -14));
                    }
                }
            }
        }

        return $commands;
    }

    /**
     * Validates if the given class is a valid console controller class.
     * @param string $controllerClass
     * @return bool
     */
    protected function validateControllerClass($controllerClass)
    {
        if (class_exists($controllerClass)) {
            $class = new \ReflectionClass($controllerClass);
            return !$class->isAbstract() && $class->isSubclassOf('Hprose\Yii\Controller');
        } else {
            return false;
        }
    }

    /**
     * 生成缓存依赖
     * @return Dependency
     */
    protected function createDependency()
    {
        $dependFile = Yii::getAlias($this->cacheFilePath);
        return new FileDependency(['fileName' => $dependFile]);
    }
}