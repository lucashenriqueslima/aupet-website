<?php

use System\Core\Bootstrap;

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();

class __classe__ extends Bootstrap {

    public $module = "";
    public $permissao_ref = __classe__;

    function __construct() {
        parent::__construct();
        
        $this->getLevelsAccess();

        $this->table = "hbrd_desk_assunto";
        $this->module_title = "Assuntos";
        $this->module_icon = "icomoon-icon-exclamation";
        $this->module_link = $this->getPath();
        $this->retorno = $this->getPath();
    }

    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();

        $id = $this->getParameter("id");
        $this->DB_update($this->table, ['delete_at' => date('Y-m-d H:i:s')], "WHERE id = {$id}");
        echo $this->getPath();
    }

    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        
        $this->list = $this->DB_fetch_array("SELECT A.* FROM {$this->table} A WHERE delete_at IS NULL ORDER BY nome");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $query = $this->DB_fetch_array("SELECT * FROM {$this->table}  WHERE id = {$this->id}", "form");
            $this->registro = $query->rows[0];
        }

        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();

            try {
                $data = $this->formularioObjeto($_POST, $this->table);

                if ($formulario->id == "") {
                    if (!$this->permissions[$this->permissao_ref]['gravar']) exit();

                    $formulario->id = $this->DB_insert($this->table, $data);
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                } else {
                    if (!$this->permissions[$this->permissao_ref]['editar']) exit();

                    $this->DB_update($this->table, $data, "WHERE id = {$formulario->id}");
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                }
            } catch (\Exception $e) {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

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
        } else {
            return $resposta;
        }
    }

    public function getService() {
        return __service__;
    }

    public function getModule() {
        return __classe__;
    }

    public function getPath() {
        return __service__ . '/' . __classe__;
    }

    public function setAction() {
        #acionar o método da classe de acordo com o parâmetro da url
        $action = $this->getParameter(__classe__);
        $action = explode("?", $action);
        $newAction = $action[0] . "Action";

        #antes de acioná-lo, verifica se ele existe
        if (method_exists($this, $newAction)) {
            $this->$newAction();
        } else if ($newAction == "Action") {
            if (method_exists($this, 'indexAction'))
                $this->indexAction();
            else
                $this->renderView($this->getService(), $this->getModule(), "404");
        } else {
            $this->renderView($this->getService(), $this->getModule(), "404");
        }
    }
}