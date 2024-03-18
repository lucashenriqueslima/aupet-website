<?php

namespace main\module\smtp\Model;

use System\Core\Bootstrap;

class SMTP extends Bootstrap {

    public $permissao_ref = __classe__;

    public function __construct() {
        parent::__construct();
        $this->getLevelsAccess();
        $this->_table = "hbrd_".__service__."_".__classe__;
    }

    public function update($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idSmtp = $data['smtp']->id;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();

            $updatetSMTP = $this->DB_sqlUpdate($this->_table, $data['smtp'], " WHERE id=" . $idSmtp);

            if (empty($updatetSMTP) OR ! $this->mysqli->query($updatetSMTP))
                throw new \Exception($updatetSMTP);

            $this->mysqli->commit();

            $this->inserirRelatorio("Alterou SMTP [" . $data['smtp']->email_user . "] id [$idSmtp]", __service__);

            $response->query = $idSmtp;
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
