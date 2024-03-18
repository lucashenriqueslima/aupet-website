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

    $assunto = "Formulário de Senha do sistema de arquivos.";  //Assunto da mensagem de contato.

    $query = $sistema->DB_fetch_array("SELECT * FROM $main_table WHERE email = '$formulario->email'");
    $usuario = $query->rows[0];


    $to[] = array("email" => $usuario['email'], "nome" => utf8_decode($usuario['nome']));

    $body = "
        
        Você solicitou um lembrete de senha do sistema de arquivos da " . $sistema->_empresa['nome'] . "!
        <br><br>
        A sua senha é: <b>" . $sistema->desembaralhar($usuario['senha']) . "</b>
        <br><br>
        Obrigado!
        
    ";

    if ($sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body))) {
        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "A sua senha foi enviada para o e-mail $formulario->email";
        $sistema->inserirRelatorio("Recuperação de senha: [" . $usuario['email'] . "] Id: [" . $usuario['id'] . "]");
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

    //$sistema = new sistema();
    global $sistema, $main_table;

    if ($form->email != "") {
        $usuarios = $sistema->DB_fetch_array("SELECT * FROM $main_table WHERE email = '$form->email'");
    }

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
    } else if (!$usuarios->num_rows) {
        $resposta->type = "validation";
        $resposta->message = "E-mail não encontrado em nossa base de dados!";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>