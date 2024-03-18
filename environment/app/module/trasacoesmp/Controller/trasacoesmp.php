<?php

use System\Base\{SistemaBase, BadRequest};
use app\classes\{Variaveis, Util};

class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "user";
    public $table = "hbrd_app_pet_especie";
    function __construct() {
        parent::__construct();
        $this->module_title = "Transações Mercado Pago";
        $this->module_icon = "fa fa-dollar";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();
    }
    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $id = $this->getParameter("id");
        $query = $this->delete($id);
        if (!$query->query) {
            echo "error";
        } else {
            echo $this->getPath();
        }
    }
    public function delete($id) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();
            $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
            $delete = $this->DB_sqlDelete($this->table, "id=" . $id);
            if (empty($delete) or !$this->mysqli->query($delete))
                throw new \Exception($delete);
            $this->mysqli->commit();
            $response->query = true;
            $this->inserirRelatorio("Apagou departamento [" . $dados->rows[0]['nome'] . "] id [$id]");
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli))
                $this->mysqli->close();
            return $response;
        }
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $access_token = $this->DB_fetch_array("SELECT access_token FROM hbrd_adm_integration")->rows[0]['access_token'];
        $this->list = json_decode($this->handlerHttp('GET', 'https://api.mercadopago.com/v1/payments/search?sort=date_created&criteria=desc', ['headers' => ['Content-Type' => 'application/json', 'Authorization' => "Bearer $access_token"]]), true)["results"];
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            $this->noPermission();
        } else {
            $access_token = $this->DB_fetch_array("SELECT access_token FROM hbrd_adm_integration")->rows[0]['access_token'];
            $this->registro = json_decode($this->handlerHttp('GET', "https://api.mercadopago.com/v1/payments/$this->id", ['headers' => ['Content-Type' => 'application/json', 'Authorization' => "Bearer $access_token"]]), true);
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
            $data = $this->formularioObjeto($_POST, $this->table);
            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();
                $query = $this->save($data);
                if ($query->query) {
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
                $query = $this->update($data);
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
    public function save($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();
            $insertDepartment = $this->DB_sqlInsert($this->table, $data);
            if (empty($insertDepartment) or !$this->mysqli->query($insertDepartment))
                throw new \Exception($insertDepartment);
            $idDepartment = $this->mysqli->insert_id;
            $this->mysqli->commit();
            $this->inserirRelatorio("Cadastrou departamento [" . $data->nome . "] id [$idDepartment]");
            $response->query = $idDepartment;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli))
                $this->mysqli->close();
            return $response;
        }
    }
    public function update($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idDepartment = $data->id;
        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();
            $updateDepartment = $this->DB_sqlUpdate($this->table, $data, " WHERE id=" . $idDepartment);
            if (empty($updateDepartment) or !$this->mysqli->query($updateDepartment))
                throw new \Exception($updateDepartment);
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou departamento [" . $data->nome . "] id [$idDepartment]");
            $response->query = $idDepartment;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli))
                $this->mysqli->close();
            return $response;
        }
    }
    protected function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;
        if ($form->titulo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
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
