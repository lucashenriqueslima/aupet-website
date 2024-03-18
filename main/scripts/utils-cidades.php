<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$cidades = $sistema->getCidades($_GET["id"]);

echo '<option value="">Selecione...</option>';

foreach ($cidades as $cidade) {
    echo '<option value="' . $cidade['cidade'] . '">' . $cidade['cidade'] . '</option>';
}
?>