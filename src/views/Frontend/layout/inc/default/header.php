<!DOCTYPE html>
<html lang="en">

<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta http-equiv="cache-control" content="no-cache">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta http-equiv="x-UA-compatible" content="IE=9">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description"
      content="K'nGELL est un cabinet de conseil et d'ingénierie Logistique spécialisé dans la maitrise des process logistique et production grâce à l'usage de stratégies et tactiques Lean Management et six Sigma (6Sigma">
   <meta name="robots" content="index,follow">
   <meta name="csrftoken" content="<?=''?>" />
   <meta name="frm_name" content="<?=$this->getPageTitle()?>" />
   <link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">

   <title>
      <?= $this->getPageTitle()?>
   </title>
   <!-- Main style -->
   <?= $this->css('css/librairies/frontlib', 'css') ?? '' ?>
   <!-- Plugins css -->
   <?= $this->css('css/plugins/homeplugins', 'css') ?? '' ?>
   <!-- Main style -->
   <?= $this->css('css/client/main', 'css') ?? '' ?>
   <!-- CkEditor -->
   <?= $this->css('css/ckeditor/ckeditor', 'css') ?? '' ?>
   <link rel="shortcut icon" href="data:," />
   <?= $this->content('head'); ?>
</head>

<body id="body">