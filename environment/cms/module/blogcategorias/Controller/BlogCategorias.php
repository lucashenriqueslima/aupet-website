<?php

use cms\module\blogcategorias\Model\BlogCategorias;
use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase
{
    public $module = "";
    public $permissao_ref = "blog";
    public $table = "hbrd_cms_blog_categorias";
    public $model;

    function __construct()
    {
        parent::__construct();
        $this->module_title = "Blog - Categorias";
        $this->module_icon = "icomoon-icon-tree";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();
        $this->model = new BlogCategorias();
    }

    protected function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");
        $this->model->delete($id);

        echo $this->getPath();
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A ORDER BY A.ordem");

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
            $query = $this->DB_fetch_array("SELECT * FROM {$this->table} WHERE id = {$this->id}", "form");
            $this->registro = $query->rows[0];
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    protected function orderAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    protected function saveAction()
    {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data['blog_categorias'] = $this->formularioObjeto($_POST, $this->table);
            $data['blog_categorias']->seo_url = $this->formataUrlAmiga($formulario->nome);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar']) {
                    exit();
                }
                $ordem = $this->DB_fetch_array("SELECT MAX(ordem) ordem FROM $this->table");
                $data['blog_categorias']->ordem = 1;

                if ($ordem->num_rows) {
                    $data['blog_categorias']->ordem = $ordem->rows[0]['ordem'] + 1;
                }

                if (!$this->validaUrlAmiga($data['blog_categorias']->seo_url, 0, $this->table)) {
                    $data['blog_categorias']->seo_url = $data['blog_categorias']->seo_url . "-" . uniqid();
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

                if (!$this->validaUrlAmiga($data['blog_categorias']->seo_url, $data['blog_categorias']->id, $this->table)) {
                    $data['blog_categorias']->seo_url = $data['blog_categorias']->seo_url . "-" . uniqid();
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
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();
