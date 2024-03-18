<?php

use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase {

    public $module = "";
    public $permissao_ref = "contato";
    public $table = "hbrd_main_contato";

    function __construct() {
        parent::__construct();
        $this->module_title = "Contatos";
        $this->module_icon = "icomoon-icon-bubbles";
        $this->module_link = __classe__;        
        $this->retorno = $this->getPath();
    }

    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();

        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

         $this->inserirRelatorio("Apagou o contato: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getPath();
    }

    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->list = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.create_at,'%d/%m/%Y %H:%i:%s') AS registrado_em FROM $this->table A ORDER BY A.create_at DESC");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction() {
        $this->id = $this->getParameter("id");
        
        if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();

        $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.create_at,'%d/%m/%Y %H:%i:%s') AS registrado_em FROM $this->table A WHERE id = $this->id", "form");
        $this->registro = $query->rows[0];

        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    // protected function orderAction() {
    //     if (!$this->permissions[$this->permissao_ref]['excluir'])
    //         exit();
    //     $this->ordenarRegistros($_POST["array"], $this->table);
    // }

    protected function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao_form = $this->validaFormulario($formulario);

        //if (!$validacao_form->return || !$validacao_upload->return) {
        
        if (!$validacao_form->return) {
            echo json_encode($validacao_form);
        } else {
            $resposta = new \stdClass();
            $data = $this->formularioObjeto($_POST, $this->table);
               
            if (!$this->permissions[$this->permissao_ref]['editar']) exit();
            $this->DB_update($this->table, $data, " WHERE id=" . $data->id);
            $resposta->type = "success";
            $resposta->message = "Registro alterado com sucesso!";
            $this->inserirRelatorio("Alterou contato: [" . $data->nome . "]");                    
                
            echo json_encode($resposta);
        }        
    }

    protected function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->telefone == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "telefone";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();