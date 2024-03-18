<?php

use System\Core\Bootstrap;

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();

class __classe__ extends Bootstrap {

    public $module = "";
    public $permissao_ref = __classe__;

    function __construct() {
        parent::__construct();
        
        
        $this->table = "hbrd_".__service__."_".__classe__;
        $this->module_title = "Atividades do Sistema";
        $this->module_icon = "icomoon-icon-stack";
        $this->module_link = $this->getPath();        
        $this->retorno = $this->getPath();


    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
 
        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A ORDER BY A.date DESC");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    private function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();
        
        //defina os campos da tabela
        $aColumns = array('DATE_FORMAT(A.date, "%d/%m/%Y %H:%i") date', 'A.usuario', 'A.atividade');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('DATE_FORMAT(A.date, "%d/%m/%Y %H:%i")', 'A.usuario', 'A.atividade');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        if(isset($_POST["identifier"])){
            $sTable = "hbrd_{$_POST["identifier"]}_log A";
        }
        else if ($this->allowedMain())
            $sTable = "$this->table A";
        else
            exit;

        //echo $sTable;exit;

        //declarar condições extras
        $sWhere = "";

        /*
         * INÍCIO DA ROTINA
         */

        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . $_POST['iDisplayStart'] . ", " .
                    $_POST['iDisplayLength'];
        }

        if (isset($_POST['iSortCol_0'])) {

            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {

                //PEGANDO A PRIMEIRA PALAVRA, PARA TIRAR O ÁLIAS
                $campo_array = explode(" ", $aColumns[intval($_POST['iSortCol_' . $i])]);
                $campo = $campo_array[0];

                if ($_POST['iSortCol_0'] == 0)
                    $campo = "A.date";
                else if ($_POST['iSortCol_0'] == 4)
                    $campo = "DATE_ADD(A.date, INTERVAL 5 MINUTE)";

                if (in_array("DATE_FORMAT", $campo_array)) {
                    $campo = end($campo_array);
                }


                if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
                    $sOrder .= $campo . "
                        " . $_POST['sSortDir_' . $i] . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
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
                $sWhere .= $aColumnsWhere[$i] . " LIKE '%" . $_POST['sSearch'] . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            if ($_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }

                $sWhere .= $aColumns[$i] . " LIKE '%" . $_POST['sSearch_' . $i] . "%' ";
            }
        }

        $rResult = array();
        $sQuery = $this->DB_fetch_array("SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere
            $sOrder
            $sLimit");
        if ($sQuery->num_rows)
            $rResult = $sQuery->rows;
        
        $queryExport = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere
            $sOrder
            $sLimit";

        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
            $sWhere
        ");
        $iFilteredTotal = $sQuery;


        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable $sWhere
        ");
        $iTotal = $sQuery;

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        for ($i = 0; $i < count($aColumns); $i++) {
            $aColumns[$i] = explode(".", $aColumns[$i]);
            $aColumns[$i] = end($aColumns[$i]);
        }
        /*
         * MONTA A TBODY
         */

        if ($rResult) {
            foreach ($rResult as $aRow) {
                $row = array();

                //DATA
                $data_array = explode(" ", $aColumns[0]);
                $data = end($data_array);
                $row[] = '<div align="left">' . $aRow [$data] . '';

                //USUÁRIO
                $row[] = "<div align=left>" . $aRow [$aColumns[1]] . "</div>";

                //ATIVIDADE
                $row[] = "<div align=left>" . $aRow [$aColumns[2]] . "</div>";

                $output['aaData'][] = $row;
            }
        }
        
        $output['queryExport'] = $queryExport;

        echo json_encode($output);
    }

    /*
     * Métodos padrões da classe
     */

    public function getService() {
        return __service__;
    }

    public function getModule() {
        return __classe__;
    }

    public function getPath() {
        return __service__ . '/' . __classe__;
    }

    public function setAction() {
        #acionar o método da classe de acordo com o parâmetro da url
        $action = $this->getParameter(__classe__);
        $action = explode("?", $action);
        $newAction = $action[0] . "Action";

        #antes de acioná-lo, verifica se ele existe
        if (method_exists($this, $newAction)) {
            $this->$newAction();
        } else if ($newAction == "Action") {
            if (method_exists($this, 'indexAction'))
                $this->indexAction();
            else
                $this->renderView($this->getService(), $this->getModule(), "404");
        } else {
            $this->renderView($this->getService(), $this->getModule(), "404");
        }
    }

}
