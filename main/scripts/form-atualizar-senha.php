<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_espaco_usuarios";

$formulario = $sistema->formularioObjeto($_POST);

$validacao = validaFormulario($formulario);

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {
    $resposta = new stdClass();
    $resposta->time = 4000;

    $formulario = $sistema->formularioObjeto($_POST);

    $formulario->senha_nova = $sistema->embaralhar($formulario->senha_nova);

    $query = $sistema->DB_update($main_table, "senha = '$formulario->senha_nova' WHERE id = {$_SESSION['corretor_id']}");
    if ($query) {
        $resposta->type = "success";
        $resposta->message = "Senha alterada com sucesso!";
        $sistema->inserirRelatorio("Corretor alterou a senha, nome: [{$_SESSION['corretor_nome']}], e-mail: [{$_SESSION['corretor_email']}], id: [{$_SESSION['corretor_id']}]");
    } else {
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function validaFormulario($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    global $sistema, $main_table;

    $query = $sistema->DB_fetch_array("SELECT * FROM $main_table WHERE id = {$_SESSION['corretor_id']}");
    if ($sistema->desembaralhar($query->rows[0]['senha']) != $form->senha_atual) {
        $resposta->type = "validation";
        $resposta->message = "Senha atual não confere";
        $resposta->field = "senha_atual";
        $resposta->return = false;
        return $resposta;
    } else if ($form->senha_nova == "" || ($form->senha_nova != $form->senha_nova_)) {
        $resposta->type = "validation";
        $resposta->message = "Senhas não conferem";
        $resposta->field = "senha_nova";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>