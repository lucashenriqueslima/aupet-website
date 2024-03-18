<?php

session_start();

require_once '../System/Core/Loader.php';

require_once "../System/Core/General.php";

$sistema = new General();

$id = $_POST["id"];

$cidades = $sistema->getCidades($id);

echo '<option value="">Selecione...</option>';

foreach ($cidades as $cidade) {
    echo '<option value="' . $cidade['id'] . '">' . $cidade['cidade'] . '</option>';
}
?>