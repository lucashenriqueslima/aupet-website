<?php
use System\Base\{SistemaBase, BadRequest};
use adm\classes\{Variaveis, Util};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'ongs';
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_app_ong";
        $this->table2 = "hbrd_app_ong_fotos";

        $this->module_title = "Ongs";
        $this->module_icon = "icomoon-icon-users";
        $this->logo_uploaded = "";
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
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->bairros = $this->DB_fetch_array("SELECT DISTINCT bairro FROM hbrd_app_ong");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    public function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        } else {
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id")->rows[0];
            $this->contas = $this->DB_fetch_array("SELECT id,banco, agencia, operacao, conta, cpf_cnpj FROM $this->table WHERE id = $this->id AND banco IS NOT NULL");
            $this->doacoes = $this->DB_fetch_array("SELECT A.*, date_format(A.create_at, '%d/%m/%Y às %H:%i') criacao, B.nome indicador, C.nome campanha FROM hbrd_app_doacoes A INNER JOIN hbrd_app_pessoa B ON A.id_pessoa = B.id  LEFT JOIN hbrd_app_campanha_doacoes C ON A.id_campanha = C.id  WHERE A.id_ong = $this->id AND A.status_pagamento = 'approved' ");
        }
        $this->campanhas = $this->DB_fetch_array("SELECT A.*, B.id_ong FROM hbrd_app_campanha_doacoes A LEFT JOIN hbrd_app_ong_use_campanha B ON (B.id_campanha = A.id)". ((bool)$this->id ? " AND B.id_ong = $this->id" : "")." GROUP BY A.id ORDER BY A.ordem");
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    public function saveAction() {
		$formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();

        if ($formulario->id)  $this->registro = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.id = $formulario->id")->rows[0];

        if ($_FILES["logo"]['size'] > 0) {
            if($this->registro['logo']){ 
                $fotokey = explode('/', $this->registro['logo']);
                $key = $fotokey[3] . '/' . $fotokey[4]; 

                $this->deleteObjectAWS($key);
            } 

            $simpleImage = new SimpleImage3();
            $simpleImage->fromFile($_FILES["logo"]["tmp_name"]);
            $imagem = $simpleImage->bestFit(300,300)->toString();
          
            $_POST['logo'] = $this->putObjectAws($imagem);
        }

        $data = $this->formularioObjeto($_POST, $this->table);

        if (!(bool)$formulario->id) {
            $validacao = $this->validaFormulario($formulario);
            if (!$validacao->return) {
                echo json_encode($validacao);
            }else{
                $insertSituation = $this->DB_sqlInsert($this->table, $data);
                if (empty($insertSituation) OR !  $this->mysqli->query($insertSituation)) throw new \Exception($insertSituation);
    
                $formulario->id = $this->mysqli->insert_id;
    
                $this->inserirRelatorio("Adicionou a ong [".$data->nome."] id [{$formulario->id}]");
                // $this->setTiposOficina($_POST['tipos'], $formulario->id);
    
                $resposta->redirect = $this->getPath()."/edit/id/".$formulario->id;
                $resposta->type = "success";
                $resposta->message = "Registro incluido com sucesso!";
            }
        } else {
            $validacao = $this->validaFormulario($formulario);
            if (!$validacao->return) {
                echo json_encode($validacao);
            }else{
                $updateSituation = $this->DB_sqlUpdate($this->table, $data, " WHERE id=".$formulario->id);
                if (empty($updateSituation) OR ! $this->mysqli->query($updateSituation))
                    throw new \Exception($updateSituation);
                
                $this->inserirRelatorio("Alterou ong [".$data->nome."] id [{$formulario->id}]");
                // $this->setTiposOficina($_POST['tipos'], $formulario->id);
    
                $resposta->redirect = $this->getPath()."/edit/id/".$formulario->id;
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
            }
           
        }

        $this->DBDelete('hbrd_app_ong_use_campanha',"WHERE id_ong = {$formulario->id}");

        foreach ($_POST['campanhas'] as $campanha) {
            $this->DBInsert('hbrd_app_ong_use_campanha', ['id_ong' => $formulario->id, 'id_campanha' => $campanha]);
		}

        echo json_encode($resposta);
       
    }
 
    
    public function datatableAction() {
		if (!$this->permissions[$this->permissao_ref]['ler']) exit();
		$aColumns = array('A.*', 'B.estado', 'C.cidade');
		$sortColumns = array("A.nome","A.telefone1","A.endereco","C.cidade","B.estado");
        $aColumnsWhere = array("A.nome");
        $sIndexColumn = "A.id";
        $sTable = "$this->table A
                LEFT JOIN
            hbrd_main_util_state B ON B.id = A.id_estado
                LEFT JOIN
            hbrd_main_util_city C ON C.id = A.id_cidade
               
            ";
            // LEFT JOIN
            // hbrd_adm_store_has_type D ON D.id_store = A.id
        $sWhere = ' WHERE A.delete_at IS NULL ';
		if ((bool)$_POST['tipos']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['tipos']) as $value) {
                $sWhere .= " D.id_type = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        /* ================================================================ */
        
        if (isset($_POST['estado']) AND $_POST['estado'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['estado']) as $value) {
                $sWhere .= " B.id = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (isset($_POST['cidades']) AND $_POST['cidades'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['cidades']) as $value) {
                $sWhere .= " C.id = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        if (isset($_POST['bairros']) AND $_POST['bairros'] != '') {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['bairros']) as $value) {
                $sWhere .= " A.bairro LIKE '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }

        /* ================================================================ */


        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT ".$_POST['iDisplayStart'].", " .$_POST['iDisplayLength'];
		}
        if (isset($_POST['iSortCol_0']) && (bool)$sortColumns[$_POST['iSortCol_0']]) {
			$sOrder = "ORDER BY ";
            $campo_array = $sortColumns[$_POST['iSortCol_0']];
             $sOrder .= $campo_array." ".$_POST['sSortDir_0'];
            if ($sOrder == "ORDER BY ") {
                $sOrder = "";
            }
        }
        if ($_POST['sSearch'] != "") {
            if ($sWhere == "") {
                $sWhere = "WHERE (";
            } else {
                $sWhere .= " and (";
            }
            for ($i = 0; $i < count($aColumnsWhere); $i++) {
                $sWhere .= $aColumnsWhere[$i]." LIKE '%".addslashes($_POST['sSearch'])."%' OR ";
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
                $sWhere .= $aColumns[$i]." LIKE '%" .($_POST['sSearch_'.$i])."%' ";
            }
        }
        $rResult = array();
        $collums = str_replace(" , ", " ", implode(", ", $aColumns));
        $queryExport = "SELECT SQL_CALC_FOUND_ROWS ".$collums."
             FROM $sTable
            $sWhere
            GROUP BY A.id
            $sOrder
            $sLimit";
        // die($queryExport);
        $_SESSION['query_export'] = null;
        $_SESSION['query_export'] = "SELECT SQL_CALC_FOUND_ROWS {$collums} FROM {$sTable} {$sWhere} GROUP BY A.id {$sOrder}";
		$sQuery = $this->DB_fetch_array($queryExport);
        if ($sQuery->num_rows) $rResult = $sQuery->rows;
        $iFilteredTotal = $sQuery->num_rows;
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        if ($rResult) {
            foreach ($rResult as $aRow) {
                $row = array();
                $id = $aRow['id'];
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['nome'].'</a></div>';
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$aRow ['telefone1']. '</a></div>';
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$aRow['estado']. '</a></div>';
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$aRow['cidade']. '</a></div>';
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">' .$aRow ['bairro']. '</a></div>';

                if($aRow['stats'] == 0){
                    $row[] = '<div align="center"><a href="#" class="bt_system_stats" data-permit="'. $this->permissao_ref .'" data-table="'. $this->table .'" data-action="ativar" data-id="'. $aRow['id'] .'" data-db="cms"><img src="'. $this->main_template . 'images/status_vermelho.png" alt="Ativar"></a></div>';
                }else {
                    $row[] = '<div align="center"><a href="#" class="bt_system_stats" data-permit="'. $this->permissao_ref .'" data-table="'. $this->table .'" data-action="desativar" data-id="'. $aRow['id'] .'" data-db="cms"><img src="'. $this->main_template . 'images/status_verde.png" alt="Desativar"></a></div>';
                }
                
                if ($this->permissions[$this->permissao_ref]['excluir']) {
                    $row[] = '<div align="center"><a href="'.$this->getPath().'/edit/id/'.$id.'"><span class="icon12 icomoon-icon-pencil"></span></a> <span style="cursor:pointer" class="bt_system_delete" data-controller="'.$this->getPath().'" data-id="'.$id.'"><span class="icon12 icomoon-icon-remove"></span></span></div>';
                } else {
                    $row[] = '<div align="center"><a href="'.$this->getPath().'/edit/id/'.$id.'"><span class="icon12 icomoon-icon-pencil"></span></a></div>';
                }
                $output['aaData'][] = $row;
            }
		}
        $output['queryExport'] = preg_replace( "/\r|\n/", "", $queryExport );
        echo json_encode($output);
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
        header("Content-Disposition: attachment; filename=Ongs".date("Y-m-d H-i-s").".xls");
        header("Pragma: no-cache");  
        $this->dados = $this->DB_fetch_array($_SESSION['query_export']);
        ob_start();
		include(dirname(__DIR__)."/export/export.phtml");
		$content = ob_get_clean();
		echo utf8_decode($content);
    }

    public function exportDtabAction(){
        $id = $this->getParameter("id");
        $Doacoes = $this->DB_fetch_array("SELECT A.*, date_format(A.create_at, '%d/%m/%Y às %H:%i') criacao, B.nome indicador, C.nome campanha FROM hbrd_app_doacoes A INNER JOIN hbrd_app_pessoa B ON A.id_pessoa = B.id  LEFT JOIN hbrd_app_campanha_doacoes C ON A.id_campanha = C.id  WHERE A.id_ong = $id AND A.status_pagamento = 'approved' ");
        header('Content-Type: text/html; charset=utf-8', true);
        require_once dirname(__DIR__)."/export/pdfD.phtml";
    }

    public function exportDAction(){
        $id = $this->getParameter("id");
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=Ongs".date("Y-m-d H-i-s").".xls");
        header("Pragma: no-cache");

        $Doacoes = $this->DB_fetch_array("SELECT A.*, date_format(A.create_at, '%d/%m/%Y às %H:%i') criacao, B.nome indicador, C.nome campanha FROM hbrd_app_doacoes A INNER JOIN hbrd_app_pessoa B ON A.id_pessoa = B.id  LEFT JOIN hbrd_app_campanha_doacoes C ON A.id_campanha = C.id  WHERE A.id_ong = $id AND A.status_pagamento = 'approved' ");

        ob_start();
		include(dirname(__DIR__)."/export/exportD.phtml");
		$content = ob_get_clean();
		echo utf8_decode($content);
    }

    public function anotationAction() {
        if ($_POST['anotacao'] == "") {
            echo json_encode(['type' => 'validation', 'message' => "Informe a anotação", 'field' => 'anotacao']);
            return;
        }
        $_POST['anotacao'] = ($_POST['anotacao']);
        $_POST['usuario'] = $_SESSION["admin_nome"];
        $_POST['id_usuario'] = $_SESSION['admin_id'];
        $_POST['create_at'] = date("Y-m-d H:i:s");
        $this->DB_insert('hbrd_adm_sinister_annotation', $_POST);
        echo json_encode(['type' => 'success', 'message' => 'Anotação registrada com sucesso']);
    }

    protected function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;
        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->telefone1 == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "telefone1";
            $resposta->return = false;
            return $resposta;
        } else if ($form->cep == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "cep";
            $resposta->return = false;
            return $resposta;
        } else if ($form->rua == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "rua";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_estado == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_estado";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_cidade == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_cidade";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
	}

    /* FUNÇÕES DE MANIPULAÇÂO DE GALERIA DE IMAGENS */

    public function fotosAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->id = $this->getParameter("id");

        $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_ong = $this->id order by ordem");

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

        $fields = array('id_ong', 'ordem', 'url');

        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $type = explode('.',$_FILES['file']['name']);

            $simpleImage = new SimpleImage3();
            $simpleImage->fromFile($_FILES["file"]["tmp_name"]);
            $imagem = $simpleImage->bestFit(300,300)->toString();
            
            $key = $this->putObjectAws($imagem);

            if ($key) {
                $file_uploaded = $upload->file_uploaded;

                $dados = $this->DB_fetch_array("SELECT id, MAX(ordem) as ordem FROM $this->table2 WHERE id_ong=" . $this->id . " GROUP BY id_ong");
                if ($dados->num_rows == 0) {
                    $ordem = 1;
                } else {
                    $ordem = $dados->rows[0]['ordem'];
                    $ordem++;
                }

                $values = array($this->id, "'" . $ordem . "'", "'" . $key . "'");

                $query = $this->DB_insertData($this->table2, implode(',', $fields), implode(',', $values));
                
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

    // DADOS BANCARIOS

    // protected function novaContaAction() {
    //     $this->DB_insert($this->table, ['cpf_cnpj' => $_POST["cpf_cnpj"], 'banco' => $_POST["banco"], 'conta' => $_POST["conta"], 'agencia' => $_POST["agencia"], 'operacao' => $_POST["operacao"]]);
    // }
    protected function alterarContaAction() {
        $this->DB_update($this->table, ['cpf_cnpj' => $_POST["cpf_cnpj"], 'banco' => $_POST["banco"], 'conta' => $_POST["conta"], 'agencia' => $_POST["agencia"], 'operacao' => $_POST["operacao"]], ' where id = '.$_POST["id"]);

    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();