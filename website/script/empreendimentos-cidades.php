<?php

session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

$cidades = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_main_cidades A INNER JOIN hbrd_cms_empreendimentos B ON A.id = B.id_cidade WHERE A.id_estado = {$_GET['id']} GROUP BY A.id ORDER BY A.cidade");

echo '<option value="">Cidade</option>';

foreach ($cidades->rows as $cidade) {
    echo '<option data-id="'.$cidade['id'].'" value="' . $cidade['seo_url'] . '">' . $cidade['cidade'] . '</option>';
}
?>