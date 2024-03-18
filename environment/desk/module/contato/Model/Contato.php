<?php
namespace desk\module\contato\Model;

use System\Core\Bootstrap;

class Contato extends Bootstrap {
    public $_table = "hbrd_desk_contato";
    public $_table_desc = "hbrd_cms_paginas";
    public $permissao_ref = "contatos";

    public function __construct() {
        parent::__construct();
    }

    public function delete($id) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");
            $delete = $this->DB_sqlDelete($this->_table, "id=" . $id);

            if (empty($delete) OR ! $this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            $this->mysqli->commit();
            $response->query = true;
            $this->inserirRelatorio("Apagou contato: [" . $dados->rows[0]['nome'] . "] id: [$id]");

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
        $idContact = $data['contact']->id;

        try {
            $updateContact = $this->DB_sqlUpdate($this->_table, $data['contact'], " WHERE id=" . $idContact);

            if (empty($updateContact) OR ! $this->mysqli->query($updateContact)) {
                throw new \Exception($updateContact);
            }
            $result = $this->DB_fetch_array("SELECT * FROM hbrd_desk_situacoes WHERE id = {$data['contact']->id_situacao}");
            $data["history"]->id_contato = $idContact;
            $data["history"]->situacao = $result->rows[0]['nome'];
            $data["history"]->usuario = $_SESSION['admin_id'] . " - " . $_SESSION['admin_nome'];
            $insertHistory = $this->DB_sqlInsert('hbrd_desk_contato_situacoes_historico', $data["history"]);
            
            if (empty($insertHistory) OR ! $this->mysqli->query($insertHistory)) {
                throw new \Exception($insertHistory);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou contato: [" . $data['contact']->nome . "], id: [$idContact]");
            $response->query = $idContact;

        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
    
    public function updatePagina($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idPag = 14;

        try {
            $updatetPag = $this->DB_sqlUpdate($this->_seo_table, $data['pagina'], " WHERE id=" . $idPag);

            if (empty($updatetPag) OR ! $this->mysqli->query($updatetPag)) {
                throw new \Exception($updatetPag);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou página contato");
            $response->query = $idPag;

        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function updateDesc($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $id_descricao = $data['descricao']->id;

        try {
            $update_descricao = $this->DB_sqlUpdate($this->_table_desc, $data['descricao'], " WHERE id=" . $id_descricao);

            if (empty($update_descricao) OR ! $this->mysqli->query($update_descricao)) {
                throw new \Exception($update_descricao);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou contato: [" . $data['descricao']->nome . "], id: [$id_descricao]");
            $response->query = $id_descricao;

        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
}
