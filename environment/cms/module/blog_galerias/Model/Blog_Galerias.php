<?php

namespace cms\module\gallery\Model;

use System\Core\Bootstrap;

class Gallery extends Bootstrap {

    public $permissao_ref = "blog-galerias";
    public $_table = "hbrd_cms_empreendimentos_galerias";
    public $_table2 = "hbrd_cms_empreendimentos_galerias_fotos";

    public function __construct() {
        parent::__construct();
        $this->getLevelsAccess();
        
    }

    public function setCropSizes($array = array()) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function delete($id) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {
            $this->mysqli->begin_transaction();
            $this->mysqli->autocommit(FALSE);

            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");

            $imagemGaleria = $this->getFileName($this->_table, 'imagem', "id = $id");

            $fotos = array();
            $query = $this->DB_fetch_array("SELECT * FROM $this->_table2 WHERE id_galeria = $id");
            if ($query->num_rows) {
                foreach ($query->rows as $foto) {
                    $fotos[] = $this->getFileName($this->_table2, 'url', "id = {$foto['id']}");
                }
            }

            $delete = $this->DB_sqlDelete($this->_table2, "id_galeria=" . $id);
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);

            $delete = $this->DB_sqlDelete($this->_table, "id=" . $id);
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);

            $this->mysqli->commit();

            $this->deleteFile('', '', '', $this->crop_galeria, $imagemGaleria);

            if (count($fotos) > 0) {
                foreach ($fotos as $foto) {
                    $this->deleteFile('', '', '', $this->crop_fotos, $foto);
                }
            }

            $response->query = true;

            $this->inserirRelatorio("Apagou galeria [" . $dados->rows[0]['titulo'] . "] id [$id]");
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli))
                $this->mysqli->close();

            return $response;
        }
    }

    public function save($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();

            $insertGallery = $this->DB_sqlInsert($this->_table, $data['gallery']);
            if (empty($insertGallery) OR ! $this->mysqli->query($insertGallery))
                throw new \Exception($insertGallery);

            $idGaleria = $this->mysqli->insert_id;

            $this->mysqli->commit();

            $this->inserirRelatorio("Cadastrou galeria [" . $data['gallery']->titulo . "] id [$idGaleria]");

            $response->query = $idGaleria;
        } catch (\Exception $e) {
            if (isset($data['gallery']->imagem) AND $data['gallery']->imagem != '')
                $this->deleteFile('', '', '', $this->crop_galeria, $data['gallery']->imagem);
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli))
                $this->mysqli->close();

            return $response;
        }
    }

    public function update($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idGaleria = $data['gallery']->id;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();

            $file = $this->getFileName($this->_table, 'imagem', "id = {$data['gallery']->id}");

            $updatetGallery = $this->DB_sqlUpdate($this->_table, $data['gallery'], " WHERE id=" . $idGaleria);
            if (empty($updatetGallery) OR ! $this->mysqli->query($updatetGallery))
                throw new \Exception($updatetGallery);

            if (isset($data['gallery']->imagem) AND $data['gallery']->imagem != '')
                $this->deleteFile($this->_table, "", "id=" . $data['gallery']->id, $this->crop_sizes, $file);

            $this->mysqli->commit();

            $this->inserirRelatorio("Alterou galeria [" . $data['gallery']->titulo . "] id [$idGaleria]");

            $response->query = $idGaleria;
        } catch (\Exception $e) {
            if (isset($data['gallery']->imagem) AND $data['gallery']->imagem != '')
                $this->deleteFile('', '', '', $this->crop_sizes, $data['gallery']->imagem);
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli))
                $this->mysqli->close();

            return $response;
        }
    }

}
