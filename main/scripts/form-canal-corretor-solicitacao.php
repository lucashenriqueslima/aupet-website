<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_espaco_solicitacoes";

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
        'id_seo',
        'nome',
        'email',
        'telefone',
        'celular',
        'creci',
        'imobiliaria',
        'mensagem',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->id_seo . '"',
        '"' . $formulario->nome . '"',
        '"' . $formulario->email . '"',
        '"' . $formulario->telefone . '"',
        '"' . $formulario->celular . '"',
        '"' . $formulario->creci . '"',
        '"' . $formulario->imobiliaria . '"',
        '"' . $formulario->mensagem . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );

    $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
    $idContato = $query->insert_id;

    if ($query->query) {
        
        $sistema->DB_update("tb_seo_acessos", "solicitacao = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
        $sistema->DB_update("tb_seo_acessos_historicos", "solicitacao = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");



        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 5");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "5,$idEmail");
        }


        // PREPARA EMAIL -------------------
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 6 GROUP BY B.id_user");

        if ($emails->num_rows) {

            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }
            $assunto = "Formulário Cadastre sua Solicitação.";  // Assunto da mensagem de contato.

            $mensagem = str_replace(array('\\n', '\\r'), '<br>', $formulario->mensagem);

            $body = file_get_contents("../mailing_templates/form_espaco_solicitacao.html");
            $body = str_replace("{NOME}", $formulario->nome, $body);
            $body = str_replace("{EMAIL}", $formulario->email, $body);
            $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
            $body = str_replace("{CELULAR}", $formulario->celular, $body);
            $body = str_replace("{CRECI}", $formulario->creci, $body);
            $body = str_replace("{IMOBILIARIA}", $formulario->imobiliaria, $body);
            $body = str_replace("{MENSAGEM}", $mensagem, $body);



            $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
        }

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Solicitação enviada com sucesso, aguarde nosso contato!";
        $sistema->inserirRelatorio("Solicitação: [" . $formulario->email . "] Id: [" . $idContato . "]");
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
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo nome";
        $resposta->field = "nome";
        $resposta->return = false;
        return $resposta;
    } else if ($form->email == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo e-mail";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo e-mail com um endereço válido";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($form->telefone == "" AND $form->celular == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo telefone ou celular";
        $resposta->field = "telefone";
        $resposta->return = false;
        return $resposta;
    } else if ($form->creci == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo creci";
        $resposta->field = "creci";
        $resposta->return = false;
        return $resposta;
    } else if ($form->imobiliaria == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo imobiliária";
        $resposta->field = "imobiliaria";
        $resposta->return = false;
        return $resposta;
    } else if (str_replace(array("\\n","\\r","\\r\\n"), '', $form->mensagem) == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo mensagem";
        $resposta->field = "mensagem";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>