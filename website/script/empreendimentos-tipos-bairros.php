<?php

session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

if (isset($_GET['id']) AND ! empty($_GET['id'])) {
    $tipos = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_cms_empreendimentos_tipos A INNER JOIN hbrd_cms_empreendimentos_cms_empreendimentos_tipos B ON B.id_tipo = A.id INNER JOIN hbrd_cms_empreendimentos C ON C.id = B.id_empreendimento WHERE C.id_bairro = {$_GET['id']} AND C.stats = 1 GROUP BY A.id ORDER BY A.nome");
} else {
    $tipos = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_cms_empreendimentos_tipos A INNER JOIN hbrd_cms_empreendimentos_cms_empreendimentos_tipos B ON B.id_tipo = A.id INNER JOIN hbrd_cms_empreendimentos C ON C.id = B.id_empreendimento WHERE C.stats = 1 GROUP BY A.id ORDER BY A.nome");
}

echo '<option value="">Tipo</option>';

foreach ($tipos->rows as $tipo) {
    echo '<option data-id=' . $tipo['id'] . ' value="' . $sistema->formataUrlAmiga($tipo['seo_url']) . '">' . $tipo['nome'] . '</option>';
}
?>