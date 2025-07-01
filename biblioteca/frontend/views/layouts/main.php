<?php
/* frontend/views/layouts/main.php */

use yii\helpers\Html;
use frontend\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body id="app" data-theme="light">
    <?php $this->beginBody() ?>

    <!-- Sidebar -->
    <aside id="sidebar-toogle" class="fixed inset-y-0 left-0 w-60 bg-white border-r z-40">
        <div class="sidebar-header p-4" onclick="window.location.href='<?= \yii\helpers\Url::to(['site/index']) ?>'">
            <h1 class="text-xl font-bold">BiblioTech</h1>
            <p class="text-sm text-gray-500">Sistema de Biblioteca</p>
        </div>
        <?= \frontend\widgets\SidebarWidget::widget() ?>
    </aside>


    <!-- Conteúdo principal -->
    <div class="main-wrapper flex-1 flex flex-col">
        <header>
            <?= \frontend\widgets\HeaderWidget::widget() ?>
        </header>
        <main>
            <?= $content ?>
        </main>
    </div>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>