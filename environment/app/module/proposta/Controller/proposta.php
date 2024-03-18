<?php
use System\Base\{SistemaBase, BadRequest};
use PhpZip\ZipFile;
use app\classes\{Util,Variaveis};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "indication";
    public $solicitacao_adesao;
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_proposta";
        $this->table2 = "hbrd_app_pet_proposta";
        $this->module_title = "Propostas";
        $this->module_icon = "icomoon-icon-enter";
        $this->variaveis = new Variaveis();
    }
    function getReturn() {
        if(!empty($this->getParameter("id"))) return __service__.'/'.__classe__ .'/edit/id/'.$this->getParameter("id");
        else return __service__.'/'.__classe__;
    }

    public function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet WHERE id = $id")->rows[0];
        $this->DB_update('hbrd_app_pet', array("id_proposta" => null, "id_plano" => null), " WHERE id=".$id);
        $this->inserirRelatorio("Deletou pet [".$dados['nome']."] id [$id]");
        echo $this->getPath();
    }

    public function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->indicacoes = $this->DB_fetch_array("SELECT COUNT(id) count FROM hbrd_app_pet WHERE id_proposta IS NOT NULL ")->rows[0]['count'];
        $this->indicacoes_pendentes = $this->DB_fetch_array("SELECT COUNT(id) count FROM hbrd_app_pet WHERE classificacao = 'pendente' AND id_proposta IS NOT NULL")->rows[0]['count'];
        $this->indicacoes_arquivadas = $this->DB_fetch_array("SELECT COUNT(id) count FROM hbrd_app_pet WHERE classificacao = 'arquivada' AND id_proposta IS NOT NULL")->rows[0]['count'];
        $this->indicacoes_ativadas = $this->DB_fetch_array("SELECT COUNT(id) count FROM hbrd_app_pet WHERE classificacao = 'ativada' AND id_proposta IS NOT NULL")->rows[0]['count'];
		$this->dados_grafico = $this->DB_fetch_array ("SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at, SUM(indicacoes) indicacoes FROM ( SELECT *, COUNT(create_at) indicacoes FROM (SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at FROM $this->table WHERE DATE(create_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ) sub GROUP BY dia_mes ORDER BY create_at ) tb_tabela GROUP BY create_at");
        $this->consultores = $this->DB_fetch_array("SELECT A.id, B.nome FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE (A.delete_at is null AND A.stats = 1)");
        $this->planos = $this->DB_fetch_array("SELECT A.id, A.titulo FROM hbrd_app_planos A LIMIT 0,3")->rows;
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    public function chartAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission(false);
        $de = "'{$_POST['de']}'";
        $ate = "'{$_POST['ate']}'";
        $this->alvo = "visitors-chart";
        if (isset($_POST['alvo'])) $this->alvo = $_POST['alvo'];
        $this->dados_grafico = $this->DB_fetch_array( "SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at, SUM(indicacoes) indicacoes FROM (SELECT *, COUNT(create_at) indicacoes FROM (SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at FROM hbrd_app_proposta WHERE DATE(create_at) >= $de AND DATE(create_at) <= $ate ) sub GROUP BY dia_mes ORDER BY create_at) tb_tabela GROUP BY create_at");
        if ($this->dados_grafico->num_rows) $this->renderAjax($this->getService(), $this->getModule(), "chart");
        else echo "<div class='well' style='color:black'>$this->_sem_dados</div>";
    }
    public function editAction() {
        $this->id = $this->getParameter("id");
        if(!(bool)$this->id) {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = [
                "pet" => $this->DB_fetch_array("SELECT A.*, D.titulo status_etapa FROM hbrd_app_pet A INNER JOIN hbrd_app_proposta B ON B.id = A.id_proposta INNER JOIN hbrd_app_pessoa C ON C.id = B.id_pessoa LEFT JOIN hbrd_app_pet_proposta_status D ON D.id = A.id_status WHERE A.id = $this->id")->rows[0],
                "associado" => $this->DB_fetch_array("SELECT C.*, DATE_FORMAT(C.data_nascimento, '%d/%m/%Y') data_nascimento FROM hbrd_app_pet A INNER JOIN hbrd_app_proposta B ON B.id = A.id_proposta INNER JOIN hbrd_app_pessoa C ON C.id = B.id_pessoa WHERE A.id = $this->id")->rows[0],
                "proposta" => $this->DB_fetch_array("SELECT B.* FROM hbrd_app_pet A INNER JOIN hbrd_app_proposta B ON B.id = A.id_proposta INNER JOIN hbrd_app_pessoa C ON C.id = B.id_pessoa WHERE A.id = $this->id")->rows[0],
            ];
            if(!(bool)$this->registro['pet'] || !(bool)$this->registro['associado'] || !(bool)$this->registro['pet']['id_proposta']) throw new BadRequest('Registro não encontrado');
            // $this->vistoria = $this->DB_fetch_array("SELECT A.id, B.descricao, DATE_FORMAT(A.create_at, '%d/%m/%Y às %H:%i') criacao, A.observacao, A.status FROM hbrd_app_pet_vistoria A LEFT JOIN hbrd_app_vistoria_modelo B ON B.id = A.id_modelo WHERE A.id_pet = $this->id")->rows[0];
            // if((bool)$this->vistoria) {
            //     $this->vistoria['items'] = $this->DB_fetch_array("SELECT A.*, B.descricao, B.required FROM hbrd_app_pet_vistoria_item A LEFT JOIN hbrd_app_vistoria_modelo_item B ON B.id = A.id_modelo_item WHERE A.id_vistoria = {$this->vistoria['id']} ORDER BY A.ordem")->rows;
            //     $this->vistoria['anexos'] = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_vistoria_arquivo A WHERE A.id_vistoria = {$this->vistoria['id']} ORDER BY A.id")->rows;
            // }
            // $this->termo = $this->DB_fetch_array("SELECT A.*, B.titulo contrato FROM hbrd_app_pet_termo A LEFT JOIN hbrd_app_termo B ON B.id = A.id_contrato where A.id_pet = ?",[$this->id])->rows[0];
            // $this->termo['doc_recusados'] = json_decode($this->termo['doc_recusados']);
            $this->anotacaoHistorico = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.create_at, '%d/%m/%Y às %H:%i') data, IF(A.id_pessoa IS NOT NULL, B.nome, A.nome) usuario FROM hbrd_app_proposta_anotacao A LEFT JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE A.id_proposta = {$this->registro['pet']['id_proposta']}");
            $this->historico = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.create_at, '%d/%m/%Y às %H:%i') data, IF(A.id_pessoa IS NOT NULL, B.nome, A.nome) usuario FROM hbrd_app_proposta_hist A LEFT JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE A.id_proposta = {$this->registro['pet']['id_proposta']}")->rows;
            if($this->registro['proposta']['id_associado']) $this->assinatura = $this->DB_fetch_array("SELECT A.*, B.id_pessoa FROM hbrd_app_assinatura A INNER JOIN hbrd_app_associado B ON B.id = A.id_associado WHERE B.id = {$this->registro['proposta']['id_associado']}")->rows[0];
            
        }
        $this->motivos = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_motive A WHERE A.delete_at IS NULL AND stats = 1 ORDER BY nome")->rows;
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->consultores = $this->DB_fetch_array("SELECT A.id, B.nome FROM hbrd_app_consultor A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE (A.delete_at is null AND A.stats = 1)")->rows;
        $this->clinicas = $this->DB_fetch_array("SELECT A.id, A.nome_fantasia nome FROM hbrd_app_clinica A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE (A.delete_at is null AND A.stats = 1)")->rows;
        $this->associados = $this->DB_fetch_array("SELECT A.id, B.nome FROM hbrd_app_associado A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE (A.delete_at is null AND A.stats = 1)")->rows;
        $this->especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_especie A WHERE A.delete_at IS NULL ORDER BY A.titulo")->rows;
        $this->planos = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_planos A WHERE A.delete_at IS NULL ORDER BY A.titulo")->rows;
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    public function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) exit();
        $aColumns = [
            "DATE_FORMAT(PROP.create_at, '%d/%m/%Y às %H:%i') prop_criacao",
            "PET.id id_pet",
            "PET.nome pet_nome",
            "ASSOC.nome assoc_nome",
            "ASSOC.telefone assoc_telefone",
            "DATE_FORMAT(PET.activated_at, '%d/%m/%Y às %H:%i') pet_ativado_em",
            "PLAN.valor plano_valor",
            "PLAN.titulo plano_nome",
            "PET.classificacao pet_classificacao",
        ];
        $aColumnsWhere = ['PET.nome',"ASSOC.nome"];
        $sortColumns = ["PET.create_at","ASSOC.nome","","PET.nome","PLAN.valor","PET_STATUS.titulo","PET.classificacao"];
        $sTable = "
            hbrd_app_pet PET
            INNER JOIN hbrd_app_proposta PROP ON PROP.id = PET.id_proposta
            INNER JOIN hbrd_app_pessoa ASSOC ON ASSOC.id = PROP.id_pessoa
            LEFT JOIN hbrd_app_pet_proposta_status PET_STATUS ON PET_STATUS.id = PET.id_status
            LEFT JOIN hbrd_app_planos PLAN ON PLAN.id = PET.id_plano"
            ;
        $sWhere = '';
        $sOrder = 'ORDER BY PROP.create_at DESC';

        /* ================================================================================= */
        
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

        if ($_POST['plano']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['plano']) as $value) {
                $sWhere .= " PET.id_plano = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (isset($_POST['classificacao']) AND $_POST['classificacao'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE (" : " AND (";
            $sWhere .= " PET.classificacao = '{$_POST['classificacao']}' )";
        }


        /* ================================================================================= */
        
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
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            for ($i = 0; $i < count($aColumnsWhere); $i++) {
                $sWhere .= $aColumnsWhere[$i]." LIKE '%".addslashes($_POST['sSearch'])."%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

		$rResult = array();
		$collums =  implode(", ", $aColumns);
		$query = "SELECT SQL_CALC_FOUND_ROWS {$collums} FROM {$sTable} {$sWhere} GROUP BY PET.id {$sOrder} {$sLimit}";

        $_SESSION['query_indication'] = $query;

        $sQuery = $this->DB_fetch_array($query);
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
            $row[] = '<div align="center"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow['prop_criacao'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow['assoc_nome'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow['assoc_telefone'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow['pet_nome'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'. 'R$ ' . Util::formataMoeda($aRow['plano_valor']) .'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'. 'Plano '. $aRow['plano_nome'].'</a></div>';
            $corClassificação['Ativada'] = '#82f683';
            $corClassificação['Pendente'] = '#f2f167';
            $corClassificação['Arquivada'] = '#ef6563';
            $row[] = '<div align="left" class="'.$aRow['pet_classificacao'].'"><a style="color: black" href="'.$this->getPath().'/edit/id/'.$id.'">'.ucfirst($aRow['pet_classificacao']).'</a></div>';
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
    }
    public function exporttabAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
		$this->dados = $this->DB_fetch_array($_SESSION['query_indication']);
        header('Content-Type: text/html; charset=utf-8', true);
        require_once dirname(__DIR__)."/export/export.phtml";
    }
    public function exportAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=indicacoes-".date("Y-m-d H-i-s").".xls");
        header("Pragma: no-cache");
        $this->dados = $this->DB_fetch_array($_SESSION['query_indication']);
        ob_start();
		include(dirname(__DIR__)."/export/export.phtml");
		$content = ob_get_clean();
		echo utf8_decode($content);
    }
    public function exportPdfAction() {
        $this->dados = $this->DB_fetch_array($_SESSION['query_indication']);
        $this->dados = ($this->dados->num_rows) ? $this->dados->rows : [];
		ob_start();
		include(dirname(__DIR__).'/export/pdf.phtml');
		$content = ob_get_clean();
		$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('L', 'A4', 'pt');
		$html2pdf->setDefaultFont('Arial');
		$html2pdf->writeHTML($content);
		$html2pdf->Output("Indicações-" . date("Y-m-d") . ".pdf");
    }
    public function saveAction() {
        $proposta = $this->arrayTable($_POST["proposta"], "hbrd_app_proposta");
        $pessoa = $this->arrayTable($_POST["associado"], "hbrd_app_pessoa");
        $pet = $this->arrayTable($_POST["pet"], "hbrd_app_pet");
        $primeiro_status = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet_proposta_status A ORDER BY A.ordem")->rows[0];
        $pessoa['data_nascimento'] = ((bool)$pessoa['data_nascimento']) ? Util::maskToDate($pessoa['data_nascimento']) : null;
        if(!(bool)$pet['id']) {
            $pet['id_status'] = $primeiro_status['id'];
            $plano = $this->DB_fetch_array("SELECT * FROM hbrd_app_planos where id = ?",[$pet['id_plano']])->rows[0];
            $pet['valor'] = $plano['valor'];
        }
        if($pet['foto'] && !strpos($pet['foto'],'com/aupet/')) {
		    $pet['foto'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($pet['foto']));
		}
        $proposta['id_pessoa'] = ((bool)$pessoa['id']) ? $this->DBUpdateId("hbrd_app_pessoa", $pessoa) : $this->DBInsert("hbrd_app_pessoa", $pessoa);
        $pet['id_proposta'] = ((bool)$proposta['id']) ? $this->DBUpdateId("hbrd_app_proposta", $proposta) : $this->DBInsert("hbrd_app_proposta", $proposta);
        $pet['id'] = ((bool)$pet['id']) ? $this->DBUpdateId("hbrd_app_pet", $pet) : $this->DBInsert("hbrd_app_pet", $pet);
        $this->inserirRelatorio("Alterou proposta");
        echo $pet['id'];
    }
    protected function updateAnotationAction() {
        $data = $this->formularioArray($_POST, 'hbrd_app_proposta_anotacao');
        if(!(bool)$data['anotacao'] || $data['anotacao'] === '<p>&nbsp;</p>') throw new BadRequest('Valor da anotação não pode ser vazio');
        $data['nome'] = $_SESSION['admin_nome'];
        $this->DBInsert("hbrd_app_proposta_anotacao", $data);
        $this->inserirRelatorio("Adicionou anotação para proposta id [{$data['id_proposta']}] [{$data['anotacao']}]");
    }
    protected function buscacpfAction() {
        $pessoa = $this->DB_fetch_array("SELECT * FROM hbrd_app_pessoa WHERE cpf = ?", [$_POST["cpf"]])->rows[0];
        echo $pessoa ? json_encode($pessoa) : null;
    }
    protected function buscaCepAction() {
        header("Cache-Control: public, max-age=31536000");
        header_remove('Pragma');
        header_remove('Expires');
        $cep = preg_replace('/[^0-9]/', '', $_POST['cep']);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://viacep.com.br/ws/$cep/json/",
            CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $data = json_decode($response, true);
        if($data['erro']) return;
        $estado = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state WHERE uf = '{$data['uf']}'")->rows[0];
        $cidade = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_city WHERE cidade = '{$data['localidade']}'")->rows[0];
        $data['id_estado'] = $estado['id'];
        $data['id_cidade'] = $cidade['id'];
        echo json_encode($data);
    }
    private function formatDocumentoAcao($tipoDocumento) {
        switch ($tipoDocumento) {
            case 'assinatura':
                return 'Assinatura';
            case 'selfie':
                return 'Selfie';
            case 'frente_doc':
                return 'Frente Documento';
            case 'atras_doc':
                return 'Verso Documento';
            default;
        }
    }    
    protected function ativarPetAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();
        $pet = $this->DB_fetch_array("SELECT A.*, C.id id_associado FROM hbrd_app_pet A INNER JOIN hbrd_app_proposta B ON B.id = A.id_proposta LEFT JOIN hbrd_app_associado C ON C.id_pessoa = B.id_pessoa WHERE A.id = ?",[$_POST['id_pet']])->rows[0];
        $proposta = $this->DB_fetch_array("SELECT * FROM  hbrd_app_proposta WHERE id = ?",[$pet['id_proposta']])->rows[0];
        $pessoa = $this->DB_fetch_array("SELECT * FROM  hbrd_app_pessoa WHERE id = ?",[$proposta['id_pessoa']])->rows[0];
        $history = ['id_proposta' => $pet['id_proposta'], 'atividade' => 'Ativou indicação do(a) '.$pet['nome'], 'nome' => $_SESSION["admin_nome"]];
        $this->DB_insert("hbrd_app_proposta_hist", $history);
        if(!(bool)$pet['id_associado']) $pet['id_associado'] = $this->DB_insert("hbrd_app_associado", ['id_pessoa' => $pessoa['id']]);
        $this->DB_update('hbrd_app_pet',['id_associado' => $pet['id_associado'], 'classificacao' => 'ativada', 'activated_at' => date("Y-m-d H:i:s"), 'activated_by' => $_SESSION['admin_id']]," WHERE id = ".$pet['id']);
        $integracao = $this->DB_fetch_array("SELECT * FROM hbrd_adm_integration")->rows[0];
        if($integracao['leadszapp_status'] != '1') return;
        $info = $this->DB_fetch_array("SELECT B.titulo raca, C.titulo especie, D.titulo plano, (SELECT GROUP_CONCAT(Z.nome) FROM hbrd_app_plano_beneficio Z LEFT JOIN hbrd_app_plano_use_beneficio Y ON Z.id = Y.id_beneficio WHERE Y.id_plano = A.id_plano) beneficios_virgula FROM hbrd_app_pet A LEFT JOIN hbrd_app_pet_raca B ON B.id = A.id_raca LEFT JOIN hbrd_app_pet_especie C ON C.id = A.id_especie LEFT JOIN hbrd_app_planos D ON D.id = A.id_plano WHERE A.id = ?",[$pet['id']])->rows[0];
        $query = [];
        $query['{[assos_nome]}'] = $pessoa['nome'];
        $query['{[assos_telefone]}'] = $pessoa['telefone'];
        $query['{[pet_nome]}'] = $pet['nome'];
        $query['{[pet_especie]}'] = $info['especie'];
        $query['{[pet_raca]}'] = $info['raca'];
        $query['{[pet_plano_nome]}'] = $info['plano'];
        $query['{[pet_plano_valor]}'] = "R$ ". Util::formataMoeda($pet['valor']);
        $query['{[beneficios]}'] = str_replace(',',PHP_EOL,$info['beneficios_virgula']);
        $query['{[beneficios_virgula]}'] =  $info['beneficios_virgula'];
        if($proposta['indicador'] == 'consultor') {
            $consultor = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_consultor A inner join hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = {$proposta['id_consultor']}")->rows[0];
            $query['{[indicador_nome]}'] = $consultor['nome'];
            $query['{[indicador_telefone]}'] = $consultor['telefone'];
            $notficacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where id = 1")->rows[0];
            if($notficacao['desativado'] == '0') {
                foreach ($this->variaveis->notf1 as $value) {
                    $notficacao->mensagem = str_replace($value['variavel'], $query[$value['variavel']],$notficacao->mensagem);
                }
                $this->sendLeadszApp($consultor, $notficacao->mensagem);
                $this->enviarNotificacao($consultor['id_pessoa'], $notficacao->mensagem, '',"/#/consultor/proposta/".$proposta['id']);
            }
        }
       
        if($proposta['indicador'] == 'associado') {
            $associado = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_associado A inner join hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = {$proposta['id_associado']}")->rows[0];
            $query['{[indicador_nome]}'] = $associado['nome'];
            $query['{[indicador_telefone]}'] = $associado['telefone'];
            $notficacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where id = 2")->rows[0];
            if($notficacao['desativado'] == '0') {
                foreach ($this->variaveis->notf1 as $value) {
                    $notficacao->mensagem = str_replace($value['variavel'], $query[$value['variavel']],$notficacao->mensagem);
                }
                // $this->sendLeadszApp($consultor, $notficacao->mensagem);
                $this->enviarNotificacao($associado['id_pessoa'], $notficacao->mensagem, '','');
            }
        } 
    }
    protected function arquivarAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();

        $pet = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet WHERE id = ?",[$_POST['id_pet']])->rows[0];
        $proposta = $this->DB_fetch_array("SELECT * FROM  hbrd_app_proposta WHERE id = ?",[$pet['id_proposta']])->rows[0];

        $this->DB_update('hbrd_app_pet',['delete_at' => date("Y-m-d H:i:s")], " WHERE id= ?",[$_POST["id_pet"]]);
        $history = [ 'id_proposta' => $pet['id_proposta'], 'atividade' => 'Excluiu a proposta do pet '.$pet['nome'], 'nome' => $_SESSION["admin_nome"] ];  
        $this->DB_insert("hbrd_app_proposta_hist", $history);
        
        if($proposta['indicador'] == 'consultor') {
            $consultor = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_consultor A inner join hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = {$proposta['id_consultor']}")->rows[0];
            $query['{[indicador_nome]}'] = $consultor['nome'];
            $query['{[indicador_telefone]}'] = $consultor['telefone'];
            $notficacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where id = 4")->rows[0];
            if($notficacao['desativado'] == '0') {
                foreach ($this->variaveis->notf14 as $value) {
                    $notficacao->mensagem = str_replace($value['variavel'], $query[$value['variavel']],$notficacao->mensagem);
                }
                $this->sendLeadszApp($consultor, $notficacao->mensagem);
                $this->enviarNotificacao($consultor['id_pessoa'], $notficacao->mensagem, '',"/#/consultor/proposta/".$proposta['id']);
            }
        }
       
        if($proposta['indicador'] == 'associado') {
            $associado = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_associado A inner join hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = {$proposta['id_associado']}")->rows[0];
            $query['{[indicador_nome]}'] = $associado['nome'];
            $query['{[indicador_telefone]}'] = $associado['telefone'];
            $notficacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where id = 2")->rows[0];
            if($notficacao['desativado'] == '0') {
                foreach ($this->variaveis->notf14 as $value) {
                    $notficacao->mensagem = str_replace($value['variavel'], $query[$value['variavel']],$notficacao->mensagem);
                }
                // $this->sendLeadszApp($consultor, $notficacao->mensagem);
                $this->enviarNotificacao($associado['id_pessoa'], $notficacao->mensagem, '','');
            }
        } 

        // $this->enviarNotificacao($pessoa['id_pessoa'], $notficacao->mensagem);   
    }
    protected function desarquivarAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();
        // $history = [ 'id_indicacao' => $_POST['id'], 'acao' => 'Desarquivou indicação', 'usuario' => $_SESSION["admin_nome"] ];
        // $this->DB_insert("hbrd_app_proposta_history", $history);
        $pet = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet WHERE id = ?",[$_POST['id_pet']])->rows[0];
        $info = $this->DB_fetch_array("SELECT B.titulo raca, C.titulo especie, D.titulo plano, (SELECT GROUP_CONCAT(Z.nome) FROM hbrd_app_plano_beneficio Z LEFT JOIN hbrd_app_plano_use_beneficio Y ON Z.id = Y.id_beneficio WHERE Y.id_plano = A.id_plano) beneficios_virgula FROM hbrd_app_pet A LEFT JOIN hbrd_app_pet_raca B ON B.id = A.id_raca LEFT JOIN hbrd_app_pet_especie C ON C.id = A.id_especie LEFT JOIN hbrd_app_planos D ON D.id = A.id_plano WHERE A.id = ?",[$pet['id']])->rows[0];
        $history = [ 'id_proposta' => $pet['id_proposta'], 'atividade' => 'Desarquivou proposta do pet '.$pet['nome'], 'nome' => $_SESSION["admin_nome"] ];
        $this->DB_insert("hbrd_app_proposta_hist", $history);
        $this->DB_update('hbrd_app_pet',['classificacao'=>'pendente', 'id_arquivamento'=>null, "arquivado_em" => null], " WHERE id= ?",[$_POST["id_pet"]]);
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();