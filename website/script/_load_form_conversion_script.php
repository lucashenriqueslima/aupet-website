<?php

session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

$sistema->DB_connect();

$analytics = array();
$query = $sistema->DB_fetch_array("SELECT script FROM hbrd_cms_codigos WHERE id = {$_GET['id']}");
if ($query->num_rows) {
    $analytics = $query->rows[0];
    echo $analytics['script'];
}


unset($query);

$sistema->DB_disconnect();