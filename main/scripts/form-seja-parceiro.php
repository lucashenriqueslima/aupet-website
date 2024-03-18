<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_seja_parceiro";

$formulario = $sistema->formularioObjeto($_POST);

$validacao = validaFormulario($formulario);

$crop_sizes = array();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

$anexo = "";

if (!$validacao->return) {
    echo json_encode($validacao);
} else {

    $validacao = validaUpload($formulario);

    if (!$validacao->return) {
        echo json_encode($validacao);
    } else {
        $resposta = new stdClass();
        $resposta->time = 4000;

        if ($anexo != "") {
            $formulario->arquivo = $anexo;
        } else {
            $formulario->arquivo = "";
        }

        $mensagem = $formulario->mensagem;

        $fields = array(
            'nome',
            'email',
            'cpf',
            'fone',
            'celular',
            'estado',
            'cidade',
            'mensagem',
            'foto',
            'ip',
            'session'
        );

        $values = array(
            '"' . $formulario->nome . '"',
            '"' . $formulario->email . '"',
            '"' . $formulario->cpf . '"',
            '"' . $formulario->fone . '"',
            '"' . $formulario->celular . '"',
            '"' . $formulario->estado . '"',
            '"' . $formulario->cidade . '"',
            '"' . $mensagem . '"',
            '"' . $formulario->arquivo . '"',
            '"' . $_SERVER['REMOTE_ADDR'] . '"',
            '"' . $_SESSION["seo_session"] . '"'
        );


        $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
        $idContato = $query->insert_id;

        if ($query->query) {

            $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 4");
            if (!$verifica) {
                $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
                if (!$verifica->num_rows) {
                    $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                    $idEmail = $addEmail->insert_id;
                } else {
                    $idEmail = $verifica->rows[0]['id'];
                }
                $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "3,$idEmail");
            }


            // PREPARA EMAIL -------------------
            $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 4 GROUP BY B.id_user");
            if ($emails->num_rows) {

                if ($emails->num_rows) {
                    foreach ($emails->rows as $mail) {
                        $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                    }
                }

                $assunto = "Formulário Seja um Revendedor.";  //Assunto da mensagem de contato.

                $mensagem = str_replace(array('\\n', '\\r'), '<br>', $formulario->mensagem);

                $body = file_get_contents("../mailing_templates/form_seja_parceiro.html");
                $body = str_replace("{NOME}", $formulario->nome, $body);
                $body = str_replace("{EMAIL}", $formulario->email, $body);
                $body = str_replace("{CPF}", $formulario->cpf, $body);
                $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
                $body = str_replace("{CELULAR}", $formulario->celular, $body);
                $body = str_replace("{ESTADO}", $formulario->estado, $body);
                $body = str_replace("{CIDADE}", $formulario->cidade, $body);
                $body = str_replace("{MENSAGEM}", $mensagem, $body);

                $arquivo['arquivo'] = $anexo;
                $arquivo['nome'] = utf8_decode("Foto do local de $formulario->nome");

                $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body), '', $arquivo);
            }

            $resposta->type = "success";
            $resposta->time = 4000;
            $resposta->message = "Mensagem enviada com sucesso, em breve entraremos em contato!";
            $sistema->inserirRelatorio("Seja um Parceiro: [" . $formulario->email . "] Id: [" . $idContato . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }
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
    } else if ($form->cpf == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cpf";
        $resposta->return = false;
        return $resposta;
    } else if (!$sistema->validaCPF($form->cpf)) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo com um CPF válido";
        $resposta->field = "cpf";
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

function validaUpload($form) {

    global $sistema, $anexo, $crop_sizes;

    $resposta = new \stdClass();
    $resposta->return = true;

    if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {

        $upload = $sistema->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $crop_sizes);
        if ($upload->return) {
            $anexo = $upload->file_uploaded;
        }
    }

    if (isset($upload) && !$upload->return) {
        $resposta->type = "attention";
        $resposta->message = $upload->message;
        $resposta->field = "foto-anexo";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>