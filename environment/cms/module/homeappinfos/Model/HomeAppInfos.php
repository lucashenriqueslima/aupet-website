<?php

namespace cms\module\homeappinfos\Model;

use System\Core\Bootstrap;

class HomeAppInfos extends Bootstrap
{
    public $permissao_ref = "app-infos";
    public $_table = "hbrd_cms_home_app_infos";

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
        $id = $data['home-app-infos']->id;

        try {
            if (isset($data['home-app-infos']->imagem)) {
                $this->deleteFile($this->_table, "imagem", "id=" . $id, $this->crop_sizes);
            }
            $updatetInstitutional = $this->DB_sqlUpdate($this->_table, $data['home-app-infos'], " WHERE id=" . $id);

            if (empty($updatetInstitutional) or !$this->mysqli->query($updatetInstitutional)) {
                throw new \Exception($updatetInstitutional);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou informaçoes referentes a home app id [$id]");
            $response->query = $id;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

}
