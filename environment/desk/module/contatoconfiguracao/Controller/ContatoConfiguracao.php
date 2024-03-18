<?php

use System\Base\{SistemaBase, BadRequest};
use desk\module\contatoconfiguracao\Model\ContatoConfiguracao as Model;

class __classe__ extends SistemaBase
{
    public $module = "";
    public $permissao_ref = "contato";
    public $table_situacao = "hbrd_desk_situacoes";
    public $table_grupo = "tb_atendimentos_grupo";
    public $table_notificacao = "hbrd_cms_notificacoes";
    public $id_formulario = 1;

    function __construct()
    {
        parent::__construct();
        $this->module_icon = "minia-icon-checkmark";
        $this->module_link = __class__;
        $this->module_title = "Configuração de Atendimento";
        $this->retorno = $this->getPath();
        $this->id_formulario = 1;
        $this->model = new Model();
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->listSituacoes = $this->DB_fetch_array("SELECT * FROM $this->table_situacao WHERE id_formulario = $this->id_formulario ORDER BY ordem");
        // $this->listAssuntos = $this->DB_fetch_array("SELECT * FROM $this->table_grupo WHERE id_formulario = $this->id_formulario ORDER BY ordem");
        $this->notificacoes = $this->DB_fetch_array("SELECT * FROM $this->table_notificacao WHERE id_form = $this->id_formulario");
        $query = $this->DB_fetch_array("SELECT * FROM hbrd_cms_formularios WHERE id = " . $this->id_formulario);
        $this->registro = $query->rows[0];
        // $this->dbMain();
        $this->users = $this->DB_fetch_array("SELECT * FROM hbrd_main_usuarios WHERE stats = 1 ORDER BY nome");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editSituacaoAction()
    {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $query = $this->DB_fetch_array("SELECT * FROM $this->table_situacao  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];
        }
        $this->renderView($this->getService(), $this->getModule(), "editSituacao");
    }

    protected function orderSituacaoAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $this->ordenarRegistros($_POST["array"], $this->table_situacao);
    }

    protected function delSituacaoAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");
        $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_desk_contatos WHERE id_situacao = $id");

        if (!$verifica->num_rows) {
            $dados = $this->DB_fetch_array("SELECT * FROM $this->table_situacao WHERE id = $id");
            $query = $this->model->deleteSituacao($id);
            if ($query->query) {
                $this->inserirRelatorio("Apagou situação de atendimento: [" . $dados->rows[0]['nome'] . "] id: [$id]");
            }
        }
        echo ($query->query) ? $this->getPath() : "error";
    }

    protected function saveSituacaoAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormularioSituacao($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['situation'] = $this->formularioObjeto($_POST, $this->table_situacao);
            $data['situation']->id_formulario = $this->id_formulario;
            if ($formulario->id == "") {
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();
                $query = $this->model->saveSituacao($data);
                if ($query->query) {
                    $this->inserirRelatorio("Cadastrou situação de atendimento: [" . $data['situation']->nome . "] id: [" . $query->query . "]");
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();
                $query = $this->model->updateSituacao($data);
                if ($query->query) {
                    $this->inserirRelatorio("Alterou situação de atendimento: [" . $data['situation']->nome . "] id: [" . $query->query . "]");
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
            echo json_encode($resposta);
        }
    }

    protected function editAssuntoAction()
    {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();
            $campos = $this->DB_columns($this->table_grupo);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
            $this->notificacoes->num_rows = 0;
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();
            $query = $this->DB_fetch_array("SELECT * FROM $this->table_grupo  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];
            $this->notificacoes = $this->DB_fetch_array("SELECT * FROM $this->table_notificacao WHERE id_formulario = $this->id_formulario AND id_grupo = $this->id");
        }
        $this->dbMain();
        $this->users = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE stats = 1 ORDER BY nome");
        $this->renderView($this->getService(), $this->getModule(), "editAssunto");
    }

    protected function orderAssuntoAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $this->ordenarRegistros($_POST["array"], $this->table_situacao);
    }

    protected function delAssuntoAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table_grupo WHERE id = $id");
        $query = $this->model->deleteGrupo($id);
        if ($query->query) {
            $this->inserirRelatorio("Apagou assunto de atendimento: [" . $dados->rows[0]['assunto'] . "] id: [$id]");
        }
        echo ($query->query) ? $this->getPath() : "error";
    }

    protected function saveAssuntoAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormularioAssunto($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['grupo'] = $this->formularioObjeto($_POST, $this->table_grupo);
            $data['grupo']->id_formulario = $this->id_formulario;
            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();
                $query = $this->model->saveGrupo($data);
                if ($query->query) {
                    $this->inserirRelatorio("Cadastrou assunto de atendimento: [" . $data['grupo']->assunto . "] id: [" . $query->query . "]");
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();
                $query = $this->model->updateGrupo($data);
                if ($query->query) {
                    $this->inserirRelatorio("Alterou assunto de atendimento: [" . $data['grupo']->assunto . "] id: [" . $query->query . "]");
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
            echo json_encode($resposta);
        }
    }

    protected function saveConfigAction()
    {
        $resposta = new \stdClass();

        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }
        $data['config'] = $this->formularioObjeto($_POST, "hbrd_cms_formularios");
        $data['config']->id = $this->id_formulario;

        if (!$_POST["ckbox_1_stats"]) {
            $data['config']->ckbox_1_stats = 0;
        }
        if (!$_POST["ckbox_2_stats"]) {
            $data['config']->ckbox_2_stats = 0;
        }
        $this->DBUpdate("hbrd_cms_formularios", $data['config'], " WHERE id=" . $data['config']->id);

        $resposta->type = "success";
        $resposta->message = "Registro alterado com sucesso!";

        echo json_encode($resposta);
    }

    protected function validaFormularioSituacao($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->id == "") {
            $duplicidade = $this->DB_fetch_array('SELECT * FROM ' . $this->table_situacao . ' WHERE id_formulario = ' . $this->id_formulario . ' AND nome = "' . $form->nome . '"');
        } else {
            $duplicidade = $this->DB_fetch_array('SELECT * FROM ' . $this->table_situacao . ' WHERE id_formulario = ' . $this->id_formulario . ' AND nome = "' . $form->nome . '" AND id <> ' . $form->id);
        }

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($duplicidade->num_rows) {
            $resposta->type = "attention";
            $resposta->message = "Esta situação já está registrada no sistema";
            $resposta->return = false;
        }
        return $resposta;
    }

    protected function validaFormularioAssunto($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;
        if ($form->id == "") {
            $duplicidade = $this->DB_fetch_array('SELECT * FROM ' . $this->table_grupo . ' WHERE id_formulario = ' . $this->id_formulario . ' AND assunto = "' . $form->assunto . '"');
        } else {
            $duplicidade = $this->DB_fetch_array('SELECT * FROM ' . $this->table_grupo . ' WHERE id_formulario = ' . $this->id_formulario . ' AND assunto = "' . $form->assunto . '" AND id <> ' . $form->id);
        }
        if ($form->assunto == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "assunto";
            $resposta->return = false;
            return $resposta;
        } else if ($duplicidade->num_rows) {
            $resposta->type = "attention";
            $resposta->message = "Este assunto já está registrada no sistema";
            $resposta->return = false;
        }
        return $resposta;
    }
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-", (str_replace(".php", "", basename(__FILE__))))));

$class = new __classe__();
$class->setAction();