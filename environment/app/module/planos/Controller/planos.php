<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Util,Variaveis};
class __classe__ extends SistemaBase {
	public $module = "";
	public $permissao_ref = "planos";
	function __construct() {
		parent::__construct();
		$this->table = "hbrd_app_planos";
		$this->module_icon = "fa fa-money";
		$this->module_title = "Planos";
		
	}
	protected function indexAction() {
		if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
		$this->list = $this->DB_fetch_array("SELECT A.titulo, A.id, (SELECT GROUP_CONCAT(C.titulo SEPARATOR ', ') FROM hbrd_app_plano_in_regional B LEFT JOIN hbrd_app_regional C ON C.id = B.id_regional WHERE B.id_plano = A.id) regionais FROM hbrd_app_planos A WHERE A.delete_at IS NULL ORDER BY A.ordem");
		$this->renderView($this->getService(), $this->getModule(), "index");
	}
	protected function editAction() {
		$this->id = $this->getParameter("id");
		if ($this->id == "") {
			if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
		} else {
			if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
			// (SELECT json_arrayagg(id_beneficio) FROM hbrd_adm_plan_has_benefit WHERE id_plano = $this->id) beneficios,
			$this->registro = $this->DB_fetch_array("SELECT A.*, (SELECT JSON_ARRAYAGG(id_regional) FROM hbrd_app_plano_in_regional WHERE id_plano = $this->id) regionais,  (SELECT id_termo FROM hbrd_app_plano_has_termo WHERE id_plano = $this->id) contratos FROM hbrd_app_planos A WHERE A.id = $this->id")->rows[0];
		}
		$this->beneficios = $this->DB_fetch_array("SELECT A.*, B.id_plano FROM hbrd_app_plano_beneficio A LEFT JOIN hbrd_app_plano_use_beneficio B ON (B.id_beneficio = A.id )". ((bool)$this->id ?  " AND B.id_plano = $this->id" : "")." GROUP BY  A.id ORDER BY A.ordem");
		$this->modelo_contrato = $this->DB_fetch_array("SELECT * FROM hbrd_app_termo A WHERE delete_at IS NULL");
		$this->regionais = $this->DB_fetch_array("SELECT * FROM hbrd_app_regional WHERE delete_at IS NULL AND stats = 1")->rows;
		$variaveis = new Variaveis();
		$this->variaveis = $variaveis->planovariaveis;
		$this->renderView($this->getService(), $this->getModule(), "edit");
	}
	protected function saveAction() {
		$data = $this->formularioObjeto($_POST, $this->table);
		unset($data->delete_at);
		unset($data->ordem);
		unset($data->stats);

		if (!$data->id) {
			$data->stats = 0;
			if (!$this->permissions($this->permissao_ref,'gravar')) throw new BadRequest('Você não permissao para gravar');

			$table = $this->DB_fetch_array("SELECT * FROM $this->table WHERE delete_at is null");
            $data->ordem = $table->num_rows + 1;
			$data->id = $this->DBInsert($this->table, $data);
			$this->inserirRelatorio("Criou plano id " . $data->id);
		} else {
			if (!$this->permissions($this->permissao_ref,'editar')) throw new BadRequest('Você não permissao para editar');

			$this->DB_update('hbrd_app_planos', $data, " WHERE id='{$data->id}'");
			$this->DB_delete("hbrd_app_plano_in_regional", " id_plano = '{$data->id}' ");
			$this->DB_delete("hbrd_app_plano_use_beneficio", " id_plano = '{$data->id}' ");
			$this->DB_delete('hbrd_app_plano_has_termo', "id_plano = '{$data->id}'");
			$this->inserirRelatorio("Editou plano id " . $data->id);
			//setar novo valor do plano nos Pets 
			$this->DB_update('hbrd_app_pet', ['valor'=> $data->valor], " WHERE id_plano ='{$data->id}'");

		}

		$this->DBInsert('hbrd_app_plano_has_termo', ['id_termo' => $_POST['contratos'], 'id_plano' => $data->id]);

		foreach ($_POST['regionais'] as $regional) {
            $this->DBInsert('hbrd_app_plano_in_regional', ['id_plano' => $data->id, 'id_regional' => $regional]);
		}
		foreach ($_POST['beneficios'] as $beneficio) {
            $this->DBInsert('hbrd_app_plano_use_beneficio', ['id_plano' => $data->id, 'id_beneficio' => $beneficio]);
		}
		echo $data->id;
	}
	protected function deletePlanAction() {
		if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
		$id = $this->getParameter("deletePlan");
		$this->DB_update('hbrd_app_planos', array("delete_at" => date("Y-m-d H:i:s")), " WHERE id=" . $id);
		$this->inserirRelatorio("Deletou plano id " . $id);
	}
	protected function changestatusAction() {
		$this->DB_update($this->table, ['stats' => ($_POST['stats'] === '1') ? '0' : '1'], " where id =" . $_POST['id']);
	}
	protected function cloneAction() {
		$planId = (int) $_POST['id'];
		if (!$planId) return;
		$sourcePlan = $this->DB_fetch_array("SELECT * FROM hbrd_app_planos WHERE id = {$planId}")->rows[0];
		$items = $this->DB_fetch_array("SELECT * FROM hbrd_app_plano_use_beneficio WHERE id_plano = {$planId} ");
		$regional = $this->DB_fetch_array("SELECT * FROM hbrd_app_plano_in_regional WHERE id_plano = {$planId}");
		$modelo = $this->DB_fetch_array("SELECT * FROM hbrd_app_plano_has_termo WHERE id_plan = {$planId}");
		unset($sourcePlan['id']);
		$sourcePlan['titulo'] = $sourcePlan['titulo'] . ' (Cópia)';
		$newPlanId = $this->DB_insert($this->table, $sourcePlan);
		if ($items->num_rows) {
			foreach ($items->rows as $item) {
				unset($item['id']);
				$item['id_plano'] = $newPlanId;
				$this->DB_insert('hbrd_app_plano_use_beneficio', $item);
			}
		}
		if ($regional->num_rows) {
			foreach ($regional->rows as $item) {
				$item['id_plano'] = $newPlanId;
				$this->DB_insert('hbrd_app_plano_in_regional', $item);
			}
		}
		if ($modelo->num_rows) {
			foreach ($modelo->rows as $item) {
				$item['id_plano'] = $newPlanId;
				$this->DB_insert('hbrd_app_plano_has_termo', $item);
			}
		}
		$this->inserirRelatorio("Clonou o plano de id [{$planId}] para gerar plano de id [{$newPlanId}]");
	}
	protected function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
	}
	protected function previewPDFAction() {
		$id = $this->getParameter("previewPDF");
		$data = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_planos A WHERE A.id = $id")->rows[0];
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8','default_font' => 'helvetica', 'margin_top' => 5,'margin_bottom' => 5,'margin_left'=> 5,'margin_right'=>5]);
        $mpdf->WriteHTML($data['shared_pdf']);
        $name = 'Proposta_de_adesao.pdf';
        $mpdf->Output($name,\Mpdf\Output\Destination::INLINE);
	}
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();
