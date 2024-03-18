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
    $mensagem=$formulario->mensagem;
    //$mensagem = str_replace(array('\\n', '\\r'), '<br>', $formulario->mensagem);

    $fields = array(
        'nome',
        'email',
        'fone',
        'celular',
        'estado',
        'cidade',
        'mensagem',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->nome . '"',
        '"' . $formulario->email . '"',
        '"' . $formulario->fone . '"',
        '"' . $formulario->celular . '"',
        '"' . $formulario->estado . '"',
        '"' . $formulario->cidade . '"',
        '"' . $mensagem . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );


    $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
    $idContato = $query->insert_id;

    if ($query->query) {


        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 1");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "1,$idEmail");
        }


        // PREPARA EMAIL -------------------
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 2 GROUP BY B.id_user");
        if ($emails->num_rows) {

            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }

            $assunto = "Formulário de Contato.";  //Assunto da mensagem de contato.

            $mensagem = trim(str_replace(array('\\r\\n','\\n', '\\r'), '<br>', $formulario->mensagem));

            $body = file_get_contents("../mailing_templates/form_contato.html");
            //$body = str_replace("{IP}", $_SERVER['REMOTE_ADDR'], $body);
            $body = str_replace("{NOME}", $formulario->nome, $body);
            $body = str_replace("{EMAIL}", $formulario->email, $body);
            $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
            $body = str_replace("{CELULAR}", $formulario->celular, $body);
            $body = str_replace("{ESTADO}", $formulario->celular, $body);
            $body = str_replace("{CIDADE}", $formulario->celular, $body);
            $body = str_replace("{MENSAGEM}", $mensagem, $body);

            $sistema->DB_update("tb_seo_acessos", "contato = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            $sistema->DB_update("tb_seo_acessos_historicos", "contato = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");

            $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
        }

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Mensagem enviada com sucesso, em breve entraremos em contato!";
        $sistema->inserirRelatorio("Contato: [" . $formulario->email . "] Id: [" . $idContato . "]");
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
    } else if ($form->fone == "" AND $form->celular == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "fone";
        $resposta->return = false;
        return $resposta;
    } else if ($form->estado == "") {
        $resposta->type = "validation";
        $resposta->message = "Informe o estado";
        $resposta->field = "estado";
        $resposta->return = false;
        return $resposta;
    } else if ($form->cidade == "") {
        $resposta->type = "validation";
        $resposta->message = "Informe a cidade";
        $resposta->field = "cidade";
        $resposta->return = false;
        return $resposta;
    } else if (str_replace(array("\\n","\\r","\\r\\n"), '', $form->mensagem) == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "mensagem";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>