<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Variaveis, Util};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'consultor';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_associado";
        $this->module_title = "Associados";
        $this->module_icon = "icomoon-icon-user";
        $this->logo_uploaded = "";
        // $this->variaveis = new Variaveis();
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
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    public function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            $this->registro = $this->DB_fetch_array("SELECT B.*, A.* FROM $this->table A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = $this->id")->rows[0];
            $this->pets = $this->DB_fetch_array("SELECT A.*, C.titulo AS plano, D.titulo AS raca, E.titulo AS especie FROM hbrd_app_pet A INNER JOIN hbrd_app_associado B ON B.id = A.id_associado LEFT JOIN hbrd_app_planos C ON A.id_plano = C.id LEFT JOIN hbrd_app_pet_raca D ON A.id_raca = D.id LEFT JOIN hbrd_app_pet_especie E ON A.id_especie = E.id WHERE B.id = $this->id AND A.delete_at IS NULL")->rows;
        }
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->equipes = $this->DB_fetch_array("SELECT id, titulo FROM hbrd_app_equipe where delete_at is null");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    public function saveAction() {
        $associado = $this->arrayTable($_POST, $this->table);
        $pessoa = $this->arrayTable($_POST, $this->table_pessoa);
        $pessoa['data_nascimento'] = ((bool)$pessoa['data_nascimento']) ? Util::maskToDate($pessoa['data_nascimento']) : null;
        if((bool)$_POST['novaSenha']) {
            $pessoa['salt'] = Util::createSalt();
            $pessoa['senha'] = Util::createSenhaHash($_POST['novaSenha'], $pessoa['salt']);
        }
        if($pessoa['foto'] && !strpos($pessoa['foto'],'com/aupet/')){
			$pessoa['foto'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['foto']));
		} else unset($pessoa['foto']);

        if((bool)$associado['id']) {
            $this->DBUpdate($this->table, $associado, " WHERE id = ".$associado['id']);
            $this->DBUpdate($this->table_pessoa, $pessoa, " WHERE id = ".$associado['id_pessoa']);
            $acao = "Atualizou Associado id {$associado['id']}";
        } else {
            $id_pessoa = $this->DB_fetch_array("SELECT id FROM hbrd_app_pessoa WHERE email like '". $pessoa['email'] ."'")->rows[0];

            if($id_pessoa['id']){
                $this->DBUpdate($this->table_pessoa, $pessoa, " WHERE id = ". $id_pessoa['id']);
                $associado['id_pessoa'] = $id_pessoa['id'];
                $associado['id'] = $this->DBInsert($this->table, $associado);
                $acao = "Registrou Associado id {$associado['id']}"; 
            } else {
                $associado['id_pessoa'] = $this->DBInsert($this->table_pessoa, $pessoa);
                $associado['id'] = $this->DBInsert($this->table, $associado);
                $acao = "Registrou Associado id {$associado['id']}";
            }

        }
        $this->inserirRelatorio($acao);        
        echo $associado['id'];
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
        header("Content-Disposition: attachment; filename=Associados".date("Y-m-d H-i-s").".xls");
        header("Pragma: no-cache");  
        $this->dados = $this->DB_fetch_array($_SESSION['query_export']);
        ob_start();
		include(dirname(__DIR__)."/export/export.phtml");
		$content = ob_get_clean();
		echo utf8_decode($content);
    }
    private function dataTableQuery() {
        $aColumns = array('A.*',"B.nome","B.email","B.telefone","DATE_FORMAT(A.create_at, '%d/%m/%Y') criado_em_dt", "DATE_FORMAT(A.create_at, 'às %H:%i') criado_em_hr", "GROUP_CONCAT(C.nome) AS pets", "SUM(C.valor) AS valor"
		);
		$sortColumns = array("","A.create_at","nome","B.email");
        $aColumnsWhere = array("B.nome", 'A.id','B.email');
        $sTable = "
            $this->table A
            LEFT JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa
            LEFT JOIN hbrd_app_pet C ON C.id_associado = A.id
           ";
        $sWhere = ' WHERE (A.delete_at IS NULL AND C.delete_at IS NULL)';
        if (isset($_POST['equipes']) AND $_POST['equipes'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['equipes']) as $value) {
                $sWhere .= " E.id_equipe = '{$value}' OR ";
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
            $row[] = "<div style='max-width:20px;width:20px' align='center'><input onclick='selectListItem(this, event)' id='$id' nome='{$aRow['nome']}' class='selecionados checkbox' type='checkbox' value={$aRow["id"]} /></div>";
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow['criado_em_dt'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow['nome'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow['email'].'<br>'.$aRow['telefone'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$aRow['pets']. '</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$this->formataMoedaDeBanco($aRow['valor']). '</a></div>';
            
            $acoes  = '<div align="center">';
            $img = $this->main_template.($aRow['stats'] == 0 ? 'images/status_vermelho.png' : 'images/status_verde.png');
            $acoes .= "<a onclick='updateStatusItem($id)' class='cursor' title='".($aRow['stats'] == 0 ? 'Ativar' : 'Desativar')."'><img src='{$img}'></a>";
            $acoes .= '<a href="'.$this->getPath().'/edit/id/'.$id.'"><span class="icon12 icomoon-icon-pencil"></span></a>';
            // $acoes .= "<span onclick='deleteItem($id)' class='cursor'><span class='icon12 icomoon-icon-remove'></span></span>";
            $acoes .= '</div>';
            $row[] = $acoes;
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
    }

    protected function updateStatusAction() {
        $client = $this->DB_fetch_array("SELECT A.*, B.nome FROM $this->table A INNER JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE A.id =".$_POST['id'])->rows[0];
        $this->DB_update($this->table,['stats'=> ($client['stats'] == 1 ? 0:1)],' where id ='.$_POST['id']);

        if($client['stats'] == 1){
            $pets = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet where id_associado = ". $client['id'])->rows;

            foreach($pets as $pet){
                $this->DBUpdate('hbrd_app_pet',['classificacao' => 'arquivada', 'arquivado_em' => date("Y-m-d H:i:s")],"WHERE id = ".$pet['id']);

                /* ASSINATURA */
                $this->access_token = $this->DB_fetch_array("SELECT access_token FROM hbrd_adm_integration")->rows[0];
                $this->assinatura = $this->DB_fetch_array("SELECT A.*, B.id_pessoa FROM hbrd_app_assinatura A INNER JOIN hbrd_app_associado B ON B.id = A.id_associado WHERE B.id = {$pet['id_associado']}")->rows[0];

                if($this->assinatura != null){
                    $curl = curl_init('https://api.mercadopago.com/preapproval/'.$this->assinatura['assinatura']);
                    $novoValor = ((float)$this->assinatura['valor'] - (float)$pet['valor']);

                    $petAssinatura = explode(",",$this->assinatura['descricao']);

                    for($i=0;$i<count($petAssinatura); $i++){
                        if((bool)strpos($petAssinatura[$i],$pet['nome']) || $petAssinatura[$i] == " " || $petAssinatura[$i] == ""){
                            unset($petAssinatura[$i]);
                        }
                        if((bool)strpos($petAssinatura[$i],"Cancelado")){
                            unset($petAssinatura[$i]);
                        }
                    }

                    $novaDescricao = implode(",",$petAssinatura);

                    $headers = array(
                        "Content-Type: application/json",
                        "Authorization: Bearer ". $this->access_token['access_token']
                    );

                    //Confira novo valor do plano

                    if($novoValor > 0){
                        $data_request = json_encode(array(
                            "application_id" => $this->assinatura['application_id'],
                            "reason" => $novaDescricao,
                            "auto_recurring" => [
                                "currency_id" => "BRL",
                                "transaction_amount" => $novoValor
                            ]
                        ));
                        $message =  "A sua solicitação de cancelamento ao plano foi aceita! O pet ". $pet['nome'] ." já foi retirado a sua assinatura e o valor da mensalidade já foi ajustado.";
                        $status = "authorized";
                    }
                    else{
                        $data_request = json_encode(array(
                            "application_id" => $this->assinatura['application_id'],
                            "status" => "paused",
                            "reason" => $this->assinatura['descricao'] ."(Cancelado)",
                            "auto_recurring" => [
                                "currency_id" => "BRL",
                                "transaction_amount" => 1
                            ]
                        ));
                        $message = "A sua solicitação de cancelamento para o pet ". $pet['nome'] ." foi aceita e a sua assinatura foi cancelada!";
                        $status = "paused";
                    }

                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 

                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_request);
                    
                    $request = curl_exec($curl);
            
                    curl_close($curl);
            
                    $response = json_decode($request);

                    if($this->assinatura['status'] =='authorized'){
                        $this->DB_update('hbrd_app_assinatura',["status" => $status,"descricao" => ($novoValor != 0) ? $novaDescricao : $this->assinatura['descricao'] ."(Cancelado)", "valor" => $novoValor],"WHERE id =". $this->assinatura['id']);
                    }else{    
                        $this->DB_update('hbrd_app_assinatura',["status" => $status],"WHERE id =". $this->assinatura['id']);
                    }
                    
                }else{
                    // $this->enviarNotificacao($proposta['id_pessoa'], "A sua solicitação de adesão ao plano " . $info['plano'] . " foi aceita! Acesse o aplicativo e insira seus dados bancarios para ativar a assinatura.", '',"/#/associado/cartao-pagamento/");
                }
            }

            $this->enviarNotificacao($client['id_pessoa'], 'Olá '. $client['nome'] .', seu acesso ao ambiente de associado foi bloqueado, seu pets arquivados e/ou sua assinatura(caso possua uma) pausada.', '','');
        }
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();