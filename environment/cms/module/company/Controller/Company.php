<?php

use System\Base\SistemaBase;
use adm\classes\{Util};

class __classe__ extends SistemaBase
{
    public $module = "";
    public $permissao_ref = 'company';

    function __construct()
    {
        parent::__construct();
        $this->table = "hbrd_adm_company";
        $this->module_title = "Configurações Gerais";
        $this->module_icon = "fa fa-building";
    }

    public function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        $this->registro = $this->DB_fetch_array("SELECT * FROM $this->table")->rows[0];
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }

    public function saveAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
        $resposta = new \stdClass();

        // if ($_FILES["logo"]['size'] > 0) {
        //     $simpleImage = new SimpleImage3();
        //     $imagem = $simpleImage->fromFile($_FILES["logo"]["tmp_name"])->bestFit(300,300)->toString();
        //     $_POST['logo'] = ($imagem);
        // }

        $this->registro = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.id = 1")->rows[0];

        if ($_FILES["logo"]['size'] > 0) {
            if ($this->registro['logo']) {
                $fotokey = explode('/', $this->registro['logo']);
                $key = $fotokey[3] . '/' . $fotokey[4];

                $this->deleteObjectAWS($key);
            }
            $simpleImage = new SimpleImage3();
            $simpleImage->fromFile($_FILES["logo"]["tmp_name"]);
            $imagem = $simpleImage->bestFit(300, 300)->toString();

            $_POST['logo'] = $this->putObjectAws($imagem);
        }

        if ($_FILES["logo_sistema"]['size'] > 0) {
            if ($this->registro['logo_sistema']) {
                $fotokey = explode('/', $this->registro['logo']);
                $key = $fotokey[3] . '/' . $fotokey[4];
                $this->deleteObjectAWS($key);
            }
            $simpleImage = new SimpleImage3();
            $simpleImage->fromFile($_FILES["logo_sistema"]["tmp_name"]);
            $imagem = $simpleImage->bestFit(300, 300)->toString();

            $_POST['logo_sistema'] = $this->putObjectAws($imagem);
        }
        $data = $this->formularioObjeto($_POST, $this->table);
        $this->DB_update($this->table, $data, " WHERE id=1");
        $this->inserirRelatorio("Alterou dados da empresa");
        $resposta->type = "success";
        $resposta->message = "Registro alterado com sucesso!";

        echo json_encode($resposta);
    }

    public function logoAction()
    {
        $file = $this->DB_fetch_array("SELECT * FROM hbrd_adm_company where id = 1")->rows[0]['logo'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer($file);
        header("Content-type: {$type}");

        echo $file;
    }

    public function logosistemaAction()
    {
        $file = $this->DB_fetch_array("SELECT * FROM $this->table where id = 1")->rows[0]['logo_sistema'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer($file);
        header("Content-type: {$type}");

        echo $file;
    }
}

define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));

$class = new __classe__();
$class->setAction();