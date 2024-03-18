<?php

namespace cms\module\paginas\Model;

use System\Core\Bootstrap;

class Paginas extends Bootstrap {
    
    public $_table = "hbrd_cms_paginas";
    public $permissao_ref = "seo";

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


    public function update($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idSEO = $data['SEO']->id;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();
            
            $file = $this->getFileName($this->_table, 'imagem', "id = {$data['SEO']->id}");

            $updateSEO = $this->DB_sqlUpdate($this->_table, $data['SEO'], " WHERE id=" . $idSEO);
            if (empty($updateSEO) OR ! $this->mysqli->query($updateSEO))
                throw new \Exception($updateSEO);

            $this->mysqli->commit();

            $this->inserirRelatorio("Alterou seo página [" . $data['SEO']->seo_title . "] id [$idSEO]");

            $response->query = $idSEO;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            if (isset($this->mysqli))
                $this->mysqli->close();

            return $response;
        }
    }

}
