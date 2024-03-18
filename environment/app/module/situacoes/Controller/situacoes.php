<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Util,Variaveis};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "status";
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_pet_proposta_status";
        $this->module_icon = "minia-icon-checkmark";
        $this->module_title = "Situações da proposta";
        $this->variaveis = new Variaveis();
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table WHERE delete_at IS NULL ORDER BY ordem");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id")->rows[0];
            $this->items = $this->variaveis->varStApp;
            if($this->registro['tiposvistorias']) $this->registro['tiposvistorias'] = json_decode($this->registro['tiposvistorias'], true);
        }
        $this->modelos = $this->DB_fetch_array("SELECT * FROM hbrd_app_vistoria_modelo WHERE delete_at is null AND stats = 1")->rows;
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    protected function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }
    protected function saveAction() {
        $data = $this->formularioObjeto($_POST, $this->table);
        unset($data->delete_at);
        unset($data->ordem);
        if(!$data->id) {
            if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
            $table = $this->DB_fetch_array("SELECT * FROM $this->table WHERE delete_at is null");
            $data->ordem = $table->num_rows + 1;
            $data->id = $this->DB_insert($this->table, $data);
            $this->inserirRelatorio("Inseriu situação: [".$data->nome."] id: [".$data->id."]");
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) exit();
            $this->DB_update($this->table, $data, " WHERE id=".$data->id);
            $this->inserirRelatorio("Alterou situação: [".$data->nome."] id: [".$data->id."]");
        }
    }
    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = ".$id)->rows[0];
        $this->DB_update($this->table,['delete_at'=>date('Y-m-d H:i:s')], " where id=".$id);
        $this->inserirRelatorio("Apagou situação: [".$dados['nome']."] id: [{$id}]");
    }
    protected function savetableAction() {
        foreach ($_POST as $row) {
            $this->DB_update($this->table,['dados'=>$row['dados'],'vistoria'=>$row['vistoria'],'contrato'=>$row['contrato']], " where id=".$row['id']);
        }
    }
    protected function changeWhatsAction() {
        $this->DB_update($this->table,['whastapp'=>$_POST['whastapp']], " where id=".$_POST['id']);
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();