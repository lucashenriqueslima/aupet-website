<?php

session_start();

require_once '../System/Core/Loader.php';

use System\Core\General;

require_once "../System/Core/General.php";

$sistema = new General();

$cidades = $sistema->getCidades($_GET["id"]);

echo '<option value="">Selecione a Cidade</option>';

foreach ($cidades as $cidade) {
    echo '<option value="' . $cidade['id'] . '">' . $cidade['cidade'] . '</option>';
}
?>