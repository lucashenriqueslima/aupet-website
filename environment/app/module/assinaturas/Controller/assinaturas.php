<?php
use System\Base\{SistemaBase, BadRequest};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'assinaturas';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_pet_termo";
        $this->module_icon = "icomoon-icon-pen";
        $this->module_title = "Termos de filiação";
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();

        $this->equipes = $this->DB_fetch_array("SELECT id, titulo FROM hbrd_app_equipe where delete_at is null");
        $this->especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_especie A ORDER BY A.titulo");
        $this->consultores = $this->DB_fetch_array("SELECT A.id, B.nome FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE (A.delete_at is null AND A.stats = 1)");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function datatableAction() {
		if (!$this->permissions[$this->permissao_ref]['ler']) exit();
		$aColumns = [
            "DATE_FORMAT(PROP.create_at, '%d/%m/%Y às %H:%i') create_ind",
            "DATE_FORMAT(TERMO.dt_envio, '%d/%m/%Y às %H:%i') dt_envio_mask",
            "CASE 
            WHEN (TERMO.doc_recusados IS NOT NULL) THEN 'Reprovado' 
            WHEN (TERMO.status = 'reanalise' AND TERMO.doc_recusados IS NULL) THEN 'Reanalise' 
            WHEN (TERMO.status = 'pendente' AND TERMO.doc_recusados IS NULL) THEN 'Pendente' 
            WHEN (TERMO.status = 'aprovada' AND TERMO.doc_recusados IS NULL) THEN 'Aprovado'
            END term_status",
            "PET.nome pet_nome"," ASSOC.nome assoc_nome", "PET.valor","TERMO.status","CONSULT.nome consultor_nome",
            "CLINICA.nome clinica_nome","IND_ASSOC.nome associado_ind_nome","PROP.indicador","EQUIPE.titulo equipe",
            "PET.id id_pet","ESPC.titulo pet_especie"
		];
        $aColumnsWhere = ['B.nome',"B.placa","C.nome","B.modelo"];
        $sortColumns = ["PROP.create_at","TERMO.dt_envio","ASSOC.nome","CONSULT.nome","PET.nome","g.valor","ESPC.titulo","TERMO.status"];
        $sTable = "
        hbrd_app_pet_termo TERMO
        LEFT JOIN hbrd_app_pet PET ON PET.id = TERMO.id_pet
        LEFT JOIN hbrd_app_proposta PROP ON PROP.id = PET.id_proposta
        LEFT JOIN hbrd_app_pessoa ASSOC ON ASSOC.id = PROP.id_pessoa
        LEFT JOIN (SELECT Z.*, Y.nome FROM hbrd_app_consultor Z INNER JOIN hbrd_app_pessoa Y ON Y.id = Z.id_pessoa) CONSULT ON CONSULT.id = PROP.id_consultor
        LEFT JOIN (SELECT Z.*, Y.nome FROM hbrd_app_clinica Z INNER JOIN hbrd_app_pessoa Y ON Y.id = Z.id_pessoa) CLINICA ON CLINICA.id = PROP.id_clinica
        LEFT JOIN (SELECT Z.*, Y.nome FROM hbrd_app_associado Z INNER JOIN hbrd_app_pessoa Y ON Y.id = Z.id_pessoa) IND_ASSOC ON IND_ASSOC.id = PROP.id_associado
        LEFT JOIN hbrd_app_equipe EQUIPE ON EQUIPE.id = CONSULT.id_equipe OR EQUIPE.id = CLINICA.id_equipe OR EQUIPE.id = IND_ASSOC.id_equipe
        LEFT JOIN hbrd_app_pet_especie ESPC ON ESPC.id = PET.id_especie
        ";
        // $sWhere = "WHERE A.id_contrato IS NOT NULL AND B.classificacao = 'pendente' AND A.assinatura IS NOT NULL AND (A.status IS NULL OR A.status <> 'aprovada') AND (A.doc_recusados IS NULL OR A.doc_recusados NOT LIKE '%null%')";
        $sWhere = "";

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

        if (isset($_POST['dtenvde']) AND $_POST['dtenvde'] != '') {
            $dtenvde = implode('-', array_reverse(explode('/',$_POST['dtenvde'])));
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " DATE_FORMAT(TERMO.dt_envio, '%Y-%m-%d') >= '{$dtenvde}' )";
        }   
        if (isset($_POST['dtenvate']) AND $_POST['dtenvate'] != '') {
            $dtenvate = implode('-', array_reverse(explode('/',$_POST['dtenvate'])));
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " DATE_FORMAT(TERMO.dt_envio, '%Y-%m-%d') <= '{$dtenvate}' )";
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
            $sWhere .= " TERMO.status = '{$_POST['classificacao']}' )";
        }

        /* ==================================================================================== */


        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT ".$_POST['iDisplayStart'].", " .$_POST['iDisplayLength'];
		}
        if ($_POST['sSearch'] != "") {
            if ($sWhere == "") {
                $sWhere = "WHERE (";
            } else {
                $sWhere .= " and (";
            }
            for ($i = 0; $i < count($aColumnsWhere); $i++) {
                $sWhere .= $aColumnsWhere[$i]." LIKE '%".$_POST['sSearch']."%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        if (isset($_POST['iSortCol_0']) && (bool)$sortColumns[$_POST['iSortCol_0']]) {
            $sOrder = "ORDER BY ";
            $campo_array = $sortColumns[$_POST['iSortCol_0']];
            $sOrder .= $campo_array." ".$_POST['sSortDir_0'];
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
        // if (isset($_POST['colaborador']) AND $_POST['colaborador'] != '') {
        //     $sWhere .= ($sWhere == '') ? "WHERE (" : " AND ";
        //     if($_POST['colaborador'] > 0) $sWhere .= " B.id_colaborador_responsavel = {$_POST['colaborador']} ";
        //     else $sWhere .= " B.id_colaborador_responsavel is null";
        // }
        $rResult = array();
        $queryExport = "SELECT SQL_CALC_FOUND_ROWS ".implode(", ", $aColumns)."
             FROM $sTable
            $sWhere
            GROUP BY TERMO.id_pet
            $sOrder
			$sLimit";
            //ORDER BY TERMO.dt_envio DESC, PET.create_at desc

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
            $row = array();
            $id = $aRow['id_pet'];
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$aRow['create_ind'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$aRow['dt_envio_mask'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$aRow['assoc_nome'].'</a></div>';
            $indicador = ($aRow['indicador'] == 'consultor' ? $aRow['consultor_nome'] : ($aRow['indicador'] == 'clinica' ? $aRow['clinica_nome'] : $aRow['associado_ind_nome']));

            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$aRow['indicador'].'<br>'.$indicador .'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$aRow['equipe'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$aRow['pet_nome'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$aRow['pet_especie'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPathIndication().'/edit/id/'.$id.'/alvo/termo">'.$this->formataMoedaShow($aRow['valor']).'</a></div>';
            switch($aRow['term_status']) {
                case 'Pendente': $cor = '#f9f88b'; break;
                case 'Reanalise': $cor = '#7ad0fb'; break;
                case 'Reprovado': $cor = '#f79898'; break;
                case 'Aprovado': $cor = '#90ee90'; break;
            }
            $row[] = "<div style='background-color:$cor' align='center'><a style='color: black;' href='{$this->getPathIndication()}/edit/id/$id'>{$aRow['term_status']}</a></div>";
            $output['aaData'][] = $row;
        }
        $output['queryExport'] = preg_replace( "/\r|\n/", "", $queryExport );
        echo json_encode($output);
    }
    private function getPathIndication(){
		return 'app/proposta';
	}
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();