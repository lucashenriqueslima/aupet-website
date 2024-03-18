<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Variaveis, Util};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "racas";
    public $table = "hbrd_app_pet_raca";
    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "main/login");
            exit;
        }
        $this->module_title = "Raças";
        $this->module_icon = "icomoon-icon-paw";
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
            $delete = $this->DB_sqlUpdate($this->table, array("delete_at" => date("Y-m-d H:i:s")), " WHERE id=".$id);
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);
            $this->mysqli->commit();
            $response->query = true;
            $this->inserirRelatorio("Apagou raça [" . $dados->rows[0]['nome'] . "] id [$id]");
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
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->especies = $this->DB_fetch_array("SELECT A.id, A.titulo FROM hbrd_app_pet_especie A")->rows;
        $this->getRacasAction();
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function getRacasAction(){
        $where = "";
        if ($_POST['especies']) {
            $where .= ($where == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['especies']) as $value) {
                if($value=="")continue;
                $where .= " A.id_especie = '{$value}' OR ";
            }
            $where = substr_replace($where, "", -3);
            $where .= ' )';
        }
        $this->list = $this->DB_fetch_array("SELECT SQL_CALC_FOUND_ROWS A.*, (SELECT B.titulo FROM hbrd_app_pet_especie B WHERE B.id = A.id_especie) AS especie FROM $this->table A $where ORDER BY A.titulo");
        
        $output = [
            "sEcho" => intval($_POST['sEcho']),
            "iTotalDisplayRecords" => $this->list->total,
            "aaData" => $this->list->rows
        ];

        return json_encode($output);
    }

    protected function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();
            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
            $this->especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_especie A ORDER BY A.titulo");
        } else {
            //edit
            if ($_SESSION['admin_id'] != $this->id) {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    $this->noPermission();
            }
            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
            $this->especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_especie A ORDER BY A.titulo");
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
            if (empty($insertDepartment) OR ! $this->mysqli->query($insertDepartment))
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
        $idRaca = $data->id;
        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();
            $updateRaca = $this->DB_sqlUpdate($this->table, $data, " WHERE id=" . $idRaca);
            if (empty($updateRaca) OR ! $this->mysqli->query($updateRaca))
                throw new \Exception($updateRaca);
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou departamento [" . $data->titulo . "] id [$idRaca]");
            $response->query = $idRaca;
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
            $resposta->field = "raca";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-",(str_replace(".php", "", basename(__FILE__))))));
$class = new __classe__();
$class->setAction();