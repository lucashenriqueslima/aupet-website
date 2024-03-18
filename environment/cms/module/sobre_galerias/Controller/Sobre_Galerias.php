<?php

use System\Base\{SistemaBase, BadRequest};
use cms\classes\{Util};

class __classe__ extends SistemaBase
{
    public function uploadFoto($data)
    {
        $simpleImage = new SimpleImage3();
        if ($_FILES["file"]["type"] == 'image/svg+xml') {
            $imagem = file_get_contents($_FILES["file"]["tmp_name"]);
        } else {
            $simpleImage->fromFile($_FILES["file"]["tmp_name"]);
            $imagem = $simpleImage->bestFit(300,300)->toString();
        }
        $url = $this->putObjectAws($imagem);
        return $url;
    }

    public function deleteFoto($key)
    {
        $this->deleteObjectAWS($key);
    }

    public $module = "";
    public $crop_fotos = array();
    public $permissao_ref = "sobre";
    public $table = "hbrd_cms_sobre_galeria";
    public $table2 = "hbrd_cms_sobre_galeria_fotos";

    function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "main/login");
        }

        $this->module_icon = "icomoon-icon-images";
        $this->module_link = "gallery";
        $this->module_title = "Galeria";
        $this->retorno = $this->getPath();
        $this->galeria_uploaded = "";

        array_push($this->crop_fotos, array("width" => 75, "height" => 75));
    }

    protected function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
    }

    protected function editAction()
    {
        if (!$this->getParameter('id_sobre')) {
            echo '<META http-equiv="refresh" content="0;URL=' . $this->system_path . '"> ';
        } else {
            $this->id = $this->getParameter("id");
            $this->id_sobre = $this->getParameter("id_sobre");
            $query = $this->DB_fetch_array("SELECT * FROM hbrd_cms_sobre WHERE id = {$this->getParameter('id_sobre')}");

            if ($query->num_rows)
                $this->produto = $query->rows[0];

            $this->module_icon = "silk-icon-office";
            $this->module_link = "cms/sobre";
            $this->module_title = "Sobre - Galeria";

            if ($this->id == "") {
                //NOVO CADASTRO DE GALERIA
                if (!$this->permissions[$this->permissao_ref]['gravar']) {
                    $this->noPermission();
                }
                $campos = $this->DB_columns($this->table);

                foreach ($campos as $campo) {
                    $this->registro[$campo] = "";
                }
            } else {
                //EDITAR GALERIA
                if (!$this->permissions[$this->permissao_ref]['editar']) {
                    $this->noPermission();
                }
                $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id AND id_sobre = $this->id_sobre")->rows[0];
                $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_galeria = $this->id", "form");
            }
            $this->renderView($this->getService(), $this->getModule(), "edit");
        }
    }

    protected function delAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id")->rows[0];
        $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_galeria = $id");

        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                $this->deleteFile($this->table2, "url", "id=" . $foto['id'], $this->crop_fotos);
            }
        }

        $this->inserirRelatorio("Apagou galeria: [" . $dados['titulo'] . "] id: [$id] do sobre id: [" . $dados['id_sobre'] . "]");

        $this->DB_delete($this->table2, "id_galeria=$id");
        $this->DB_delete($this->table, "id=$id");

        $this->getParameter("lista");

        echo "cms/sobre";
    }

    protected function saveAction()
    {

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {

            $resposta = new \stdClass();

            $data = $this->formularioObjeto($_POST, $this->table);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();


                $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM $this->table");
                if ($ordem->num_rows)
                    $data->ordem = $ordem->rows[0]['ordem'] + 1;
                else
                    $data->ordem = 1;
                $data->stats = 1;
                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }

                unset($fields[0]);
                unset($values[0]);

                $query = $this->DB_insert($this->table, $fields, $values);

                if ($query->query) {
                    if (isset($formulario->ongs) && $formulario->ongs !== "") {
                        $resposta->retorno = "cms/sobre_galeria/edit/id/" . $query->insert_id . "/id_sobre/" . $data->id_sobre . "/ongs/" . $data->id_sobre;
                    } else {
                        $resposta->retorno = "cms/sobre_galeria/edit/id/" . $query->insert_id . "/id_sobre/" . $data->id_sobre;
                    }
                    // $resposta->retorno = $this->getPath()."/edit/id/" . $query->insert_id . "/id_sobre/" . $data->id_sobre;
                    $resposta->time = 5000;
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso. Por favor, envie as fotos para a galeria!";
                    $this->inserirRelatorio("Cadastrou galeria: [" . $data->titulo . "] do sobre id: [$data->id_sobre]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                foreach ($data as $key => $value) {
                    if ($value == "NULL")
                        $fields_values[] = "$key=$value";
                    else
                        $fields_values[] = "$key='$value'";
                }

                $query = $this->DB_update2($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                if ($query) {
                    $resposta->retorno = "cms/sobre_galerias/edit/id/" . $formulario->id . "/id_sobre/" . $data->id_sobre;
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou galeria: [" . $data->titulo . "] do sobre id: [$data->id_sobre]");
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

        if ($form->titulo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "titulo";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    protected function fotosAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->id = $this->getParameter("id");

        if ($this->getParameter("ongs")) {
            $this->table = "hbrd_cms_sobre_galeria_ong";
            $this->table2 = "hbrd_cms_sobre_galeria_fotos_ong";
        } else {
            $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_galeria = $this->id order by ordem");
        }

        $this->renderAjax($this->getService(), $this->getModule(), "fotos");
    }

    protected function fotoAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");
        $this->id_galeria = $this->getParameter("id_galeria");

        $foto = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id AND id_galeria = $this->id_galeria");
        $this->foto = $foto->rows[0];

        $this->renderAjax($this->getService(), $this->getModule(), "foto");
    }

    protected function uploadAction()
    {
        $this->id = $this->getParameter("id");

        if ($this->getParameter("ongs")) {
            $this->table = "hbrd_cms_sobre_galeria_ong";
            $this->table2 = "hbrd_cms_sobre_galeria_fotos_ong";
        } else {
            $fields = array('id_galeria', 'ordem', 'url');
        }

        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $type = explode('.', $_FILES['file']['name']);
            $key = $this->uploadFoto($_POST);

            if ($key) {
                $dados = $this->DB_fetch_array("SELECT id, MAX(ordem) as ordem FROM $this->table2 WHERE id_galeria=" . $this->id . " GROUP BY id_galeria");

                if ($dados->num_rows == 0) {
                    $ordem = 1;
                } else {
                    $ordem = $dados->rows[0]['ordem'];
                    $ordem++;
                }
                $values = array($this->id, "'" . $ordem . "'", "'" . $key . "'");
                $this->DB_insertData($this->table2, implode(',', $fields), implode(',', $values));
                
                echo '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';
            }
        }
    }

    protected function uploadDelAction()
    {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            $this->noPermission(false);
        }
        $this->id = $this->getParameter("id");
        $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id");

        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                $fotokey = explode('/', $foto['url']);
                $key = $fotokey[3] . '/' . $fotokey[4];
                $this->deleteFoto($key);
            }
        }
        $this->inserirRelatorio("Apagou imagem sobre legenda: [" . $fotos->rows[0]['legenda'] . "] id: [$this->id]");
        $this->DB_delete($this->table2, "id=$this->id");
    }

    protected function orderAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            $this->noPermission(false);
        }
        $this->ordenarRegistros($_POST["array"], $this->table2);
    }

    protected function orderGalleryAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            $this->noPermission(false);
        }
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    protected function editPhotoAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            $this->noPermission(false);
        }
        $id = $_POST['id'];
        $legenda = $_POST['legenda'];

        $fields_values = array(
            "legenda='" . $legenda . "'"
        );
        $query = $this->DB_update2($this->table2, implode(',', $fields_values) . " WHERE id=" . $id);
        $resposta = new stdClass();

        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Registro alterado com sucesso!";
            $this->inserirRelatorio("Editou imagem sobre legenda: [" . $legenda . "] id: [" . $id . "]");
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