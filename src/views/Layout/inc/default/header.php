<!DOCTYPE html>
<html lang="en">

<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta http-equiv="cache-control" content="no-cache">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta http-equiv="x-UA-compatible" content="IE=9">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="robots" content="index,follow">
   <meta name="description"
      content="K'nGELL est un cabinet de conseil et d'ingénierie Logistique spécialisé dans l'optimisation des process supply chain, logistique et production grâce à l'usage de stratégies et tactiques Lean Management et six Sigma (6Sigma)">
   <meta name="description"
      content="K'nGELL is a consultancy agency spcialized in a supply chain, logistics and production flows improvement with the help of lean and six sigma tactics and strategies">
   <meta name="csrftoken" content="<?= $this->token()?>" />
   <meta name="frm_name" content="<?=$this->getPageTitle()?>" />
   <?= $this->getPageTitle()?>
   <!-- Vendors -->
   <?= $this->css('commons/frontend/vendors') ?>
   <?= $this->css('css/librairies/frontlib') ?>
   <!-- Main style -->
   <?= $this->css('css/main/main') ?? '' ?>
   <link rel="shortcut icon" href="data:," />
   <?= $this->content('head'); ?>
</head>

<body id="body">
   <div class="layout-container">