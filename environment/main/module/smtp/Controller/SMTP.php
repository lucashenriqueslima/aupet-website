<?php

use System\Core\Bootstrap;
use main\module\smtp\Model\SMTP as Model;

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();

class __classe__ extends Bootstrap {
    public $module = "";
    public $permissao_ref = __classe__;
    function __construct() {
        parent::__construct();
        
        $this->table = "hbrd_".__service__."_".__classe__;
        $this->module_title = "Configurações SMTP";
        $this->module_icon = "icomoon-icon-envelop-2";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();
        $this->model = new Model();
    }

    private function indexAction() {
        $this->id = 1;
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission();
				$query = $this->DB_fetch_array("SELECT A.* FROM $this->table A");
				if($query->num_rows) 
					$this->registro = $query->rows[0];
				else {
					$campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
				}
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['smtp'] = $this->formularioObjeto($_POST, $this->table);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                $resposta = new stdClass();
                $resposta->type = "error";
                $resposta->time = 4000;
                $resposta->message = "Só é possível editar, para criar use o banco de dados!";
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

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
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;


        if ($form->autenticado == 1 && $form->email_host == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_host";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_port == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_port";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_user == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_user";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_password == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_password";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_padrao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_padrao";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
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
