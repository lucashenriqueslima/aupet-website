<?php
$conf = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_configuracoes_gerais")->rows[0];
$company = $sistema->DB_fetch_array("SELECT * FROM hbrd_adm_company WHERE id = 1")->rows[0];
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="pt-br">
<head>
    <base href="<?= $sistema->root_path; ?>" />
    <meta charset="utf-8">
    <?php if(isset($currentpage)){ ?>
        <meta name="url" content="<?= $sistema->root_path.$currentpage['seo_url'];?>" />
        <meta name="description" content="<?php if($currentpage['seo_description'] == "") echo $sistema->seo_pages['padrao']['seo_description']; else echo $currentpage['seo_description']; ?>" />
        <meta name="keywords" content="<?php if($currentpage['seo_keywords'] == "") echo $sistema->seo_pages['padrao']['seo_keywords']; else echo $currentpage['seo_keywords'];?>" />
        <title><?php if($currentpage['seo_title'] == "") echo $sistema->seo_pages['padrao']['seo_title']; else echo $currentpage['seo_title'];?></title>
        <meta property="og:url" content="<?= $sistema->root_path.$currentpage['seo_url'];?>" />
        <meta property="og:title" content="<?php if($currentpage['seo_title'] == "") echo $sistema->seo_pages['padrao']['seo_title']; else echo $currentpage['seo_title'];?>" />
        <meta property="og:site_name" content="<?= $empresa['nome'];?>" />
        <meta property="og:description" content="<?php if($currentpage['seo_description'] == "") echo $sistema->seo_pages['padrao']['seo_description']; else echo $currentpage['seo_description']; ?>" />
        <meta property="og:type" content="website" />
        <?php if($currentpage['seo_imagemw']) : ?>
            <meta property="og:image:width" content="<?= $currentpage['seo_imagemw']; ?>" />
        <?php endif ?>
        <?php if($currentpage['seo_imagemh']) : ?>
            <meta property="og:image:height" content="<?= $currentpage['seo_imagemh']; ?>" />
        <?php endif ?>
        <?php if($currentpage['seo_imagem']) : ?>
            <meta property="og:image" content="<?= $sistema->root_path.$currentpage['seo_imagem']; ?>" />
        <?php else : ?>
            <meta property="og:image" content="<?= $sistema->getImageFileOriginal($conf['logomarca']); ?>" />
        <?php endif ?>
    <?php } else { ?>
        <meta name="description" content="<?= $sistema->seo_pages['padrao']['seo_description'] ?>" />
        <meta name="keywords" content="<?= $sistema->seo_pages['padrao']['seo_keywords'] ?>" />
        <?php if (!empty($real_current_page)) : ?>
            <title><?= $real_current_page['seo_title'] ?></title>
        <?php else : ?>
            <title><?= $sistema->seo_pages['padrao']['seo_title'] ?></title>
        <?php endif; ?>
        <meta property="og:url" content="<?= $sistema->root_path;?>" />
        <meta property="og:title" content="<?= $sistema->seo_pages['padrao']['seo_title'];?>" />
        <meta property="og:site_name" content="<?= $empresa['nome'];?>" />
        <meta property="og:description" content="<?= $sistema->seo_pages['padrao']['seo_description']; ?>" />
        <meta property="og:type" content="website" />
        <meta property="og:image" content="<?= $sistema->getImageFileOriginal($conf['logomarca']); ?>" />
    <?php } ?>
    <meta id="viewport" name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <!-- <link rel="shortcut icon" href="<?= $sistema->getImageFileSized($conf['favicon'],16,16) ?>" type="image/x-icon" /> -->
    <link rel="shortcut icon" href="website/img/favicon.png" type="image/x-icon" />
    <link href="website/css/principal.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="website/plugins/slick/slick.css">
    <link rel="stylesheet" type="text/css" href="website/plugins/slick/slick-theme.css">
    <link rel="stylesheet" type="text/css" href="website/plugins/bootstrap/bootstrap.min.css">

    <script type="text/javascript">
        var pagina = "<?= $pagina; ?>";
        var path = "<?= $sistema->root_path;?>";
        var is_desktop = <?php if (!$sistema->detect->isMobile() && !$sistema->detect->isTablet()){echo 1;}else{echo 0;} ?>;
        var is_tablet = <?php if ($sistema->detect->isTablet()){echo 1;}else{echo 0;} ?>;
        var is_mobile = <?php if ($sistema->detect->isMobile()){echo 1;}else{echo 0;} ?>;
        var scripts_path = "website/scripts/";
        var id_seo = "<?= (isset($dynamic_id)) ? $dynamic_id : 2 ?>";
    </script>
