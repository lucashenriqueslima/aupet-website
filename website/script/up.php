<?php

session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

$bairros = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_main_estados A");

foreach ($bairros->rows as $bairro) {
	$sistema->DB_update("hbrd_main_estados", "seo_url = '".$sistema->formataUrlAmiga($bairro['estado'])."' WHERE id = {$bairro['id']}");
}
?>