<?php
use System\Base\SistemaBase;
use app\classes\{Util,Variaveis};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'apptemplates';
    function __construct() {
        parent::__construct();
        $this->module_title = "Notificações";
        $this->module_icon = "icomoon-icon-pencil-3";
        $this->variaveis = new Variaveis();
    }
    public function editAction() {
        $this->id = $this->getParameter("id");
        if (!$this->permissions[$this->permissao_ref]['editar']) return $this->noPermission();
        $this->registro = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao WHERE id = $this->id")->rows[0];
        $led = 'notf'.$this->id;            
        $this->items = $this->variaveis->$led;        
        $this->renderView($this->getService(), $this->getModule(), "leadszapp");
    }
    public function leadszappSaveAction() {
        unset($_POST['local']);
        $data = $this->formularioObjeto($_POST, 'hbrd_app_notificacao');
        if($data->id){
            $this->DB_update('hbrd_app_notificacao', $data, " WHERE id=" . $data->id);
            $msg = "Alterou template do app leadszapp [".$data->descricao."]";
        } 
        $this->inserirRelatorio($msg);
    }
    public function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->notificacoes = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where ta_pronto = 1");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function changestatusAction() {
        $notificacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao WHERE id = {$_POST['id']}")->rows[0];
        if($notificacao['desativado'] == '1') {
            $this->DB_update('hbrd_app_notificacao',['desativado'=>0]," where id = {$notificacao['id']}");
            $msg = "Ativou disparo de LeadsZapp [{$notificacao['descricao']}]";
        } else {
            $this->DB_update('hbrd_app_notificacao',['desativado'=>1]," where id = {$notificacao['id']}");
            $msg = "Desativou disparo de LeadsZapp [{$notificacao['descricao']}]";
        }
        $this->inserirRelatorio($msg);
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();