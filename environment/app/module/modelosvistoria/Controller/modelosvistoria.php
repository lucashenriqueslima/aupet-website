<?php
use System\Base\{SistemaBase, BadRequest};
use main\classes\Util;
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'inspectionmodel';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_vistoria_modelo";
        $this->module_icon = "minia-icon-checkmark";
        $this->module_title = "Modelos de vistorias";
	}
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
		$this->list = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_vistoria_modelo A WHERE A.delete_at is null");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function editAction() {
        $this->id = $this->getParameter("id");
        if (!$this->id) { 
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
			$this->registro = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id")->rows[0];			
			$this->items = $this->DB_fetch_array("SELECT id, descricao, id_modelo, imagem_exemplo as imagem, lib_access, required, ordem FROM hbrd_app_vistoria_modelo_item WHERE id_modelo = $this->id AND delete_at IS NULL ORDER BY ordem")->rows;
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
	}
    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
		$this->DB_update($this->table,['delete_at' => date('Y-m-d H:i:s')], " WHERE id=" . $id);
        $this->inserirRelatorio("Deletou modelo de vistoria id [{$id}]");
        echo $this->getPath();
    }
    protected function saveAction() {
		$data = $this->arrayTable($_POST, $this->table);
		unset($data['delete_at']);
		unset($data['stats']);
		unset($data['ordem']);
		if (!$data['id']) {
			if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
			$table = $this->DB_fetch_array("SELECT * FROM $this->table WHERE delete_at is null");
            $data['ordem'] = $table->num_rows + 1;
			$data['id'] = $this->DB_insert($this->table, $data);
			$this->inserirRelatorio("Criou modelo de vistoria id ".$data['id']);
		} else {
			if (!$this->permissions[$this->permissao_ref]['editar']) exit();
			$this->DB_update($this->table, $data, " where id =".$data['id']);
			$this->inserirRelatorio("Alterou modelo de vistoria id ".$data['id']);
		}
		echo $data['id'];
	}
	protected function itemAction() {
		$this->table = "hbrd_app_vistoria_modelo_item";
		$data = $this->arrayTable($_POST, $this->table);
		unset($data['delete_at']);
		unset($data['ordem']);
		if($data['imagem_exemplo']){
			$data['imagem_exemplo'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['imagem_exemplo']));
		} else unset($data['imagem_exemplo']);
		if (!$data['id']) { 
			if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
			$table = $this->DB_fetch_array("SELECT * FROM $this->table WHERE delete_at is null AND id_modelo = {$data['id_modelo']}");
            $data['ordem'] = $table->num_rows + 1;
			$data['id'] = $this->DB_insert($this->table, $data);
			$this->inserirRelatorio("Criou item de modelo de vistoria id ".$data['id']);
		} else {
			if (!$this->permissions[$this->permissao_ref]['editar']) exit();
			$this->DB_update($this->table, $data, " where id =".$data['id']);
			$this->inserirRelatorio("Alterou item de modelo de vistoria id ".$data['id']);
		}
	}
	function deleteItemAction(){
		$this->table = "hbrd_app_vistoria_modelo_item";
		if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("deleteItem");
		$this->DB_update($this->table, ['delete_at' => date('Y-m-d H:i:s')], " WHERE id=" . $id);
        $this->inserirRelatorio("Deletou item de modelo de vistoria id [{$id}]");
        echo $this->getPath();
	}
	protected function imageItemAction() {
		$key = $this->getParameter("imageItem");
		if(strlen($key) != 22 || $key == '{{row.imagem_exemplo}}') return;
		$file = base64_decode($this->getObjectAws($key, 'hbrd_app_vistoria_modelo_item'));
        Util::echoFile($file);
	}
	protected function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
	}
	protected function ordenarItemsAction() {
		$this->id = $this->getParameter("id");
		$this->items = $this->DB_fetch_array("SELECT id, descricao, required FROM hbrd_app_vistoria_modelo_item  WHERE id_modelo = $this->id AND delete_at IS NULL ORDER BY ordem")->rows;
        $this->renderView($this->getService(), $this->getModule(), "ordenar");
	}
	protected function orderItemsAction() {
		$this->ordenarRegistros($_POST["array"], "hbrd_app_vistoria_modelo_item");
	}
	protected function deleteModelAction() {
		if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("deleteModel");
		$this->DB_update($this->table, ['delete_at' => date('Y-m-d H:i:s')], " WHERE id=" . $id);
        $this->inserirRelatorio("Deletou item de modelo de vistoria id [{$id}]");
	}
	protected function changestatusAction() {
		$this->DB_update($this->table, ['stats' => ($_POST['stats'] == '1') ? '0' : '1'], " where id =" . $_POST['id']);
	}
	protected function cloneAction() {
		$modelId = (int) $_POST['id'];
		if (!$modelId) return;
		$sourceModel = $this->DB_fetch_array("SELECT * FROM hbrd_app_vistoria_modelo WHERE id = {$modelId}")->rows[0];
		$items = $this->DB_fetch_array("SELECT * FROM hbrd_app_vistoria_modelo_item WHERE id_modelo = {$modelId}");
		unset($sourceModel['id']);
		$sourceModel['descricao'] = $sourceModel['descricao'] . ' (Cópia)';
		$newmodelId = $this->DB_insert($this->table, $sourceModel);
		foreach ($items->rows as $item) {
			unset($item['id']);
			$item['id_modelo'] = $newmodelId;
			$this->DB_insert('hbrd_app_vistoria_modelo_item', $item);
		}
		$this->inserirRelatorio("Clonou o modelo de vistoria de id [{$modelId}] para gerar modelo de id [{$newmodelId}]");
	}
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();