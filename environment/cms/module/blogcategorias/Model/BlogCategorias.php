<?php

namespace cms\module\blogcategorias\Model;

use System\Core\Bootstrap;

class BlogCategorias extends Bootstrap
{
    public $permissao_ref = "blog";
    public $_table = "hbrd_cms_blog_categorias";

    public function __construct()
    {
        parent::__construct();
    }

    public function delete($id)
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");
            $delete = $this->DB_sqlDelete($this->_table, "id=" . $id);

            if (empty($delete) or !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            $this->mysqli->commit();
            $response->query = true;
            $this->inserirRelatorio("Apagou a categoria de blog [" . $dados->rows[0]['titulo'] . "] id [$id]");
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function save($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $insertCategory = $this->DB_sqlInsert($this->_table, $data['blog_categorias']);

            if (empty($insertCategory) or !$this->mysqli->query($insertCategory)) {
                throw new \Exception($insertCategory);
            }
            $idCategory = $this->mysqli->insert_id;
            $this->mysqli->commit();
            $this->inserirRelatorio("Cadastrou a categoria de blog [" . $data['blog_categorias']->nome . "] id [$idCategory]");
            $response->query = $idCategory;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function update($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idCategory = $data['blog_categorias']->id;

        try {
            $updateCategory = $this->DB_sqlUpdate($this->_table, $data['blog_categorias'], " WHERE id=" . $idCategory);

            if (empty($updateCategory) or !$this->mysqli->query($updateCategory)) {
                throw new \Exception($updateCategory);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou a categoria de blog [" . $data['blog_categorias']->nome . "] id [$idCategory]");
            $response->query = $idCategory;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

}
