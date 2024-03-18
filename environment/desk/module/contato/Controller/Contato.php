<?php

use desk\module\contato\Model\Contato as Model;
use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase
{
    public $module = "";
    public $id_formulario = 1;
    public $imagem_uploaded = "";
    public $crop_sizes = array();
    public $permissao_ref = "contato";
    public $table = "hbrd_desk_contato";
    public $table_desc = "hbrd_cms_paginas";

    function __construct()
    {
        parent::__construct();
        $this->module_icon = "entypo-icon-email";
        $this->module_link = __classe__;
        $this->module_title = "Contato";
        $this->retorno = $this->getPath();
        $this->model = new Model();

        array_push($this->crop_sizes, array("width" => 600, "height" => 600)); // Home
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("
            SELECT 
                DATE_FORMAT(A.create_at, '%d/%m/%Y %H:%i') AS registro,
                A.*,
                S.nome AS situacao,
                B.utm_source,
                B.utm_campaign
            FROM
                hbrd_desk_contato A
                    LEFT JOIN
                hbrd_desk_situacoes S ON S.id = A.id_situacao
                    LEFT JOIN
                hbrd_cms_seo_acessos_historico B ON B.session = A.session
            GROUP BY A.id
            ORDER BY A.create_at DESC");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction()
    {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }
            $this->registro = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.create_at, '%d/%m/%Y %H:%i') AS create_at FROM $this->table A WHERE A.id = $this->id")->rows[0];
            $this->statusHistorico = $this->DB_fetch_array("
                SELECT 
                    *,
                    DATE_FORMAT(data, '%d/%m/%Y %H:%i') AS data,
                    data AS registro
                FROM
                    hbrd_desk_contato_situacao_historico
                WHERE
                    id_contato = {$this->registro['id']}");
            $this->situacoes = $this->DB_fetch_array("SELECT * FROM hbrd_desk_situacoes WHERE id_formulario = $this->id_formulario ORDER BY ordem");
        }
        $this->historicos = $this->DB_fetch_array("
            SELECT 
                DATE_FORMAT(B.date, '%d/%m/%Y às %H:%i:%s') AS registro,
                B.date,
                C.seo_title AS titulo,
                B.origem,
                CONCAT(B.cidade, ', ', B.estado, ' - ', B.pais) AS localizacao,
                B.pais,
                B.estado,
                B.cidade,
                B.dispositivo,
                B.ip,
                B.session,
                B.utm_source,
                B.utm_medium,
                B.utm_term,
                B.utm_content,
                B.utm_campaign
            FROM
                hbrd_desk_contato A
                    LEFT JOIN
                hbrd_cms_seo_acessos_historico B ON B.session = A.session
                    INNER JOIN
                $this->_seo_table C ON C.id = B.id_seo
            WHERE
                A.session = '{$this->registro['session']}'
            ORDER BY B.date");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function saveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();

        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }

        if (isset($formulario->seo_pagina_conteudo)) {
            $data['pagina'] = $this->formularioObjeto($_POST, "$this->_seo_table");
            $query = $this->model->updatePagina($data);
            if ($query->query) {
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        } else {
            $data['contact'] = $this->formularioObjeto($_POST, $this->table);
            $data['history'] = new \stdClass();
            $data['history']->comentario = $_POST["comentario"];
            $query = $this->model->update($data);
            if ($query->query) {
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        }
        echo json_encode($resposta);
    }

    protected function exportContactsAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=contato-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");
//        $this->statusHistorico = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') AS data, data registro FROM hbrd_desk_contato_situacoes_historico WHERE id_contato = {$this->registro['id']}");
        $this->dados = $this->DB_fetch_array("
            SELECT 
                A.*,
                DATE_FORMAT(A.create_at, '%d/%m/%Y %H:%i') AS data,
                S.nome AS situacao
            FROM
                hbrd_desk_contato A
                    LEFT JOIN
                hbrd_desk_situacoes S ON S.id = A.id_situacao
            GROUP BY A.id
            ORDER BY A.create_at DESC");
        $this->renderExport($this->getService(), $this->getModule(), "contacts");
    }

    protected function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");
        $query = $this->model->delete($id);
        echo ($query->query) ? $this->getPath() : "error";
    }

    protected function editSituationAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }
        foreach ($_POST['i'] as $id) {
            $query = $this->DB_update($this->table, "id_situacao = {$_POST['situacao']} WHERE id = $id");
            if ($query) {
                $nome = $this->DB_fetch_array("SELECT nome FROM hbrd_desk_situacoes WHERE id = {$_POST['situacao']}");
                $this->DB_insert("hbrd_desk_contato_situacoes_historico", "status,id_contato,usuario", "'{$nome->rows[0]['nome']}',$id,'" . $_SESSION['admin_id'] . " - " . $_SESSION['admin_nome'] . "'");
            }
        }
        if (!$query) {
            echo "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
    }

    protected function descricaoAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->registro = $this->DB_fetch_array("
            SELECT 
                *
            FROM
                hbrd_cms_paginas
            WHERE
                seo_url LIKE 'contato'")->rows[0];

        $this->height = $this->crop_sizes[0]['height'];
        $this->width = $this->crop_sizes[0]['width'];

        $this->renderView($this->getService(), $this->getModule(), "descricao");
    }

    protected function descricaoSaveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();

        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }
        $validacao = $this->validaUpload($_FILES);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            if (isset($formulario->seo_pagina_conteudo)) {
                $data['descricao'] = $this->formularioObjeto($_POST, $this->table_desc);

                if ($this->imagem_uploaded != "") {
                    $data['descricao']->imagem_dois = $this->imagem_uploaded;
                    if ($formulario->id != "") {
                        $this->deleteFile($this->table_desc, "imagem_dois", "id=" . $formulario->id, $this->crop_sizes);
                    }
                }
                $this->DBUpdate($this->table_desc, $data['descricao'], " WHERE id=" . $data['descricao']->id);
                $this->inserirRelatorio("Alterou contato: [" . $data['descricao']->nome . "], id: [{$data['descricao']->id}]");

                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";

            }
            echo json_encode($resposta);
        }
    }

    protected function validaUpload($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($form["fileupload"]["tmp_name"])) {
            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->imagem_uploaded = $upload->file_uploaded;
            }
        }

        if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-", (str_replace(".php", "", basename(__FILE__))))));

$class = new __classe__();
$class->setAction();