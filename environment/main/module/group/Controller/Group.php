<?php
use System\Base\SistemaBase;
use main\module\group\Model\Group as Model;
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "group";
    public $table = "hbrd_main_grupos";
    function __construct() {
        parent::__construct();
        $this->module_title = "Grupos de Usuário";
        $this->module_icon = "icomoon-icon-users-2";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();
        $this->model = new Model();
    }
    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $id = $this->getParameter("id");
        if ($id == 1 OR $id == 2) {
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
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id != 1 ORDER BY nome");
        $this->renderView($this->getService(), $this->getModule(), "index");
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
            $this->funcoes = $this->DB_fetch_array("SELECT * FROM hbrd_main_funcoes ORDER BY nome");
            $this->servicos = $this->DB_fetch_array("SELECT * FROM hbrd_main_servicos A LEFT JOIN hbrd_main_grupos_main_servicos B ON B.id_servico = A.id AND B.id_grupo = 0");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();
            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
            $this->funcoes = $this->DB_fetch_array("SELECT A.id, A.nome, B.ler, B.gravar, B.excluir, B.editar FROM hbrd_main_funcoes A LEFT JOIN (SELECT * FROM hbrd_main_permissoes WHERE id_grupo = $this->id) B ON A.id=B.id_funcao ORDER BY A.nome");
            $this->servicos = $this->DB_fetch_array("SELECT * FROM hbrd_main_servicos A LEFT JOIN hbrd_main_grupos_main_servicos B ON B.id_servico = A.id AND B.id_grupo = {$this->registro['id']}" );
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
            $data['group'] = $this->formularioObjeto($_POST, $this->table);
            if (isset($_POST['ler']))
                $data['permissoes']['ler'] = $_POST['ler'];
            if (isset($_POST['gravar']))
                $data['permissoes']['gravar'] = $_POST['gravar'];
            if (isset($_POST['editar']))
                $data['permissoes']['editar'] = $_POST['editar'];
            if (isset($_POST['excluir']))
                $data['permissoes']['excluir'] = $_POST['excluir'];
            $data['servico'] = array();
            if (isset($_POST['servicos']))
                $data['servico'] = $_POST['servicos'];
            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();
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
    public function validaFormulario($form) {
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
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-",(str_replace(".php", "", basename(__FILE__))))));
$class = new __classe__();
$class->setAction();