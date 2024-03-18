<?php
use System\Base\{SistemaBase, BadRequest};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "sectional";
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_regional";
        $this->module_icon = "icomoon-icon-list-2";
        $this->module_title = "Regionais";
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $cols = [
            'Z.*',"Z.meta_ativacoes meta",
            "(SELECT GROUP_CONCAT(titulo) FROM hbrd_app_equipe WHERE id_regional = Z.id and delete_at is null) equipes",
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE C.id_regional = Z.id) total', 
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE C.id_regional = Z.id  AND E.classificacao = "pendente") pendentes', 
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE C.id_regional = Z.id  AND E.classificacao = "arquivada") arquivadas', 
            '(SELECT COUNT(E.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE C.id_regional = Z.id  AND E.classificacao = "ativada") ativadas', 
            '(SELECT GROUP_CONCAT(A.nome) FROM hbrd_app_pessoa A INNER JOIN hbrd_app_consultor B ON A.id = B.id_pessoa INNER JOIN hbrd_app_regional_gestor C ON C.id_consultor = B.id WHERE C.id_regional = Z.id) gestores'
        ];
        $query = "SELECT ".implode(', ',$cols)." FROM hbrd_app_regional Z LEFT JOIN hbrd_app_regional_gestor C ON Z.id = C.id_regional LEFT JOIN hbrd_app_consultor D ON C.id_consultor = D.id LEFT JOIN hbrd_app_pessoa E ON D.id_pessoa = E.id WHERE Z.delete_at IS NULL GROUP BY Z.id ORDER BY Z.titulo;";
        $result = $this->DB_fetch_array($query)->rows;
        $this->list = array_map(function ($item) {
            $calc = (float) ((($item['ativadas'] ?? 0) / ($item['total'] ?? 0)) * 100) ?? 0;
            $item['converted'] = number_format( is_nan($calc) ? 0 : $calc, 2, ',', '.' );
            $item['equipes_list'] = $this->DB_fetch_array("SELECT id, meta, equipe nome FROM hbrd_adm_team WHERE id_regional = {$item['id']} AND stats = 1 AND delete_at IS NULL")->rows;
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
            $this->registro = $this->DB_fetch_array("SELECT A.titulo, A.id FROM $this->table A WHERE A.id = $this->id")->rows[0];
            $this->consultores = $this->DB_fetch_array("SELECT A.id, B.nome, A.id_equipe, (SELECT IF(id_consultor IS NULL, 0, 1) FROM hbrd_app_regional_gestor WHERE id_regional = $this->id AND id_consultor = A.id) gestor FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa INNER JOIN hbrd_app_equipe C ON C.id = A.id_equipe WHERE A.delete_at IS NULL AND A.stats = 1 AND C.id_regional = $this->id AND A.solicitado = 0 ")->rows;
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
            $this->DBDelete('hbrd_app_regional_gestor', "WHERE id_regional = {$formulario->id}");
            $this->inserirRelatorio("Alterou equipe id {$formulario->id}");
        }
        foreach ($_POST['gestores'] as $row) {
            $this->DB_insert('hbrd_app_regional_gestor', ['id_regional' => $formulario->id, 'id_consultor' => $row]);
        }
        echo $formulario->id;
    }
    protected function deleteSectionalAction() {
		if (!$this->permissions[$this->permissao_ref]['excluir']) throw new BadRequest('Você não tem permissão para excluir');
		$id = $this->getParameter("deleteSectional");
		$this->DB_update($this->table, array("delete_at" => date("Y-m-d H:i:s")), " WHERE id=".$id);
		$this->DB_update('hbrd_adm_team', ["id_regional" => null], " WHERE id_regional=".$id);
		$this->DB_delete('hbrd_app_plano_in_regional', " id_regional=".$id);
		$this->inserirRelatorio("Deletou regional id ".$id);
    }
    protected function changestatusAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) throw new BadRequest('Você não tem permissão para editar');
        $this->DB_update($this->table, ['stats' => ($_POST['stats'] == '1') ? '0' : '1'], " where id =".$_POST['id']);
    }
    protected function exportAction() {
		foreach ($_GET["reg_meta"] as $key => $value) {
			if(!(bool)$value) continue;
            $this->DB_exec("UPDATE hbrd_app_regional SET meta_ativacoes='$value' WHERE id='$key' ");
            // $indicacoes = $this->DB_fetch_array(" SELECT COUNT(DISTINCT (A.id)) total, D.nome regional FROM hbrd_adm_indication A INNER JOIN hbrd_adm_consultant_has_team B ON B.id_consultor = A.id_consultor INNER JOIN hbrd_adm_team C ON C.id = B.id_equipe INNER JOIN hbrd_app_regional D ON D.id = C.id_regional WHERE D.id = {$key} AND A.classificacao = 'ativada' AND STR_TO_DATE('{$_GET['datade']} 00-00', '%d/%m/%Y %H-%i') <= A.activated_at AND STR_TO_DATE('{$_GET['dataate']} 23-59', '%d/%m/%Y %H-%i') >= A.activated_at ")->rows[0];
            $indicacoes = $this->DB_fetch_array("SELECT COUNT(E.id) total, D.titulo regional FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_regional D ON D.id = C.id_regional LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE D.id = {$key} AND E.classificacao = 'ativada' AND STR_TO_DATE('{$_GET['datade']} 00-00', '%d/%m/%Y %H-%i') <= E.activated_at AND STR_TO_DATE('{$_GET['dataate']} 23-59', '%d/%m/%Y %H-%i') >= E.activated_at")->rows[0];
            $this->dados[] = ["regional" => $indicacoes['regional'], "meta" => $value,"ativadas" => $indicacoes['total'],"diferenca"=> ($indicacoes['total'] - $value)];
		}
		function cmp($a, $b) { if ($a['ativadas'] == $b['ativadas']) return 0; return ($a['ativadas'] < $b['ativadas']) ? 1 : -1; }
		usort($this->dados, "cmp");
		ob_start();
        include(dirname(__DIR__).'/export/rel-regionais.phtml');
        $content = ob_get_clean();
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'pt');
		$html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($content);
		$html2pdf->Output("relatorio regionais ".date("Y-m-d").".pdf");
    }
    
    protected function exportWithTeamAction() {
		foreach ($_GET["reg_meta"] as $key => $value) {
            if(!(bool)$value) continue;
            $this->DB_exec("UPDATE hbrd_app_regional SET meta_ativacoes='$value' WHERE id='$key' ");
            // $indicacoes = $this->DB_fetch_array(" SELECT COUNT(DISTINCT (A.id)) total, D.nome regional FROM hbrd_adm_indication A INNER JOIN hbrd_adm_consultant_has_team B ON B.id_consultor = A.id_consultor INNER JOIN hbrd_adm_team C ON C.id = B.id_equipe INNER JOIN hbrd_app_regional D ON D.id = C.id_regional WHERE D.id = {$key} AND A.classificacao = 'ativada' AND STR_TO_DATE('{$_GET['datade']} 00-00', '%d/%m/%Y %H-%i') <= A.activated_at AND STR_TO_DATE('{$_GET['dataate']} 23-59', '%d/%m/%Y %H-%i') >= A.activated_at ")->rows[0];
            $indicacoes = $this->DB_fetch_array("SELECT COUNT(E.id) total, D.titulo regional FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_regional D ON D.id = C.id_regional LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE D.id = {$key} AND E.classificacao = 'ativada' AND STR_TO_DATE('{$_GET['datade']} 00-00', '%d/%m/%Y %H-%i') <= E.activated_at AND STR_TO_DATE('{$_GET['dataate']} 23-59', '%d/%m/%Y %H-%i') >= E.activated_at")->rows[0];
            foreach ($_GET["reg_".$key] as $key2 => $value2) {
                if(!(bool)$value2) continue;
                $this->DB_exec("UPDATE hbrd_adm_team SET meta='$value2' WHERE id='$key2' ");
            }
            // $equipes = $this->DB_fetch_array("SELECT COUNT(DISTINCT (A.id)) ativadas, C.equipe, C.meta FROM hbrd_adm_indication A INNER JOIN hbrd_adm_consultant_has_team B ON B.id_consultor = A.id_consultor INNER JOIN hbrd_adm_team C ON C.id = B.id_equipe INNER JOIN hbrd_app_regional D ON D.id = C.id_regional WHERE D.id = {$key} AND A.classificacao = 'ativada' AND STR_TO_DATE('{$_GET['datade']} 00-00', '%d/%m/%Y %H-%i') <= A.activated_at AND STR_TO_DATE('{$_GET['dataate']} 23-59', '%d/%m/%Y %H-%i') >= A.activated_at GROUP BY C.id ORDER BY COUNT(DISTINCT (A.id)) DESC")->rows;
            $equipes = $this->DB_fetch_array("SELECT COUNT(DISTINCT (A.id)) ativadas, C.titulo equipe, C.meta FROM hbrd_app_proposta A INNER JOIN hbrd_app_consultor B ON B.id = A.id_consultor LEFT JOIN hbrd_app_equipe C ON C.id = B.id_equipe LEFT JOIN hbrd_app_regional D ON D.id = C.id_regional LEFT JOIN hbrd_app_pet E ON E.id_proposta = A.id WHERE D.id = {$key} AND E.classificacao = 'ativada' AND STR_TO_DATE('01/05/2021 00-00', '%d/%m/%Y %H-%i') <= E.activated_at AND STR_TO_DATE('18/05/2021 23-59', '%d/%m/%Y %H-%i') >= E.activated_at GROUP BY C.id ORDER BY COUNT(DISTINCT (A.id)) DESC")->rows;
            $this->dados[] = [ "regional" => $indicacoes['regional'], "meta" => $value, "ativadas" => $indicacoes['total'], "diferenca"=> ($indicacoes['total'] - $value), "equipes" => $equipes ];
		}
		function cmp($a, $b) { if ($a['ativadas'] == $b['ativadas']) return 0; return ($a['ativadas'] < $b['ativadas']) ? 1 : -1; }
		usort($this->dados, "cmp");
		ob_start();
        include(dirname(__DIR__).'/export/rel-regionais-equipes.phtml');
        $content = ob_get_clean();
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'pt');
		$html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($content);
		$html2pdf->Output("relatorio regionais com equipes ".date("Y-m-d").".pdf");
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();
