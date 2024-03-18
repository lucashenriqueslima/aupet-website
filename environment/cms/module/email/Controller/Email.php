<?php

use System\Base\{SistemaBase, BadRequest};
use cms\module\email\Model\Email;

class __classe__ extends SistemaBase
{

    public $module = "";
    public $permissao_ref = "lista-emails";
    public $table = "hbrd_cms_email";

    protected function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");
        $lista = $this->getParameter("lista");
        $query = $this->model->delete($id, $lista);

        if (!$query->query) {
            echo "error";
        } else {
            if (isset($_POST['retorno']) && $_POST['retorno'] != "") {
                echo $_POST['retorno'];
            } else {
                echo $this->getPath();
            }
        }
    }

    function __construct()
    {
        parent::__construct();
        $this->module_title = "E-mails";
        $this->module_icon = "entypo-icon-email";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();
        $this->model = new Email();
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
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
            $this->listas = $this->DB_fetch_array("SELECT A.*, B.id_lista FROM hbrd_cms_listas A LEFT JOIN hbrd_cms_email_cms_listas B ON B.id_lista = A.id AND B.id_email = 0 ORDER BY A.nome", "form");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }
            $query = $this->DB_fetch_array("SELECT *, CASE WHEN nascimento IS NULL THEN nascimento ELSE DATE_FORMAT(nascimento, '%d/%m/%Y') END nascimento FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
            $this->listas = $this->DB_fetch_array("SELECT A.*, B.id_lista FROM hbrd_cms_listas A LEFT JOIN hbrd_cms_email_cms_listas B ON B.id_lista = A.id AND B.id_email = $this->id ORDER BY A.nome", "form");
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function saveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['email'] = $this->formularioObjeto($_POST, $this->table);

            if (!isset($formulario->id) || $formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar']) {
                    exit();
                }

                if ($data['email']->nascimento == "") {
                    $data['email']->nascimento = "NULL";
                } else {
                    $data['email']->nascimento = $this->formataDataDeMascara($data['email']->nascimento);
                }

                if (isset($formulario->id_lista)) {
                    $data['id_lista'] = $formulario->id_lista;
                }

                if (isset($formulario->listas)) {
                    $data['listas'] = $formulario->listas;
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
                $existe = $this->DB_fetch_array("SELECT * FROM $this->table WHERE email = '{$data['email']->email}' AND id != {$data['email']->id}");

                if ($existe->num_rows) {
                    $resposta->type = "error";
                    $resposta->message = "Este e-mail já está cadastrado!";
                    echo json_encode($resposta);
                    exit();
                }

                if ($data['email']->nascimento == "") {
                    $data['email']->nascimento = "NULL";
                } else {
                    $data['email']->nascimento = $this->formataDataDeMascara($data['email']->nascimento);
                }

                if (isset($formulario->listas)) {
                    $data['listas'] = $formulario->listas;
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

    protected function listasByIdEmail($id = null)
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        if ($id != null) {
            $query = $this->DB_fetch_array("SELECT B.nome nome FROM hbrd_cms_email_cms_listas A INNER JOIN hbrd_cms_listas B ON B.id = A.id_lista WHERE A.id_email = $id");
            if ($query->num_rows) {
                $result = "";
                $complemento = "";
                foreach ($query->rows as $row) {
                    $result .= $complemento . $row['nome'];
                    $complemento = ", ";
                }
                return $result;
            }
        }
    }

    protected function datatableEmailsAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        /*
         * CONFIGURAÇÕES INICIAIS
         */
        //defina os campos da tabela
        $aColumns = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y") nascimento', 'B.id_lista', 'A.id');
        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y")', 'B.id_lista', 'A.id');
        //defina o coluna índice
        $sIndexColumn = "A.id";
        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "hbrd_cms_email A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "hbrd_cms_email A LEFT JOIN hbrd_cms_email_cms_listas B ON B.id_email = A.id";
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
                $campo = str_replace(array("DATE_FORMAT(", ","), "", $campo);

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
            GROUP BY A.id
            $sOrder
            $sLimit");
        if ($sQuery->num_rows) {
            $rResult = $sQuery->rows;
        }
        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn FROM $sTable $sWhere GROUP BY A.id");
        $iFilteredTotal = $sQuery;
        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn FROM $sTable GROUP BY A.id");
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
                $indice = 4;
                $aColumns[$indice] = explode(".", $aColumns[$indice]);
                $aColumns[$indice] = end($aColumns[$indice]);
                $id = $aRow[$aColumns[$indice]];
                //NOME
                $row[] = '<div align="left"><a href="' . $this->getPath() . '/edit/id/' . $id . '">' . $aRow [$aColumns[0]] . '</a></div>';
                //E-MAIL
                $row[] = '<div align="left"><a href="' . $this->getPath() . '/edit/id/' . $id . '">' . $aRow [$aColumns[1]] . '</a></div>';
                //DATA DE NASCIMENTO
                $nascimento = explode(" ", $aColumns[2]);
                $nascimento = end($nascimento);
                $row[] = '<div align="left"><a href="' . $this->getPath() . '/edit/id/' . $id . '">' . $aRow [$nascimento] . '</a></div>';
                //LISTA
                $row[] = '<div align="left"><a href="' . $this->getPath() . '/edit/id/' . $id . '">' . $this->listasByIdEmail($id) . '</a></div>';
                //AÇÃO
                if ($this->permissions[$this->permissao_ref]['excluir']) {
                    $row[] = '<div align="center"><a href="' . $this->getPath() . '/edit/id/' . $id . '"><span class="icon12 icomoon-icon-pencil"></span></a> <span style="cursor:pointer" class="bt_system_delete" data-controller="' . $this->getPath() . '" data-id="' . $id . '"><span class="icon12 icomoon-icon-remove"></span></span> <input type="checkbox" id="del_' . $id . '" value="' . $id . '" class="del-this checkbox" /></div>';
                } else {
                    $row[] = '<div align="center"><a href="' . $this->getPath() . '/edit/id/' . $id . '"><span class="icon12 icomoon-icon-pencil"></span></a></div>';
                }
                $output['aaData'][] = $row;
            }
        }
        echo json_encode($output);
    }

    protected function exportEmailsAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $data = array();
        $query = $this->DB_fetch_array("SELECT nome, email, DATE_FORMAT(nascimento, '%d/%m/%Y') nascimento FROM hbrd_cms_email ORDER BY nome");

        if ($query->num_rows) {
            $data = $query->rows;
        }

        if (count($data) < 1) {
            echo '<script>javascript:history.back() </script>';
            exit;
        }

        function array_para_csv(array &$array)
        {
            if (count($array) == 0) {
                return null;
                exit;
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

        cabecalho_download_csv("emails-" . date("Y-m-d") . ".csv");
        echo array_para_csv($data);
        die();
    }

    protected function relateListAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit;
        }
        $formulario = $this->formularioObjeto($_POST);
        $resposta = false;

        if (!$formulario->lista) {
            $resposta = "Selecione uma Lista";
        } else {
            foreach ($formulario->i as $idEmail) {
                $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email_cms_listas A INNER JOIN hbrd_cms_listas B ON B.id = A.id_lista WHERE A.id_email = $idEmail AND A.id_lista = {$formulario->lista}");

                if (!$verifica->num_rows) {

                    $fields = array('id_lista', 'id_email');
                    $values = array("{$formulario->lista}", "$idEmail");
                    $query = $this->DB_insert('hbrd_cms_email_cms_listas', implode(',', $fields), implode(',', $values));

                    if ($query->query)
                        $query = true;
                    else
                        $query = "Aconteceu algum erro";
                }
            }
        }
        echo $resposta;
    }

    protected function validaFormulario($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome_email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($this->validaEmail($form->email) == 0) {
            $resposta->type = "validation";
            $resposta->message = "Formato de Email Incorreto";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->nascimento != "" && $this->checkdate($form->nascimento) == 0) {
            $resposta->type = "validation";
            $resposta->message = "Data inválida";
            $resposta->field = "nascimento";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    protected function importEmailAction()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass ();
        if ($_FILES ['csv']['tmp_name'] == "") {
            $resposta->type = "error";
            $resposta->message = 'Escolha um arquivo do formato CSV!';
            return json_encode($resposta);
        }
        $upload = $this->uploadFile("csv", array("csv"));
        if (!$upload->return) {
            $resposta->type = "error";
            $resposta->message = "Formato inválido!";
            return json_encode($resposta);
        }
        $this->model->importEmail($formulario, $upload);
        $resposta->type = "success";
        $resposta->message = "E-mails importados com sucesso!";
        if ($formulario->retorno) $resposta->retorno = $formulario->retorno;
        return json_encode($resposta);
    }
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-", (str_replace(".php", "", basename(__FILE__))))));
$class = new __classe__();
$class->setAction();
