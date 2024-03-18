<?php

use System\Core\Bootstrap;
use cms\module\notificacoes\Model\Notificacoes as Model;
use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase
{
    public $module = "";
    public $permissao_ref = "notification";
    public $table = "hbrd_cms_formularios";
    public $_table = "hbrd_cms_notificacoes";

    function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "main/login");
            exit;
        }
        $this->module_title = "Notificações";
        $this->module_icon = "icomoon-icon-bubble-notification";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();

        // $this->model = new Model();
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction()
    {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                $this->noPermission();
            }
            $campos = $this->DB_columns($this->table);

            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }
            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
        }
        $this->users = $this->DB_fetch_array("SELECT * FROM hbrd_main_usuarios A LEFT JOIN (SELECT * FROM hbrd_cms_notificacoes WHERE id_form = $this->id) B ON A.id=B.id_usuario", "form");
        $this->nome = $this->getById($this->id);
        $this->nome = $this->nome['nome'];

        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    public function getById($id)
    {
        $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        if ($query->num_rows) {
            return $query->rows[0];
        }
    }

    protected function saveAction()
    {
        $resposta = new \stdClass();
        $formulario = $this->formularioObjeto($_POST);
        $data['notification'] = $this->formularioObjeto($_POST);

        if ($formulario->id == "") {
            //criar
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                exit();
            }
            $resposta = new stdClass();
            $resposta->type = "error";
            $resposta->time = 4000;
            $resposta->message = "Só é possível criar formulários manualmente, use o banco de dados!";
        } else {
            //alterar
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                exit();
            }
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

    public function update($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idForm = $data['notification']->id;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();
            $delete = $this->DB_sqlDelete($this->_table, "id_form = $idForm");

            if (empty($delete) or !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }

            if (isset($_POST['emails'])) {
                foreach ($_POST['emails'] as $email) {
                    $objeto['id_form'] = $idForm;
                    $objeto['id_usuario'] = $email;
                    $insertNotification = $this->DB_sqlInsert($this->_table, $objeto);
                    if (empty($insertNotification) or !$this->mysqli->query($insertNotification)) {
                        throw new \Exception($insertNotification);
                    }
                }
            }
            $updatetFormMessage = $this->DB_sqlUpdate($this->table, array("mensagem" => $data['notification']->mensagem), " WHERE id=" . $idForm);

            if (empty($updatetFormMessage) or !$this->mysqli->query($updatetFormMessage)) {
                throw new \Exception($updatetFormMessage);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou notificações formulário id [$idForm]");
            $response->query = $idForm;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli)) {
                $this->mysqli->close();
            }
            return $response;
        }
    }
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-", (str_replace(".php", "", basename(__FILE__))))));

$class = new __classe__();
$class->setAction();