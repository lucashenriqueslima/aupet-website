<?php

if (!isset($_SESSION))
    session_start();

/**
 * 
 * @author	<contato@hibrida.biz>
 * @copyright	Copyright (c) 2015 Híbrida
 * @link	http://www.hibrida.biz
 */
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 1);
ini_set('safe_mode', 0);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8', true);

require_once 'System/Core/Loader.php';

use System\Core\Bootstrap;

require_once 'System/Libs/SimpleImage.php';

if (!isset($sistema)) {
    $sistema = new Bootstrap();
}

if (!isset($_SESSION['admin_logado']) OR $_SESSION['admin_logado'] !== true) {
    header("Location: $sistema->root_path");
    exit();
}
?>
<title>Híbrida Inteligência Web</title>
<form action="" method="get">
    <input type="hidden" name="start" value="1" >    
    <input type="text" name="tabela" value="" placeholder="tabela">
    <input type="text" name="campo" value="" placeholder="campo">
    <input type="text" name="width" value="" placeholder="largura">
    <input type="text" name="height" value="" placeholder="altura">
    Best Fit:
    <input type="radio" name="best_fit" value="true"> Sim
    <input type="radio" name="best_fit" value="false"> Não
    <input type="submit" value="Prosseguir">
</form>

<?php

$sistema->dbCMS();

if (isset($_GET['start']) AND $_GET['start'] == 1) {

    if (isset($_GET['pag']) && $_GET['pag'] != "")
        $pag = $_GET['pag'];
    else {
        $pag = 1;
    }

    if ($pag == 1) {
        $limit = " LIMIT 0, 50 ";
        $next = 2;
    } else {
        $inicio = $pag * 50 - 50;
        $fim = $pag * 50;
        $limit = " LIMIT " . $inicio . "," . $pag * 50;
        $next = $pag + 1;
    }

    $crop_sizes_from = array();

    array_push($crop_sizes_from, array("width"=>1920,"height"=>450));

    $crop_sizes_to = array();

    //array_push($crop_sizes_to, array("width" => 705, "height" => 531, "best_fit" => true));
    array_push($crop_sizes_to, array("width" => $_GET['width'], "height" => $_GET['height'], "best_fit" => false));

    $images = $sistema->DB_fetch_array("SELECT * FROM {$_GET['tabela']} WHERE {$_GET['campo']} != '' $limit");

    $flag = false;
    if ($images->num_rows) {
        $flag = true;
        foreach ($images->rows as $image) {

            if (file_exists($sistema->upload_path . $image[$_GET['campo']])) {
                $file = $image[$_GET['campo']];
                $file_array = explode(".", $file);
                $format = end($file_array);
                $folder_file = $file_array[0];

                $img = new SimpleImage($sistema->upload_path . $image[$_GET['campo']]);
                $folder_resized = str_replace("original/", "resized/", $folder_file);

                $delete = $sistema->upload_path.$folder_resized."[".$crop_sizes_from[0]['width']."x".$crop_sizes_from[0]['height']."].".$format;

                $saveto = $sistema->upload_path . $folder_resized . "[" . $crop_sizes_to[0]['width'] . "x" . $crop_sizes_to[0]['height'] . "]." . $format;

                echo "<b>Salvou {$image['id']}</b>: " . $saveto . "<br>";


                if (isset($crop_sizes_to[0]["best_fit"]) AND $crop_sizes_to[0]["best_fit"]) {
                    $img->best_fit($crop_sizes_to[0]['width'], $crop_sizes_to[0]['height'])->save($saveto);
                } else {

                    $img->adaptive_resize($crop_sizes_to[0]['width'], $crop_sizes_to[0]['height'])->save($saveto);
                }
            }
        }
    } else {
        $flag = false;
    }
    if ($flag)
        echo '<meta http-equiv="refresh" content="5; url=crop.php?start=1&tabela=' . $_GET['tabela'] . '&campo=' . $_GET['campo'] . '&width=' . $_GET['width'] . '&height=' . $_GET['height'] . '&pag=' . $next . '" />';
    else
        echo 'Crop finalizado!';
}