<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Variaveis, Util};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'consultor';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_consultor";
        $this->module_title = "Lista de Consultores";
        $this->module_icon = "icomoon-icon-user";
        $this->logo_uploaded = "";
        $this->variaveis = new Variaveis();
    }
    public function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->equipes = $this->DB_fetch_array("SELECT id, titulo FROM hbrd_app_equipe where delete_at is null");
		$this->regionais = $this->DB_fetch_array("SELECT * FROM hbrd_app_regional WHERE delete_at IS NULL AND stats = 1")->rows;
        $this->reativar_consultores = $this->DB_fetch_array("SELECT DATE_FORMAT(B.create_at, '%d/%m/%Y às %H:%i') criado_em, B.nome, A.id, B.cpf, B.email, B.telefone, C.titulo equipe FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa INNER JOIN hbrd_app_equipe C ON C.id = A.id_equipe WHERE A.delete_at IS NULL AND A.solicitado = 0 AND A.reativar = 1")->rows;
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    public function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            $this->registro = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = $this->id ")->rows[0];
        }
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->equipes = $this->DB_fetch_array("SELECT id, titulo FROM hbrd_app_equipe where delete_at is null");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    public function saveAction() {
		$consultor = $this->arrayTable($_POST, $this->table);
        $pessoa = $this->arrayTable($_POST, $this->table_pessoa);
        $pessoa['data_nascimento'] = ((bool)$pessoa['data_nascimento']) ? Util::maskToDate($pessoa['data_nascimento']) : null;
        if((bool)$_POST['novaSenha']) {
            $pessoa['salt'] = Util::createSalt();
            $pessoa['senha'] = Util::createSenhaHash($_POST['novaSenha'], $pessoa['salt']);
        }
        // FOTO PERFIL
        if($pessoa['foto'] && !strpos($pessoa['foto'],'com/aupet/')){
			$pessoa['foto'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['foto']));
		} else unset($pessoa['foto']);
        // RG
        if($pessoa['rg_frente'] && !strpos($consultor['rg_frente'],'com/aupet/')){
			$consultor['rg_frente'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['rg_frente']));
		} else unset($consultor['rg_frente']);
        if($pessoa['rg_verso'] && !strpos($consultor['rg_verso'],'com/aupet/')){
			$consultor['rg_verso'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['rg_verso']));
		} else unset($consultor['rg_verso']);
        // CNH
        if($pessoa['cnh_frente'] && !strpos($consultor['cnh_frente'],'com/aupet/')){
			$consultor['cnh_frente'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['cnh_frente']));
		} else unset($consultor['cnh_frente']);
        if($pessoa['cnh_verso'] && !strpos($consultor['cnh_verso'],'com/aupet/')){
			$consultor['cnh_verso'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['cnh_verso']));
		} else unset($consultor['cnh_verso']);
        if((bool)$consultor['id']) {
            if($consultor['solicitado'] == 1)  $consultor['solicitado'] = 0;
            $this->DBUpdate($this->table, $consultor, " WHERE id = ".$consultor['id']);
            $this->DBUpdate($this->table_pessoa, $pessoa, " WHERE id = ".$consultor['id_pessoa']);
            $acao = "Atualizou consultor id {$consultor['id']}";
        } else {
            $consultor['solicitado'] = 0;
            $consultor['id_pessoa'] = $this->DBInsert($this->table_pessoa, $pessoa);
            $consultor['id'] = $this->DBInsert($this->table, $consultor);
            $acao = "Registrou consultor id {$consultor['id']}";
        }
        $this->inserirRelatorio($acao);        
        echo $consultor['id'];
    }
    public function exportAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->dados = $this->DB_fetch_array($_SESSION['query_indication']);
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=Relatorio consultores ".date("Y-m-d H-i-s").".xls");
        header("Pragma: no-cache");  
        ob_start();
		include(dirname(__DIR__)."/export/export.phtml");
		$content = ob_get_clean();
		echo utf8_decode($content);
	}
    public function exporttabAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->dados = $this->DB_fetch_array($_SESSION['query_indication']);
        header('Content-Type: text/html; charset=utf-8', true);
        require_once dirname(__DIR__)."/export/export.phtml";
    }
	public function exportPdfAction() {
        $where = $whereTodos = '';
		if ($_GET["de"]) {
            $where .= " AND STR_TO_DATE('{$_GET['de']} 00-00', '%d/%m/%Y %H-%i') <= activated_at";
            $whereTodos .= " AND STR_TO_DATE('{$_GET['de']} 00-00', '%d/%m/%Y %H-%i') <= create_at";
        }
		if ($_GET["ate"]) {
            $where .= " AND STR_TO_DATE('{$_GET['ate']} 23-59', '%d/%m/%Y %H-%i') >= activated_at";
            $whereTodos .= " AND STR_TO_DATE('{$_GET['ate']} 23-59', '%d/%m/%Y %H-%i') >= create_at";
        }
        if ($_GET["equipe"]) $where .= " AND F.id = '{$_GET["equipe"]}' ";
        if ($_GET["id"]) $where .= " AND A.id In (".$_GET['id'].") ";
        $aColumns = array("A.nome",'F.equipe',
        "(SELECT COUNT(id) count FROM hbrd_adm_indication WHERE id_consultor = A.id AND classificacao = 'ativada' {$where} ) ativada",
        "(SELECT COUNT(id) count FROM hbrd_adm_indication WHERE id_consultor = A.id {$whereTodos}) total"
        );
        $select = "SELECT ".str_replace(" , ", " ", implode(", ", $aColumns));
        $where = " WHERE A.delete_at IS NULL AND (A.solicitado is null OR A.solicitado = 0) AND (SELECT COUNT(id) count FROM hbrd_adm_indication WHERE id_consultor = A.id AND classificacao = 'ativada' {$where} ) > 0 group by A.id";
        $query = $this->dataTableQuery();
        $queryExport = "{$select} {$query['from']} {$where}";
        // die($queryExport);
        $this->dados = $this->DB_fetch_array($queryExport);
        $this->dados = ($this->dados->num_rows) ? $this->dados->rows : [];
		usort($this->dados, function($a, $b){ if ($a['ativada'] == $b['ativada']) return 0; return ($a['ativada'] < $b['ativada']) ? 1 : -1; });
		ob_start();
		include(dirname(__DIR__).'/export/pdf.phtml');
		$content = ob_get_clean();
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'pt');
		$html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($content);
        $html2pdf->pdf->SetTitle("Relatório consultores ".date("d/m/Y"));
		$html2pdf->Output("consultores-".date("Y-m-d").".pdf");
    }
    private function dataTableQuery() {
        $aColumns = array(
            "Z.id", "Z.stats",
            "B.nome","B.email","B.telefone",
            "DATE_FORMAT(Z.create_at, '%d/%m/%Y') criado_em_dt", 
            "DATE_FORMAT(Z.create_at, 'às %H:%i') criado_em_hr", 
            "C.titulo equipe", 
            "D.titulo regional",
            "(SELECT COUNT(B.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_pet B ON B.id_proposta = A.id WHERE A.id_consultor = Z.id AND B.classificacao = 'pendente' GROUP BY A.id_consultor ) pendente",
            "(SELECT COUNT(B.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_pet B ON B.id_proposta = A.id WHERE A.id_consultor = Z.id AND B.classificacao = 'arquivada' GROUP BY A.id_consultor ) arquivada",
            "(SELECT COUNT(B.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_pet B ON B.id_proposta = A.id WHERE A.id_consultor = Z.id AND B.classificacao = 'ativada' GROUP BY A.id_consultor ) ativada",
            "(SELECT COUNT(B.id) FROM hbrd_app_proposta A INNER JOIN hbrd_app_pet B ON B.id_proposta = A.id WHERE A.id_consultor = Z.id GROUP BY A.id_consultor ) total"
		);
		$sortColumns = array("","Z.create_at","nome","Z.email","equipe", "qtd_comissionados","pendente","arquivada","ativada","proprio","total");
        $aColumnsWhere = array("B.nome", 'C.titulo', 'Z.id','B.email');
        $sTable = "
            $this->table Z
            LEFT JOIN hbrd_app_pessoa B ON B.id = Z.id_pessoa
            LEFT JOIN hbrd_app_equipe C ON C.id = Z.id_equipe
            LEFT JOIN hbrd_app_regional D ON D.id = C.id_regional
           ";
        $sWhere = ' WHERE (Z.delete_at IS NULL AND Z.solicitado = 0)';
        if (isset($_POST['equipes']) AND $_POST['equipes'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['equipes']) as $value) {
                $sWhere .= " Z.id_equipe = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }
        if (!empty($_POST['regionais'])) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['regionais']) as $value) {
                $sWhere .= " C.id_regional = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }
        if (isset($_POST['datade']) AND $_POST['datade'] != '') {
            $datade = implode('-', array_reverse(explode('/',$_POST['datade'])));
            $sWhere .= ($sWhere == '') ? "WHERE " : " AND ";
            $sWhere .= " DATE_FORMAT(Z.create_at, '%Y-%m-%d') >= '{$datade}' ";
        }
        if (isset($_POST['dataate']) AND $_POST['dataate'] != '') {
            $dataate = implode('-', array_reverse(explode('/',$_POST['dataate'])));
            $sWhere .= ($sWhere == '') ? "WHERE " : " AND ";
            $sWhere .= " DATE_FORMAT(Z.create_at, '%Y-%m-%d') <= '{$dataate}' ";
        }
        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT ".$_POST['iDisplayStart'].", " .$_POST['iDisplayLength'];
		}
        if ((bool)$sortColumns[$_POST['iSortCol_0']]) {
			$sOrder = "ORDER BY ";
            $campo_array = $sortColumns[$_POST['iSortCol_0']];
             $sOrder .= $campo_array." ".$_POST['sSortDir_0'];
            if ($sOrder == "ORDER BY ") {
                $sOrder = "";
            }
        }
        if ($_POST['sSearch'] != "") {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            for ($i = 0; $i < count($aColumnsWhere); $i++) {
                $sWhere .= $aColumnsWhere[$i]." LIKE '%".$_POST['sSearch']."%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }
        for ($i = 0; $i < count($aColumns); $i++) {
            if ($_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i]." LIKE '%".$_POST['sSearch_'.$i]."%' ";
            }
        }
        return [
            'select' => "SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns)),
            'from' => " FROM $sTable",
            'where' => $sWhere,
            'order' => $sOrder,
            'limit' => $sLimit
        ];        
    }
    public function datatableAction() {
		if (!$this->permissions[$this->permissao_ref]['ler']) exit();
        $query = $this->dataTableQuery();
        $q = "{$query['select']} {$query['from']} {$query['where']} GROUP BY Z.id {$query['order']} {$query['limit']}";
        $_SESSION['query_indication'] = null;
        $_SESSION['query_indication'] = $q;
        $sQuery = $this->DB_fetch_array($q);
        if ($sQuery->num_rows) $rResult = $sQuery->rows;
        $iFilteredTotal = $sQuery->num_rows;
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            $id = $aRow['id'];
            $row[] = "<div style='max-width:20px;width:20px' align='center'><input onclick='selectListItem(this, event)' id='$id' nome='{$aRow ['nome']}' class='selecionados checkbox' type='checkbox' value={$aRow["id"]} /></div>";
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['criado_em_dt'].'<br>'.$aRow ['criado_em_hr'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['nome'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['email'].'<br>'.$aRow ['telefone'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$aRow ['equipe']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$aRow ['regional']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .($aRow ['pendente'] ?? 0). '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .($aRow ['arquivada'] ?? 0). '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .($aRow ['ativada'] ?? 0). '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .($aRow['total'] ?? 0). '</a></div>';
            $conversao = ((($aRow['ativada']?? 0) / ($aRow['total']?? 0)) * 100) ?? 0 ;
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$this->formataMoedaDeBanco($conversao). '</a></div>';
            $acoes  = '<div align="center">';
            $img = $this->main_template.($aRow['stats'] == '0' ? 'images/status_vermelho.png' : 'images/status_verde.png');
            $acoes .= "<a onclick='updateStatusItem($id)' class='cursor' title='".($aRow['stats'] == '0' ? 'Ativar' : 'Desativar')."'><img src='{$img}'></a>";
            $acoes .= '<a href="'.$this->getPath().'/edit/id/'.$id.'"><span class="icon12 icomoon-icon-pencil"></span></a>';
            $acoes .= "<span onclick='deleteItem($id)' class='cursor'><span class='icon12 icomoon-icon-remove'></span></span>";
            $acoes .= '</div>';
            $row[] = $acoes;
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
    }
    public function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id")->rows[0];
        $updateConsultant = $this->DB_sqlUpdate($this->table, array("delete_at" => date("Y-m-d H:i:s")), " WHERE id=".$id);
        if (empty($updateConsultant) OR ! $this->mysqli->query($updateConsultant)) throw new \Exception($updateConsultant);
        $this->inserirRelatorio("Deletou oficina [".$dados['nome']."] id [$id]");
        echo $this->getPath();
    }
    protected function aprovarSolicitacaoAction() {
        $dados = $this->DB_fetch_array("SELECT B.nome, A.id FROM $this->table A LEFT JOIN $this->table_pessoa B ON B.id = A.id_pessoa WHERE A.id = ?", [$_POST['id']])->rows[0];
        $this->DBUpdate($this->table, ['solicitado'=> 0], " WHERE id = ?", [$dados['id']]);
        $this->inserirRelatorio("Aprovou solicitação de consultor [{$dados['nome']}] id [{$dados['id']}]");        
    }
    protected function updateStatusAction() {
        $info = $this->DB_fetch_array("SELECT * from hbrd_app_consultor where id = ".$_POST['id'])->rows[0];
        $this->DBUpdate('hbrd_app_consultor',['stats'=> ($info['stats'] == 1 ? 0: 1)],' where id ='.$_POST['id']);
        if($info['reativar'] == 1) $this->DB_update('hbrd_app_consultor',['reativar'=> 0],' where id ='.$_POST['id']);
        // adicionar log sistema
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();