<?php
use Hprose\Yii\ManualAsset;
use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\web\View;

/* @var $this View */
/* @var $reflects ReflectionFunction[] */

ManualAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <!-- saved from url=(0059)http://php.net/manual/zh/function.array-change-key-case.php -->
    <html lang="zh" class="js flexbox flexboxlegacy datauri">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="docs ">
    <?php $this->beginBody() ?>

    <div id="layout" class="clearfix">
        <aside class="layout-menu">
            <ul class="parent-menu-list">
                <li>
                    <ul class="child-menu-list">
                        <?php foreach ($reflects as $name => $reflect): ?>
                            <li>
                                <a href="#<?= $name ?>" title="<?= $name ?>"><?= $name ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </aside>

        <?php foreach ($reflects as $name => $reflect): ?>
            <section id="layout-content">
                <div id="<?= $name ?>"></div>
                <div id="function.array-change-key-case" class="refentry">
                    <div class="refsect1 description" id="refsect1-function.array-change-key-case-description">
                        <h3 class="title"><?= $name ?></h3>
                        <p class="para rdfs-comment">
                            <span>
                                <?php
                                $doc = $reflect->getDocComment();
                                $doc = str_replace("\n", "<br>", $doc);
                                $doc = str_replace(['*', '/'], "", $doc);

                                echo $doc;
                                ?>
                            </span>
                        </p>
                        <div class="methodsynopsis dc-description">
                            <span class="type"><?= method_exists($reflect, 'getReturnType') ? $reflect->getReturnType() : '' ?></span>
                            <span class="methodname"><strong><?= $name ?></strong></span>
                            <?php
                            $parameters = $reflect->getParameters();
                            $data = [];
                            foreach ($parameters as $parameter) {
                                $default = '';
                                if ($parameter->isDefaultValueAvailable()) {
                                    $default = '= ' . VarDumper::export($parameter->getDefaultValue());
                                }
                                $type = method_exists($parameter, 'getType') ? $parameter->getType() : '';
                                $data[] = <<<HTML
<span class="methodparam">
    <span class="type">{$type}</span> 
    <code class="parameter">\${$parameter->getName()} {$default}</code>
</span>
HTML;
                            }
                            ?>
                            ( <?= join(' , ', $data) ?> )
                        </div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>


    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>