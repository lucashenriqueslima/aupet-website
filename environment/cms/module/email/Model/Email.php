<?php

namespace cms\module\email\Model;

use System\Core\Bootstrap;

class Email extends Bootstrap {

    public $_table = "hbrd_cms_email";
    public $permissao_ref = "lista-emails";

    public function __construct() {
        parent::__construct();
    }

    public function delete($id, $lista) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");

            if ($lista != "undefined" && $lista != "") {
                $delete = $this->DB_sqlDelete('hbrd_cms_email_cms_listas', "id_email = $id AND id_lista = $lista");
                if (empty($delete) OR ! $this->mysqli->query($delete)) {
                    throw new \Exception($delete);
                }
            } else {
                $delete = $this->DB_sqlDelete($this->_table, "id = $id");
                if (empty($delete) OR ! $this->mysqli->query($delete)) {
                    throw new \Exception($delete);
                }
            }
            $this->mysqli->commit();

            if ($lista != "undefined" && $lista != "") {
                $this->inserirRelatorio("Tirou e-mail [" . $dados->rows[0]['email'] . "] id [$id] da lista de id [$lista]");
            } else {
                $this->inserirRelatorio("Apagou e-mail [" . $dados->rows[0]['email'] . "] id [$id]");
            }
            $response->query = true;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function save($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $flag = false;
            $existe = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE email = '{$data['email']->email}'");

            if ($existe->num_rows) {
                $idEmail = $existe->rows[0]['id'];
                $flag = true;
            } else {
                $insertEmail = $this->DB_sqlInsert($this->_table, $data['email']);
                if (empty($insertEmail) OR ! $this->mysqli->query($insertEmail)) {
                    throw new \Exception($insertEmail);
                }
                $idEmail = $this->mysqli->insert_id;
            }

            $relacionou_lista = false;
            if (isset($data['id_lista'])) {
                $existe = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email_cms_listas WHERE id_lista = {$data['id_lista']} AND id_email = $idEmail");
                if ($data['id_lista'] != "" && !$existe->num_rows) {
                    $objetoEmailList['id_lista'] = $data['id_lista'];
                    $objetoEmailList['id_email'] = $idEmail;
                    $insertEmailList = $this->DB_sqlInsert('hbrd_cms_email_cms_listas', $objetoEmailList);
                    if (empty($insertEmailList) OR ! $this->mysqli->query($insertEmailList)) {
                        throw new \Exception($insertEmailList);
                    }
                    $relacionou_lista = true;
                }
            }

            if (isset($data['listas'])) {
                foreach ($data['listas'] as $lista) {
                    $existe = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email_cms_listas WHERE id_lista = $lista AND id_email = $idEmail");
                    if (!$existe->num_rows) {
                        $insertData[] = "($lista, $idEmail)";
                    }
                }
                if (isset($insertData)) {
                    $insertEmailList = "INSERT INTO hbrd_cms_email_cms_listas (id_lista,id_email) VALUES " . implode(',', $insertData);
                    if (empty($insertEmailList) OR ! $this->mysqli->query($insertEmailList)) {
                        throw new \Exception($insertEmailList);
                    }
                }
            }
            $this->mysqli->commit();

            if ($relacionou_lista) {
                $this->inserirRelatorio("Relacionou e-mail: [{$data['email']->email}] à lista de id: [{$data['id_lista']}]");
            }

            if (!$flag) {
                $this->inserirRelatorio("Cadastrou e-mail [" . $data['email']->nome . "] id [$idEmail]");
            }
            $response->query = $idEmail;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            

            return $response;
        }
    }

    public function update($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idEmail = $data['email']->id;

        try {
            $updateEmail = $this->DB_sqlUpdate($this->_table, $data['email'], " WHERE id=" . $idEmail);

            if (empty($updateEmail) OR ! $this->mysqli->query($updateEmail)) {
                throw new \Exception($updateEmail);
            }
            $delete = $this->DB_sqlDelete('hbrd_cms_email_cms_listas', "id_email = $idEmail");

            if (empty($delete) OR ! $this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }

            if (isset($data['listas'])) {
                foreach ($data['listas'] as $lista) {
                    $existe = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email_cms_listas WHERE id_lista = $lista AND id_email = $idEmail");
                    if (!$existe->num_rows) {
                        $insertData[] = "($lista, $idEmail)";
                    }
                }
                if (isset($insertData)) {
                    $insertEmailList = "INSERT INTO hbrd_cms_email_cms_listas (id_lista,id_email) VALUES " . implode(',', $insertData);
                    if (empty($insertEmailList) OR ! $this->mysqli->query($insertEmailList)) {
                        throw new \Exception($insertEmailList);
                    }
                }
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou e-mail [" . $data['email']->nome . "] id [$idEmail]");
            $response->query = $idEmail;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function importEmail($formulario, $upload) {
        $dir = dirname(dirname(dirname(dirname(dirname(__DIR__))))).DIRECTORY_SEPARATOR.'main/uploads/';
        $data = $this->covertCsvArray($dir.$upload->file_uploaded);

        foreach ($data as $row) {
            if(!(bool)$row['email']) continue;
            if(!(bool)$this->validaEmail($row['email'])) continue;
            if ($this->checkdate($row['nascimento'])) $row['nascimento'] = $this->formataDataDeMascara($row['nascimento']);
            else $row['nascimento'] = null;
            if((bool)$this->DB_fetch_array("SELECT * FROM hbrd_cms_email WHERE email = '{$row['email']}'")->rows[0]) continue;
            $this->DBInsert('hbrd_cms_email', $row);
        }
        $this->inserirRelatorio("Importou lista de emails do arquivo [".$_FILES ['csv'] ['name']."]");
    }

    function covertCsvArray($fileUrl) {
        $handle = @fopen($fileUrl, "r");
        $return = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $return[] = ['nome' => trim($data[0]), 'email' => trim($data[1]), 'nascimento' => $data[2]];
        }
        @fclose($handle);
        @unlink($fileUrl);
        return $return;
    }

}
