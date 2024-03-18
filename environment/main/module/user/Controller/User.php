<?php

use System\Base\SistemaBase;
use main\module\user\Model\User as Model;

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-", (str_replace(".php", "", basename(__FILE__))))));
$class = new __classe__();
$class->setAction();

class __classe__ extends SistemaBase
{
    public $module = "";
    public $permissao_ref = "user";
    public $table = "hbrd_main_usuarios";

    function __construct()
    {
        parent::__construct();

        $this->module_title = "Usuários";
        $this->module_icon = "icomoon-icon-users";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();
        $this->avatar_uploaded = "";
        $this->crop_sizes = array();
        array_push($this->crop_sizes, array("width" => 38, "height" => 33));
        array_push($this->crop_sizes, array("width" => 40, "height" => 40));
        $this->model = new Model();
        $this->model->setCropSizes(array('crop_sizes' => $this->crop_sizes));
    }

    protected function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $id = $this->getParameter("id");
        if ($id == 1) {
            echo "error";
            exit();
        }
        $query = $this->model->delete($id);
        if (!$query->query) {
            echo "error";
        } else {
            echo $this->getPath();
        }
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("SELECT B.nome grupo, A.* FROM $this->table A INNER JOIN hbrd_main_grupos B ON B.id = A.id_grupo ORDER BY A.nome");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction()
    {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();
            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
            $this->registro["id_estado"] = "";
            $this->funcoes = $this->DB_fetch_array("SELECT B.id_usuario, A.id, A.nome, B.ler, B.gravar, B.excluir, B.editar FROM hbrd_main_funcoes A LEFT JOIN hbrd_main_usuarios_permissoes B ON A.id=B.id_funcao GROUP BY A.id ORDER BY A.nome");
            // $this->unidades = $this->DB_fetch_array("SELECT * FROM hbrd_main_unidades A LEFT JOIN hbrd_main_usuarios_main_unidades B ON B.id_unidade = A.id AND B.id_usuario = 0 ORDER BY A.nome");
            // $this->departamentos = $this->DB_fetch_array("SELECT * FROM hbrd_main_departamentos A LEFT JOIN hbrd_main_usuarios_main_departamentos B ON B.id_departamento = A.id AND B.id_usuario = 0  WHERE A.stats = 1 ORDER BY A.nome");
            // $this->cargos = $this->DB_fetch_array("SELECT * FROM hbrd_main_cargos A LEFT JOIN hbrd_main_usuarios_main_cargos B ON B.id_cargo = A.id AND B.id_usuario = 0  WHERE A.stats = 1 ORDER BY A.nome");
            $this->servicos = $this->DB_fetch_array("SELECT * FROM hbrd_main_servicos A LEFT JOIN hbrd_main_usuarios_main_servicos B ON B.id_servico = A.id AND B.id_usuario = 0 GROUP BY A.id");
            $this->registro['pi'] = "";
            $this->registro['pi2'] = "";
        } else {
            //edit
            if ($_SESSION['admin_id'] != $this->id) {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    $this->noPermission();
            }
            $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data_nascimento, '%d/%m/%Y') data_nascimento, C.id_estado FROM $this->table A LEFT JOIN hbrd_main_util_city C ON A.id_cidade = C.id LEFT JOIN hbrd_main_util_state D ON C.id_estado = D.id WHERE A.id = $this->id");
            $this->registro = $query->rows[0];
            $this->funcoes = $this->DB_fetch_array("SELECT B.id_usuario, A.id, A.nome, B.ler, B.gravar, B.excluir, B.editar FROM hbrd_main_funcoes A LEFT JOIN hbrd_main_usuarios_permissoes B ON A.id=B.id_funcao AND B.id_usuario = $this->id GROUP BY A.id ORDER BY A.nome");
            $this->servicos = $this->DB_fetch_array("SELECT * FROM hbrd_main_servicos A LEFT JOIN hbrd_main_usuarios_main_servicos B ON B.id_servico = A.id AND B.id_usuario = {$this->registro['id']} GROUP BY A.id ORDER BY B.id_usuario DESC");
            $this->registro['pi'] = "";

            if ($this->funcoes->rows[0]['id_usuario']) {
                $this->registro['pi'] = 1;
            }
            $this->registro['pi2'] = "";

            if ($this->servicos->rows[0]['id_usuario']) {
                $this->registro['pi2'] = 1;
            }
        }
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->grupos = $this->DB_fetch_array("SELECT * FROM hbrd_main_grupos WHERE id <> 1", "form");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function saveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $validacao = $this->validaUpload($formulario);
            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {
                $resposta = new \stdClass();
                $data['user'] = $this->formularioObjeto($_POST, $this->table);
                if ($this->avatar_uploaded != "") {
                    $data['user']->avatar = $this->avatar_uploaded;
                }
                $data['pi'] = '';
                if (isset($_POST['pi']) && $_POST['pi'] == 1) {
                    $data['pi'] = 1;
                    if (isset($_POST['ler'])) {
                        $data['permissoes']['ler'] = $_POST['ler'];
                    }
                    if (isset($_POST['gravar'])) {
                        $data['permissoes']['gravar'] = $_POST['gravar'];
                    }
                    if (isset($_POST['editar'])) {
                        $data['permissoes']['editar'] = $_POST['editar'];
                    }
                    if (isset($_POST['excluir'])) {
                        $data['permissoes']['excluir'] = $_POST['excluir'];
                    }
                }
                if ($formulario->id == "") {
                    //criar
                    if (!$this->permissions[$this->permissao_ref]['gravar']) {
                        exit();
                    }
                    $data['user']->senha = $this->embaralhar($data['user']->senha);
                    $data['user']->data_nascimento = $this->formataDataDeMascara($data['user']->data_nascimento);
                    $data['servico'] = array();

                    if (isset($_POST['pi2']) && $_POST['pi2'] == 1 && isset($_POST['servicos'])) {
                        $data['servico'] = $_POST['servicos'];
                    }
                    $data['empresa'] = array();

                    if (isset($_POST['empresas'])) {
                        $data['empresa'] = $_POST['empresas'];
                    }
                    $data['departamento'] = array();

                    if (isset($_POST['departamentos'])) {
                        $data['departamento'] = $_POST['departamentos'];
                    }
                    $data['cargo'] = array();

                    if (isset($_POST['cargos'])) {
                        $data['cargo'] = $_POST['cargos'];
                    }
                    $query = $this->model->save($data);

                    if ($query->query) {
                        $resposta->type = "success";
                        $resposta->message = "Registro cadastrado com sucesso!";
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    }
                } else {
                    //alterar
                    if ($_SESSION['admin_id'] != $formulario->id) {
                        if (!$this->permissions[$this->permissao_ref]['editar']) {
                            exit();
                        }
                    } else {
                        $_SESSION['admin_apelido'] = $formulario->apelido;
                    }
                    if ($data['user']->senha != "") {
                        $data['user']->senha = $this->embaralhar($data['user']->senha);
                    } else {
                        unset($data['user']->senha);
                    }
                    $data['user']->data_nascimento = $this->datebr($data['user']->data_nascimento);
                    $data['servico'] = array();

                    if (isset($_POST['pi2']) && $_POST['pi2'] == 1 && isset($_POST['servicos'])) {
                        $data['servico'] = $_POST['servicos'];
                    }
                    $data['empresa'] = array();

                    if (isset($_POST['empresas'])) {
                        $data['empresa'] = $_POST['empresas'];
                    }
                    $data['departamento'] = array();

                    if (isset($_POST['departamentos'])) {
                        $data['departamento'] = $_POST['departamentos'];
                    }
                    $data['cargo'] = array();

                    if (isset($_POST['cargos'])) {
                        $data['cargo'] = $_POST['cargos'];
                    }
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
    }

    protected function validaFormulario($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;
        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->apelido == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "apelido";
            $resposta->return = false;
            return $resposta;
        } else if ($form->email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->data_nascimento == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "data_nascimento";
            $resposta->return = false;
            return $resposta;
        } else if (!$this->checkdate($form->data_nascimento)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com uma data válida";
            $resposta->field = "data_nascimento";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_estado == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "estado";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_cidade == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_cidade";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_grupo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_grupo";
            $resposta->return = false;
            return $resposta;
        } /*else if ($form->id_nivel == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_nivel";
            $resposta->return = false;
            return $resposta;
        } */ else if ($form->usuario == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "usuario";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id == "" && $form->senha = "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "senha";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    protected function validaUpload($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;
        if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {
            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->avatar_uploaded = $upload->file_uploaded;
                if ($_SESSION['admin_id'] == $form->id) {
                    $_SESSION['admin_avatar'] = $this->getImageFileSized($upload->file_uploaded, 38, 33);
                }
            }
        }
        if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    public function getSelectAction()
    {
        $id = $_POST["id"];
        $edit = true;
        if ($_POST["id_register"] == "") {
            $edit = false;
        }
        if ($edit && $id != 1) {
            $this->unidades = $this->DB_fetch_array("SELECT * FROM hbrd_main_unidades A LEFT JOIN hbrd_main_usuarios_main_unidades B ON B.id_unidade = A.id AND B.id_usuario = {$_POST['id_register']} ORDER BY A.nome");
        } else {
            $this->unidades = $this->DB_fetch_array("SELECT * FROM hbrd_main_unidades A LEFT JOIN hbrd_main_usuarios_main_unidades B ON B.id_unidade = A.id AND B.id_usuario = 0 ORDER BY A.nome");
        }
        if ($id == 1) {
            $this->renderAjax($this->getService(), $this->getModule(), "empresas_master");
        } else if ($id == 2) {
            $this->renderAjax($this->getService(), $this->getModule(), "empresas_duallist");
        } else {
            $this->renderAjax($this->getService(), $this->getModule(), "empresas_combobox");
        }
    }
}
