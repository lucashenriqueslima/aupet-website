<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

use System\Libs\EmailMarketingCliente;

$mkt = new EmailMarketingCliente();

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_agendamentos_agendamentos";

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

    $formulario->dia_visita = $sistema->formataDataDeMascara($formulario->dia_visita);

    $fields = array(
        'id_seo',
        'nome',
        'email',
        'telefone',
        'celular',
        'endereco',
        'cidade',
        'estado',
        'dia_visita',
        'hora_visita',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->id_seo . '"',
        '"' . $formulario->nome . '"',
        '"' . $formulario->email . '"',
        '"' . $formulario->telefone . '"',
        '"' . $formulario->celular . '"',
        '"' . $formulario->endereco . '"',
        '"' . $formulario->cidade . '"',
        '"' . $formulario->estado . '"',
        '"' . $formulario->dia_visita . '"',
        '"' . $formulario->hora_visita . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );

    $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
    $idContato = $query->insert_id;

    if ($query->query) {


        $formulario->id_lista = 132;
        $formulario->origem = "lista";

        $mkt->getEmailMarketing("salvar-email", $formulario);


        $produto = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE id_seo = $formulario->id_seo");
        if ($produto->num_rows) {
            $list = $produto->rows[0]['id_lista'];
            
            $formulario->id_lista = $produto->rows[0]['ws_id_lista'];
            $formulario->origem = "lista";

            $mkt->getEmailMarketing("salvar-email", $formulario);
            
            
            $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = $list");
            if (!$verifica) {
                $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
                if (!$verifica->num_rows) {
                    $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                    $idEmail = $addEmail->insert_id;
                } else {
                    $idEmail = $verifica->rows[0]['id'];
                }
                $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "$list,$idEmail");
            }
        }

        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 6");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "6,$idEmail");
        }

        $whereNotification = "";
        if ($produto->num_rows)
            $whereNotification = " OR A.id = {$produto->rows[0]['id_form']} ";

        // PREPARA EMAIL -------------------
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 19 $whereNotification GROUP BY B.id_user");
        $moreNotify = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_produtos_produtos A INNER JOIN tb_admin_email_notification_produtos_cadastros B ON B.id_form = A.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id_seo = $formulario->id_seo");

        if ($emails->num_rows || $moreNotify->num_rows) {

            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }

            if ($moreNotify->num_rows) {
                foreach ($moreNotify->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }


            $assunto = "Formulário Agende uma Visita.";  // Assunto da mensagem de contato.

            $body = file_get_contents("../mailing_templates/form_agende_visita.html");
            $body = str_replace("{NOME}", $formulario->nome, $body);
            $body = str_replace("{EMAIL}", $formulario->email, $body);
            $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
            $body = str_replace("{CELULAR}", $formulario->celular, $body);
            $body = str_replace("{ENDERECO}", $formulario->endereco, $body);
            $body = str_replace("{CIDADE}", $formulario->cidade, $body);
            $body = str_replace("{ESTADO}", $formulario->estado, $body);
            $body = str_replace("{DIAVISITA}", $sistema->formataDataDeBanco($formulario->dia_visita), $body);
            $body = str_replace("{HORAVISITA}", $formulario->hora_visita, $body);


            if (isset($produto->num_rows) && $produto->num_rows) {
                $body = str_replace("{HOTSITE}", $produto->rows[0]['titulo'], $body);
            } else {
                $body = str_replace("{HOTSITE}", '', $body);
            }

            $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
        }

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Agendamento efetuado com sucesso, aguarde nosso contato!";
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
    } else if ($form->dia_visita == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "dia_visita";
        $resposta->return = false;
        return $resposta;
    } else if (!$sistema->checkdate($form->dia_visita)) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "dia_visita";
        $resposta->return = false;
        return $resposta;
    } else if ($form->hora_visita == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "hora_visita";
        $resposta->return = false;
        return $resposta;
    } else if ($form->codigo == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "codigo";
        $resposta->return = false;
        return $resposta;
    } else if (strtolower($form->codigo) != strtolower($_SESSION["securimage_code_value"]["default"])) {
        $resposta->type = "validation";
        $resposta->message = "Código inválido";
        $resposta->field = "codigo";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>