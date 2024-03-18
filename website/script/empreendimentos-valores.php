<?php

session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

if (isset($_GET['id']) AND ! empty($_GET['id'])) {
    $valores = $sistema->DB_fetch_array("SELECT valor FROM hbrd_cms_empreendimentos WHERE stats = 1 AND id_cidade = {$_GET['id']} GROUP BY valor ORDER BY valor");
} else {
    echo '<option value="">Valor</option>';
    exit;
}

echo '<option value="">Valor</option>';

foreach ($valores->rows as $valor) {
    echo '<option value="' . $sistema->formataMoeda($valor['valor']) . '">' . $sistema->formataMoedaShow($valor['valor']) . '</option>';
}
?>