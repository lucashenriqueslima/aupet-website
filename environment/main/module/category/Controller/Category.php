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

        $this->table = "hbrd_main_categorias";
        $this->module_title = "Categorias";
        $this->module_icon = "icomoon-icon-coin";
        $this->module_link = $this->getPath();
        $this->retorno = $this->getPath();
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = (int) $this->getParameter("id");

        try {
            $this->DB_delete($this->table, "id = {$id}");

            $this->inserirRelatorio("Removeu uma categoria [{$id}]");

            die($this->getPath());
        } catch (\Exception $e) {
            die('error');
        }
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

    $this->list = $this->DB_fetch_array("SELECT * FROM {$this->table}");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") { //new
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

        } else { //edit
            if ($_SESSION['admin_id'] != $this->id && !$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }

            $query = $this->DB_fetch_array("SELECT * FROM {$this->table} WHERE id = {$this->id}");
            $this->registro = $query->rows[0];
        }

        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    private function saveAction() {

        $formulario = $this->formularioObjeto($_POST);

        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            die(json_encode($validacao));
        }

        $resposta = new \stdClass();
        $data = $this->formularioObjeto($_POST, $this->table);

        try {
            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar']) {
                    die();
                }

                $id = $this->DB_insert($this->table, $data);

                $this->inserirRelatorio("Registrou uma categoria [{$id}]");

                $resposta->type = "success";
                $resposta->message = "Registrado com sucesso!";
            } else {
                //alterar

                if (!$this->permissions[$this->permissao_ref]['editar']) {
                    die();
                }

                $this->DB_update($this->table, $data, "WHERE id = {$formulario->id}");

                $this->inserirRelatorio("Atualizou uma categoria [{$formulario->id}]");

                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
            }

        } catch (\Exception $e) {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        die(json_encode($resposta));
    }

    private function validaFormulario($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if (! trim($form->nome)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;

            return $resposta;
        }

        return $resposta;
    }

    private function validaUpload($form) {

        $resposta = new \stdClass();
        $resposta->return = true;
        
        return $resposta;
    }

    /*
     * Métodos padrões da classe
     */

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
