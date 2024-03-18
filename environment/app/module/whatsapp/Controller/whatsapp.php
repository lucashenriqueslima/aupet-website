<?php
use System\Base\SistemaBase;
use main\classes\{Util};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'integration';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_adm_integration";
        $this->module_title = "Integrações";
        $this->module_icon = "fa fa-building";
    }
    public function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table")->rows[0];
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    public function saveAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
        $data = $this->formularioObjeto($_POST, $this->table);
        $this->DB_update($this->table, $data, " WHERE id=1");
        $this->inserirRelatorio("Alterou dados da integração");
    }
    
    public function verifyLeadszappTokenAction() {
        $token = $this->getParameter("token");
        $this->DB_update('hbrd_adm_integration',['leadszapp_token'=>$token],' where id = 1');
        $options = $this->getheaderLeadzapp();
        $this->handlerHttp('GET',"https://hub.leadszapp.com/api/v1/tags",$options);
    }
    
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();