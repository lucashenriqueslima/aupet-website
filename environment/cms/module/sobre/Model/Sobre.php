<?php

namespace cms\module\sobre\Model;

use System\Core\Bootstrap;

class Sobre extends Bootstrap
{
    public $_table = "hbrd_cms_sobre";
    public $permissao_ref = "sobre";

    public function __construct()
    {
        parent::__construct();
    }

    public function update($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $id = $data['sobre']->id;

        try {
            $updated = $this->DB_sqlUpdate($this->_table, $data['sobre'], " WHERE id=" . $id);

            if (empty($updated) or !$this->mysqli->query($updated)) {
                throw new \Exception($updated);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou informaçoes referentes a sobre id [$id]");
            $response->query = $id;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
}
