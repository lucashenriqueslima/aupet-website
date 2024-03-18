<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Variaveis, Util};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'consultor';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_clinica";
        $this->table2 = "hbrd_app_clinica_fotos";
        $this->table_pessoa = "hbrd_app_pessoa";
        $this->module_title = "Lista de clinicas";
        $this->module_icon = "icomoon-icon-user";
        $this->logo_uploaded = "";
        $this->variaveis = new Variaveis();
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
    public function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->solicitados_clinicas = $this->DB_fetch_array("SELECT DATE_FORMAT(B.create_at, '%d/%m/%Y às %H:%i') criado_em, B.nome, A.id, A.cnpj, B.email, B.telefone
        FROM hbrd_app_clinica A 
        INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa 
        WHERE A.delete_at IS NULL AND A.solicitado = 1")->rows;

        $this->reativar_clinicas = $this->DB_fetch_array("SELECT DATE_FORMAT(B.create_at, '%d/%m/%Y às %H:%i') criado_em, B.nome, A.id, A.cnpj, B.email, B.telefone
        FROM hbrd_app_clinica A 
        INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa 
        WHERE A.delete_at IS NULL AND A.solicitado = 0 AND A.reativar = 1")->rows;
                
		$this->beneficios = $this->DB_fetch_array("SELECT A.*, B.id_clinica FROM hbrd_app_plano_beneficio A LEFT JOIN hbrd_app_clinica_use_beneficio B ON (B.id_beneficio = A.id)". ((bool)$this->id ? " AND B.id_clinica = $this->id" : "")." GROUP BY A.id ORDER BY A.ordem");
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    public function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            $this->registro = $this->DB_fetch_array("SELECT B.*, A.* FROM $this->table A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = $this->id ")->rows[0];
        }
		$this->beneficios = $this->DB_fetch_array("SELECT A.*, B.id_clinica FROM hbrd_app_plano_beneficio A LEFT JOIN hbrd_app_clinica_use_beneficio B ON (B.id_beneficio = A.id) ". ((bool)$this->id ? "AND B.id_clinica = $this->id" : "")." GROUP BY A.id ORDER BY A.ordem");
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->equipes = $this->DB_fetch_array("SELECT id, titulo FROM hbrd_app_equipe where delete_at is null");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    public function saveAction() {
		$clinica = $this->arrayTable($_POST, $this->table);
        $pessoa = $this->arrayTable($_POST, $this->table_pessoa);
        $pessoa['data_nascimento'] = ((bool)$pessoa['data_nascimento']) ? Util::maskToDate($pessoa['data_nascimento']) : null;
        unset($pessoa['delete_at']);

        if($clinica['logo'] && !strpos($clinica['logo'],'com/aupet/')){
			$clinica['logo'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['logo']));
		} else unset($clinica['logo']);

        if((bool)$_POST['novaSenha']) {
            $pessoa['salt'] = Util::createSalt();
            $pessoa['senha'] = Util::createSenhaHash($_POST['novaSenha'], $pessoa['salt']);
        }

        if((bool)$clinica['id']) {
            $this->DB_delete("hbrd_app_clinica_use_beneficio", " id_clinica = '{$clinica['id']}' ");
            $this->DBUpdate($this->table, $clinica, " WHERE id = ".$clinica['id']);
            $this->DBUpdate($this->table_pessoa, $pessoa, " WHERE id = ".$clinica['id_pessoa']);
            $acao = "Atualizou clinica id {$clinica['id']}";
        } else {
            $id_pessoa = $this->DB_fetch_array("SELECT id FROM hbrd_app_pessoa WHERE email like '". $pessoa['email'] ."'")->rows[0];

            if($id_pessoa['id']){
                $this->DBUpdate($this->table_pessoa, $pessoa, " WHERE id = ". $id_pessoa['id']);
                $clinica['id_pessoa'] = $id_pessoa['id'];;
                $clinica['id'] = $this->DBInsert($this->table, $clinica);
                $acao = "Registrou clinica id {$clinica['id']}";
            } else {
                $clinica['id_pessoa'] = $this->DBInsert($this->table_pessoa, $pessoa);
                $clinica['id'] = $this->DBInsert($this->table, $clinica);
                $acao = "Registrou clinica id {$clinica['id']}";
            }

        }

        foreach ($_POST['beneficios'] as $beneficio) {
            $this->DBInsert('hbrd_app_clinica_use_beneficio', ['id_clinica' => $clinica['id'], 'id_beneficio' => $beneficio]);
		}

        $this->inserirRelatorio($acao);        
        echo $clinica['id'];
    }
    public function exporttabAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();

        $this->dados = $this->DB_fetch_array($_SESSION['query_export']);

        header('Content-Type: text/html; charset=utf-8', true);
        require_once dirname(__DIR__)."/export/export.phtml";
    }

    public function exportAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=Relatorio Clinicas".date("Y-m-d H-i-s").".xls");
        header("Pragma: no-cache");  
        $this->dados = $this->DB_fetch_array($_SESSION['query_export']);
        ob_start();
		include(dirname(__DIR__)."/export/export.phtml");
		$content = ob_get_clean();
		echo utf8_decode($content);
    }
    private function dataTableQuery() {
        $aColumns = array('A.*',"B.nome","B.email","B.telefone","DATE_FORMAT(A.create_at, '%d/%m/%Y') criado_em_dt", "DATE_FORMAT(A.create_at, 'às %H:%i') criado_em_hr", "C.titulo equipe", "D.titulo regional, E.estado estado,
        F.cidade cidade,
        GROUP_CONCAT(H.nome) especialidades,
        GROUP_CONCAT(H.id) id_especialidades,
        (SELECT COUNT(B.id) FROM hbrd_app_proposta P INNER JOIN hbrd_app_pet Z ON Z.id_proposta = P.id WHERE P.id_clinica = A.id AND Z.classificacao = 'pendente' GROUP BY P.id_clinica ) pendente,
        (SELECT COUNT(B.id) FROM hbrd_app_proposta P INNER JOIN hbrd_app_pet Z ON Z.id_proposta = P.id WHERE P.id_clinica = A.id AND Z.classificacao = 'arquivada' GROUP BY P.id_clinica ) arquivada,
        (SELECT COUNT(B.id) FROM hbrd_app_proposta P INNER JOIN hbrd_app_pet Z ON Z.id_proposta = P.id WHERE P.id_clinica = A.id AND Z.classificacao = 'ativada' GROUP BY P.id_clinica ) ativada,
        (SELECT COUNT(B.id) FROM hbrd_app_proposta P   INNER JOIN hbrd_app_pet Z   ON Z.id_proposta = P.id WHERE P.id_clinica = A.id GROUP BY P.id_clinica ) total
        "
		);
		$sortColumns = array("","A.create_at","nome","A.email","equipe", "qtd_comissionados","pendente","arquivada","ativada","proprio","total");
        $aColumnsWhere = array("B.nome", 'C.titulo', 'A.id','B.email');
        $sTable = "
            $this->table A
            LEFT JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa
            LEFT JOIN hbrd_app_equipe C ON C.id = A.id_equipe
            LEFT JOIN hbrd_app_regional D ON D.id = C.id_regional
            LEFT JOIN hbrd_main_util_state E ON B.id_estado = E.id
		    LEFT JOIN hbrd_main_util_city F ON B.id_cidade = F.id
            LEFT JOIN hbrd_app_clinica_use_beneficio G ON G.id_clinica = A.id 
            LEFT JOIN hbrd_app_plano_beneficio H ON G.id_beneficio = H.id
            
           ";
        $sWhere = 'WHERE (A.delete_at IS NULL AND A.solicitado = 0)';
        if (isset($_POST['equipes']) AND $_POST['equipes'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['equipes']) as $value) {
                $sWhere .= " A.id_equipe = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (isset($_POST['estado']) AND $_POST['estado'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['estado']) as $value) {
                $sWhere .= " B.id_estado = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (isset($_POST['cidades']) AND $_POST['cidades'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['cidades']) as $value) {
                $sWhere .= " B.id_cidade = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (isset($_POST['beneficios']) AND $_POST['beneficios'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['beneficios']) as $value) {
                $sWhere .= " H.id = '{$value}' OR";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (!empty($_POST['regionais'])) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['regionais']) as $value) {
                $sWhere .= " F.id_regional = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }
        if (isset($_POST['datade']) AND $_POST['datade'] != '') {
            $datade = implode('-', array_reverse(explode('/',$_POST['datade'])));
            $sWhere .= ($sWhere == '') ? "WHERE " : " AND ";
            $sWhere .= " DATE_FORMAT(A.create_at, '%Y-%m-%d') >= '{$datade}' ";
        }
        if (isset($_POST['dataate']) AND $_POST['dataate'] != '') {
            $dataate = implode('-', array_reverse(explode('/',$_POST['dataate'])));
            $sWhere .= ($sWhere == '') ? "WHERE " : " AND ";
            $sWhere .= " DATE_FORMAT(A.create_at, '%Y-%m-%d') <= '{$dataate}' ";
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
        $q = "{$query['select']} {$query['from']} {$query['where']} GROUP BY A.id {$query['order']} {$query['limit']}";
        $_SESSION['query_export'] = null;
        $_SESSION['query_export'] = $q;
        $sQuery = $this->DB_fetch_array($q);
        if ($sQuery->num_rows) $rResult = $sQuery->rows;
        $iFilteredTotal = $sQuery->total;
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
            $img = $this->main_template.($aRow['stats'] == 0 ? 'images/status_vermelho.png' : 'images/status_verde.png');
            $acoes .= "<a onclick='updateStatusItem($id)' class='cursor' title='".($aRow['stats'] == 0 ? 'Ativar' : 'Desativar')."'><img src='{$img}'></a>";
            $acoes .= '<a href="'.$this->getPath().'/edit/id/'.$id.'"><span class="icon12 icomoon-icon-pencil"></span></a>';
            $acoes .= "<span onclick='deleteItem($id)' class='cursor'><span class='icon12 icomoon-icon-remove'></span></span>";
            $acoes .= '</div>';
            $row[] = $acoes;
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
    }

    protected function updateStatusAction() {
        $client = $this->DB_fetch_array("SELECT * from $this->table where id = ".$_POST['id'])->rows[0];
        $this->DB_update($this->table,['stats'=> ($client['stats'] == 1 ? 0:1)],' where id ='.$_POST['id']);
        if($client['reativar'] == 1) $this->DB_update($this->table,['reativar'=> 0],' where id ='.$_POST['id']);
    }
    /* FUNÇÕES DE MANIPULAÇÂO DE GALERIA DE IMAGENS */

    public function fotosAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->id = $this->getParameter("id");
        $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_clinica = $this->id order by ordem");
        $this->renderAjax($this->getService(), $this->getModule(), "fotos");
    }

    public function fotoAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");

        $foto = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id");
        $this->foto = $foto->rows[0];

        $this->renderAjax($this->getService(), $this->getModule(), "foto");
    }

    public function uploadAction() {
        $this->id = $this->getParameter("id");

        $fields = array('id_clinica', 'ordem', 'url');

        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $type = explode('.',$_FILES['file']['name']);

            $simpleImage = new SimpleImage3();
            $simpleImage->fromFile($_FILES["file"]["tmp_name"]);
            $imagem = $simpleImage->bestFit(300,300)->toString();
            
            $key = $this->putObjectAws($imagem);

            if ($key) {
                $file_uploaded = $upload->file_uploaded;

                $dados = $this->DB_fetch_array("SELECT id, MAX(ordem) as ordem FROM $this->table2 WHERE id_clinica=" . $this->id . " GROUP BY id_clinica");
                if ($dados->num_rows == 0) {
                    $ordem = 1;
                } else {
                    $ordem = $dados->rows[0]['ordem'];
                    $ordem++;
                }

                $values = array($this->id,  $ordem , $key);

                $data = [];
                for($i = 0; $i < count($fields); $i++) { $data[$fields[$i]] = $values[$i]; }

                $query = $this->DB_sqlInsert($this->table2, $data);
                if (empty($query) OR !  $this->mysqli->query($query)) throw new \Exception($query);
                
                echo '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';
            }
        }
    }

    public function uploadDelAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");

        $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id");
        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                $fotokey = explode('/', $foto['url']);
                $key = $fotokey[3] . '/' . $fotokey[4]; 
                $this->deleteObjectAWS($key);
            }
        }

        $this->inserirRelatorio("Apagou imagem instiucional legenda: [" . $fotos->rows[0]['legenda'] . "] id: [$this->id]");
        $this->DB_delete($this->table2, "id=$this->id");
    }

    public function orderAction() {

        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $this->ordenarRegistros($_POST["array"], $this->table2);
    }

    public function orderGalleryAction() {

        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    public function editPhotoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $id = $_POST['id'];
        $legenda = $_POST['legenda'];

        $fields_values['legenda'] = $legenda;

        $query = $this->DB_sqlUpdate($this->table2, $fields_values, " WHERE id=" . $id);

        $resposta = new stdClass();

        if ($this->mysqli->query($query)) {
            $resposta->type = "success";
            $this->inserirRelatorio("Editou imagem institucional legenda: [" . $legenda . "] id: [" . $id . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    protected function aprovarSolicitacaoAction() {
        $dados = $this->DB_fetch_array("SELECT B.nome, A.id FROM $this->table A LEFT JOIN $this->table_pessoa B ON B.id = A.id_pessoa WHERE A.id = ?", [$_POST['id']])->rows[0];
        $this->DBUpdate($this->table, ['solicitado'=> 0, 'situacao' => 'aprovado'], " WHERE id = ?", [$dados['id']]);
        $this->inserirRelatorio("Aprovou solicitação da Clinica [{$dados['nome']}] id [{$dados['id']}]");        
    }

}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();