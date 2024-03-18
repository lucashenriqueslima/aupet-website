<?php

session_start();

require_once '../../main/System/Core/Loader.php';

use System\Core\General;

require_once "../../main/System/Core/General.php";

$sistema = new General();

$sistema->dbCMS();

$formulario = $sistema->formularioObjeto($_POST);

if (isset($formulario->id) && isset($formulario->session)) {
    $fields_values = array();

    if (isset($formulario->pais) && $formulario->pais != "" && $formulario->pais != "null")
        array_push($fields_values, "pais = '$formulario->pais'");

    if (isset($formulario->estado) && $formulario->estado != "" && $formulario->estado != "null")
        array_push($fields_values, "estado = '$formulario->estado'");

    if (isset($formulario->cidade) && $formulario->cidade != "" && $formulario->cidade != "null")
        array_push($fields_values, "cidade = '$formulario->cidade'");

    $query = $sistema->DB_update('hbrd_cms_seo_acessos', implode(',', $fields_values) . " WHERE id = $formulario->id AND session = '$formulario->session'");
    $query = $sistema->DB_update('hbrd_cms_seo_acessos_historico', implode(',', $fields_values) . " WHERE id = $formulario->id AND session = '$formulario->session'");

    $_SESSION['localidade_analytics'] = true;
}
