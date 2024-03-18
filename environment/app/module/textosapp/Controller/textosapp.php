<?php
use System\Base\{SistemaBase, BadRequest};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "motive";
    function __construct()  {
        parent::__construct();
        $this->table = "hbrd_app_textos_app";
        $this->module_icon = "icomoon-icon-list-2";
        $this->module_title = "Textos do Aplicativo";
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.delete_at IS NULL");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id")->rows[0];
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    protected function saveAction() { unset($_POST['delete_at']);
        if(!$_POST['id']) {
            $this->DB_insert($this->table,['titulo'=>$_POST['titulo'],'texto'=>$_POST['texto']]);
        } else {
            $this->DB_update($this->table,['titulo'=>$_POST['titulo'],'texto'=>$_POST['texto']],"where id = {$_POST['id']}");
        }
    }
    protected function itemAction() { $data = $this->formularioObjeto($_POST, "hbrd_app_motive");
        unset($data->delete_at);
        if (!$data->id) {
            if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
            $data->id = $this->DB_insert("hbrd_app_motive", $data);
            $this->inserirRelatorio("Criou item Motivo id " . $data->id);
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) exit();
            $this->DB_update("hbrd_app_motive", $data, " WHERE id=" . $data->id);
            $this->inserirRelatorio("Editou item Motivo id " . $data->id);
        }
        echo json_encode($this->DB_fetch_array("SELECT A.*, B.nome as categoria FROM hbrd_app_motive A left join hbrd_adm_category B on B.id = A.id_categoria WHERE id_plan = $data->id_plan AND A.delete_at is null order by id_categoria, de_val")->rows);
    }
    protected function deleteMotiveAction() { if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
		$id = $this->getParameter("deleteMotive");
		$this->DB_update('hbrd_app_motive', array("delete_at" => date("Y-m-d H:i:s")), " WHERE id=" . $id);
		$this->inserirRelatorio("Deletou motivo id " . $id);
    }
    protected function changestatusAction() { $this->DB_update($this->table, ['stats' => ($_POST['stats'] === '1') ? '0' : '1'], " where id =" . $_POST['id']);
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();
