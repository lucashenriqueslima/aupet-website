<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_espaco_arquivos_solicitacoes";

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
        'id_usuario',
        'mensagem',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->id_seo . '"',
        '"' . $_SESSION['corretor_id'] . '"',
        '"' . $formulario->mensagem . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );

    $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
    

    if ($query->query) {
        $idContato = $query->insert_id;

        // PREPARA EMAIL -------------------
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 21 GROUP BY B.id_user");
        $usuarios = $sistema->DB_fetch_array("SELECT * FROM tb_espaco_usuarios A WHERE A.id_grupo = 1");

        if ($emails->num_rows || $usuarios->num_rows) {

            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }

            if ($usuarios->num_rows) {
                foreach ($usuarios->rows as $usuario) {
                    $to[] = array("email" => $usuario['email'], "nome" => utf8_decode($usuario['nome']));
                }
            }
        }
        $assunto = "Formulário de Solicitação de Arquivos.";  //Assunto da mensagem de contato.

        $linksolicitacao = "<a href='" . $sistema->system_path . "spacebrokerfilesolicitation/edit/id/$idContato'>" . $sistema->system_path . "spacebrokerfilesolicitation/edit/id/$idContato</a>";
        $linkcorretor = "<a href='" . $sistema->system_path . "spacebrokeruser/edit/id/{$_SESSION['corretor_id']}'>" . $sistema->system_path . "spacebrokeruser/edit/id/{$_SESSION['corretor_id']}</a>";

        $mensagem = str_replace(array('\\n', '\\r'), '<br>', $formulario->mensagem);
        
        $body = file_get_contents("../mailing_templates/form_solicitar_arquivo.html");
        $body = str_replace("{NOME}", $_SESSION['corretor_nome'], $body);
        $body = str_replace("{EMAIL}", $_SESSION['corretor_email'], $body);
        $body = str_replace("{LINKSOLICITACAO}", $linksolicitacao, $body);
        $body = str_replace("{LINKCORRETOR}", $linkcorretor, $body);
        $body = str_replace("{MENSAGEM}", $mensagem, $body);

        if ($sistema->enviarEmail($to, $_SESSION['corretor_email'], utf8_decode($assunto), utf8_decode($body))) {
            $resposta->type = "success";
            $resposta->time = 4000;
            $resposta->message = "Solicitação registrada com sucesso, em breve entraremos em contato!";
            $sistema->inserirRelatorio("Solicitação de Arquivo - Espaço do Corretor id: [" . $idContato . "] Corretor:  [" . $_SESSION['corretor_nome'] . "] Id Usuário: [" . $_SESSION['corretor_id'] . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
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

    if ($form->mensagem == "") {
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