<!DOCTYPE html>
<html lang="en">

<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta http-equiv="cache-control" content="no-cache">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta http-equiv="x-UA-compatible" content="IE=9">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="robots" content="index,follow">
   <meta name="csrftoken" content="<?= $this->token()?>" />
   <meta name="frm_name" content="<?=$this->getPageTitle()?>" />
   <?= $this->getPageTitle()?>
   <link rel="shortcut icon" href="data:," type="image/x-icon" />
   <!-- Main style -->
   <?= $this->css('commons/frontend/vendors') ?>
   <?= $this->css('css/librairies/backend/lib') ?? '' ?>
   <!-- Plugins css -->
   <?= $this->css('css/plugins/homeplugins') ?? '' ?>
   <!-- Main style -->
   <?= $this->css('css/backend/main/main') ?? '' ?>
   <!-- CkEditor -->
   <?= $this->css('css/ckeditor/ckeditor', 'css') ?? '' ?>
   <?= $this->content('head'); ?>
</head>

<body id="body">