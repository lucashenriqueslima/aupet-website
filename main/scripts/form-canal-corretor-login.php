<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_contatos_contatos";

$formulario = $sistema->formularioObjeto($_POST);

$validacao = validaFormulario($formulario);

$crop_sizes = array();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {
    $resposta = new stdClass();
    $resposta->time = 4000;

    $formulario = $sistema->formularioObjeto($_POST, 'tb_espaco_usuarios');

    $query = $sistema->DB_fetch_array("SELECT * FROM tb_espaco_usuarios WHERE email = '$formulario->email' AND CASE WHEN expiracao IS NOT NULL THEN expiracao >= CURDATE() ELSE 1 = 1 END");
    if ($query->num_rows) {
        if ($sistema->desembaralhar($query->rows[0]['senha']) == $formulario->senha) {

            $_SESSION['corretor_logado'] = true;
            $_SESSION['corretor_id'] = $query->rows[0]['id'];
            $_SESSION['corretor_nome'] = $query->rows[0]['nome'];
            $_SESSION['corretor_email'] = $query->rows[0]['email'];
            $_SESSION['corretor_id_grupo'] = $query->rows[0]['id_grupo'];

            $resposta->type = "success";
            $resposta->redirect = $sistema->seo_pages["canal-do-corretor-logado"]["seo_url"];
            $resposta->message = $_SESSION['corretor_nome'] . ", Seja Bem Vindo(a)!";
            $sistema->inserirRelatorio("Corretor Logou, nome: [{$query->rows[0]['nome']}], e-mail: [{$query->rows[0]['email']}], id: [{$query->rows[0]['id']}]");


            $agora = date('Y-m-d H:i:s');
            $sistema->DB_update("tb_espaco_usuarios", "last_login = '$agora' WHERE id = {$_SESSION['corretor_id']}");

            $_SESSION['corretor_last_login'] = $agora;
        } else {
            $resposta->type = "attention";
            $resposta->message = "Senha não confere!";
        }
    } else {
        $resposta->type = "error";
        $resposta->message = "Usuário não econtrado!";
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function validaFormulario($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    global $sistema, $main_table;

    if ($form->email == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo com um E-mail válido";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($form->senha == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "senha";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>