<?php
use cms\module\paginas\Model\Paginas as Model;
use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase {

    public $module = "";
    public $crop_sizes = array();
    public $banner_uploaded = "";
    public $permissao_ref = "paginas";
    public $table = "hbrd_cms_paginas";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "main/login");
            exit;
        }
        $this->model = new Model();
        $this->module_title = "SEO / Páginas";
        $this->module_icon = "icomoon-icon-stats-up";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();

        $this->crop_sizes["home"] = [];
        $this->crop_sizes["sobre"] = [];
        $this->crop_sizes["contatos"] = [];
        $this->crop_sizes["rede-credenciada"] = [];
        array_push($this->crop_sizes["home"], array("width" => 875, "height" => 585)); // Home
        array_push($this->crop_sizes["sobre"], array("width" => 620, "height" => 660)); // Sobre
        array_push($this->crop_sizes["contatos"], array("width" => 1920, "height" => 350)); // Blog / Seja Consultor / Contato
        array_push($this->crop_sizes["rede-credenciada"], array("width" => 1920, "height" => 420)); // Rede Credenciada
    }

    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A WHERE A.seo_pagina_dinamica = 0 ORDER BY A.seo_title");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    protected function editAction() {
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
            $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id", "form")->rows[0];
            $current_crop = $this->getCropBySeoUrl($this->registro['seo_url']);

            if(!$this->registro['crop_h']) {
                $this->height = $current_crop[0]['height'];
            } else {
                $this->height = $this->registro['crop_h'];
            }
            if(!$this->registro['crop_w']) {
                $this->width = $current_crop[0]['width'];
            } else {
                $this->width = $this->registro['crop_w'];
            }
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $validacao = $this->validaUpload($_FILES, $this->getCropBySeoUrl($formulario->seo_url));

            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {
                $resposta = new \stdClass();
                $data['SEO'] = $this->formularioObjeto($_POST, $this->table);

                if ($this->banner_uploaded != "") {
                    $data['SEO']->imagem = $this->banner_uploaded;
                    if ($formulario->id != "") {
                        $this->deleteFile($this->table, "imagem", "id=" . $formulario->id, $this->crop_sizes);
                    }
                }

                //UPDATE
                if (!$this->permissions[$this->permissao_ref]['editar']) {
                    exit();
                }
                $data['SEO']->seo_url = $this->formataUrlAmiga($data['SEO']->seo_url);

                if (!$this->validaUrlAmiga($data['SEO']->seo_url, $data['SEO']->id)) {
                    $data['SEO']->seo_url = $data['SEO']->seo_url . "-" . uniqid();
                }
                $current_crop = $this->getCropBySeoUrl($data['SEO']->seo_url);
                $data['SEO']->crop_w = $current_crop[0]['width'];
                $data['SEO']->crop_h = $current_crop[0]['height'];

                $this->getFileName($this->table, 'imagem', "id = {$data['SEO']->id}");
                $this->DBUpdate($this->table, $data['SEO'], " WHERE id=" . $data['SEO']->id);

                $this->inserirRelatorio("Alterou seo página [" . $data['SEO']->seo_title . "] id [{$data['SEO']->id}]");
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";

                echo json_encode($resposta);
            }
        }
    }

    protected function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->seo_title == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "seo_title";
            $resposta->return = false;
            return $resposta;
        } else if ($form->seo_url == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "seo_url";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    protected function validaUpload($form, $crop) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {
            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $crop);
            if ($upload->return) {
                $this->banner_uploaded = $upload->file_uploaded;
            }
        }

        if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    public function getCropBySeoUrl($seo_url) {
        if ($seo_url == 'blog' || $seo_url == 'seja-consultor' || $seo_url == 'contato') {
            $seo_url = 'contatos';
        }
        switch ($seo_url) {
            case "home" :
                $crop = $this->crop_sizes['home'];
                break;
            case "sobre" :
                $crop = $this->crop_sizes['sobre'];
                break;
            case "contatos" :
                $crop = $this->crop_sizes['contatos'];
                break;
            case "rede-credenciada" :
                $crop = $this->crop_sizes['rede-credenciada'];
                break;
            default;
        }
        return $crop;
    }

}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();