<?php

namespace desk\module\contatoconfiguracao\Model;

use System\Core\Bootstrap;

class ContatoConfiguracao extends Bootstrap
{
    public $_table_situacao = "hbrd_desk_situacoes";
    // public $_table_grupo = "tb_atendimentos_grupo";
    public $_table_notificacao = "hbrd_cms_notificacoes";
    public $permissao_ref = "contatos";

    public function __construct()
    {
        parent::__construct();
        // $this->dbCMS();
    }

    //CONFIGS
    public function updateConfigs($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $id = $data['config']->id;

        try {
            $delete = $this->DB_sqlDelete($this->_table_notificacao, "id_form = $id");
            if (empty($delete) or !$this->mysqli->query($delete))
                throw new \Exception($delete);
            if (isset($_POST['emails'])) {
                foreach ($_POST['emails'] as $email) {
                    $objeto['id_form'] = $id;
                    $objeto['id_usuario'] = $email;
                    $insertNotification = $this->DB_sqlInsert($this->_table_notificacao, $objeto);
                    if (empty($insertNotification) or !$this->mysqli->query($insertNotification)) {
                        throw new \Exception($insertNotification);
                    }
                }
            }
            $update = $this->DB_sqlUpdate("hbrd_cms_formularios", $data['config'], " WHERE id=" . $id);

            if (empty($update) or !$this->mysqli->query($update)) {
                throw new \Exception($update);
            }
            $this->mysqli->commit();
            $response->query = $id;
        } catch (\Exception $e) {
            var_dump($e);
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
    //----------------------------
    //SITUACAO
    public function deleteSituacao($id)
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $delete = $this->DB_sqlDelete($this->_table_situacao, "id=" . $id);
            if (empty($delete) or !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            $this->mysqli->commit();
            $response->query = true;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function saveSituacao($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $insert = $this->DB_sqlInsert($this->_table_situacao, $data['situation']);
            if (empty($insert) or !$this->mysqli->query($insert)) {
                throw new \Exception($insert);
            }
            $id = $this->mysqli->insert_id;
            $this->mysqli->commit();
            $response->query = $id;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function updateSituacao($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $id = $data['situation']->id;

        try {
            $update = $this->DB_sqlUpdate($this->_table_situacao, $data['situation'], " WHERE id=" . $id);
            if (empty($update) or !$this->mysqli->query($update)) {
                throw new \Exception($update);
            }
            $this->mysqli->commit();
            $response->query = $id;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
    //----------------------------
    //GRUPO
    public function deleteGrupo($id)
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $delete = $this->DB_sqlDelete($this->_table_grupo, "id=" . $id);
            if (empty($delete) or !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            $this->mysqli->commit();
            $response->query = true;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function saveGrupo($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;

        try {
            $insert = $this->DB_sqlInsert($this->_table_grupo, $data['grupo']);
            if (empty($insert) or !$this->mysqli->query($insert)) {
                throw new \Exception($insert);
            }
            $id = $this->mysqli->insert_id;
            if (isset($_POST['emails'])) {
                foreach ($_POST['emails'] as $email) {
                    $objeto['id_formulario'] = $data['grupo']->id_formulario;
                    $objeto['id_usuario'] = $email;
                    $objeto['id_grupo'] = $id;
                    $insertNotification = $this->DB_sqlInsert($this->_table_notificacao, $objeto);
                    if (empty($insertNotification) or !$this->mysqli->query($insertNotification)) {
                        throw new \Exception($insertNotification);
                    }
                }
            }
            $this->mysqli->commit();
            $response->query = $id;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function updateGrupo($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $id = $data['grupo']->id;

        try {
            $update = $this->DB_sqlUpdate($this->_table_grupo, $data['grupo'], " WHERE id=" . $id);
            if (empty($update) or !$this->mysqli->query($update)) {
                throw new \Exception($update);
            }
            $delete = $this->DB_sqlDelete($this->_table_notificacao, "id_formulario = " . $data['grupo']->id_formulario . " AND id_grupo = $id");
            if (empty($delete) or !$this->mysqli->query($delete)) {
                throw new \Exception($delete);
            }
            if (isset($_POST['emails'])) {
                foreach ($_POST['emails'] as $email) {
                    $objeto['id_formulario'] = $data['grupo']->id_formulario;
                    $objeto['id_usuario'] = $email;
                    $objeto['id_grupo'] = $id;
                    $insertNotification = $this->DB_sqlInsert($this->_table_notificacao, $objeto);
                    if (empty($insertNotification) or !$this->mysqli->query($insertNotification)) {
                        throw new \Exception($insertNotification);
                    }
                }
            }
            $this->mysqli->commit();
            $response->query = $id;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }
    //----------------------------
}
