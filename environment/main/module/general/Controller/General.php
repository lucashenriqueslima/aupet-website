<?php
use System\Base\{SistemaBase, BadRequest};
class __classe__ extends SistemaBase {
    function __construct() {
        parent::__construct();
    }
    protected function statusAction() {
        $a = $_POST["a"];
        $table = $_POST["t"];
        $id = $_POST["i"];
        $permit = $_POST["p"];
        /*if (($instaceDb = "db" . $_POST["db"]) !== "db")
            $this->$instaceDb();*/
        if ($this->permissions[$permit]['editar']) {
            if ($a == "ativar") {
                $this->inserirRelatorio("Ativou registro na tabela [$table] id [$id]");
                $this->DB_update($table, ["stats" => 1], " WHERE id = $id");
                echo "desativar";
            } else if ("desativar") {
                $this->inserirRelatorio("Desativou registro na tabela [$table] id [$id]");
                $this->DB_update($table, ["stats" => 0], "WHERE id=$id");
                echo "ativar";
            }
        } else {
            echo "Você não tem permissão para editar esta função";
        }
    }
    protected function getCidadesAction() {
        $id = $_POST["id"];
        $cidades = $this->getCidades($id);
        echo json_encode($cidades);
    }
    protected function getCidadesByEstadoAction() {
        $id = $_POST["id"];
        $idCidade = $_POST['idCidade'];
        $cidades = $this->getCidades($id);
        echo '<option value="">Selecione...</option>';
        foreach ($cidades as $cidade) {
            echo '<option data-name="' . $cidade['cidade'] . '" value="' . $cidade['id'] . '">' . $cidade['cidade'] . '</option>';
        }
    }
    protected function getCidadesByEstadoJsonAction() {
        $id = $_POST["id"];
        $cidades = $this->getCidades($id);
        echo json_encode($cidades);
    }
    protected function getCidadesByEstadoUfAction() {
        $estado = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state where uf = '{$_POST["uf"]}'")->rows[0];
        $cidades = $this->getCidades($estado['id']);
        echo json_encode($cidades);
    }
    protected function getCidadesByEstadoIdAction() {
        $cidades = $this->getCidades($_POST["id"]);
        echo json_encode($cidades);
    }
    protected function setCurrentServiceAction() {
        $_SESSION["admin_service"]["current"] = $_POST["id"];
    }
    protected function existContributorAction(){
        $response = new \stdClass();
        $query = $this->DB_fetch_array("SELECT * FROM hbrd_rh_contributor WHERE id_usuario = ".$_SESSION['admin_id']);
        $response->id = ($query->num_rows) ? $query->rows[0]['id'] : false;
        if(!empty($_POST))
            echo json_encode($response);
        else    
            return $response->id;
    }
    protected function helperEmailAction() {
        $this->renderView($this->getService(), $this->getModule(), "helper/email");
    }
    protected function helperTextAction() {
        $this->renderView($this->getService(), $this->getModule(), "helper/text");
    }
    protected function helperTextModalAction() {
        $this->renderView($this->getService(), $this->getModule(), "helper/textModal");
    }
    protected function helperPhoneAction() {
        $this->telefone_tipos = $this->getIndices(5);
        $this->renderView($this->getService(), $this->getModule(), "helper/phone");
    }
    protected function helperDocumentAction() {
        $this->documento_tipos = $this->getIndices(1);
        $this->renderView($this->getService(), $this->getModule(), "helper/document");
    }
    protected function helperContactAction() {
        $this->renderView($this->getService(), $this->getModule(), "helper/contact");
    }
    protected function getPeopleAction() {
        $search = $_POST['search'];
        $searchInt = preg_replace("[^0-9]", "", $search);
        $tabela = $_POST['tabela'];
        $where = "";
        if (!empty($search)) {
            $campos = $this->DB_columns($this->_tb_prefix."main_person");
            $where .= " WHERE ( ";
            $separador = "";
            foreach ($campos as $row) {
                $where .= $separador . "A.$row LIKE '%$search%'";
                $separador = " OR ";
            }
            $where .= " OR concat(IFNULL(A.nome, ''), ' ', IFNULL(A.sobrenome, '')) LIKE '%$search%'";
            $where .= " OR T.telefone LIKE '%$search%'";
            $where .= " OR REPLACE(REPLACE(REPLACE(REPLACE(T.telefone, ' ', ''), '-',''),'(',''),')','') LIKE '%$searchInt%'";
            $where .= " OR D.documento LIKE '%$search%'";
            $where .= " OR REPLACE(REPLACE(REPLACE(REPLACE(D.documento, '.' , ''), '-' , ''), '/', ''),' ', '')  LIKE '%$searchInt%'";
            $where .= " OR A.cep LIKE '%$search%'";
            $where .= " OR REPLACE(REPLACE(REPLACE(A.cep, '-', ''),'.',''),' ','') LIKE '%$searchInt%'";
            $where .= " OR E.email LIKE '%$search%'";
            $where .= ")";
        }
        $query = $this->DB_fetch_array("SELECT PT.tabela, A.id, CASE A.tipo WHEN '#000000018' THEN CONCAT(IFNULL(A.nome,''),' ',IFNULL(A.sobrenome,'')) WHEN '#000000019' THEN A.razao_social END nome, A.foto, T.telefone, D.documento, E.email FROM hbrd_main_person A LEFT JOIN hbrd_main_person_phone T ON T.id_pessoa = A.id  LEFT JOIN hbrd_main_person_email E ON E.id_pessoa = A.id  LEFT JOIN hbrd_main_person_document D ON D.id_pessoa = A.id  LEFT JOIN hbrd_main_person_table PT ON PT.id_pessoa = A.id AND PT.id_pessoa $where GROUP BY A.id ORDER BY A.nome");
        if ($query->num_rows) {
            $return = array();
            $idPessoa = array();
            foreach ($query->rows as $row) {
                if ($tabela == $row['tabela']) {
                    $idPessoa[] = $row['id'];
                }
            }
            foreach ($query->rows as $row) {
                $info = '';
                if (!in_array($row['id'], $idPessoa)) {
                    if (!empty($row['nome'])) {
                        $info .= $row['nome'];
                    }
                    $sep = '';
                    if (!empty($row['email'])) {
                        if (!empty($info))
                            $sep = " - ";
                        $info .= $sep . $row['email'];
                    }
                    $sep = '';
                    if (!empty($row['telefone'])) {
                        if (!empty($info))
                            $sep = " - ";
                        $info .= $sep . $row['telefone'];
                    }
                    $sep = '';
                    if (!empty($row['documento'])) {
                        if (!empty($info))
                            $sep = " - ";
                        $info .= $sep . $row['documento'];
                    }
                    $return['info'][] = $info;
                    $return['id'][] = $row['id'];
                }
            }
            if (!empty($return['id']))
                echo json_encode(Array('status' => 1, 'rows' => $return, 'sql' => "SELECT A.id, concat(IFNULL(A.nome, ''), ' ', IFNULL(A.sobrenome, '')) nome, A.foto, T.telefone, D.documento, E.email FROM hbrd_main_person A LEFT JOIN hbrd_main_person_phone T ON T.id_pessoa = A.id AND T.principal = 1 LEFT JOIN hbrd_main_person_email E ON E.id_pessoa = A.id AND E.principal = 1 LEFT JOIN hbrd_main_person_document D ON D.id_pessoa = A.id AND D.principal = 1 INNER JOIN hbrd_main_person_table TB ON TB.id_pessoa = A.id AND TB.tabela != '$tabela' $where GROUP BY A.id ORDER BY A.nome"));
            else
                echo json_encode(Array('status' => 0));
        } else {
            echo json_encode(Array('status' => 0));
        }
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();