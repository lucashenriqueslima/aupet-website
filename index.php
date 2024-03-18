<?php
if (!isset($_SESSION)) session_start();
error_reporting(0);
header("Content-Type:text/html;charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');
if (!isset($_SESSION["seo_session"])) $_SESSION["seo_session"] = uniqid();
require_once __DIR__.'/main/System/Core/Loader.php';
require __DIR__.'/vendor/autoload.php';

use website\Classes\Sys;
$sistema = new Sys();
$internas = [ "blog-interno"];
$seo_table = "hbrd_cms_paginas";
$utm_source = $utm_medium = $utm_campaign = $utm_content = $utm_term = "";
if (isset($_GET['utm_medium'])) {
    $_SESSION['utm_medium'] = $_GET['utm_medium'];
}
if (isset($_GET['utm_term'])) {
    $_SESSION['utm_term'] = $_GET['utm_term'];
}
if (isset($_GET['utm_content'])) {
    $_SESSION['utm_content'] = $_GET['utm_content'];
}
if (isset($_GET['utm_campaign'])) {
    $_SESSION['utm_campaign'] = $_GET['utm_campaign'];
}
if (isset($_GET['utm_source'])) {
    $_SESSION['utm_source'] = $_GET['utm_source'];
}
if (isset($_SESSION['utm_medium'])) {
    $utm_medium = $_SESSION['utm_medium'];
}
if (isset($_SESSION['utm_term'])) {
    $utm_term = $_SESSION['utm_term'];
}
if (isset($_SESSION['utm_content'])) {
    $utm_content = $_SESSION['utm_content'];
}
if (isset($_SESSION['utm_campaign'])) {
    $utm_campaign = $_SESSION['utm_campaign'];
}
if (isset($_SESSION['utm_source'])) {
    $utm_source = $_SESSION['utm_source'];
}
$indice = 0;
$uri = explode("/", str_replace(strrchr($_SERVER["REQUEST_URI"], "?"), "", $_SERVER["REQUEST_URI"]));
array_shift($uri);
$urlamiga = $uri[$indice];
$urlamiga = str_replace("%E2%80%93", "–", $urlamiga);
$urlamiga = $sistema->DB_anti_injection($urlamiga);
if ($urlamiga == "") {
    require_once "website/views/home.php";
} else if ($urlamiga == "proposta") {
    require_once "environment/externo/proposta/index.php";
} else if ($urlamiga == 'sistema') {
    require_once 'main/index.php';
} else if (isset($sistema->internas[$urlamiga]) || in_array($urlamiga, $internas)) {
    $urldinamica = str_replace("%E2%80%93", "–",end($uri));
    $pagina = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_paginas WHERE seo_url='$urldinamica' LIMIT 1")->rows[0];
    $dynamic_id = (int)$pagina['id'];
    require_once "website/views/".$pagina['seo_pagina'].".php";
} else {
    $query = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_paginas WHERE seo_url='$urlamiga' LIMIT 1");
    if ($query->num_rows) {
        $dynamic_id = $query->rows[0]['id'];
        if (file_exists("website/views/".$query->rows[0]['seo_pagina'].".php")) 
            require_once "website/views/".$query->rows[0]['seo_pagina'].".php";
        else 
            require_once "website/views/404.php";
    } else {
        if (file_exists("website/views/".$urlamiga.".php")) 
            require_once "website/views/".$urlamiga.".php";
        else 
            require_once "website/views/404.php";
    }
}