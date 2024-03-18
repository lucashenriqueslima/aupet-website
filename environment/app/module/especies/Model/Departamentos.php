<?php

namespace main\module\departamentos\Model;

use System\Core\Bootstrap;

class Departamentos extends Bootstrap {

    public $_table = "hbrd_main_departamentos";
    public $permissao_ref = "admin-departamentos";

    public function __construct() {
        parent::__construct();
        $this->getLevelsAccess();
    }

    public function delete($id) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();

            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");

            $delete = $this->DB_sqlDelete($this->_table, "id=" . $id);
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);

            $this->mysqli->commit();

            $response->query = true;

            $this->inserirRelatorio("Apagou departamento [" . $dados->rows[0]['nome'] . "] id [$id]");
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

            $insertDepartment = $this->DB_sqlInsert($this->_table, $data['department']);
            if (empty($insertDepartment) OR ! $this->mysqli->query($insertDepartment))
                throw new \Exception($insertDepartment);

            $idDepartment = $this->mysqli->insert_id;

            $this->mysqli->commit();

            $this->inserirRelatorio("Cadastrou departamento [" . $data['department']->nome . "] id [$idDepartment]");

            $response->query = $idDepartment;
        } catch (\Exception $e) {
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
        $idDepartment = $data['department']->id;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();

            $updateDepartment = $this->DB_sqlUpdate($this->_table, $data['department'], " WHERE id=" . $idDepartment);
            if (empty($updateDepartment) OR ! $this->mysqli->query($updateDepartment))
                throw new \Exception($updateDepartment);

            $this->mysqli->commit();

            $this->inserirRelatorio("Alterou departamento [" . $data['department']->nome . "] id [$idDepartment]");

            $response->query = $idDepartment;
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
