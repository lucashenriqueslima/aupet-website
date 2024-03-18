<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$table1 = "tb_espaco_arquivos";
$table2 = "tb_espaco_arquivos_has_tb_espaco_categorias";
$table3 = "tb_espaco_arquivos_has_tb_espaco_grupos";
$table4 = "tb_espaco_arquivos_has_tb_espaco_usuarios";

$formulario = $sistema->formularioObjeto($_POST);

$ids = [];
$arquivo = "";
$arquivo_size = "";

function getIds($id) {
    global $sistema, $ids, $table1, $table2, $table3;
    $query = $sistema->DB_fetch_array("SELECT * FROM tb_espaco_categorias WHERE id = $id");
    if ($query->num_rows) {
        foreach ($query->rows as $row) {
            $ids[] = $row['id'];
        }
        if ($row['id_pai'] != "")
            return getIds($row['id_pai']);
    }
}

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}


$validacao = validaFormulario($formulario);
if (!$validacao->return) {
    echo json_encode($validacao);
} else {
    $validacao = validaUpload($formulario);
    if (!$validacao->return) {
        echo json_encode($validacao);
    } else {

        $resposta = new stdClass();
        $resposta->time = 4000;

        $data = $sistema->formularioObjeto($_POST, $table1);

        if (isset($_POST['categoria'])) {
            for ($i = 0; $i < count($_POST['categoria']); $i++) {
                getIds($_POST['categoria'][$i]);
            }
        }

        if (count($ids) < 1) {
            $resposta->type = "attention";
            $resposta->message = "Selecione ao menos uma Categoria!";
            $resposta->field = "titulo";
            $resposta->return = false;
            echo json_encode($resposta);
            exit();
        }

        $data->id_usuario_pai = $_SESSION['corretor_id'];
        $data->arquivo = $arquivo;
        $data->tamanho = $arquivo_size;

        foreach ($data as $key => $value) {
            $fields[] = $key;
            if ($value == "NULL")
                $values[] = "$value";
            else
                $values[] = "'$value'";
        }

        $query = $sistema->DB_insert($table1, implode(',', $fields), implode(',', $values));
        $idArquivo = $query->insert_id;

        unset($fields, $values);

        if ($query->query) {

            foreach ($ids as $id_categoria) {
                $sistema->DB_insert($table2, "id_arquivo, id_categoria", "$idArquivo, $id_categoria");
            }

            $sistema->DB_insert($table3, "id_arquivo, id_grupo", "$idArquivo, {$_SESSION['corretor_id_grupo']}");

            $sistema->DB_insert($table4, "id_arquivo, id_usuario", "$idArquivo, {$_SESSION['corretor_id']}");


            notificacoes($idArquivo, true);

            $resposta->type = "success";
            $resposta->time = 4000;
            $resposta->message = "Arquivo enviado com sucesso!";
            $sistema->inserirRelatorio("Cadastrou arquivo do espaço do corretor: [" . $data->titulo . "], id [$idArquivo]");
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

    if ($form->titulo == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "titulo";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

function tamanhoArquivo($arquivo) {
    $tamanhoarquivo = $arquivo['size'];

    /* Medidas */
    $medidas = array(' kb', ' mb', ' gb', ' tb');

    /* Se for menor que 1KB arredonda para 1KB */
    if ($tamanhoarquivo < 999) {
        $tamanhoarquivo = 1000;
    }

    for ($i = 0; $tamanhoarquivo > 999; $i++) {
        $tamanhoarquivo /= 1024;
    }

    return round($tamanhoarquivo) . $medidas[$i - 1];
}

function validaUpload($form) {
    global $sistema, $arquivo, $arquivo_size;

    $resposta = new \stdClass();
    $resposta->return = true;


    if (is_uploaded_file($_FILES["arquivo"]["tmp_name"])) {

        $upload = $sistema->uploadFile("arquivo", array("rar", "zip", "tar", "pdf", "doc", "xls", "xlsx", "txt", "odt", "jpg", "jpeg", "gif", "png"), "");
        if ($upload->return) {
            $arquivo = $upload->file_uploaded;
        }
    }

    if (isset($_FILES["arquivo"]["size"]))
        $arquivo_size = tamanhoArquivo($_FILES["arquivo"]);

    if (!isset($upload)) {
        $resposta->type = "attention";
        $resposta->message = "Arquivo não selecionado.";
        $resposta->return = false;
        return $resposta;
    } else if (isset($upload) && !$upload->return) {
        $resposta->type = "attention";
        $resposta->message = $upload->message;
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

function notificacoes($idArquivo, $new = false) {
    global $sistema, $table1, $table2, $table3;
    $cats = "";
    $categorias = $sistema->DB_fetch_array("SELECT * FROM tb_espaco_categorias A INNER JOIN $table2 B ON B.id_categoria = A.id INNER JOIN tb_espaco_arquivos C ON C.id = B.id_arquivo WHERE C.id = $idArquivo AND A.id_pai IS NULL");
    if ($categorias->num_rows) {
        foreach ($categorias->rows as $categoria) {
            $cats .= "<b>*" . $categoria['nome'] . "</b><br>";
            if (subcat($idArquivo, $categoria['id_categoria']))
                $cats .= subcat($idArquivo, $categoria['id_categoria'], null, $categoria['nome']);
        }
    }

    $file = explode("/", $categorias->rows[0]['arquivo']);
    $file = end($file);

    if ($new)
        $arquivo = "O arquivo {$categorias->rows[0]['titulo']} ($file) foi adicionado ao sistema com permissão a você! Este encontra-se na(s) seguinte(s) categoria(s):<br><br>";
    else
        $arquivo = "O arquivo {$categorias->rows[0]['titulo']} ($file) foi alterado e agora você possui permissão para acessá-lo! Caso já possua permissão para ele, por favor desconsidere este email. O arquivo encontra-se na(s) seguinte(s) categoria(s):<br><br>";

    $usuarios = $sistema->DB_fetch_array("SELECT * FROM tb_espaco_usuarios A INNER JOIN tb_espaco_arquivos_has_tb_espaco_usuarios B ON B.id_usuario = A.id WHERE B.id_arquivo = $idArquivo");
    if ($usuarios->num_rows) {
        foreach ($usuarios->rows as $usuario) {
            $to[] = array("email" => $usuario['email'], "nome" => utf8_decode($usuario['nome']));

            $assunto = "Novo arquivo para você, {$usuario['nome']}.";

            $mensagem = "<b>Olá {$usuario['nome']}</b><br><br>";

            $mensagem .= $arquivo;

            $mensagem .= $cats . "<br><br>";

            $mensagem .= "Para visualizar o arquivo <a href='" . $sistema->root_path . "espaco-corretor-cadastro-solicitacao'>CLIQUE AQUI</a> e informe seu usuário e senha em GERENCIADOR DE ARQUIVOS!";

            $sistema->enviarEmail($to, "", utf8_decode($assunto), utf8_decode($mensagem));

            unset($to);
        }
    }
}

function subcat($idArquivo, $idCategoria, $conteudo = null, $cat = null) {
    global $sistema, $table1, $table2, $table3;
    $query = $sistema->DB_fetch_array("SELECT * FROM tb_espaco_categorias A INNER JOIN $table2 B ON B.id_categoria = A.id INNER JOIN tb_espaco_arquivos C ON C.id = B.id_arquivo WHERE C.id = $idArquivo AND A.id_pai = $idCategoria");

    if ($query->num_rows) {
        foreach ($query->rows as $categoria) {
            $conteudo .= "*" . $cat . " > <b>" . $categoria['nome'] . "</b><br>";
            $sub = subcat($idArquivo, $categoria['id_categoria'], $conteudo, $cat . " > " . $categoria['nome']);
            if ($sub != "")
                return $sub;
        }
    }

    return $conteudo;
}

?>