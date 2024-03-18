<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_indique_um_amigo";

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
        'amigo_nome',
        'amigo_email',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->id_seo . '"',
        '"' . $formulario->nome . '"',
        '"' . $formulario->email . '"',
        '"' . $formulario->amigo_nome . '"',
        '"' . $formulario->amigo_email . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );


    $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
    $idContato = $query->insert_id;

    if ($query->query) {

        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 8");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "8,$idEmail");
        }
        
        $dados = new stdClass();
        
        $dados->nome = $formulario->nome;
        $dados->email = $formulario->email;
         
        $dados->id_lista = 134;
        $dados->origem = "lista";

        $mkt->getEmailMarketing("salvar-email", $dados);

        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->amigo_email' AND B.id_lista = 7");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->amigo_email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->amigo_nome','$formulario->amigo_email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "7,$idEmail");
        }
        
        $dados = new stdClass();
        
        $dados->nome = $formulario->amigo_nome;
        $dados->email = $formulario->amigo_email;
         
        $dados->id_lista = 133;
        $dados->origem = "lista";

        $mkt->getEmailMarketing("salvar-email", $dados);

        $to[] = array("email" => $formulario->amigo_email, "nome" => utf8_decode($formulario->amigo_nome));

        $assunto = "Indicação de um amigo";  // Assunto da mensagem de contato.

        $body = file_get_contents("../mailing_templates/form_indique_um_amigo.html");
        $body = str_replace("{NOME}", $formulario->nome, $body);
        $body = str_replace("{EMAIL}", $formulario->email, $body);
        $body = str_replace("{AMIGONOME}", $formulario->amigo_nome, $body);
        $body = str_replace("{AMIGOEMAIL}", $formulario->amigo_email, $body);
        $body = str_replace("{EMPREENDIMENTO}", $formulario->empreendimento, $body);
        $body = str_replace("{LINK}", $formulario->link, $body);


        $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));

        unset($to);
        $to[] = array("email" => $formulario->email, "nome" => utf8_decode($formulario->nome));

        $assunto = "Cópia - Indicação de um amigo";  // Assunto da mensagem de contato.

        $body = file_get_contents("../mailing_templates/form_indique_um_amigo.html");
        $body = str_replace("{NOME}", $formulario->nome, $body);
        $body = str_replace("{EMAIL}", $formulario->email, $body);
        $body = str_replace("{AMIGONOME}", $formulario->amigo_nome, $body);
        $body = str_replace("{AMIGOEMAIL}", $formulario->amigo_email, $body);
        $body = str_replace("{EMPREENDIMENTO}", $formulario->empreendimento, $body);
        $body = str_replace("{LINK}", $formulario->link, $body);


        $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Indicação enviada com sucesso!";
        $sistema->inserirRelatorio("Agendamento: [" . $formulario->email . "] Id: [" . $idContato . "]");
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
    } else if ($form->amigo_nome == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "amigo_nome";
        $resposta->return = false;
        return $resposta;
    } else if ($form->amigo_email == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "amigo_email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->amigo_email) == 0) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo com um E-mail válido";
        $resposta->field = "amigo_email";
        $resposta->return = false;
        return $resposta;
    } else {

        if (isset($form->codigo)) {
            if (strtolower($form->codigo) != strtolower($_SESSION["securimage_code_value"]["default"])) {
                $resposta->type = "validation";
                $resposta->message = "Código inválido";
                $resposta->field = "codigo";
                $resposta->return = false;
                return $resposta;
                exit;
            } else {
                return $resposta;
            }
        } else {
            return $resposta;
        }
    }
}

?>