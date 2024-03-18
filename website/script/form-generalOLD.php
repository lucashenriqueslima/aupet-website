<?php
session_start();

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

$sistema->DB_connect();

$anexo = array("file" => "", "field" => "");
$formulario = $sistema->formularioObjeto($_POST);
$options    = $sistema->formularioObjeto($formulario->options);
$validate   = $sistema->formularioObjeto($formulario->validate);
unset($formulario->options);
unset($formulario->validate);

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

$validacao = validaFormulario($validate);

if (!$validacao->return) {
    echo json_encode($validacao);
} else {

    $resposta = new \stdClass();
    $resposta->time = 4000;

    /**
    * 28/03/2018 | 11:45 
    * Leo Santana (leosantana@hibridaweb.com.br)
    * Problema: Não estava salvando o arquivo anexado ao formulário de Trabalhe Conosco
    * Solução: Correção no IF abaixo.
    * Linhas: 43 a 46
    * Código anterior:
        if ($anexo["file"] != "")
            $formulario->$anexo["field"] = $anexo["file"];
    **/
    if ($anexo["file"] != ""){
        $c = $anexo["field"];
        $formulario->$c = $anexo["file"];
    }

    $campos = $sistema->DB_columns($options->table);

    $fields = array();
    $values = array();

    foreach ($campos as $campo) {
        if (array_key_exists($campo, $formulario)) {
            $fields[] = $campo;
            $values[] = ( array_key_exists("data", $validate->$campo) ) ? '"' . $sistema->datebr($formulario->$campo) . '"' : '"' . $formulario->$campo . '"';
        }
    }

    $fields[] = "ip";
    $fields[] = "session";

    $values[] = '"' . $_SERVER['REMOTE_ADDR'] . '"';
    $values[] = '"' . $_SESSION["seo_session"] . '"';

    $query = $sistema->DB_insert($options->table, implode(',', $fields), implode(',', $values));
    $idInserted = $query->insert_id;

    if ($query->query) {
        $form = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_formularios WHERE id = $options->form")->rows[0];

        // CASO SETADO 'field_lista' ESTE CAMPO SERÁ PEGO PARA ARMAZENAR O CONTATO DO VISITANTE, NO CASO DE NÃO HAVER EMAIL NO FORMULÁRIO
        if (isset($options->field_lista))
            $aux = $options->field_lista;

        $email = (isset($options->field_lista)) ? $formulario->$aux : $formulario->email;

        $verifica = $sistema->DB_num_rows("SELECT * FROM hbrd_cms_email A INNER JOIN hbrd_cms_email_cms_listas B ON B.id_email = A.id AND A.email = '$email' AND B.id_lista = $options->list");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_email WHERE email = '$email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('hbrd_cms_email', "nome,email", "'$formulario->nome','$email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('hbrd_cms_email_cms_listas', "id_lista,id_email", "$options->list,$idEmail");
        }

        // PREPARA EMAIL PARA ENVIAR AOS USUÁRIOS DO SISTEMA QUE ESTÃO CONFIGURADOS
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM hbrd_cms_formularios A INNER JOIN hbrd_cms_notificacoes B ON A.id = B.id_form INNER JOIN hbrd_main_usuarios C ON B.id_usuario = C.id WHERE A.id = 1 OR A.id = $options->form GROUP BY B.id_usuario");
        if ($emails->num_rows) {
            $emailfrom = isset($formulario->email) ? $formulario->email : "";
            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }

            $assunto = "Formulário de $options->form_name.";  //Assunto da mensagem de contato.

            if (isset($formulario->mensagem))
                $formulario->mensagem = trim(str_replace(array('\\r\\n', '\\n', '\\r'), '<br>', $formulario->mensagem));

            $body = file_get_contents("../mailing_templates/form_scaffold.html");
            $body = str_replace("{FORMULARIO}", $options->form_name, $body);
            $body_fields = "";
            foreach ($validate as $field => $data) {
                if ($field != "id_seo")
                    $body_fields .= str_replace("{FIELD_VALUE}", $formulario->$field, str_replace("{FIELD_NAME}", $data["label"], file_get_contents("../mailing_templates/form_fields.html")));
            }
            $body = str_replace("{CONTENT}", $body_fields, $body);

            $sistema->enviarEmail($to, $emailfrom, utf8_decode($assunto), utf8_decode($body));
        }

        if (isset($formulario->id_seo) AND ! empty($formulario->id_seo)) {
            if ($options->form == 6) {
                $sistema->DB_update("hbrd_cms_seo_acessos", "contato = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                $sistema->DB_update("hbrd_cms_seo_acessos_historico", "contato = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            } else if ($options->form == 9) {
                $sistema->DB_update("hbrd_cms_seo_acessos", "ligacao = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                $sistema->DB_update("hbrd_cms_seo_acessos_historico", "ligacao = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            } else if ($options->form == 7) {
                $sistema->DB_update("hbrd_cms_seo_acessos", "venda_terreno = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                $sistema->DB_update("hbrd_cms_seo_acessos_historico", "venda_terreno = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            } else if ($options->form == 8) {
                $sistema->DB_update("hbrd_cms_seo_acessos", "trabalhe_conosco = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                $sistema->DB_update("hbrd_cms_seo_acessos_historico", "trabalhe_conosco = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            } else {
                $hotsites = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_empreendimentos WHERE stats = 1");
                if ($hotsites->num_rows) {
                    foreach ($hotsites->rows as $hotsite) {
                        if ($hotsite['id_form_cadastro'] == $options->form) {
                            $sistema->DB_update("hbrd_cms_seo_acessos", "cadastro_hotsite = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                            $sistema->DB_update("hbrd_cms_seo_acessos_historico", "cadastro_hotsite = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                            break;
                        }else if ($hotsite['id_form_indique'] == $options->form) {
                            $sistema->DB_update("hbrd_cms_seo_acessos", "indique_hotsite = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                            $sistema->DB_update("hbrd_cms_seo_acessos_historico", "indique_hotsite = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
                            break;
                        }
                    }
                }
            }
        }

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = ($form["mensagem"] != "" AND $form["mensagem"] != NULL) ? $form["mensagem"] : "Mensagem enviada com sucesso, em breve entraremos em contato!";
        $sistema->inserirRelatorio("$options->form_name: [" . $email . "] Id: [" . $idInserted . "]");
    } else {
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function validaFormulario($validade) {

    $resposta = new stdClass();
    $resposta->return = true;

    global $sistema, $formulario, $anexo, $options;

    foreach ($validade as $field => $data) {
        $custom = (isset($data["custom"])) ? $data["custom"] : "";
        if (isset($data["required"]) AND /* isset($formulario->field) AND */ $formulario->$field == "") {
            $resposta->type = $data["type"];
            $resposta->message = isset($data["custom_msg"]) ? $data["custom_msg"] : "Preencha este campo corretamente";
            $resposta->field = $field;
            $resposta->return = false;
            return $resposta;
        } else if (isset($data["custom"]) AND ! $sistema->$custom($formulario->$field)) {
            $resposta->type = $data["type"];
            $resposta->message = isset($data["custom_msg"]) ? $data["custom_msg"] : "Preencha este campo corretamente";
            $resposta->field = $field;
            $resposta->return = false;
            return $resposta;
        } else if (isset($data["upload"])) {
            if (is_uploaded_file($_FILES[$field]["tmp_name"])) {

                $upload = $sistema->uploadFile($field, explode(",", $data["upload"]), '');
                if ($upload->return) {
                    $anexo["file"] = $upload->file_uploaded;
                    $anexo["field"] = $field;
                }
            }

            if (isset($upload) && !$upload->return) {
                $resposta->type = "attention";
                $resposta->message = $upload->message;
                $resposta->field = $field;
                $resposta->return = false;
                return $resposta;
            }
        }
    }

    return $resposta;
}

?>