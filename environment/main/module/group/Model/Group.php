<?php

namespace main\module\group\Model;

use System\Core\Bootstrap;

class Group extends Bootstrap {

    public $_table = "hbrd_main_grupos";
    public $permissao_ref = "admin-grupos";

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
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);

            $this->mysqli->commit();

            $response->query = true;

            $this->inserirRelatorio("Apagou grupo [" . $dados->rows[0]['nome'] . "] id [$id]");
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
            
            

            $insertGroup = $this->DB_sqlInsert($this->_table, $data['group']);
            if (empty($insertGroup) OR ! $this->mysqli->query($insertGroup))
                throw new \Exception($insertGroup);

            $idGroup = $this->mysqli->insert_id;

            if (isset($data['servico']) AND $data['servico'] != '') {
                for ($i = 0; $i < count($data['servico']); $i++) {
                    $objetoServico['id_grupo'] = $idGroup;
                    $objetoServico['id_servico'] = $data['servico'][$i];
                    $insertServico = $this->DB_sqlInsert('hbrd_main_grupos_main_servicos', $objetoServico);
                    if (empty($insertServico) OR ! $this->mysqli->query($insertServico))
                        throw new \Exception($insertServico);
                }
            }


            if (isset($data['permissoes'])) {
                $insertPermissao = $this->setPermissions($idGroup, $data['permissoes']);
                if (empty($insertPermissao) OR ! $this->mysqli->query($insertPermissao))
                    throw new \Exception($insertPermissao);
            }

            $this->mysqli->commit();

            $this->inserirRelatorio("Cadastrou grupo [" . $data['group']->nome . "] id [$idGroup]");

            $response->query = $idGroup;
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
        $idGroup = $data['group']->id;

        try {
            
            

            $updateGroup = $this->DB_sqlUpdate($this->_table, $data['group'], " WHERE id=" . $idGroup);

            if (empty($updateGroup) OR ! $this->mysqli->query($updateGroup))
                throw new \Exception($updateGroup);

            $delete = $this->DB_sqlDelete('hbrd_main_grupos_main_servicos', "id_grupo=" . $idGroup);
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);
            if (isset($data['servico']) AND $data['servico'] != '') {
                for ($i = 0; $i < count($data['servico']); $i++) {
                    $objetoServico['id_grupo'] = $idGroup;
                    $objetoServico['id_servico'] = $data['servico'][$i];
                    $insertServico = $this->DB_sqlInsert('hbrd_main_grupos_main_servicos', $objetoServico);
                    if (empty($insertServico) OR ! $this->mysqli->query($insertServico))
                        throw new \Exception($insertServico);
                }
            }

            $delete = $this->DB_sqlDelete('hbrd_main_permissoes', "id_grupo=" . $idGroup);
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);

            if (isset($data['permissoes'])) {
                $insertPermissao = $this->setPermissions($idGroup, $data['permissoes']);
                if (empty($insertPermissao) OR ! $this->mysqli->query($insertPermissao))
                    throw new \Exception($insertPermissao);
            }

            $this->mysqli->commit();

            $this->inserirRelatorio("Alterou grupo [" . $data['group']->nome . "] id [$idGroup]");

            $response->query = $idGroup;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            

            return $response;
        }
    }

    protected function setPermissions($idGrupo, $post) {
        $funcoes = $this->DB_fetch_array("SELECT * FROM hbrd_main_funcoes");

        $insertData = array();
        foreach ($funcoes->rows as $funcao) {
            $ler = 0;
            if (isset($post['ler'])) {
                foreach ($post['ler'] as $value) {
                    if ($value == $funcao['id']) {
                        $ler = 1;
                    }
                }
            }
            $gravar = 0;
            if (isset($post['gravar'])) {
                foreach ($post['gravar'] as $value) {
                    if ($value == $funcao['id']) {
                        $gravar = 1;
                    }
                }
            }
            $editar = 0;
            if (isset($post['editar'])) {
                foreach ($post['editar'] as $value) {
                    if ($value == $funcao['id']) {
                        $editar = 1;
                    }
                }
            }
            $excluir = 0;
            if (isset($post['excluir'])) {
                foreach ($post['excluir'] as $value) {
                    if ($value == $funcao['id']) {
                        $excluir = 1;
                    }
                }
            }

            $insertData[] = "(" . $idGrupo . "," . $funcao['id'] . ",$ler,$gravar,$editar,$excluir)";
        }

        if (isset($insertData))
            return "INSERT INTO hbrd_main_permissoes (id_grupo,id_funcao,ler,gravar,editar,excluir) VALUES " . implode(',', $insertData);
    }

}
