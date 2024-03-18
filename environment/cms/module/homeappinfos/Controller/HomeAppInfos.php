<?php

use cms\module\homeappinfos\Model\HomeAppInfos;
use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase
{
    public $module = "";
    public $imagem_uploaded = "";
    public $crop_sizes = array();
    public $permissao_ref = "app-infos";
    public $table = "hbrd_cms_home_app_infos";

    function __construct()
    {
        parent::__construct();
        $this->model = new HomeAppInfos();
        $this->module_title = "Home App - Infos";
        $this->module_icon = "icomoon-icon-paragraph-left";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();

        $this->w_imagem = 295;
        $this->h_imagem = 600;
        array_push($this->crop_sizes, array("width" => $this->w_imagem, "height" => $this->h_imagem));

        $this->model->setCropSizes([
            'crop_sizes' => $this->crop_sizes
        ]);
    }

    protected function indexAction()
    {
        $this->id = 1;

        if (!$this->permissions[$this->permissao_ref]['editar']) {
            $this->noPermission();
        }
        $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form")->rows[0];

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function saveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $validacao = $this->validaUpload($_FILES);
            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {
                $resposta = new \stdClass();
                $data['home-app-infos'] = $this->formularioObjeto($_POST, $this->table);

                if ($this->imagem_uploaded != "") {
                    $data['home-app-infos']->imagem = $this->imagem_uploaded;
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

        if ($form->texto_um == "") {
            $resposta->type = "validation";
            $resposta->message = "Preecha o texto";
            $resposta->field = "texto_um";
            $resposta->return = false;
            return $resposta;
        } elseif ($form->texto_dois == "") {
            $resposta->type = "validation";
            $resposta->message = "Preecha o texto";
            $resposta->field = "texto_dois";
            $resposta->return = false;
            return $resposta;
        } elseif ($form->texto_tres == "") {
            $resposta->type = "validation";
            $resposta->message = "Preecha o texto";
            $resposta->field = "texto_tres";
            $resposta->return = false;
            return $resposta;
        } elseif ($form->texto_quatro == "") {
            $resposta->type = "validation";
            $resposta->message = "Preecha o texto";
            $resposta->field = "texto_quatro";
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

        if (is_uploaded_file($_FILES["imagem"]["tmp_name"])) {
            $uploadimagem = $this->uploadFile("imagem", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($uploadimagem->return) {
                $this->imagem_uploaded = $uploadimagem->file_uploaded;
            }
        }
        if (isset($uploadimagem) and !$uploadimagem->return) {
            $resposta->type = "attention";
            $resposta->message = $uploadimagem->message;
            $resposta->field = "imagem_target";
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