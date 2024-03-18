<?php

use System\Core\Bootstrap;

class __classe__ extends Bootstrap
{
    public $module = "";
    public $permissao_ref = "contato";

    function __construct()
    {
        parent::__construct();
        $this->module_icon = "entypo-icon-email";
        $this->module_link = __classe__;
        $this->module_title = "Soluções - Cadastros";
        $this->retorno = $this->getPath();
        $this->table = "hbrd_desk_fale_conosco";
        $this->table_situacoes_historico = $this->table . "_situacoes_historico";
        $this->id_formulario = 1;
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->list = $this->DB_fetch_array("SELECT DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') registro, A.*, S.nome situacao, B.utm_source, B.utm_campaign
        FROM $this->table A 
        LEFT JOIN hbrd_desk_situacoes S ON S.id = A.id_situacao 
        LEFT JOIN hbrd_cms_seo_acessos_historico B ON B.session = A.session 
        GROUP BY A.id");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction()
    {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            $this->noPermission();
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
            $this->registro = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') data FROM $this->table A WHERE A.id = $this->id")->rows[0];
            $this->statusHistorico = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') data, data registro FROM $this->table_situacoes_historico WHERE id_cadastro = {$this->registro['id']}");
            $this->situacoes = $this->DB_fetch_array("SELECT * FROM hbrd_desk_situacoes WHERE id_formulario = $this->id_formulario ORDER BY ordem");
        }
        $this->historicos = $this->DB_fetch_array("SELECT DATE_FORMAT(B.date, '%d/%m/%Y às %H:%i:%s') registro, B.date, C.seo_title titulo, B.origem, CONCAT(B.cidade, ', ', B.estado, ' - ', B.pais) localizacao, B.pais, B.estado, B.cidade, B.dispositivo, B.ip, B.session, B.utm_source, B.utm_medium, B.utm_term, B.utm_content, B.utm_campaign FROM $this->table A LEFT JOIN hbrd_cms_seo_acessos_historico B ON B.session = A.session INNER JOIN $this->_seo_table C ON C.id = B.id_seo WHERE A.session = '{$this->registro['session']}' ORDER BY B.date");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function saveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();

        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }
        $data['contact'] = $this->formularioObjeto($_POST, $this->table);
        $data['history'] = new \stdClass();
        $data['history']->comentario = $_POST["comentario"];
        $idContact = $data['contact']->id;
        // $this->DBUpdate($this->table, $data['contact'], " WHERE id=" . $idContact);
        $updateEmail = $this->DB_sqlUpdate($this->table, $data['contact'], " WHERE id=" . $idContact);

        if (empty($updateEmail) or !$this->mysqli->query($updateEmail)) {
            throw new \Exception($updateEmail);
        }
        $situacaoAtual = ((bool)$data['contact']->id_situacao) ? $this->DB_fetch_array("SELECT * FROM hbrd_desk_situacoes WHERE id = {$data['contact']->id_situacao}")->rows[0] : [];
        $data["history"]->id_cadastro = $idContact;
        $data["history"]->situacao = $situacaoAtual['nome'];
        $data["history"]->usuario = $_SESSION['admin_id'] . " - " . $_SESSION['admin_nome'];
        // $this->DBInsert($this->table_situacoes_historico, $data["history"]);
        $insertSituacao = $this->DB_sqlInsert($this->table_situacoes_historico, $data["history"]);

        if (empty($insertSituacao) or !$this->mysqli->query($insertSituacao)) {
            throw new \Exception($insertSituacao);
        }
        $this->inserirRelatorio("Alterou cadastro: [" . $data['contact']->nome . "], id: [$idContact]");

        echo json_encode(["type" => "success", "message" => "Sucesso!"]);
    }

    protected function exportContactsAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=cadastro-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");
        $this->dados = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') data, S.nome situacao FROM $this->table A LEFT JOIN hbrd_desk_situacoes S ON S.id = A.id_situacao GROUP BY A.id ORDER BY A.data DESC");

        $this->renderExport($this->getService(), $this->getModule(), "contacts");
    }

    protected function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        $this->DBDelete($this->table, "where id=" . $id);
        $this->inserirRelatorio("Apagou cadastro: [" . $dados->rows[0]['nome'] . "] id: [$id]");

        echo $this->getPath();
    }

    protected function editSituationAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }
        foreach ($_POST['i'] as $id) {
            $query = $this->DB_update($this->table, "id_situacao = {$_POST['situacao']} WHERE id = $id");
            if ($query) {
                $nome = $this->DB_fetch_array("SELECT nome FROM hbrd_desk_situacoes WHERE id = {$_POST['situacao']}");
                $this->DB_insert($this->table_situacoes_historico, "status,id_cadastro,usuario", "'{$nome->rows[0]['nome']}',$id,'" . $_SESSION['admin_id'] . " - " . $_SESSION['admin_nome'] . "'");
            }
        }
        if (!$query) {
            echo "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
    }
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace("_", "-", (str_replace(".php", "", basename(__FILE__))))));

$class = new __classe__();
$class->setAction();