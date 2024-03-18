<?php

use cms\module\blog\Model\Blog;
use System\Base\{SistemaBase, BadRequest};

class __classe__ extends SistemaBase
{
    public $module = "";
    public $blog_uploaded = "";
    public $crop_sizes = array();
    public $permissao_ref = "blog";
    public $table = "hbrd_cms_blog";
    public $table2 = "hbrd_cms_blog_galerias_fotos";

    function __construct()
    {
        parent::__construct();
        $this->model = new Blog();
        $this->module_title = "Blog";
        $this->module_icon = "icomoon-icon-newspaper";
        $this->module_link = __classe__;
        $this->retorno = $this->getPath();

        array_push($this->crop_sizes, array("width" => 555, "height" => 295));

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

    public function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");
        $this->model->delete($id);

        echo $this->getPath();
    }

    public function indexAction()
    {
        $this->list = $this->DB_fetch_array(" SELECT A.*, C.nome AS nome_categoria FROM $this->table A LEFT JOIN hbrd_cms_blog_categorias C ON C.id = A.id_categoria GROUP BY A.id ORDER BY A.data, A.id DESC");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }

    public function editAction()
    {
        $this->id = $this->getParameter("id");
        $this->seo['seo_title'] = "";
        $this->seo['seo_description'] = "";
        $this->seo['seo_keywords'] = "";
        $this->seo['seo_url'] = "";
        $this->seo['seo_scripts'] = "";

        if ($this->id == "") {
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                $this->noPermission();
            }
            $campos = $this->DB_columns($this->table);

            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
            $this->registro['agendar_entrada_data'] = "";
            $this->registro['agendar_entrada_hora'] = "";
            $this->registro['agendar_saida_data'] = "";
            $this->registro['agendar_saida_hora'] = "";
            $this->categorias = $this->DB_fetch_array(" SELECT * FROM hbrd_cms_blog_categorias A LEFT JOIN (SELECT * FROM hbrd_cms_blog_hbrd_cms_blog_categorias WHERE id_blog IS NULL) B ON A.id = B.id_categoria WHERE A.stats = 1", "form")->rows;
        } else {
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }
            $this->registro = $this->DB_fetch_array(" SELECT *, DATE_FORMAT(agendar_entrada, '%d/%m/%Y') agendar_entrada_data, DATE_FORMAT(agendar_saida, '%d/%m/%Y') agendar_saida_data, TIME(agendar_entrada) agendar_entrada_hora, TIME(agendar_saida) agendar_saida_hora FROM $this->table WHERE id = $this->id", "form")->rows[0];
            $this->registro['data'] = $this->formataDataDeBanco($this->registro['data']);
            $this->galeria = $this->DB_fetch_array("SELECT * FROM hbrd_cms_blog_galerias WHERE id_blog = {$this->registro['id']} ORDER BY ordem")->rows;

            if ($this->registro['id_pagina']) {
                $this->seo = $this->DB_fetch_array("SELECT * FROM hbrd_cms_paginas WHERE id = {$this->registro['id_pagina']}")->rows[0];
            }
            $this->categorias = $this->DB_fetch_array(" SELECT * FROM hbrd_cms_blog_categorias A LEFT JOIN hbrd_cms_blog_hbrd_cms_blog_categorias B ON B.id_categoria = A.id AND B.id_blog = {$this->registro['id']} WHERE A.stats = 1 ORDER BY A.ordem", "form")->rows;
        }
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    public function orderAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $this->ordenarRegistros($_POST["array"], $this->table2);
    }

    public function saveAction()
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
                $data['SEO'] = $this->formularioObjeto($_POST, $this->_seo_table);
                $data['SEO']->seo_url = $this->formataUrlAmiga($data['SEO']->seo_url);
                $data['SEO']->seo_pagina = "blog-interno";
                $data['SEO']->seo_url_breadcrumbs = "blog-interno/";
                $data['SEO']->seo_description = $formulario->descricao;
                $data['SEO']->seo_pagina_dinamica = 1;

                if ($data['SEO']->seo_url == '') {
                    $data['SEO']->seo_url = $this->formataUrlAmiga($formulario->titulo);
                }
                if ($formulario->seo_title == '') {
                    $data['SEO']->seo_title = $formulario->titulo;
                }
                unset($data['SEO']->id);

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
                $_POST['data'] = $this->formataDataDeMascara($_POST['data']);
                $data['blog'] = $this->formularioObjeto($_POST, $this->table);

                if ($this->blog_uploaded != "") {
                    $data['blog']->imagem = $this->blog_uploaded;
                    if ($data['blog']->id != "") {
                        $this->deleteFile($this->table, "imagem", "id=" . $data['blog']->id, $this->crop_sizes);
                    }
                }
                if ($formulario->id == "") {
                    //criar
                    if (!$this->permissions[$this->permissao_ref]['gravar']) {
                        exit();
                    }
                    $ordem = $this->DB_fetch_array("SELECT MAX(ordem) ordem FROM $this->table");
                    $data['blog']->ordem = 1;

                    if ($ordem->num_rows) {
                        $data['blog']->ordem = $ordem->rows[0]['ordem'] + 1;
                    }

                    if (!$this->validaUrlAmiga($data['SEO']->seo_url)) {
                        $data['SEO']->seo_url = $data['SEO']->seo_url . "-" . uniqid();
                    }
                    $query = $this->model->save($data);

                    if ($query->query) {
                        $resposta->type = "success";
                        $resposta->message = "Registro cadastrado com sucesso!";
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                        $resposta->sabedoria = "foi aqui em cima mano";
                    }
                } else {
                    //alterar
                    if (!$this->permissions[$this->permissao_ref]['editar']) {
                        exit();
                    }

                    if (!$this->validaUrlAmiga($data['SEO']->seo_url, $data['blog']->id_pagina)) {
                        $data['SEO']->seo_url = $data['SEO']->seo_url . "-" . uniqid();
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

    public function validaFormulario($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->data == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha o campo Data";
            $resposta->field = "data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->titulo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha o campo Titulo";
            $resposta->field = "titulo";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_data != "" && !$this->checkdate($form->agendar_entrada_data)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com uma data válida";
            $resposta->field = "agendar_entrada_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_hora != "" && !$this->checktime($form->agendar_entrada_hora)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com um horário válido";
            $resposta->field = "agendar_entrada_hora";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_hora != "" && $form->agendar_entrada_data == "") {
            $resposta->type = "validation";
            $resposta->message = "Informe a data para este horário";
            $resposta->field = "agendar_entrada_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_saida_data != "" && !$this->checkdate($form->agendar_saida_data)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com uma data válida";
            $resposta->field = "agendar_saida_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_saida_hora != "" && !$this->checktime($form->agendar_saida_hora)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com um horário válido";
            $resposta->field = "agendar_saida_hora";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_saida_hora != "" && $form->agendar_saida_data == "") {
            $resposta->type = "validation";
            $resposta->message = "Informe a data para este horário";
            $resposta->field = "agendar_saida_data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->agendar_entrada_data != "" && $form->agendar_saida_data != "" && (strtotime($this->datebr($form->agendar_entrada_data)) > strtotime($this->datebr($form->agendar_saida_data)))) {
            $resposta->type = "validation";
            $resposta->message = "Data de entrada maior que a de saída";
            $resposta->field = "agendar_entrada_data";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    public function validaUpload($form)
    {
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

    /* FUNÇÕES DE MANIPULAÇÂO DE GALERIA DE IMAGENS */

    public function fotosAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->id = $this->getParameter("id");

        $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_galeria = $this->id order by ordem");

        $this->renderAjax($this->getService(), $this->getModule(), "fotos");
    }

    public function fotoAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");

        $foto = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id");
        $this->foto = $foto->rows[0];

        $this->renderAjax($this->getService(), $this->getModule(), "foto");
    }

    public function uploadAction() {
        $this->id = $this->getParameter("id");

        $fields = array('id_galeria', 'ordem', 'url');

        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $type = explode('.',$_FILES['file']['name']);

            $simpleImage = new SimpleImage3();
            $simpleImage->fromFile($_FILES["file"]["tmp_name"]);
            $imagem = $simpleImage->bestFit(300,300)->toString();
            
            $key = $this->putObjectAws($imagem);

            if ($key) {
                $file_uploaded = $upload->file_uploaded;

                $dados = $this->DB_fetch_array("SELECT id, MAX(ordem) as ordem FROM $this->table2 WHERE id_galeria=" . $this->id . " GROUP BY id_galeria");
                if ($dados->num_rows == 0) {
                    $ordem = 1;
                } else {
                    $ordem = $dados->rows[0]['ordem'];
                    $ordem++;
                }

                $values = array($this->id, "'" . $ordem . "'", "'" . $key . "'");

                $query = $this->DB_insertData($this->table2, implode(',', $fields), implode(',', $values));
                
                echo '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';
            }
        }
    }

    public function uploadDelAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");

        $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id");
        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                $fotokey = explode('/', $foto['url']);
                $key = $fotokey[3] . '/' . $fotokey[4]; 
                $this->deleteObjectAWS($key);
            }
        }

        $this->inserirRelatorio("Apagou imagem instiucional legenda: [" . $fotos->rows[0]['legenda'] . "] id: [$this->id]");
        $this->DB_delete($this->table2, "id=$this->id");
    }

    public function editPhotoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $id = $_POST['id'];
        $legenda = $_POST['legenda'];

        $fields_values['legenda'] = $legenda;

        $query = $this->DB_sqlUpdate($this->table2, $fields_values, " WHERE id=" . $id);

        $resposta = new stdClass();

        if ($this->mysqli->query($query)) {
            $resposta->type = "success";
            $this->inserirRelatorio("Editou imagem institucional legenda: [" . $legenda . "] id: [" . $id . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();