<?php
use System\Base\{SistemaBase, BadRequest};
use adm\classes\{Util};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "consultor";
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_equipe";
        $this->module_icon = "icomoon-icon-users-2";
        $this->module_title = "Equipes";
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $cols = [ 
            'Z.*', 
            'B.titulo AS regional',
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE B.id_equipe = Z.id) total', 
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE B.id_equipe = Z.id  AND E.classificacao = "pendente") pendentes', 
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE B.id_equipe = Z.id  AND E.classificacao = "arquivada") arquivadas', 
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE B.id_equipe = Z.id  AND E.classificacao = "ativada") ativadas', 
            '(SELECT GROUP_CONCAT(A.nome) FROM hbrd_app_pessoa A INNER JOIN hbrd_app_consultor B ON A.id = B.id_pessoa INNER JOIN hbrd_app_equipe_coordenador C ON C.id_consultor = B.id WHERE C.id_equipe = Z.id) coordinators'
        ];
        $selectCols = implode(', ', $cols);
        $result = $this->DB_fetch_array("SELECT DISTINCT {$selectCols} FROM hbrd_app_equipe Z LEFT JOIN hbrd_app_regional B ON Z.id_regional = B.id LEFT JOIN hbrd_app_equipe_coordenador C ON Z.id = C.id_equipe LEFT JOIN hbrd_app_consultor D ON C.id_consultor = D.id LEFT JOIN hbrd_app_pessoa E ON D.id_pessoa = E.id WHERE Z.delete_at IS NULL ORDER BY Z.titulo")->rows;
        $this->list = array_map(function ($item) {
            $calc = (float) ((($item['ativadas'] ?? 0) / ($item['total'] ?? 0)) * 100) ?? 0;
            $item['converted'] = number_format( is_nan($calc) ? 0 : $calc, 2, ',', '.' );
            return $item;
        }, $result);
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = $this->DB_fetch_array("SELECT A.titulo, A.id, A.id_regional FROM $this->table A WHERE A.id = $this->id")->rows[0];
            $this->consultores = $this->DB_fetch_array("SELECT A.id, B.nome, A.id_equipe, (SELECT IF(id_consultor IS NULL, 0, 1) FROM hbrd_app_equipe_coordenador WHERE id_equipe = $this->id AND id_consultor = A.id) coordenador FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE A.delete_at IS NULL AND A.stats = 1 AND A.id_equipe = $this->id AND A.solicitado = 0")->rows;
        }
        $this->regionais = $this->DB_fetch_array("SELECT * FROM hbrd_app_regional WHERE delete_at IS NULL AND stats = 1")->rows;
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    protected function saveAction() {
        $formulario = $this->formularioObjeto($_POST, $this->table);
        unset($formulario->delete_at);
        unset($formulario->coordenadores);
        if (!$formulario->id) {
            if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
            $formulario->id = $this->DBInsert($this->table, $formulario);
            $this->inserirRelatorio("Cadastrou equipe id {$formulario->id}");
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) exit();
            $this->DBUpdate($this->table, $formulario, " WHERE id=" . $formulario->id);
            $this->DBDelete('hbrd_app_equipe_coordenador', "WHERE id_equipe = {$formulario->id}");
            $this->inserirRelatorio("Alterou equipe id {$formulario->id}");
        }
        foreach ($_POST['coordenadores'] as $coordenador) {
            $this->DB_insert('hbrd_app_equipe_coordenador', ['id_equipe' => $formulario->id, 'id_consultor' => $coordenador]);
        }
        echo $formulario->id;
    }
    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = ".$_POST['id'])->rows[0];
        $this->DB_update("hbrd_adm_consultant_has_team",['id_equipe'=>$_POST['id_equipe']],"where id_equipe = ".$_POST['id']);
        $this->DB_update($this->table,['delete_at'=>date('Y-m-d H:i:s')], " where id=" . $_POST['id']);
        $this->inserirRelatorio("Apagou equipe: [" . $dados['equipe'] . "] id: [{$_POST['id']}]");
        echo $this->getPath();
    }

	protected function exportAction() {
		$this->dados = array();
		foreach ($_GET as $key => $value) {
			if(strpos($key, 'data') !== 0 && $value != ''){
                $this->DB_exec("UPDATE hbrd_app_equipe SET meta='$value' WHERE id='$key' ");
                $indicacoes = $this->DB_fetch_array("SELECT COUNT(A.id) total, D.titulo as equipe FROM hbrd_app_proposta A LEFT JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe D ON D.id = B.id_equipe LEFT JOIN hbrd_app_pet P ON P.id_proposta = A.id  WHERE D.id = {$key} AND STR_TO_DATE('{$_GET['datade']} 00-00', '%d/%m/%Y %H-%i') <= P.activated_at AND STR_TO_DATE('{$_GET['dataate']} 23-59', '%d/%m/%Y %H-%i') >= P.activated_at AND P.classificacao = 'ativada'")->rows[0];
				$this->dados[] = ["equipe" =>$indicacoes['equipe'], "meta" => $value,"ativadas" => $indicacoes['total'],"diferenca"=> ($indicacoes['total'] - $value)];
			}
		}
		function cmp($a, $b){
			if ($a['ativadas'] == $b['ativadas']) return 0;
			return ($a['ativadas'] < $b['ativadas']) ? 1 : -1;
		}
		usort($this->dados, "cmp");
		ob_start();
        include(dirname(__DIR__).'/export/indicacoes.phtml');
        $content = ob_get_clean();
        // die($content);
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'pt');
		$html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($content);
		$html2pdf->Output("equipes-".date("Y-m-d").".pdf");
    }

	protected function validaGerarRelatorio($form){
		$resposta = new \stdClass();
        $resposta->return = true;
		foreach($form as $key => $value){
			if ($value == "") {
				$resposta->type = "validation";
				$resposta->message = "Preencha este campo";
				$resposta->field = $key;
				$resposta->return = false;
				return $resposta;
			}
		}
        return $resposta;
    }
    protected function exportConsultoresAction() {
        $this->dados = array();
		foreach ($_GET as $key => $value) {
			if(strpos($key, 'data') !== 0 && $value != ''){
                $this->DB_exec("UPDATE hbrd_app_equipe SET meta_consultores='$value' WHERE id='$key' ");
                $indicacoes = $this->DB_fetch_array("SELECT COUNT(B.id) total, D.titulo as equipe FROM hbrd_app_consultor B LEFT JOIN hbrd_app_equipe D ON D.id = B.id_equipe WHERE D.id = {$key} AND STR_TO_DATE('{$_GET['datade']} 00-00', '%d/%m/%Y %H-%i') <= B.create_at AND STR_TO_DATE('{$_GET['dataate']} 23-59', '%d/%m/%Y %H-%i') >= B.create_at")->rows[0];
				$this->dados[] = ["equipe" =>$indicacoes['equipe'], "meta" => $value,"ativadas" => $indicacoes['total'],"diferenca"=> ($indicacoes['total'] - $value)];
			}
		}
		function cmp($a, $b){
			if ($a['ativadas'] == $b['ativadas']) return 0;
			return ($a['ativadas'] < $b['ativadas']) ? 1 : -1;
		}
		usort($this->dados, "cmp");
		ob_start();
        include(dirname(__DIR__).'/export/consultores.phtml');
		$content = ob_get_clean();
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'pt');
		$html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($content);
		$html2pdf->Output("equipes-consultores".date("Y-m-d").".pdf");
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();