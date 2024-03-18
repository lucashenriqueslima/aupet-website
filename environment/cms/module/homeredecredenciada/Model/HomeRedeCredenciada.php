<?php

namespace cms\module\homeredecredenciada\Model;

use System\Core\Bootstrap;

class HomeRedeCredenciada extends Bootstrap
{
    public $permissao_ref = "rede-credenciada";
    public $_table = "hbrd_cms_home_rede_credenciada";

    public function __construct()
    {
        parent::__construct();
    }

    public function setCropSizes($array = array())
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function update($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $id = $data['home-rede-credenciada']->id;

        try {
            if (isset($data['home-rede-credenciada']->imagem)) {
                $this->deleteFile($this->_table, "imagem", "id=" . $id, $this->crop_sizes);
            }
            $updated = $this->DB_sqlUpdate($this->_table, $data['home-rede-credenciada'], " WHERE id=" . $id);

            if (empty($updated) or !$this->mysqli->query($updated)) {
                throw new \Exception($updated);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou informaçoes referentes a home rede credenciada id [$id]");
            $response->query = $id;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
}
