<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Util,Variaveis};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'plantemplates';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_termo";
        $this->module_title = "Modelos de termo";
        $this->module_icon = "icomoon-icon-file-6";
        $this->variaveis_ = new Variaveis();
    }
    public function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->docs = $this->DB_fetch_array("SELECT * FROM $this->table WHERE delete_at IS NULL");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    public function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {            
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else { 
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id")->rows[0];
            $this->template = $this->registro['template'];
            unset($this->registro['template']);
            $this->variaveis = $this->DB_fetch_array("SELECT * FROM hbrd_app_termo_variables WHERE id_termo = $this->id order by ordem")->rows;
        }
        $this->items = $this->variaveis_->contratoPet;
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    public function saveAction() {
        unset($_POST["delete_at"]);
        $formulario = $this->formularioObjeto($_POST);
        $data = $this->formularioObjeto($_POST, 'hbrd_app_termo');
        if (!$formulario->id) {
            $formulario->id = $this->DB_insert('hbrd_app_termo', $data);
            $msg = "Adicionou template de documento [".$data->descricao."]";
        } else {
            $this->DB_update('hbrd_app_termo', $data, " WHERE id=" . $formulario->id);
            $msg = "Alterou template de documento [".$data->descricao."]";
        }
        $this->inserirRelatorio($msg);
        header('Content-type: text/html'); 
        echo $formulario->id;
    }
    public function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
        $registro = $this->DB_fetch_array("SELECT titulo FROM hbrd_app_termo WHERE id = $id")->rows[0];
        $this->inserirRelatorio("Deletou template de documento {$registro['descricao']}");
        $this->DB_update('hbrd_app_termo', ['delete_at' => date('Y-m-d H:i:s')]," where id =".$id );
        echo $this->getPath();
    }
    protected function saveVariableAction() {
        $table = $this->DB_fetch_array("SELECT * FROM hbrd_app_termo_variables WHERE id_termo = {$_POST['id_termo']}");
        $_POST['ordem'] = $table->num_rows + 1;
        $_POST['variavel'] = '{['.$_POST['variavel'].']}';
        $_POST['id'] = $this->DB_insert('hbrd_app_termo_variables',$_POST);
        echo json_encode($_POST);
    }
    protected function removeVariableAction() {
        $id = $this->getParameter("removeVariable");
        $this->DB_delete('hbrd_app_termo_variables', "id = $id");
    }
    protected function exemploAction() {
        set_time_limit(0);
        $id = $this->getParameter("exemplo");
        $contrato = $this->DB_fetch_array("SELECT * FROM hbrd_app_termo WHERE id = $id")->rows[0];
        $assinatura = "<img src='https://hibrida.ileva.com.br/main/uploads/hibrida/Assinatura.png' />";
        $contrato['template'] = str_replace('{[img_assinatura]}',$assinatura, $contrato['template']);
        // header('Content-type: text/html'); die($contrato['template']);
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8','default_font' => 'helvetica', 'margin_top' => 10,'margin_bottom' => 10,'margin_left'=> 10,'margin_right'=>10]);
        $mpdf->WriteHTML(stripcslashes($contrato['template']));
        $name = str_replace(' ', '-',$contrato['descricao']).'.pdf';
        $mpdf->Output($name,\Mpdf\Output\Destination::INLINE);
    }
    protected function cloneAction() {
		$planId = (int) $_POST['id'];
		if (!$planId) return;
		$sourcePlan = $this->DB_fetch_array("SELECT * FROM hbrd_app_termo WHERE id = {$planId}")->rows[0];
		$items = $this->DB_fetch_array("SELECT * FROM hbrd_app_termo_variables WHERE id_termo = {$planId}");
		unset($sourcePlan['id']);
		$sourcePlan['titulo'] = $sourcePlan['titulo'] . ' (Cópia)';
		$newPlanId = $this->DB_insert('hbrd_app_termo', $sourcePlan);
        foreach ($items->rows as $item) {
            unset($item['id']);
            $item['id_termo'] = $newPlanId;
            $this->DB_insert('hbrd_app_termo_variables', $item);
        }
		$this->inserirRelatorio("Clonou Termos de Filiação id [{$planId}] para id [{$newPlanId}]");
    }
    protected function ordenarItemsAction() {
		$this->id = $this->getParameter("id");
		$this->items = $this->DB_fetch_array("SELECT id, titulo FROM hbrd_app_termo_variables  WHERE id_termo = $this->id ORDER BY ordem")->rows;
        $this->renderView($this->getService(), $this->getModule(), "ordenar");
	}
	protected function orderItemsAction() {
		$this->ordenarRegistros($_POST["array"], "hbrd_app_termo_variables");
	}
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();