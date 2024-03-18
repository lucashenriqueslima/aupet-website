<?php

use System\Base\{SistemaBase, BadRequest};
use main\classes\{Util};
class __classe__ extends SistemaBase {

    public $module = "";
    public $permissao_ref = "newsapp";
    public $table = "hbrd_main_novidadesapp";

    function __construct() {
        parent::__construct();
        $this->module_title = "Novidades App";
        $this->module_icon = "icomoon-icon-newspaper";
        $this->module_link = __classe__;        
        $this->retorno = $this->getPath();
    }

    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        $this->inserirRelatorio("Apagou banner: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");
        echo $this->getPath();
    }

    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->list = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.create_at, '%d/%m/%Y às %H:%i') criado_em FROM $this->table A ORDER BY A.create_at");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id")->rows[0];
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    protected function saveAction() {
        unset($_POST['stats']);
        unset($_POST['create_at']);
        if($_POST["imagem_post"]) {
            $key = $this->putObjectAws(Util::getFileDaraUriAws($_POST['imagem_post']),$this->table, $_POST["imagem"]);
            $_POST["imagem"] = $key;
        }
        unset($_POST['imagem_post']);
        if((bool)$_POST['id']) {
            $this->DB_update($this->table,$_POST," where id = ".$_POST['id']);
        } else {
            $this->DB_insert($this->table, $_POST);
        }
    }

    protected function getfileAction() {
        $key = $this->getParameter("getfile");
        $file = base64_decode($this->getObjectAws($key, $this->table));
        Util::echoFile($file);
    }

}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();