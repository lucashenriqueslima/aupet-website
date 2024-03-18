<?php

session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

$bairros = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_cms_empreendimentos_bairros A INNER JOIN hbrd_cms_empreendimentos B ON A.id = B.id_bairro WHERE A.id_cidade = {$_GET['id']} GROUP BY A.id ORDER BY A.nome");

echo '<option value="">Bairro</option>';

foreach ($bairros->rows as $bairro) {
    echo '<option data-id=' . $bairro['id'] . ' value="' . $sistema->formataUrlAmiga($bairro['seo_url']) . '">' . $bairro['nome'] . '</option>';
}
?>