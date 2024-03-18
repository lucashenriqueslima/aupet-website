<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Util,Variaveis};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = "pets";
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_pet";
        $this->module_icon = "minia-icon-checkmark";
        $this->module_title = "Pets";
        $this->variaveis = new Variaveis();
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_especie A ORDER BY A.titulo")->rows;
        $this->racas = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_raca A ORDER BY A.titulo")->rows;
        $this->planos = $this->DB_fetch_array("SELECT A.id, A.titulo FROM hbrd_app_planos A LIMIT 0,3")->rows;
 
        $this->indicacoes = $this->DB_fetch_array("SELECT count(A.id) count FROM hbrd_app_pet A LEFT JOIN hbrd_app_associado C ON C.id = A.id_associado WHERE A.delete_at IS NULL AND (C.stats = 1 OR C.stats IS NULL) AND A.id_associado IS NOT NULL AND C.delete_at IS NULL")->rows[0]['count'];
        $this->indicacoes_pendentes = $this->DB_fetch_array("SELECT COUNT(A.id) count FROM hbrd_app_pet A LEFT JOIN hbrd_app_associado C ON C.id = A.id_associado WHERE A.classificacao = 'pendente' AND A.delete_at IS NULL AND (C.stats = 1 OR C.stats IS NULL) AND A.id_associado IS NOT NULL AND C.delete_at IS NULL")->rows[0]['count'];
        $this->indicacoes_arquivadas = $this->DB_fetch_array("SELECT COUNT(A.id) count FROM hbrd_app_pet A LEFT JOIN hbrd_app_associado C ON C.id = A.id_associado WHERE A.classificacao = 'arquivada' AND A.delete_at IS NULL AND (C.stats = 1 OR C.stats IS NULL) AND A.id_associado IS NOT NULL AND C.delete_at IS NULL;")->rows[0]['count'];
        $this->indicacoes_ativadas = $this->DB_fetch_array("SELECT COUNT(A.id) count FROM hbrd_app_pet A LEFT JOIN hbrd_app_associado C ON C.id = A.id_associado WHERE A.classificacao = 'ativada' AND A.delete_at IS NULL AND (C.stats = 1 OR C.stats IS NULL) AND A.id_associado IS NOT NULL AND C.delete_at IS NULL;")->rows[0]['count'];
		$this->dados_grafico = $this->DB_fetch_array ("SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at, SUM(indicacoes) indicacoes FROM ( SELECT *, COUNT(create_at) indicacoes FROM (SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at FROM $this->table WHERE DATE(create_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ) sub GROUP BY dia_mes ORDER BY create_at ) tb_tabela GROUP BY create_at");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    public function chartAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission(false);
        $de = "'{$_POST['de']}'";
        $ate = "'{$_POST['ate']}'";
        $this->alvo = "visitors-chart";
        if (isset($_POST['alvo'])) $this->alvo = $_POST['alvo'];
        $this->dados_grafico = $this->DB_fetch_array("SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at, SUM(indicacoes) indicacoes FROM (SELECT *, COUNT(create_at) indicacoes FROM (SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at FROM $this->table WHERE DATE(create_at) >= $de AND DATE(create_at) <= $ate ) sub GROUP BY dia_mes ORDER BY create_at) tb_tabela GROUP BY create_at");
        if ($this->dados_grafico->num_rows) $this->renderAjax($this->getService(), $this->getModule(), "chart");
        else echo "<div class='well' style='color:black'>$this->_sem_dados</div>";
    }
    protected function editAction() {
        $this->id = $this->getParameter("id");
        $this->proposta = $this->getParameter("proposta");
        $this->associado = $this->getParameter("associado");
        if((bool)$this->id) { // editar pet
            $this->registro = $this->DB_fetch_array("SELECT A.*, D.nome responsavel FROM $this->table A LEFT JOIN hbrd_app_proposta B ON B.id = A.id_proposta LEFT JOIN hbrd_app_associado C ON C.id = A.id_associado INNER JOIN hbrd_app_pessoa D ON D.id = B.id_pessoa OR D.id = C.id_pessoa WHERE A.id = $this->id")->rows[0];
            if(!(bool)$this->registro['hash']) {
                $this->registro['hash'] = md5(uniqid(rand(), true));
                $this->DBUpdate($this->table, ['hash' => $this->registro['hash']], " where id = ".$this->id);
            }

            $this->external_reference = $this->DB_fetch_array("SELECT A.external_reference FROM hbrd_app_assinatura A WHERE A.id_pet = {$this->id} AND A.deleted_at IS NULL")->rows[0]['external_reference'];

            if((bool)$this->external_reference){
                $access_token = $this->DB_fetch_array("SELECT access_token FROM hbrd_adm_integration")->rows[0]['access_token'];
                $this->list = json_decode($this->handlerHttp('GET', 'https://api.mercadopago.com/v1/payments/search?sort=date_created&criteria=desc&external_reference='. $this->external_reference , ['headers' => ['Content-Type' => 'application/json', 'Authorization' => "Bearer $access_token"]]), true)["results"];
            }
        } else if((bool)$this->proposta) { // criar pet para proposta
            $this->registro = $this->DB_fetch_array("SELECT A.nome responsavel, A.id id_pessoa FROM hbrd_app_pessoa A LEFT JOIN hbrd_app_proposta B ON B.id_pessoa = A.id WHERE B.id = $this->proposta")->rows[0];
            $this->registro['id_proposta'] = $this->proposta;
        } else if((bool)$this->associado) { // criar novo pet para associado
            $this->registro = $this->DB_fetch_array("SELECT A.nome responsavel, A.id id_pessoa FROM hbrd_app_pessoa A LEFT JOIN hbrd_app_associado B ON B.id_pessoa = A.id WHERE B.id = $this->associado")->rows[0];
            $this->registro['id_associado'] = $this->associado;
        } else {
            return $this->noPermission();
        }
        $this->motivos = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_motive A WHERE A.delete_at IS NULL AND stats = 1 ORDER BY nome")->rows;
        $this->especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_especie A ORDER BY A.titulo")->rows;
        $this->planos = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_planos A WHERE A.delete_at IS NULL ORDER BY A.titulo")->rows;
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    protected function saveAction() {
        $data = $this->formularioArray($_POST, $this->table);
        unset($data['delete_at']);
        unset($data['ordem']);
        $this->valorPlano =  $this->DB_fetch_array("SELECT valor FROM hbrd_app_planos WHERE id = {$data['id_plano']} AND delete_at IS NULL")->rows[0];
        $data['valor'] = $this->valorPlano['valor'];
        if($data['foto'] && !strpos($data['foto'],'com/aupet/')){
			$data['foto'] = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST['foto']));
		} else unset($data['foto']);
        if(!$data['id']) {
            if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
            $data['id'] = $this->DBInsert($this->table, $data);
            $this->inserirRelatorio("Inseriu pet: [".$data['nome']."] id: [".$data['id']."]");
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) exit();
            $this->DBUpdate($this->table, $data, " WHERE id=".$data['id']);
            $this->inserirRelatorio("Alterou pet: [".$data['nome']."] id: [".$data['id']."]");
        }
        echo $data['id'];
    }
    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = ".$id)->rows[0];
        $this->DBUpdate($this->table,['delete_at'=>date('Y-m-d H:i:s')], " where id=".$id);
        $this->inserirRelatorio("Apagou situação: [".$dados['nome']."] id: [{$id}]");
    }
    public function exporttabAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->dados = $this->DB_fetch_array($_SESSION['query_indication']);
        header('Content-Type: text/html; charset=utf-8', true);
        require_once dirname(__DIR__)."/export/export.phtml";
    }
    public function exportAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->dados = $this->DB_fetch_array($_SESSION['query_indication']);
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=Relatorio Pets ".date("Y-m-d H-i-s").".xls");
        header("Pragma: no-cache");  
        ob_start();
		include(dirname(__DIR__)."/export/export.phtml");
		$content = ob_get_clean();
		echo utf8_decode($content);
	}
    public function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) exit();
        $aColumns = array(
            "A.id",
            "A.nome nome_pet",
            "A.classificacao",
            "D.nome responsavel",
            "E.titulo especie",
            "F.titulo raca",
            "IF(G.titulo IS NULL,'GRATUITO', G.titulo) plano_nome",
            "IF(G.valor IS NULL,'0.00', G.valor) plano_valor",
        );
        $aColumnsWhere = array('A.nome');
        $sortColumns = ["A.id","A.nome","responsavel","especie","raca","plano_nome","plano_valor"];
        $sTable = "
            $this->table A
            LEFT JOIN hbrd_app_associado C ON C.id = A.id_associado
            INNER JOIN hbrd_app_pessoa D ON D.id = C.id_pessoa
            LEFT JOIN hbrd_app_pet_especie E ON E.id = A.id_especie
            LEFT JOIN hbrd_app_pet_raca F ON F.id = A.id_raca
            LEFT JOIN hbrd_app_planos G ON G.id = A.id_plano
            ";
        $sWhere = 'WHERE C.delete_at IS NULL AND (C.stats = 1 OR C.stats IS NULL) AND A.delete_at IS NULL';

        $sLimit = "";

        /* ================================================================================= */


        if ($_POST['especies']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['especies']) as $value) {
                $sWhere .= " E.id = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if ($_POST['racas']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['racas']) as $value) {
                $sWhere .= " F.id = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if ($_POST['status']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['status']) as $value) {
                $sWhere .= " A.classificacao = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if ($_POST['plano']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['plano']) as $value) {
                $sWhere .= " A.id_plano = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }


        /* ================================================================================= */

        $_POST['sSortDir_0'] = 'desc';
        
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
		$query = "SELECT SQL_CALC_FOUND_ROWS {$collums} FROM {$sTable} {$sWhere} GROUP BY A.id {$sOrder} {$sLimit}";
        $_SESSION['query_indication'] = null;
        $_SESSION['query_indication'] = "SELECT SQL_CALC_FOUND_ROWS {$collums} FROM {$sTable} {$sWhere} GROUP BY A.id {$sOrder}";
        $sQuery = $this->DB_fetch_array($query);
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
            $row[] = '<div align="center"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['id'].'</a></div>';
            $row[] = '<div align="center"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['nome_pet'].'</a></div>';
            $row[] = '<div align="center"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['responsavel'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['especie'].'</a></div>';                
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['raca'].'</a></div>';
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'. 'Plano '.$aRow ['plano_nome'].'</a></div>';                
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.'R$ ' .Util::formataMoeda($aRow ['plano_valor']) .'</a></div>';                
            $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'. $aRow ['classificacao'] .'</a></div>';                
            $acoes  = '<div align="center">';
            $acoes .= "<a onclick='updateStatusItem($id)' class='cursor' title='".($aRow['desativado'] == '1' ? 'Ativar' : 'Desativar')."'><img src='{$img}'></a>";
            $acoes .= '<a href="'.$this->getPath().'/edit/id/'.$id.'"><span class="icon12 icomoon-icon-pencil"></span></a>';
            $acoes .= '</div>';
            $row[] = $acoes;
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
    }
    protected function racasAction() {
        $id_especie = $this->getParameter("especie");
        $especies = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_pet_raca A where A.id_especie = $id_especie ORDER BY A.titulo")->rows;
        return json_encode($especies);
    }
    protected function arquivarAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();
        $pet = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet WHERE id = ?",[$_POST['id_pet']])->rows[0];
        $proposta = $this->DB_fetch_array("SELECT * FROM  hbrd_app_proposta WHERE id = ?",[$pet['id_proposta']])->rows[0];
        
        $this->DB_update('hbrd_app_pet',['classificacao'=>'arquivada', 'id_arquivamento'=>$_POST['id_arquivamento'], 'arquivado_em' => date("Y-m-d H:i:s")], " WHERE id= ?",[$_POST["id_pet"]]);
        $history = [ 'id_proposta' => $pet['id_proposta'], 'atividade' => 'Arquivou proposta do pet '.$pet['nome'], 'nome' => $_SESSION["admin_nome"] ];
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
    public function excluirPetAction(){
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();
        $pet = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet WHERE id = ?",[$_POST['id_pet']])->rows[0];
        $proposta = $this->DB_fetch_array("SELECT * FROM  hbrd_app_proposta WHERE id = ?",[$pet['id_proposta']])->rows[0];

        $this->DB_update('hbrd_app_pet',['delete_at' => date("Y-m-d H:i:s")], " WHERE id= ?",[$_POST["id_pet"]]);
        $history = [ 'id_proposta' => $pet['id_proposta'], 'atividade' => 'Excluiu o pet '.$pet['nome'], 'nome' => $_SESSION["admin_nome"] ];
        
        $id_assinatura = $this->DB_fetch_array("SELECT A.assinatura FROM hbrd_app_assinatura A WHERE A.id_pet = {$_POST['id_pet']} AND A.deleted_at IS NULL")->rows[0]['assinatura'];
        $associado = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_associado A inner join hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = {$pet['id_associado']}")->rows[0];

        if((bool)$id_assinatura){

            $this->access_token = $this->DB_fetch_array("SELECT access_token FROM hbrd_adm_integration")->rows[0]['access_token'];
            MercadoPago\SDK::setAccessToken($this->access_token);

            $preapproval = new MercadoPago\Preapproval();

            $assinatura = $preapproval::find_by_id($id_assinatura);

            if((bool)$assinatura->id){
                $assinatura->status = "cancelled";
                $assinatura->update();

                $data = [
                    'status' => "cancelled", 
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'cancelado_em' => date('Y-m-d H:i:s'),
                    'id_arquivamento' => $_POST['id_arquivamento']
                ];
                $info = $this->DB_fetch_array("SELECT B.titulo raca, C.titulo especie, D.titulo plano, (SELECT GROUP_CONCAT(Z.nome) FROM hbrd_app_plano_beneficio Z LEFT JOIN hbrd_app_plano_use_beneficio Y ON Z.id = Y.id_beneficio WHERE Y.id_plano = A.id_plano) beneficios_virgula FROM hbrd_app_pet A LEFT JOIN hbrd_app_pet_raca B ON B.id = A.id_raca LEFT JOIN hbrd_app_pet_especie C ON C.id = A.id_especie LEFT JOIN hbrd_app_planos D ON D.id = A.id_plano WHERE A.id = ?",[$pet['id']])->rows[0];

                $this->DB_update('hbrd_app_assinatura',$data,"WHERE id_pet = ? AND deleted_at IS NULL",[$_POST['id_pet']]);
                $this->DB_update('hbrd_app_pet',[ 'id_plano' => null, 'classificacao' => 'pendente','valor' => '0.00' ],"WHERE id = ?",[$_POST['id_pet']]);

                $query = [];
                $query['{[assos_nome]}'] = $associado['nome'];
                $query['{[assos_telefone]}'] = $associado['telefone'];
                $query['{[pet_nome]}'] = $pet['nome'];
                $query['{[pet_especie]}'] = $info['especie'];
                $query['{[pet_raca]}'] = $info['raca'];
                $query['{[pet_plano_nome]}'] = $info['plano'];
                $query['{[pet_plano_valor]}'] = "R$ ". Util::formataMoeda($pet['valor']);
                $query['{[beneficios]}'] = str_replace(',',PHP_EOL,$info['beneficios_virgula']);
                $query['{[beneficios_virgula]}'] =  $info['beneficios_virgula'];

                $notficacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where id = 18")->rows[0];
                if($notficacao['desativado'] == '0') {
                    foreach ($this->variaveis->notf18 as $value) {
                        $notficacao['mensagem'] = str_replace($value['variavel'], $query[$value['variavel']],$notficacao['mensagem']);
                    }
                    $this->sendLeadszApp($associado, $notficacao['mensagem']);

                    if((bool)$notficacao['envia_email']){
                        $email_template = $notficacao['template_email'];
                        foreach ($this->variaveis->notf18 as $value) {
                            $email_template = str_replace($value['variavel'], $query[$value['variavel']],$email_template);
                        }
    
                        $email_assunto = $notficacao['assunto'];
                        foreach ($this->variaveis->notf18 as $value) {
                            $email_assunto = str_replace($value['variavel'], $query[$value['variavel']],$email_assunto);
                        }
    
                        $this->enviarEmail(['nome' => $associado['nome'], 'email' => $associado['email']],"",Util::utf8_decode($email_assunto), Util::utf8_decode($email_template));
                    }
                }
            }
        }
    }
    public function excluirAssinaturaAction () {
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();

        $this->access_token = $this->DB_fetch_array("SELECT access_token FROM hbrd_adm_integration")->rows[0]['access_token'];
        MercadoPago\SDK::setAccessToken($this->access_token);

        $pet = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet WHERE id = ?",[$_POST['id_pet']])->rows[0];
        $id_assinatura = $this->DB_fetch_array("SELECT A.assinatura FROM hbrd_app_assinatura A WHERE A.id_pet = {$_POST['id_pet']} AND A.deleted_at IS NULL")->rows[0]['assinatura'];

        $preapproval = new MercadoPago\Preapproval();

        $assinatura = $preapproval::find_by_id($id_assinatura);

        $associado = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_associado A inner join hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = {$pet['id_associado']}")->rows[0];

        if((bool)$assinatura->id){
            $assinatura->status = "cancelled";
            $assinatura->update();

            $data = [
                'status' => "cancelled", 
                'deleted_at' => date('Y-m-d H:i:s'),
                'cancelado_em' => date('Y-m-d H:i:s'),
                'id_arquivamento' => $_POST['id_arquivamento']
            ];
            $info = $this->DB_fetch_array("SELECT B.titulo raca, C.titulo especie, D.titulo plano, (SELECT GROUP_CONCAT(Z.nome) FROM hbrd_app_plano_beneficio Z LEFT JOIN hbrd_app_plano_use_beneficio Y ON Z.id = Y.id_beneficio WHERE Y.id_plano = A.id_plano) beneficios_virgula FROM hbrd_app_pet A LEFT JOIN hbrd_app_pet_raca B ON B.id = A.id_raca LEFT JOIN hbrd_app_pet_especie C ON C.id = A.id_especie LEFT JOIN hbrd_app_planos D ON D.id = A.id_plano WHERE A.id = ?",[$pet['id']])->rows[0];


            $this->DB_update('hbrd_app_assinatura',$data,"WHERE id_pet = ? AND deleted_at IS NULL",[$_POST['id_pet']]);
            $this->DB_update('hbrd_app_pet',[ 'id_plano' => null, 'classificacao' => 'pendente','valor' => '0.00' ],"WHERE id = ?",[$_POST['id_pet']]);

            $query = [];
            $query['{[assos_nome]}'] = $associado['nome'];
            $query['{[assos_telefone]}'] = $associado['telefone'];
            $query['{[pet_nome]}'] = $pet['nome'];
            $query['{[pet_especie]}'] = $info['especie'];
            $query['{[pet_raca]}'] = $info['raca'];
            $query['{[pet_plano_nome]}'] = $info['plano'];
            $query['{[pet_plano_valor]}'] = "R$ ". Util::formataMoeda($pet['valor']);
            $query['{[beneficios]}'] = str_replace(',',PHP_EOL,$info['beneficios_virgula']);
            $query['{[beneficios_virgula]}'] =  $info['beneficios_virgula'];

            $notficacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where id = 17")->rows[0];
            if($notficacao['desativado'] == '0') {
                foreach ($this->variaveis->notf17 as $value) {
                    $notficacao['mensagem'] = str_replace($value['variavel'], $query[$value['variavel']],$notficacao['mensagem']);
                }
                $this->sendLeadszApp($associado, $notficacao['mensagem']);

                if((bool)$notficacao['envia_email']){
                    $email_template = $notficacao['template_email'];
                    foreach ($this->variaveis->notf17 as $value) {
                        $email_template = str_replace($value['variavel'], $query[$value['variavel']],$email_template);
                    }

                    $email_assunto = $notficacao['assunto'];
                    foreach ($this->variaveis->notf17 as $value) {
                        $email_assunto = str_replace($value['variavel'], $query[$value['variavel']],$email_assunto);
                    }

                    $this->enviarEmail(['nome' => $associado['nome'], 'email' => $associado['email']],"",Util::utf8_decode($email_assunto), Util::utf8_decode($email_template));
                }
            }

        }else {
            throw 'assinatura não encontrada';
        }
        
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();