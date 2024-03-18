<?php
use System\Core\Bootstrap;
class __classe__ extends Bootstrap {
    public $module = "";
    public $permissao_ref = "contato";
    public $table_situacao = "hbrd_desk_situacoes";
    public $table_grupo = "tb_atendimentos_grupo";
    public $table_notificacao = "hbrd_cms_notificacoes";
    function __construct() {
        parent::__construct();
        $this->module_icon = "minia-icon-checkmark";
        $this->module_link = __class__;
        $this->module_title = "Configuração de Atendimento";
        $this->retorno = $this->getPath();
        $this->id_formulario = 1;
    }

    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->listSituacoes = $this->DB_fetch_array("SELECT * FROM $this->table_situacao WHERE id_formulario = $this->id_formulario ORDER BY ordem");
        $this->notificacoes = $this->DB_fetch_array("SELECT * FROM $this->table_notificacao WHERE id_form = $this->id_formulario");
        $this->registro = $this->DB_fetch_array("SELECT * FROM hbrd_cms_formularios WHERE id = ".$this->id_formulario)->rows[0];
        $this->users = $this->DB_fetch_array("SELECT * FROM hbrd_main_usuarios WHERE stats = 1 ORDER BY nome");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editSituacaoAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table_situacao  WHERE id = $this->id")->rows[0];
        }
        $this->renderView($this->getService(), $this->getModule(), "editSituacao");
    }

    protected function orderSituacaoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table_situacao);
    }

    protected function delSituacaoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
        $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_desk_contatos WHERE id_situacao = $id");
        if (!$verifica->num_rows) {
            $dados = $this->DB_fetch_array("SELECT * FROM $this->table_situacao WHERE id = $id");
            $this->DBDelete($this->table_situacao, "where id=" . $id);
            $this->inserirRelatorio("Apagou situação de atendimento: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        }
        echo $this->getPath();
    }

    protected function saveSituacaoAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormularioSituacao($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['situation'] = $this->formularioObjeto($_POST, $this->table_situacao);
            $data['situation']->id_formulario = $this->id_formulario;
            if ($formulario->id == "") {
                if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
                // $this->DB_sqlInsert($this->table_situacao, $data['situation']);

                $insertSituacao =$this->DB_sqlInsert($this->table_situacao, $data['situation']);
                if (empty($insertSituacao) OR ! $this->mysqli->query($insertSituacao))
                    throw new \Exception($insertSituacao);

                $this->inserirRelatorio("Cadastrou situação de atendimento: [" . $data['situation']->nome . "] id: [" . $query->query . "]");
            } else {
                if (!$this->permissions[$this->permissao_ref]['editar']) exit();
                // $this->DB_sqlUpdate($this->table_situacao, $data['situation'], " WHERE id=" . $formulario->id);
                
                $updateSituacao = $this->DB_sqlUpdate($this->table_situacao, $data['situation'], " WHERE id=" . $formulario->id);
                if (empty($updateSituacao) OR ! $this->mysqli->query($updateSituacao))
                    throw new \Exception($updateSituacao);

                $this->inserirRelatorio("Alterou situação de atendimento: [" . $data['situation']->nome . "] id: [" . $query->query . "]");
            }
            echo json_encode(["type" => "success", "message" => "Sucesso!"]);
        }
    }

    protected function editAssuntoAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $query = $this->DB_fetch_array("SELECT * FROM $this->table_grupo  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];
            $this->notificacoes = $this->DB_fetch_array("SELECT * FROM $this->table_notificacao WHERE id_formulario = $this->id_formulario AND id_grupo = $this->id");
        }
        $this->dbMain();
        $this->users = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE stats = 1 ORDER BY nome");
        $this->renderView($this->getService(), $this->getModule(), "editAssunto");
    }

    protected function orderAssuntoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table_grupo);
    }

    protected function delAssuntoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table_grupo WHERE id = $id");
        $this->DBDelete($this->table_grupo, " where id=" . $id);
        $this->inserirRelatorio("Apagou assunto de atendimento: [" . $dados->rows[0]['assunto'] . "] id: [$id]");
        echo $this->getPath();
    }

    protected function saveAssuntoAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormularioAssunto($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['grupo'] = $this->formularioObjeto($_POST, $this->table_grupo);
            $data['grupo']->id_formulario = $this->id_formulario;
            if ($formulario->id == "") {
                if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
                $id = $this->DBInsert($this->_table_grupo, $data['grupo']);
                if (isset($_POST['emails'])) {
                    foreach ($_POST['emails'] as $email) {
                        $objeto['id_formulario'] = $data['grupo']->id_formulario;
                        $objeto['id_usuario'] = $email;
                        $objeto['id_grupo'] = $id;
                        $this->DBInsert($this->_table_notificacao, $objeto);
                    }
                }
                $this->inserirRelatorio("Cadastrou assunto de atendimento: [" . $data['grupo']->assunto . "] id: [" . $query->query . "]");
            } else {
                if (!$this->permissions[$this->permissao_ref]['editar']) exit();
                $id = $data['grupo']->id;
                $this->DBUpdate($this->table_grupo, $data['grupo'], " WHERE id=" . $id);
                $this->DBDelete($this->_table_notificacao, "where id_formulario = ".$data['grupo']->id_formulario." AND id_grupo = $id");
                if (isset($_POST['emails'])) {
                    foreach ($_POST['emails'] as $email) {
                        $objeto['id_formulario'] = $data['grupo']->id_formulario;
                        $objeto['id_usuario'] = $email;
                        $objeto['id_grupo'] = $id;
                        $this->DBInsert($this->_table_notificacao, $objeto);
                    }
                }
                $this->inserirRelatorio("Alterou assunto de atendimento: [" . $data['grupo']->assunto . "] id: [" . $query->query . "]");
            }
            echo json_encode(["type" => "success", "message" => "Sucesso!"]);
        }
    } 

    protected function saveConfigAction() {
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();
        $data['config'] = $this->formularioObjeto($_POST, "hbrd_cms_formularios");
        $data['config']->id = $this->id_formulario;
        // if(!$_POST["ckbox_1_stats"]) $data['config']->ckbox_1_stats = 0;
        // if(!$_POST["ckbox_2_stats"]) $data['config']->ckbox_2_stats = 0;
        $id = $data['config']->id;

        $this->DB_delete($this->table_notificacao, "id_form = $id");

        if (isset($_POST['emails'])) {
            foreach ($_POST['emails'] as $email) {
                $objeto['id_form'] = $id;
                $objeto['id_usuario'] = $email;
                // $this->DB_sqlInsert($this->table_notificacao, $objeto);

                $insertCpnfig = $this->DB_sqlInsert($this->table_notificacao, $objeto);
                if (empty($insertCpnfig) OR !$this->mysqli->query($insertCpnfig))
                    throw new \Exception($insertCpnfig);
            }
        }

        $updateFormulario = $this->DB_sqlUpdate("hbrd_cms_formularios", $data['config'], " WHERE id=" . $id);
         if (empty($updateFormulario) OR ! $this->mysqli->query($updateFormulario))
            throw new \Exception($updateFormulario);
        
        echo json_encode(["type" => "success", "message" => "Sucesso!"]);
    }

    protected function validaFormularioSituacao($form) {
        $resposta = new \stdClass();
        $resposta->return = true;
        if($form->id == "")
            $duplicidade = $this->DB_fetch_array('SELECT * FROM '.$this->table_situacao.' WHERE id_formulario = '.$this->id_formulario.' AND nome = "'.$form->nome.'"');
        else
            $duplicidade = $this->DB_fetch_array('SELECT * FROM '.$this->table_situacao.' WHERE id_formulario = '.$this->id_formulario.' AND nome = "'.$form->nome.'" AND id <> '.$form->id);
        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if($duplicidade->num_rows) {
            $resposta->type = "attention";
            $resposta->message = "Esta situação já está registrada no sistema";
            $resposta->return = false;
        }
        return $resposta;
    }
    
    protected function validaFormularioAssunto($form) {
        $resposta = new \stdClass();
        $resposta->return = true;
        if($form->id == "")
            $duplicidade = $this->DB_fetch_array('SELECT * FROM '.$this->table_grupo.' WHERE id_formulario = '.$this->id_formulario.' AND assunto = "'.$form->assunto.'"');
        else
            $duplicidade = $this->DB_fetch_array('SELECT * FROM '.$this->table_grupo.' WHERE id_formulario = '.$this->id_formulario.' AND assunto = "'.$form->assunto.'" AND id <> '.$form->id);
        if ($form->assunto == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "assunto";
            $resposta->return = false;
            return $resposta;
        } else if($duplicidade->num_rows) {
            $resposta->type = "attention";
            $resposta->message = "Este assunto já está registrada no sistema";
            $resposta->return = false;
        }
        return $resposta;
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();