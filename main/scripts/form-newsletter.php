<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

use System\Libs\EmailMarketingCliente;

$mkt = new EmailMarketingCliente();

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_newsletters_newsletters";

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

    $fields = array(
        'nome',
        'email',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->nome . '"',
        '"' . $formulario->email . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );

    if (isset($formulario->id_seo)) {
        $fields[] = "id_seo";
        $values[] = $formulario->id_seo;
    }

    $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
    $idContato = $query->insert_id;

    if ($query->query) {
        
        $formulario->id_lista = 137;
        $formulario->origem = "lista";

        $mkt->getEmailMarketing("salvar-email", $formulario);

        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 2");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "2,$idEmail");
        }


        // PREPARA EMAIL -------------------
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 8 GROUP BY B.id_user");

        if ($emails->num_rows) {

            foreach ($emails->rows as $mail) {
                $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
            }

            $assunto = "Formulário Newsletter.";  // Assunto da mensagem de contato.

            $body = file_get_contents("../mailing_templates/form_newsletter.html");
            $body = str_replace("{NOME}", $formulario->nome, $body);
            $body = str_replace("{EMAIL}", $formulario->email, $body);

            $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
        }

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "E-mail cadastrado com sucesso!";
        $sistema->inserirRelatorio("Newsletter: [" . $formulario->email . "] Id: [" . $idContato . "]");
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



    if ($form->nome == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "nome";
        $resposta->return = false;
        return $resposta;
    } else if ($form->email == "") {
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
    } else {
        return $resposta;
    }
}

?>