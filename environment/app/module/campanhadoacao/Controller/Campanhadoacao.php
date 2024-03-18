<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Util,Variaveis};
class __classe__ extends SistemaBase {
	public $module = "";
	public $permissao_ref = "ongs";
	function __construct() {
		parent::__construct();
		$this->table = "hbrd_app_campanha_doacoes";
		$this->module_icon = "icomoon-icon-users";
		$this->module_title = "Campanhas de Doação";
	}
	protected function indexAction() {
		if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
		$this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.delete_at IS NULL ORDER BY A.ordem");
		$this->renderView($this->getService(), $this->getModule(), "index");
	}
	protected function editAction() {
		$this->id = $this->getParameter("id");
		if ($this->id == "") {
			if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
			
		} else {
			if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
			$this->registro = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.id = $this->id")->rows[0];
		}
		$this->renderView($this->getService(), $this->getModule(), "edit");
	}
	protected function saveAction() {
		$data = $this->formularioObjeto($_POST, $this->table);
		unset($data->delete_at);
		unset($data->ordem);
		unset($data->stats);
		if (!$data->id) {
			if (!$this->permissions($this->permissao_ref,'gravar')) throw new BadRequest('Você não tem  permissao para gravar');
			$table = $this->DB_fetch_array("SELECT * FROM $this->table WHERE delete_at is null");
            $data->ordem = $table->num_rows + 1;
			$data->id = $this->DB_insert($this->table, $data);
			$this->inserirRelatorio("Criou uma Campanha de Doação id " . $data->id);
		} else {
			if (!$this->permissions($this->permissao_ref,'editar')) throw new BadRequest('Você não tem  permissao para editar');
			
			$this->DB_update($this->table, $data, " WHERE id='{$data->id}'");
			$this->inserirRelatorio("Editou a Campanha de Doação id " . $data->id);
		}
		
	}
	protected function deleteAction() {
		if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
		$id = $this->getParameter("delete");
		$this->DB_update($this->table, array("delete_at" => date("Y-m-d H:i:s")), " WHERE id=" . $id);
		$this->inserirRelatorio("Deletou Campanha de Doação id " . $id);
	}
	protected function changestatusAction() {
		$this->DB_update($this->table, ['stats' => ($_POST['stats'] === '1') ? '0' : '1'], " where id =" . $_POST['id']);
	}
	
	protected function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
	}
	
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();
