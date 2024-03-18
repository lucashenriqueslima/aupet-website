<?php

use System\Base\{SistemaBase, BadRequest};
use cms\module\listasemail\Model\ListasEmail as Model;

class __classe__ extends SistemaBase
{
    public $module = "";
    public $permissao_ref = "lista-emails";
    public $table = "hbrd_cms_listas";

    function __construct()
    {
        parent::__construct();
        $this->module_title = "Minhas Listas";
        $this->module_icon = "minia-icon-list-3";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();

        $this->model = new Model();
    }

    protected function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");

        if ($id >= 6 or $id <= 12) {
            echo "error";
            exit();
        }
        $query = $this->model->delete($id);

        if (!$query->query) {
            echo "error";
        } else {
            echo $this->getPath();
        }
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A ORDER BY A.nome");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction()
    {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                $this->noPermission();
            }
            $campos = $this->DB_columns($this->table);

            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }
            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];

            $this->listas = $this->DB_fetch_array("SELECT * FROM hbrd_cms_listas WHERE id <> $this->id", "form");
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function datatableEmailsAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $lista = $this->getParameter("lista");

        /*
         * CONFIGURAÇÕES INICIAIS
         */

        //defina os campos da tabela
        $aColumns = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y") nascimento', 'A.id');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y")', 'A.id');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "hbrd_cms_email A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "hbrd_cms_email A INNER JOIN hbrd_cms_email_cms_listas B ON B.id_email = A.id AND B.id_lista = $lista";

        //declarar condições extras
        $sWhere = "";

        /*
         * INÍCIO DA ROTINA
         */
        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . $_POST['iDisplayStart'] . ", " . $_POST['iDisplayLength'];
        }

        if (isset($_POST['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";

            for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {
                //PEGANDO A PRIMEIRA PALAVRA, PARA TIRAR O ÁLIAS
                $campo_array = explode(" ", $aColumns[intval($_POST['iSortCol_' . $i])]);
                $campo = $campo_array[0];

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
        if ($sQuery->num_rows) {
            $rResult = $sQuery->rows;
        }
        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
            $sWhere
        ");
        $iFilteredTotal = $sQuery;


        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
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
                $indice = 3;
                $aColumns[$indice] = explode(".", $aColumns[$indice]);
                $aColumns[$indice] = end($aColumns[$indice]);
                $id = $aRow[$aColumns[$indice]];

                //NOME
                $row[] = '<div align="left"><a href="cms/email/edit/id/' . $id . '">' . $aRow [$aColumns[0]] . '</a></div>';

                //E-MAIL
                $row[] = '<div align="left"><a href="cms/email/edit/id/' . $id . '">' . $aRow [$aColumns[1]] . '</a></div>';

                //DATA DE NASCIMENTO
                $nascimento = explode(" ", $aColumns[2]);
                $nascimento = end($nascimento);
                $row[] = '<div align="left"><a href="cms/email/edit/id/' . $id . '">' . $aRow [$nascimento] . '</a></div>';

                //AÇÃO
                if ($this->permissions[$this->permissao_ref]['excluir']) {
                    $row[] = '<div align="center"><a href="cms/email/edit/id/' . $id . '"><span class="s12 icomoon-icon-pencil"></span></a> <span style="cursor:pointer" class="bt_system_delete" data-retorno="' . $this->getPath() . '/edit/id/' . $lista . '" data-id="' . $id . '" data-id-lista="' . $lista . '" data-controller="cms/email"><span class="s12 icomoon-icon-remove"></span></span> <input data-controller="cms/email/del" data-id-lista="' . $lista . '"  type="checkbox" id="del_' . $id . '" value="' . $id . '" class="del-this checkbox" /></div>';
                } else {
                    $row[] = '<div align="center"><a href="cms/email/edit/id/' . $id . '"><span class="s12 icomoon-icon-pencil"></span></a></div>';
                }
                $output['aaData'][] = $row;
            }
        }
        echo json_encode($output);
    }

    protected function importListAction()
    {
        if (!$this->permissions[$this->permissao_ref]['gravar']) {
            $this->noPermission();
        }
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass ();

        if ($formulario->id_lista_import == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_lista_import";
            $resposta->return = false;
        } else {
            $data['id_lista_import'] = $formulario->id_lista_import;
            $data['id_lista'] = $formulario->id_lista;
            $query = $this->model->importEmailByList($data);

            if ($query->query) {
                $resposta->type = "success";
                $resposta->message = "Lista importada com sucesso!";
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        }
        echo json_encode($resposta);
    }

    protected function exportListAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $data = array();
        $lista = "";

        $id = $this->getParameter("id");

        if ($id) {
            $query = $this->DB_fetch_array("SELECT A.nome, A.email, DATE_FORMAT(A.nascimento, '%d/%m/%Y') nascimento, C.nome lista FROM hbrd_cms_email A INNER JOIN hbrd_cms_email_cms_listas B ON B.id_email = A.id INNER JOIN hbrd_cms_listas C ON C.id = B.id_lista WHERE B.id_lista = $id ORDER BY A.nome");
            if ($query->num_rows) {
                $data = $query->rows;
                $lista = $query->rows[0]['lista'];
            }
        }

        if ($lista == "") {
            echo '<script>javascript:history.back() </script>';
            exit;
        }

        //FUNÇÃO TRANSFORMA OBJETO EM ARRAY
        function objectToArray($object)
        {
            $arr = array();
            for ($i = 0; $i < count($object); $i++) {
                $arr[] = get_object_vars($object[$i]);
            }
            return $arr;
        }

        //RETIRAR ID E LISTA DO ARRAY
        foreach ($data as $data) {
            unset($data['id'], $data['lista']);
            //RECONSTROI ARRAY PARA EXPORTAÇÃO
            $registros[] = $data;
        }

        function array_para_csv(array &$array)
        {
            ob_start();
            if (count($array) == 0) {
                return null;
            }
            ob_end_clean();
            $df = fopen("php://output", 'w');
            //fputcsv($df, array_keys(reset($array)));
            foreach ($array as $row) {
                fputcsv($df, $row);
            }
            fclose($df);
            return ob_get_clean();
        }

        function cabecalho_download_csv($filename)
        {
            // desabilitar cache
            $now = gmdate("D, d M Y H:i:s");
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");

            // forçar download  
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");

            // disposição do texto / codificação
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");
        }

        cabecalho_download_csv($this->formataUrlAmiga($lista) . "-emails-" . date("Y-m-d") . ".csv");
        echo array_para_csv($registros);
        die();
    }

    protected function importEmailAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass ();

        if ($_FILES ['csv'] ['tmp_name'] == "") {
            $resposta->type = "error";
            $resposta->message = 'Escolha um arquivo do formato CSV!';
            echo json_encode($resposta);
            exit();
        }

        $upload = $this->uploadFile("csv", array(
            "csv"
        ));

        if (!$upload->return) {
            $resposta->type = "error";
            $resposta->message = "Formato inválido!";
            echo json_encode($resposta);
        }
        $query = $this->model->importEmail($formulario, $upload);

        if ($query->query) {
            $resposta->type = "success";
            $resposta->message = "E-mails importados com sucesso!";
            if (isset($formulario->retorno))
                $resposta->retorno = $formulario->retorno;
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
        echo json_encode($resposta);
    }

    protected function saveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['emaillist'] = $this->formularioObjeto($_POST, $this->table);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar']) {
                    exit();
                }
                $query = $this->model->save($data);

                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar']) {
                    exit();
                }
                $query = $this->model->update($data);

                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
            echo json_encode($resposta);
        }
    }

    protected function validaFormulario($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-", (str_replace(".php", "", basename(__FILE__))))));

$class = new __classe__();
$class->setAction();