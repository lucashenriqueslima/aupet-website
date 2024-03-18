<?php

namespace cms\module\notificacoes\Model;

use System\Core\Bootstrap;

class Notificacoes extends Bootstrap {

    public $_table = "hbrd_cms_notificacoes";
    public $_table2 = "hbrd_cms_formularios";
    public $permissao_ref = "admin-notificacoes";

    public function __construct() {
        parent::__construct();
        $this->getLevelsAccess();
        
    }

    public function update($data = array()) {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        $idForm = $data['notification']->id;

        try {
            $this->mysqli->autocommit(FALSE);
            $this->mysqli->begin_transaction();

            $delete = $this->DB_sqlDelete($this->_table, "id_form = $idForm");
            if (empty($delete) OR ! $this->mysqli->query($delete))
                throw new \Exception($delete);

            if (isset($_POST['emails'])) {
                foreach ($_POST['emails'] as $email) {
                    $objeto['id_form'] = $idForm;
                    $objeto['id_usuario'] = $email;
                    $insertNotification = $this->DB_sqlInsert($this->_table, $objeto);
                    if (empty($insertNotification) OR ! $this->mysqli->query($insertNotification))
                        throw new \Exception($insertNotification);
                }
            }
            
            $updatetFormMessage = $this->DB_sqlUpdate($this->_table2, array("mensagem" => $data['notification']->mensagem), " WHERE id=" . $idForm);
            if (empty($updatetFormMessage) OR ! $this->mysqli->query($updatetFormMessage))
                throw new \Exception($updatetFormMessage);

            $this->mysqli->commit();

            $this->inserirRelatorio("Alterou notificações formulário id [$idForm]");

            $response->query = $idForm;
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
