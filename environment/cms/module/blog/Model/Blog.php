<?php

namespace cms\module\blog\Model;

use System\Core\Bootstrap;

class Blog extends Bootstrap
{
    public $_table = "hbrd_cms_blog";
    public $_category_table = 'hbrd_cms_blog_hbrd_cms_blog_categorias';
    public $permissao_ref = "blog";

    public function __construct()
    {
        parent::__construct();
        $this->getLevelsAccess();

    }

    public function setCropSizes($array = array())
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function delete($id)
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $file = $this->getFileName($this->_table, 'imagem', "id = $id");
            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");
            $delete = $this->DB_sqlDelete($this->_table, "id=" . $id);

            if (empty($delete) || !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            $this->mysqli->commit();
            $this->deleteFile('', '', '', $this->crop_sizes, $file);
            $response->query = true;
            $this->inserirRelatorio("Apagou blog [" . $dados->rows[0]['titulo'] . "] id [$id]");
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
            $insertSEO = $this->DB_sqlInsert($this->_seo_table, $data['SEO']);

            if (empty($insertSEO) || !$this->mysqli->query($insertSEO)) {
                throw new \Exception($insertSEO);
            }
            $idSeo = $this->mysqli->insert_id;
            $data['blog']->id_pagina = $idSeo;
            $insertBlog = $this->DB_sqlInsert($this->_table, $data['blog']);

            if (empty($insertBlog) || !$this->mysqli->query($insertBlog)) {
                throw new \Exception($insertBlog);
            }
            $idBlog = $this->mysqli->insert_id;

            if (isset($_POST['categorias'])) {
                foreach ($_POST['categorias'] as $category) {
                    $objeto['id_blog'] = $idBlog;
                    $objeto['id_categoria'] = $category;
                    $inserts[] = $objeto;
                    $insertCategory = $this->DB_sqlInsert($this->_category_table, $objeto);
                    if (empty($insertCategory) || !$this->mysqli->query($insertCategory)) {
                        throw new \Exception($insertCategory);
                    }
                }
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Cadastrou blog [" . $data['blog']->titulo . "] id [$idBlog]");
            $response->query = $idBlog;
        } catch (\Exception $e) {
            if (isset($data['blog']->imagem) && $data['blog']->imagem != '') {
                $this->deleteFile('', '', '', $this->crop_sizes, $data['blog']->imagem);
            }
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
        $idBlog = $data['blog']->id;

        try {
            $delete = $this->DB_sqlDelete($this->_category_table, "id_blog = $idBlog");

            if (empty($delete) || !$rest = $this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            if (isset($_POST['categorias'])) {
                foreach ($_POST['categorias'] as $category) {
                    $objeto['id_blog'] = $idBlog;
                    $objeto['id_categoria'] = $category;
                    $inserts[] = $objeto;
                    $insertCategory = $this->DB_sqlInsert($this->_category_table, $objeto);
                    if (empty($insertCategory) || !$this->mysqli->query($insertCategory)) {
                        throw new \Exception($insertCategory);
                    }
                }
            }
            $file = $this->getFileName($this->_table, 'imagem', "id = {$data['blog']->id}");
            $updatetSEO = $this->DB_sqlUpdate($this->_seo_table, $data['SEO'], " WHERE id=" . $data['blog']->id_pagina);

            if (empty($updatetSEO) || !$this->mysqli->query($updatetSEO)) {
                throw new \Exception($updatetSEO);
            }
            $updatetBlog = $this->DB_sqlUpdate($this->_table, $data['blog'], " WHERE id=" . $idBlog);

            if (empty($updatetBlog) || !$this->mysqli->query($updatetBlog)) {
                throw new \Exception($updatetBlog);
            }
            if (isset($data['blog']->imagem) && $data['blog']->imagem != '') {
                $this->deleteFile($this->_table, "", "id=" . $data['blog']->id, $this->crop_sizes, $file);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou blog [" . $data['blog']->titulo . "] id [$idBlog]");
            $response->query = $idBlog;
        } catch (\Exception $e) {
            if (isset($data['blog']->imagem) && $data['blog']->imagem != '') {
                $this->deleteFile('', '', '', $this->crop_sizes, $data['blog']->imagem);
            }
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
}
