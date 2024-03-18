<?php

namespace main\module\user\Model;

use System\Core\Bootstrap;

class User extends Bootstrap
{

    public $_table = "hbrd_main_usuarios";
    public $permissao_ref = "user";

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

    public function delete($id)
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {


            $file = $this->getFileName($this->_table, 'avatar', "id = $id");

            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");

            $delete = $this->DB_sqlDelete($this->_table, "id=" . $id);
            if (empty($delete) || !$this->mysqli->query($delete))
                throw new \Exception($delete);

            $this->deleteFile('', '', '', $this->crop_sizes, $file);

            $this->mysqli->commit();

            $response->query = true;

            $this->inserirRelatorio("Apagou usuário [" . $dados->rows[0]['nome'] . "] id [$id]");
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {


            return $response;
        }
    }

    public function save($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $insertUser = $this->DB_sqlInsert($this->_table, $data['user']);

            if (empty($insertUser) || !$this->mysqli->query($insertUser))
                throw new \Exception($insertUser);

            $idUser = $this->mysqli->insert_id;

            if (isset($data['servico']) and $data['servico'] != '') {
                for ($i = 0; $i < count($data['servico']); $i++) {
                    $objetoServico['id_usuario'] = $idUser;
                    $objetoServico['id_servico'] = $data['servico'][$i];
                    $insertServico = $this->DB_sqlInsert('hbrd_main_usuarios_main_servicos', $objetoServico);
                    if (empty($insertServico) || !$this->mysqli->query($insertServico))
                        throw new \Exception($insertServico);
                }
            }

            if (isset($data['empresa']) and $data['empresa'] != '') {
                for ($i = 0; $i < count($data['empresa']); $i++) {
                    $objetoEmpresa['id_usuario'] = $idUser;
                    $objetoEmpresa['id_unidade'] = $data['empresa'][$i];
                    $insertEmpresa = $this->DB_sqlInsert('hbrd_main_usuarios_main_unidades', $objetoEmpresa);
                    if (empty($insertEmpresa) || !$this->mysqli->query($insertEmpresa))
                        throw new \Exception($insertEmpresa);
                }
            }

            if (isset($data['departamento']) and $data['departamento'] != '') {
                for ($i = 0; $i < count($data['departamento']); $i++) {
                    $objetoDepartamento['id_usuario'] = $idUser;
                    $objetoDepartamento['id_departamento'] = $data['departamento'][$i];
                    $insertDepartamento = $this->DB_sqlInsert('hbrd_main_usuarios_main_departamentos', $objetoDepartamento);
                    if (empty($insertDepartamento) || !$this->mysqli->query($insertDepartamento))
                        throw new \Exception($insertDepartamento);
                }
            }

            if (isset($data['cargo']) and $data['cargo'] != '') {
                for ($i = 0; $i < count($data['cargo']); $i++) {
                    $objetoCargo['id_usuario'] = $idUser;
                    $objetoCargo['id_cargo'] = $data['cargo'][$i];
                    $insertCargo = $this->DB_sqlInsert('hbrd_main_usuarios_main_cargos', $objetoCargo);
                    if (empty($insertCargo) || !$this->mysqli->query($insertCargo))
                        throw new \Exception($insertCargo);
                }
            }

            if (isset($data['pi']) && $data['pi'] != "") {
                $insertPermissao = $this->setPermissions($idUser, $data['permissoes']);
                if (empty($insertPermissao) || !$this->mysqli->query($insertPermissao))
                    throw new \Exception($insertPermissao);
            }

            $this->mysqli->commit();

            $this->inserirRelatorio("Cadastrou usuário [" . $data['user']->nome . "] id [$idUser]");

            $response->query = $idUser;
        } catch (\Exception $e) {
            if (isset($data['user']->avatar) and $data['user']->avatar != '')
                $this->deleteFile('', '', '', $this->crop_sizes, $data['user']->avatar);
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {


            return $response;
        }
    }

    public function update($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idUser = $data['user']->id;

        try {
            $file = $this->getFileName($this->_table, 'avatar', "id = {$data['user']->id}");
            $updatetUser = $this->DB_sqlUpdate($this->_table, $data['user'], " WHERE id=" . $idUser);

            if (empty($updatetUser) || !$this->mysqli->query($updatetUser)) {
                throw new \Exception($updatetUser);
            }
            $delete = $this->DB_sqlDelete('hbrd_main_usuarios_main_servicos', "id_usuario=" . $idUser);

            if (empty($delete) || !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            if (isset($data['servico']) and $data['servico'] != '') {
                for ($i = 0; $i < count($data['servico']); $i++) {
                    $objetoServico['id_usuario'] = $idUser;
                    $objetoServico['id_servico'] = $data['servico'][$i];
                    $insertServico = $this->DB_sqlInsert('hbrd_main_usuarios_main_servicos', $objetoServico);
                    if (empty($insertServico) || !$this->mysqli->query($insertServico)) {
                        throw new \Exception($insertServico);
                    }
                }
            }
            // $delete = $this->DB_sqlDelete('hbrd_main_usuarios_main_unidades', "id_usuario=" . $idUser);

            // if (empty($delete) || !$this->mysqli->query($delete)) {
            //     throw new \Exception($delete);
            // }
            // if (isset($data['empresa']) and $data['empresa'] != '') {
            //     for ($i = 0; $i < count($data['empresa']); $i++) {
            //         $objetoEmpresa['id_usuario'] = $idUser;
            //         $objetoEmpresa['id_unidade'] = $data['empresa'][$i];
            //         $insertEmpresa = $this->DB_sqlInsert('hbrd_main_usuarios_main_unidades', $objetoEmpresa);
            //         if (empty($insertEmpresa) || !$this->mysqli->query($insertEmpresa)) {
            //             throw new \Exception($insertEmpresa);
            //         }
            //     }
            // }
            // $delete = $this->DB_sqlDelete('hbrd_main_usuarios_main_departamentos', "id_usuario=" . $idUser);

            // if (empty($delete) || !$this->mysqli->query($delete)) {
            //     throw new \Exception($delete);
            // }
            // if (isset($data['departamento']) and $data['departamento'] != '') {
            //     for ($i = 0; $i < count($data['departamento']); $i++) {
            //         $objetoDepartamento['id_usuario'] = $idUser;
            //         $objetoDepartamento['id_departamento'] = $data['departamento'][$i];
            //         $insertDepartamento = $this->DB_sqlInsert('hbrd_main_usuarios_main_departamentos', $objetoDepartamento);
            //         if (empty($insertDepartamento) || !$this->mysqli->query($insertDepartamento)) {
            //             throw new \Exception($insertDepartamento);
            //         }
            //     }
            // }

            // $delete = $this->DB_sqlDelete('hbrd_main_usuarios_main_cargos', "id_usuario=" . $idUser);
            // if (empty($delete) || !$this->mysqli->query($delete))
            //     throw new \Exception($delete);
            // if (isset($data['cargo']) and $data['cargo'] != '') {
            //     for ($i = 0; $i < count($data['cargo']); $i++) {
            //         $objetoCargo['id_usuario'] = $idUser;
            //         $objetoCargo['id_cargo'] = $data['cargo'][$i];
            //         $insertCargo = $this->DB_sqlInsert('hbrd_main_usuarios_main_cargos', $objetoCargo);
            //         if (empty($insertCargo) || !$this->mysqli->query($insertCargo)) {
            //             throw new \Exception($insertCargo);
            //         }
            //     }
            // }
            $delete = $this->DB_sqlDelete('hbrd_main_usuarios_permissoes', "id_usuario=" . $idUser);

            if (empty($delete) || !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }

            if (isset($data['pi']) && $data['pi'] != "") {
                $insertPermissao = $this->setPermissions($idUser, $data['permissoes']);
                if (empty($insertPermissao) || !$this->mysqli->query($insertPermissao)) {
                    throw new \Exception($insertPermissao);
                }
            }

            if (isset($data['user']->avatar) and $data['user']->avatar != '') {
                $this->deleteFile($this->_table, "", "id=" . $data['user']->id, $this->crop_sizes, $file);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou usuário [" . $data['user']->nome . "] id [$idUser]");
            $response->query = $idUser;
        } catch (\Exception $e) {
            if (isset($data['user']->avatar) and $data['user']->avatar != '') {
                $this->deleteFile('', '', '', $this->crop_sizes, $data['user']->avatar);
            }
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    protected function setPermissions($idUsuario, $post)
    {
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
            $insertData[] = "(" . $idUsuario . "," . $funcao['id'] . ",$ler,$gravar,$editar,$excluir)";
        }

        if (isset($insertData)) {
            return "INSERT INTO hbrd_main_usuarios_permissoes (id_usuario,id_funcao,ler,gravar,editar,excluir) VALUES " . implode(',', $insertData);
        }
    }
}
