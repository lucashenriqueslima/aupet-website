<?php

use cms\module\sobre\Model\Sobre;
use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase
{
    public $module = "";
    public $crop_sizes = array();
    public $sobre_uploaded = "";
    public $permissao_ref = "sobre";
    public $table = "hbrd_cms_sobre";

    function __construct()
    {
        parent::__construct();
        $this->model = new Sobre();
        $this->module_title = "Sobre";
        $this->module_icon = "icomoon-icon-newspaper";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();

        array_push($this->crop_sizes, array("width" => 580, "height" => 660));

        $this->setCropSizes([
            'crop_sizes' => $this->crop_sizes,
        ]);
    }

    public function setCropSizes($array = array())
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    protected function indexAction()
    {
        $this->id = 1;

        if (!$this->permissions[$this->permissao_ref]['editar']) {
            $this->noPermission();
        }
        $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form")->rows[0];
        $this->galeria = $this->DB_fetch_array("SELECT * FROM hbrd_cms_sobre_galeria WHERE id_sobre = {$this->registro['id']} ORDER BY ordem")->rows;

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction()
    {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                $this->noPermission();
            }
            $campos = $this->DB_columns($this->table);

            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id", "form")->rows[0];
            $this->galeria = $this->DB_fetch_array("SELECT * FROM hbrd_cms_sobre_galeria WHERE id_sobre = {$this->registro['id']} ORDER BY ordem")->rows;
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
            $validacao = $this->validaUpload($formulario);

            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {
                $resposta = new \stdClass();
                $data['sobre'] = $this->formularioObjeto($_POST, $this->table);

                if ($this->sobre_uploaded != "") {
                    $data['sobre']->imagem = $this->sobre_uploaded;
                    if ($data['sobre']->id != "") {
                        $this->deleteFile($this->table, "imagem", "id=" . $data['sobre']->id, $this->crop_sizes);
                    }
                }
                if ($formulario->id == "") {
                    //criar
                    $this->noPermission();
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
    }

    protected function validaFormulario($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->texto == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha o campo Texto";
            $resposta->field = "texto";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    protected function validaUpload($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {
            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->sobre_uploaded = $upload->file_uploaded;
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
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();