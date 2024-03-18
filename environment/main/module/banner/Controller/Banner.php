<?php

use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase {

    public $module = "";
    public $permissao_ref = "banners";
    public $table = "hbrd_main_banners";

    function __construct() {
        parent::__construct();
        $this->module_title = "Banners";
        $this->module_icon = "icomoon-icon-image-3";
        $this->module_link = __classe__;        
        $this->retorno = $this->getPath();
        $this->banner_uploaded = "";
        $this->crop_sizesD = array();
        $this->crop_sizesM = array();
        $this->w_d = 875; 
        $this->h_d = 585;
        array_push($this->crop_sizesD, array("width" => $this->w_d, "height" => $this->h_d));
        $this->w_m = 371;
        $this->h_m = 568;
        array_push($this->crop_sizesM, array("width" => $this->w_m, "height" => $this->h_m));
    }

    protected function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) exit();

        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        if ($dados->rows[0]['imagem_desktop'] != "") {
            $this->deleteFile($this->table, "imagem_desktop", "id=$id", $this->crop_sizesD);
        }

        if ($dados->rows[0]['imagem_mobile'] != "") {
            $this->deleteFile($this->table, "imagem_mobile", "id=$id", $this->crop_sizesM);
        }

        $this->inserirRelatorio("Apagou banner: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getPath();
    }

    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A ORDER BY A.ordem");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->registro['agendar_entrada_data'] = "";
            $this->registro['agendar_entrada_hora'] = "";
            $this->registro['agendar_saida_data'] = "";
            $this->registro['agendar_saida_hora'] = "";
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();

            $query = $this->DB_fetch_array("SELECT *, DATE_FORMAT(agendar_entrada, '%d/%m/%Y') agendar_entrada_data, DATE_FORMAT(agendar_saida, '%d/%m/%Y') agendar_saida_data, TIME(agendar_entrada) agendar_entrada_hora, TIME(agendar_saida) agendar_saida_hora FROM $this->table WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];
        }

        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    protected function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao_form = $this->validaFormulario($formulario);
        $validacao_upload = $this->validaUpload($formulario);

        //if (!$validacao_form->return || !$validacao_upload->return) {
        if (!$validacao_form->return) {
            echo json_encode($validacao_form);
        } elseif (!$validacao_upload->return) {
            echo json_encode($validacao_upload);
        } else {
            if (!$validacao_form->return) {
                echo json_encode($validacao_form);
            } else {
                $resposta = new \stdClass();

                if (isset($_POST['agendar_entrada_data']) && $_POST['agendar_entrada_data'] != "")
                    $_POST['agendar_entrada'] = $this->formataDataDeMascara($_POST['agendar_entrada_data']) . " " . $_POST['agendar_entrada_hora'];
                else
                    $_POST['agendar_entrada'] = "NULL";

                if (isset($_POST['agendar_saida_data']) && $_POST['agendar_saida_data'] != "")
                    $_POST['agendar_saida'] = $this->formataDataDeMascara($_POST['agendar_saida_data']) . " " . $_POST['agendar_saida_hora'];
                else
                    $_POST['agendar_saida'] = "NULL";

                $data = $this->formularioObjeto($_POST, $this->table);

                if ($this->banner_uploaded_desktop != "") {
                    $data->imagem_desktop = $this->banner_uploaded_desktop;

                    if ($data->id != "")
                        $this->deleteFile($this->table, "imagem_desktop", "id=" . $data->id, $this->crop_sizesD);
                }

                if ($this->banner_uploaded_mobile != "") {
                    $data->imagem_mobile = $this->banner_uploaded_mobile;

                    if ($data->id != "")
                        $this->deleteFile($this->table, "imagem_mobile", "id=" . $data->id, $this->crop_sizesM);
                }

                if ($formulario->id == "") {
                    if (!$this->permissions[$this->permissao_ref]['gravar']) exit();
                    $this->DB_insert($this->table, $data);
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou banner: [" . $data->nome . "]");
                } else {
                    if (!$this->permissions[$this->permissao_ref]['editar']) exit();
                    $this->DB_update($this->table, $data, " WHERE id=" . $data->id);
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou banner: [" . $data->nome . "]");                    
                }
                echo json_encode($resposta);
            }
        }
    }

    protected function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->agendar_entrada_data != "" AND !$this->checkdate($form->agendar_entrada_data)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com uma data válida";
            $resposta->field = "agendar_entrada_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_hora != "" AND !$this->checktime($form->agendar_entrada_hora)) {
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
        } else if ($form->agendar_saida_data != "" AND !$this->checkdate($form->agendar_saida_data)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com uma data válida";
            $resposta->field = "agendar_saida_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_saida_hora != "" AND !$this->checktime($form->agendar_saida_hora)) {
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
        } else if($form->agendar_entrada_data != "" AND $form->agendar_saida_data != "" AND (strtotime($this->datebr($form->agendar_entrada_data)) > strtotime($this->datebr($form->agendar_saida_data)))){
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

        if (is_uploaded_file($_FILES["fileupload_desktop"]["tmp_name"])) {
            $upload_desktop = $this->uploadFile("fileupload_desktop", array("jpg", "jpeg", "gif", "png"), $this->crop_sizesD);
            if ($upload_desktop->return) {
                $this->banner_uploaded_desktop = $upload_desktop->file_uploaded;
            }
        }

        if (is_uploaded_file($_FILES["fileupload_mobile"]["tmp_name"])) {
            $upload_mobile = $this->uploadFile("fileupload_mobile", array("jpg", "jpeg", "gif", "png"), $this->crop_sizesM);
            if ($upload_mobile->return) {
                $this->banner_uploaded_mobile = $upload_mobile->file_uploaded;
            }
        }

        if (!isset($upload_desktop) && !(bool)$form->id) {
            $resposta->type = "attention";
            $resposta->message = "Imagem Desktop não selecionada.";
            $resposta->return = false;
            return $resposta;
        // } else if (!isset($upload_mobile) && !(bool)$form->id) {
        //     $resposta->type = "attention";
        //     $resposta->message = "Imagem Mobile não selecionada.";
        //     $resposta->return = false;
        //     return $resposta;
        // }
        } else {
            return $resposta;
        }
    }

}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__)))))); 
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();