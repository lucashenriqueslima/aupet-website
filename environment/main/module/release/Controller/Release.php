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
        $this->getLevelsAccess();
        $this->table = "hbrd_main_lancamentos";
        $this->module_title = "Lançamentos";
        $this->module_icon = "icomoon-icon-coin";
        $this->module_link = $this->getPath();
        $this->retorno = $this->getPath();
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = (int) $this->getParameter("id");

        try {
            $this->DB_delete($this->table, "id = {$id}");
            $this->inserirRelatorio("Removeu um lançamento [{$id}]");
            die($this->getPath());
        } catch (\Exception $e) {
            die('error');
        }
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $date = $_POST['date'] ?? date('m/Y');
        $filter = explode('/', $date);

        $this->month = $filter[0];
        $this->year = $filter[1];
        $this->list = $this->DB_fetch_array("SELECT A.*, COALESCE(C.nome_fantasia, C.empresa, C.nickname) AS cliente FROM {$this->table} A LEFT JOIN hbrd_main_cliente C ON C.id =  A.id_cliente WHERE MONTH(A.data_vencimento) = {$this->month} AND YEAR(A.data_vencimento) = {$this->year}");
        $this->months = $this->DB_fetch_array("SELECT DATE_FORMAT(data_vencimento, '%m/%Y') AS label, MONTH(data_vencimento) as month, YEAR(data_vencimento) as year FROM hbrd_main_lancamentos GROUP BY label ORDER BY data_vencimento");

        if ($this->months->num_rows) {
            if ((int) $this->months->rows[0]['month'] !== (int) date('m')) {
                array_unshift($this->months->rows, [
                    'label' => date('m/Y'),
                    'month' => date('m'),
                    'year' => date('Y'),
                ]);
            }
        }

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") { //new
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

        } else { //edit
            if ($_SESSION['admin_id'] != $this->id && !$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }

            $query = $this->DB_fetch_array("SELECT * FROM {$this->table} WHERE id = {$this->id}");
            $this->registro = $query->rows[0];
            $this->anotacoes = $this->DB_fetch_array("SELECT A.conteudo, U.nome, A.registrado_em FROM hbrd_main_lancamento_anotacoes A INNER JOIN hbrd_main_usuarios U ON U.id = A.id_usuario WHERE A.id_lancamento = {$this->id} ORDER BY A.registrado_em DESC");            
            $this->historico = $this->DB_fetch_array("SELECT A.registrado_em, A.descricao, U.nome FROM hbrd_main_lancamentos_historico A INNER JOIN hbrd_main_usuarios U ON U.id = A.id_usuario WHERE A.id_lancamento = {$this->id} ORDER BY A.registrado_em DESC");
        }

        $this->clientes = $this->DB_fetch_array("SELECT id, COALESCE(nome_fantasia, empresa, nickname) AS nome FROM hbrd_main_cliente ORDER BY nome");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();
        $data = $this->formularioObjeto($_POST, $this->table);
        
        if ($data->data_vencimento) {
            $data->data_vencimento = (\DateTime::createFromFormat('d/m/Y', $data->data_vencimento))->format('Y-m-d');
        }

        if ($data->data_pagamento) {
            $data->data_pagamento = (\DateTime::createFromFormat('d/m/Y', $data->data_pagamento))->format('Y-m-d H:i:s');
        }

        $data->tipo = ($data->valor < 0)? 'P' : 'R';
        $data->valor = str_replace(',', '.', str_replace('.', '', $data->valor));

        try {
            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar']) {
                    die();
                }

                $validacao = $this->validaFormulario($formulario);
                if (!$validacao->return) {
                    die(json_encode($validacao));
                }

                $id = $this->DB_insert($this->table, $data);
                $this->inserirRelatorio("Registrou um lançamento [{$id}]");
                $resposta->type = "success";
                $resposta->message = "Registrado com sucesso!";
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar']) {
                    die();
                }

                $this->DB_update($this->table, $data, "WHERE id = {$formulario->id}");
                $this->DB_insert('hbrd_main_lancamentos_historico', [
                    'id_usuario' => $_SESSION['admin_id'],
                    'id_lancamento' => $formulario->id,
                    'descricao' => 'Atualizou o lançamento',
                ]);

                $this->inserirRelatorio("Atualizou um lançamento [{$formulario->id}]");

                if ($data->data_pagamento) {
                    $this->DB_insert('hbrd_main_lancamentos_historico', [
                        'id_usuario' => $_SESSION['admin_id'],
                        'id_lancamento' => $formulario->id,
                        'descricao' => 'Atualizou o lançamento para Faturado',
                    ]);
                }

                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
            }

        } catch (\Exception $e) {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        die(json_encode($resposta));
    }

    protected function statusAction() {
        $a = $_POST["a"];
        $table = $_POST["t"];
        $id = $_POST["i"];
        $permit = $_POST["p"];

        if ($this->permissions[$permit]['editar']) {
            if ($a == "ativar") {
                $this->inserirRelatorio("Ativou registro na tabela [{$table}] id [{$id}]");
                $this->DB_update($table, ["stats" => 1], " WHERE id = {$id}");
                echo "desativar";
            } else if ("desativar") {
                $this->inserirRelatorio("Desativou registro na tabela [{$table}] id [{$id}]");
                $this->DB_update($table, ["stats" => 0], "WHERE id= {$id}");
                echo "ativar";
            }
        } else {
            echo "Você não tem permissão para editar esta função";
        }
    }

    private function annotationAction() {
        $id = (int) $_POST['id'];
        $admin = (int) $_SESSION['admin_id'];

        if (!$id || !$admin) {
            die(json_encode([
                'status' => false,
                'message' => 'Aconteceu um erro no sistema, favor tente novamente mais tarde!',
            ]));
        }

        $this->DB_insert('hbrd_main_lancamento_anotacoes', [
            'id_lancamento' => $id,
            'id_usuario' => $admin,
            'conteudo' => $_POST['conteudo'],
        ]);

        die(json_encode([
            'status' => true,
            'message' => 'Registrado com sucesso!',
        ]));
    }

    private function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if (!trim($form->data_vencimento)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "data_vencimento";
            $resposta->return = false;

            return $resposta;
        }

        if (!trim($form->valor)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "valor";
            $resposta->return = false;
            
            return $resposta;
        }

        return $resposta;
    }

    private function validaUpload($form) {
        $resposta = new \stdClass();
        $resposta->return = true;        
        return $resposta;
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
