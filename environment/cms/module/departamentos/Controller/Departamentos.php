<?php

use System\Core\Bootstrap;

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-",(str_replace(".php", "", basename(__FILE__))))));

$class = new __classe__();
$class->setAction();

class __classe__ extends Bootstrap {

    public $module = "";
    public $permissao_ref = "departamentos";
    public $table = "hbrd_cms_departamentos";

    function __construct() {
        parent::__construct();

        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "main/login");
            exit;
        }

        $this->module_title = "Departamentos";
        $this->module_icon = "icomoon-icon-grid";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();
    }

    private function delAction() {
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
            if (empty($delete) OR ! $this->mysqli->query($delete))
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

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A ORDER BY A.nome");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            //edit
            if ($_SESSION['admin_id'] != $this->id) {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    $this->noPermission();
            }

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
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
            $data['department'] = $this->formularioObjeto($_POST, $this->table);

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

            $insertDepartment = $this->DB_sqlInsert($this->table, $data['department']);
            if (empty($insertDepartment) OR ! $this->mysqli->query($insertDepartment))
                throw new \Exception($insertDepartment);

            $idDepartment = $this->mysqli->insert_id;

            $this->mysqli->commit();

            $this->inserirRelatorio("Cadastrou departamento [" . $data['department']->nome . "] id [$idDepartment]");

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
        $idDepartment = $data['department']->id;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();

            $updateDepartment = $this->DB_sqlUpdate($this->table, $data['department'], " WHERE id=" . $idDepartment);
            if (empty($updateDepartment) OR ! $this->mysqli->query($updateDepartment))
                throw new \Exception($updateDepartment);

            $this->mysqli->commit();

            $this->inserirRelatorio("Alterou departamento [" . $data['department']->nome . "] id [$idDepartment]");

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

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;


        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->descricao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "descricao";
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
            //$this->$newAction();
        } else if ($newAction == "Action") {
            if (method_exists($this, 'indexAction')) {
                $action[0] = "index";
                $newAction = "indexAction";
            } else {
                $this->renderView($this->getService(), $this->getModule(), "404");
                exit;
            }
        } else {
            $this->renderView($this->getService(), $this->getModule(), "404");
            exit;
        }

        //$_SESSION['admin_request'] = array( "action" => $action[0] , "post" => (isset($_POST) ? $_POST : NULL));
        //$this->LOG_console(json_encode($_SESSION['admin_request']));

        if (!isset($_SESSION['admin_logado'])) {
            $this->renderView("main", "login", "popup_get");
            exit;
        }

        $this->$newAction();
    }

}
