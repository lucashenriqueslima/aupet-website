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

        $this->table = "hbrd_main_notificacoes";
        $this->module_title = "Notificações";
        $this->module_icon = "icomoon-icon-exclamation";
        $this->module_link = $this->getPath();
        $this->retorno = $this->getPath();
    }

    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $this->model->delete($id);

        echo $this->getPath();
    }

    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.*, B.nome AS categoria FROM {$this->table} A LEFT JOIN hbrd_main_categorias B ON B.id = A.id_categoria ORDER BY id DESC");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->registro['agendar_entrada_data'] = "";
            $this->registro['agendar_entrada_hora'] = "";
            $this->registro['agendar_saida_data'] = "";
            $this->registro['agendar_saida_hora'] = "";

            $this->clientes = $this->DB_fetch_array("SELECT A.*, B.id_notificacao FROM hbrd_main_cliente A LEFT JOIN hbrd_main_notificacao_cliente B ON B.id_cliente = A.id AND B.id_notificacao IS NULL WHERE A.stats = 1");

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT *, DATE_FORMAT(agendar_entrada, '%d/%m/%Y') agendar_entrada_data,DATE_FORMAT(agendar_saida, '%d/%m/%Y') agendar_saida_data, TIME(agendar_entrada) agendar_entrada_hora,TIME(agendar_saida) agendar_saida_hora FROM {$this->table}  WHERE id = {$this->id}", "form");

            $this->registro = $query->rows[0];

            $this->registro['data'] = $this->formataDataDeBanco($this->registro['data']);

            $this->clientes = $this->DB_fetch_array("SELECT A.*, B.id_notificacao FROM hbrd_main_cliente A LEFT JOIN hbrd_main_notificacao_cliente B ON B.id_cliente = A.id AND B.id_notificacao = {$this->id} WHERE A.stats = 1");

            $perm = $this->DB_fetch_array("SELECT A.identificador FROM nova_function A INNER JOIN hbrd_main_notificacao_function B ON B.id_funcao = A.id WHERE B.id_notificacao = {$this->id}");
            foreach ($perm->rows as $row) {
                $this->permission[$row['identificador']] = 1;
            }
        }

        $this->categorias = $this->DB_fetch_array("SELECT * FROM hbrd_main_categorias ORDER BY nome");

        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();

            try {
                if (isset($_POST['agendar_entrada_data']) && $_POST['agendar_entrada_data'] != "") {
                    $_POST['agendar_entrada'] = $this->formataDataDeMascara($_POST['agendar_entrada_data']) . " " . $_POST['agendar_entrada_hora'];
                } else {
                    $_POST['agendar_entrada'] = "NULL";
                }

                if (isset($_POST['agendar_saida_data']) && $_POST['agendar_saida_data'] != "") {
                    $_POST['agendar_saida'] = $this->formataDataDeMascara($_POST['agendar_saida_data']) . " " . $_POST['agendar_saida_hora'];
                } else {
                    $_POST['agendar_saida'] = "NULL";
                }

                $_POST['publicado_em'] = $this->formataDataDeMascara($_POST['publicado_em']);

                $data = $this->formularioObjeto($_POST, $this->table);

                if ($formulario->id == "") {
                    //criar
                    if (!$this->permissions[$this->permissao_ref]['gravar']) {
                        exit();
                    }

                    $formulario->id = $this->DB_insert($this->table, $data);

                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                } else {
                    //alterar
                    if (!$this->permissions[$this->permissao_ref]['editar']) {
                        exit();
                    }

                    $this->DB_update($this->table, $data, "WHERE id = {$formulario->id}");

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                }
                
                $this->DB_delete('hbrd_main_notificacao_cliente', "id_notificacao = {$formulario->id}");
                foreach ($formulario->clientes as $cliente) {
                    $this->DB_insert('hbrd_main_notificacao_cliente', [
                        'id_notificacao' => $formulario->id,
                        'id_cliente' => $cliente,
                    ]);
                }

                $this->DB_delete('hbrd_main_notificacao_function', "id_notificacao = {$formulario->id}");
                $newPerms = array_map(function ($item) {
                    return "'{$item}'";
                }, array_keys($_POST['perm']));

                if ($newPerms) {
                    $permissions = $this->DB_fetch_array('SELECT * FROM nova_function WHERE identificador IN (' . implode(',', $newPerms) . ')');
                    foreach ($permissions->rows as $perm) {
                        $this->DB_insert('hbrd_main_notificacao_function', [
                            'id_notificacao' => $formulario->id,
                            'id_funcao' => $perm['id'],
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

            echo json_encode($resposta);
        }
    }

    protected function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->titulo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "titulo";
            $resposta->return = false;
            return $resposta;
        } else if ($form->publicado_em == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "publicado_em";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_categoria == '') {
            $resposta->type = "validation";
            $resposta->message = "Selecione a categoria";
            $resposta->field = "id_categoria";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_data != "" AND ! $this->checkdate($form->agendar_entrada_data)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com uma data válida";
            $resposta->field = "agendar_entrada_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_hora != "" AND ! $this->checktime($form->agendar_entrada_hora)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com um horário válido";
            $resposta->field = "agendar_entrada_hora";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_hora != "" AND $form->agendar_entrada_data == "") {
            $resposta->type = "validation";
            $resposta->message = "Informe a data para este horário";
            $resposta->field = "agendar_entrada_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_saida_data != "" AND ! $this->checkdate($form->agendar_saida_data)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com uma data válida";
            $resposta->field = "agendar_saida_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_saida_hora != "" AND ! $this->checktime($form->agendar_saida_hora)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com um horário válido";
            $resposta->field = "agendar_saida_hora";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_saida_hora != "" AND $form->agendar_saida_data == "") {
            $resposta->type = "validation";
            $resposta->message = "Informe a data para este horário";
            $resposta->field = "agendar_saida_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_data != "" AND $form->agendar_saida_data != "" AND ( strtotime($this->datebr($form->agendar_entrada_data)) > strtotime($this->datebr($form->agendar_saida_data)))) {
            $resposta->type = "validation";
            $resposta->message = "Data de entrada maior que a de saída";
            $resposta->field = "agendar_entrada_data";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    protected function validaUpload($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {

            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->blog_uploaded = $upload->file_uploaded;
            }
        }

        if ((!isset($form->id) || $form->id == "") && !isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Imagem não selecionada.";
            $resposta->return = false;
            return $resposta;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
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