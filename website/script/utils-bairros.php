<?php

session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

$bairros = $sistema->getBairros($_GET["id"]);

echo '<option value="">Bairro</option>';

foreach ($bairros as $bairro) {
    echo '<option value="' . $bairro['nome'] . '">' . $bairro['nome'] . '</option>';
}
?>
