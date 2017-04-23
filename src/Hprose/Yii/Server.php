<?php
/**********************************************************\
|                                                          |
|                          hprose                          |
|                                                          |
| Official WebSite: http://www.hprose.com/                 |
|                   http://www.hprose.org/                 |
|                                                          |
\**********************************************************/

/**********************************************************\
 *                                                        *
 * Hprose/Yii/Server.php                                  *
 *                                                        *
 * hprose yii http server class for php 5.3+              *
 *                                                        *
 * LastModified: Jul 18, 2016                             *
 * Author: Ma Bingyao <andot@hprose.com>                  *
 *                                                        *
\**********************************************************/

namespace Hprose\Yii;

class Server extends Service {

    /**
     * @var array
     */
    protected $functions = [];

    /**
     * @inheritdoc
     */
    public function start() {
        \Yii::setAlias('@Hprose/Yii', __DIR__);
        $app = \Yii::$app;
        return $this->handle($app->request, $app->response);
    }

    /**
     * @inheritdoc
     */
    public function addFunction($func, $alias = '', array $options = array())
    {
        $old = count($this->getNames());
        $self = parent::addFunction($func, $alias, $options);
        if ($old < count($this->getNames())) {
            $names = $this->getNames();
            $name = end($names);
            $this->functions[$name] = $func;
        }

        return $self;
    }

    /**
     * @inheritdoc
     */
    protected function doFunctionList()
    {
        $reflects = [];
        foreach ($this->functions as $name => $closure) {
            if ($name === '#') {
                continue;
            }

            $reflect = null;
            if (is_callable($closure)) {
                if (is_array($closure)) {
                    $reflect = new \ReflectionMethod($closure[0], $closure[1]);
                } else {
                    $reflect = new \ReflectionFunction($closure);
                }
            }

            if ($reflect) {
                $reflects[$name] = $reflect;
            }
        }

        \Yii::$app->response->headers->set('Content-Type', 'text/html');

        return \Yii::$app->view->renderFile(__DIR__ . '/views/manual.php', [
            'reflects' => $reflects
        ]);
    }
}
