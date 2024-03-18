<?php
use System\Base\{SistemaBase, BadRequest};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'vistorias';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_adm_inspection";
        $this->module_icon = "icomoon-icon-list-2";
        $this->module_title = "Vistorias";
        $this->defaultWhere = "WHERE 
        A.create_at > DATE_ADD(CURDATE(), INTERVAL - 6 MONTH) AND 
        C.classificacao = 'pendente' AND 
        (SELECT COUNT(J.id) FROM hbrd_adm_inspection_item J LEFT JOIN hbrd_adm_inspection_model_item K ON K.id = J.id_model_item WHERE J.id_inspection = A.id AND J.photograp_at IS NOT NULL AND (J.aproved = 0 OR J.aproved is null) AND K.required = 1) > 0 AND 
        (SELECT COUNT(J.id) FROM hbrd_adm_inspection_item J LEFT JOIN hbrd_adm_inspection_model_item K ON K.id = J.id_model_item WHERE J.id_inspection = A.id AND K.required = 1 AND photograp_at IS NOT NULL) = (SELECT COUNT(J.id) FROM hbrd_adm_inspection_item J LEFT JOIN hbrd_adm_inspection_model_item K ON K.id = J.id_model_item WHERE J.id_inspection = A.id AND K.required = 1)";
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        // $items = $this->DB_fetch_array("SELECT ((SELECT COUNT(id) FROM hbrd_adm_inspection_item WHERE id_inspection = A.id AND aproved = 0) > 0) AS reprovado, A.reanalise FROM hbrd_adm_inspection A LEFT JOIN hbrd_adm_inspector B ON A.id_inspector = B.id LEFT JOIN hbrd_adm_indication C ON C.id_inspection = A.id LEFT JOIN hbrd_adm_consultant D ON D.id = C.id_consultor LEFT JOIN hbrd_adm_comissioned E ON E.id = C.id_comissionado LEFT JOIN hbrd_adm_inspection_model F ON F.id = A.id_model LEFT JOIN hbrd_adm_consultant_has_team G ON G.id_consultor = D.id LEFT JOIN hbrd_adm_team H ON H.id = G.id_equipe $this->defaultWhere ")->rows; 
        // $funRep = function($x){ return $x['reprovado'] == 1;};
        // $funRea = function($x){ return $x['reanalise'] == 1 && $x['reprovado'] == 0;};
        // $funPen = function($x){ return $x['reprovado'] == 0 && $x['reanalise'] == 0;};
        // $this->count = [
        //     'reprovado' => count(array_filter($items, $funRep)) ?: 0,
        //     'pendente' => count(array_filter($items, $funPen)) ?: 0,
        //     'reanalise' => count(array_filter($items, $funRea)) ?: 0
        // ];
        $this->equipes = $this->DB_fetch_array("SELECT id, titulo FROM hbrd_app_equipe where delete_at is null");
        $this->modelos = $this->DB_fetch_array("SELECT * FROM hbrd_app_vistoria_modelo WHERE delete_at is null AND stats = 1");
        $this->especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_especie A ORDER BY A.titulo");
        $this->consultores = $this->DB_fetch_array("SELECT A.id, B.nome FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE (A.delete_at is null AND A.stats = 1)");


        // $this->regionais = $this->DB_fetch_array("SELECT * FROM hbrd_adm_sectional where delete_at is null and stats = 1 order by nome");
        // $this->beneficios = $this->DB_fetch_array("SELECT C.id, C.nome FROM hbrd_adm_indication A LEFT JOIN hbrd_adm_benefit_indication B ON B.id_indication = A.id LEFT JOIN hbrd_adm_benefit C ON C.id = B.id_benefit WHERE C.id IS NOT NULL AND C.delete_at IS NULL AND C.stats = 1 GROUP BY C.id ORDER BY C.ordem");
        // $this->colabResponsavel = $this->DB_fetch_array("SELECT A.id, A.nome FROM hbrd_main_user A INNER JOIN hbrd_adm_indication B ON B.id_colaborador_responsavel = A.id GROUP BY A.nome");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function datatableAction() {
		if (!$this->permissions[$this->permissao_ref]['ler']) exit();
		$aColumns = [
            "DATE_FORMAT(VIS.create_at, '%d/%m/%Y às %H:%i') dt_vistoria",
            "DATE_FORMAT(VIS.ultimo_item_enviado, '%d/%m/%Y às %H:%i') dt_ult_ft",
            "DATE_FORMAT(PROP.create_at, '%d/%m/%Y às %H:%i') dt_proposta",
            "ASSOC.nome assoc_nome", "MOD_VIST.descricao mod_vist","ESPC.titulo pet_especie",
            "PET.nome pet_nome"," ASSOC.nome assoc_nome", "PET.valor","VIS.status",
            "CONSULT.nome consultor_nome", "CLINICA.nome clinica_nome","IND_ASSOC.nome associado_ind_nome",
            "PROP.indicador",
            "EQUIPE.titulo equipe",
            "PET.id id_pet"
        ];
		$sortColumns = ["A.create_at","C.create_at","VIS.ultimo_item_enviado","C.nome","E.nome","B.nome","H.equipe","D.nome","B.nome","C.modelo","C.modelo","F.descricao","C.valor"];
        $aColumnsWhere = ['ASSOC.nome','PROP.indicador','IND_ASSOC.nome','EQUIPE.titulo','PET.nome','CONSULT.nome'];
        $sIndexColumn = "A.id";
        $sTable = "
            hbrd_app_pet_vistoria VIS
            LEFT JOIN hbrd_app_pet PET ON PET.id = VIS.id_pet
            LEFT JOIN hbrd_app_proposta PROP ON PROP.id = PET.id_proposta
            LEFT JOIN hbrd_app_pessoa ASSOC ON ASSOC.id = PROP.id_pessoa
            LEFT JOIN (SELECT Z.*, Y.nome FROM hbrd_app_consultor Z INNER JOIN hbrd_app_pessoa Y ON Y.id = Z.id_pessoa) CONSULT ON CONSULT.id = PROP.id_consultor
            LEFT JOIN (SELECT Z.*, Y.nome FROM hbrd_app_clinica Z INNER JOIN hbrd_app_pessoa Y ON Y.id = Z.id_pessoa) CLINICA ON CLINICA.id = PROP.id_clinica
            LEFT JOIN (SELECT Z.*, Y.nome FROM hbrd_app_associado Z INNER JOIN hbrd_app_pessoa Y ON Y.id = Z.id_pessoa) IND_ASSOC ON IND_ASSOC.id = PROP.id_associado
            LEFT JOIN hbrd_app_equipe EQUIPE ON EQUIPE.id = CONSULT.id_equipe OR EQUIPE.id = CLINICA.id_equipe OR EQUIPE.id = IND_ASSOC.id_equipe
            LEFT JOIN hbrd_app_vistoria_modelo MOD_VIST ON MOD_VIST.id = VIS.id_modelo
            LEFT JOIN hbrd_app_pet_especie ESPC ON ESPC.id = PET.id_especie
            ";
        // $sWhere = $this->defaultWhere;
        $sWhere = "";
        if (isset($_POST['equipe']) AND $_POST['equipe'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND ";
            $sWhere .= " H.id= {$_POST['equipe']} ";
        }
        if (isset($_POST['regional']) AND $_POST['regional'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND ";
            $sWhere .= " I.id= {$_POST['regional']} ";
        }
        if((bool)$_POST['status']) {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND ";
            if($_POST['status'] == 'reprovado') {
                $sWhere .= " ((SELECT COUNT(id) FROM hbrd_adm_inspection_item WHERE id_inspection = A.id AND aproved = 0) > 0) ";
            } else if($_POST['status'] == 'reanalise'){
                $sWhere .= " ((SELECT COUNT(id) FROM hbrd_adm_inspection_item WHERE id_inspection = A.id AND aproved = 0) = 0) and A.reanalise = 1 ";
            } else if($_POST['status'] == 'pendente') {
                $sWhere .= " ((SELECT COUNT(id) FROM hbrd_adm_inspection_item WHERE id_inspection = A.id AND aproved = 0) = 0) and A.reanalise = 0 ";
            } else if($_POST['status'] == 'aprovada') {
                $sWhere .= " A.status = 'Aprovada' ";
            }
        }
        if (isset($_POST['beneficio']) AND $_POST['beneficio'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND ";
            $sWhere .= " J.id_benefit = {$_POST['beneficio']} ";
        }

        /* ==================================================================================== */

        if (isset($_POST['datade']) AND $_POST['datade'] != '') {
            $datade = implode('-', array_reverse(explode('/',$_POST['datade'])));
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " DATE_FORMAT(PROP.create_at, '%Y-%m-%d') >= '{$datade}' )";
        }   
        if (isset($_POST['dataate']) AND $_POST['dataate'] != '') {
            $dataate = implode('-', array_reverse(explode('/',$_POST['dataate'])));
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " DATE_FORMAT(PROP.create_at, '%Y-%m-%d') <= '{$dataate}' )";
		}

        if (isset($_POST['dtvistde']) AND $_POST['dtvistde'] != '') {
            $dtvistde = implode('-', array_reverse(explode('/',$_POST['dtvistde'])));
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " DATE_FORMAT(VIS.create_at, '%Y-%m-%d') >= '{$dtvistde}' )";
        }   
        if (isset($_POST['dtvistate']) AND $_POST['dtvistate'] != '') {
            $dtvistate = implode('-', array_reverse(explode('/',$_POST['dtvistate'])));
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " DATE_FORMAT(VIS.create_at, '%Y-%m-%d') <= '{$dtvistate}' )";
		}

        if ($_POST['consultores']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['consultores']) as $value) {
                $sWhere .= " PROP.id_consultor = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if ($_POST['equipes']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['equipes']) as $value) {
                $sWhere .= " EQUIPE.id = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if ($_POST['modVistorias']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['modVistorias']) as $value) {
                $sWhere .= " MOD_VIST.id = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if ($_POST['especies']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['especies']) as $value) {
                $sWhere .= " ESPC.id = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (isset($_POST['classificacao']) AND $_POST['classificacao'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " PET.classificacao = '{$_POST['classificacao']}' )";
        }

        /* ==================================================================================== */

        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT ".$_POST['iDisplayStart'].", " .
            $_POST['iDisplayLength'];
        }
        if (isset($_POST['iSortCol_0']) && (bool)$sortColumns[$_POST['iSortCol_0']]) {
            $sOrder = "ORDER BY ";
            $campo_array = $sortColumns[$_POST['iSortCol_0']];
            $sOrder .= $campo_array." ".$_POST['sSortDir_0'];
        }
        if ($_POST['sSearch'] != "") {
            if ($sWhere == "") { $sWhere = "WHERE ("; } 
            else { $sWhere .= " and ("; }
            for ($i = 0; $i < count($aColumnsWhere); $i++) {
                $sWhere .= $aColumnsWhere[$i]." LIKE '%".$_POST['sSearch']."%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }
        for ($i = 0; $i < count($aColumns); $i++) {
            if ($_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '') {
                if ($sWhere == "") { $sWhere = "WHERE "; } 
                else { $sWhere .= " AND "; }
                $sWhere .= $aColumns[$i]." LIKE '%".$_POST['sSearch_'.$i]."%' ";
            }
        }
        if (isset($_POST['colaborador']) AND $_POST['colaborador'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND ";
            if($_POST['colaborador'] > 0) $sWhere .= " C.id_colaborador_responsavel = {$_POST['colaborador']} ";
            else $sWhere .= " C.id_colaborador_responsavel is null";
        }
        $rResult = [];
        $_SESSION['query_export'] = "SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM $sTable $sWhere GROUP BY VIS.id $sOrder";
        $queryExport = "SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))." FROM $sTable $sWhere GROUP BY VIS.id $sOrder $sLimit";
        // die($queryExport);
		$sQuery = $this->DB_fetch_array($queryExport);
        if ($sQuery->num_rows) $rResult = $sQuery->rows;
        $iFilteredTotal = $sQuery->total;
        $output = [
            "sEcho" => intval($_POST['sEcho']),
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => []
        ];
        foreach ($rResult as $aRow) {
            $row = [];
            $id = $aRow['id_pet'];
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['dt_proposta'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['dt_vistoria']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['dt_ult_ft']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['assoc_nome']. '</a></div>';
            $indicador = ($aRow['indicador'] == 'consultor' ? $aRow['consultor_nome'] : ($aRow['indicador'] == 'clinica' ? $aRow['clinica_nome'] : $aRow['associado_ind_nome']));
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['indicador'].'<br>'.$indicador .'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['equipe']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['mod_vist']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['pet_nome']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['pet_especie']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['valor']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'">'.$aRow['status']. '</a></div>';
            $status = $this->getStatus($aRow);
            $row[] = "<div style='background-color:{$status['cor']}' align='left'><a style='color: black;' href='{$this->getPathIndication()}/edit/id/$id'>{$status['status']}</a></div>";
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
    }
    private function getStatus($aRow) {
        if($aRow['status'] == "Aprovada") {
            return ['cor' => '#83dcaa', 'status' => 'Aprovada'];
        } else if($aRow['reprovado'] == 1) {
            return ['cor' => '#f79898', 'status' => 'Reprovado'];
        } else if($aRow['reanalise'] == 1 && $aRow['reprovado'] == 0) {
            return ['cor' => '#7ad0fb', 'status' => 'Reanálise'];
        } else { 
            return ['cor' => '#f9f88b', 'status' => 'Pendente'];
        }
    }
     private function getPathIndication(){
		return 'app/proposta';
	}
    public function exportPdfAction() {
        $this->dados = $this->DB_fetch_array($_SESSION['query_export']);
        $this->dados = ($this->dados->num_rows) ? $this->dados->rows : [];
		ob_start();
		include(dirname(__DIR__).'/export/pdf.phtml');
		$content = ob_get_clean();
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A3', 'pt');
		$html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($content);
        $html2pdf->pdf->SetTitle("Relatório vistorias ".date("d/m/Y"));
		$html2pdf->Output("vitorias-".date("Y-m-d").".pdf");
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();