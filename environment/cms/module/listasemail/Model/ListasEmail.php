<?php

namespace cms\module\listasemail\Model;

use System\Core\Bootstrap;

class ListasEmail extends Bootstrap
{
    public $_table = "hbrd_cms_listas";
    public $permissao_ref = "listas-email";

    public function __construct()
    {
        parent::__construct();
    }

    public function delete($id)
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {
            $dados = $this->DB_fetch_array("SELECT * FROM $this->_table WHERE id = $id");
            $delete = $this->DB_sqlDelete($this->_table, "id=" . $id);
            if (empty($delete) or !$this->mysqli->query($delete))
                throw new \Exception($delete);
            $this->mysqli->commit();
            $response->query = true;
            $this->inserirRelatorio("Apagou lista de e-mails [" . $dados->rows[0]['nome'] . "] id [$id]");
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
            $insertEmailList = $this->DB_sqlInsert($this->_table, $data['emaillist']);
            if (empty($insertEmailList) or !$this->mysqli->query($insertEmailList))
                throw new \Exception($insertEmailList);
            $idEmailList = $this->mysqli->insert_id;
            $this->mysqli->commit();
            $this->inserirRelatorio("Cadastrou lista de e-mails [" . $data['emaillist']->nome . "] id [$idEmailList]");
            $response->query = $idEmailList;
        } catch (\Exception $e) {
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
        $idEmailList = $data['emaillist']->id;
        try {
            $updateEmailList = $this->DB_sqlUpdate($this->_table, $data['emaillist'], " WHERE id=" . $idEmailList);
            if (empty($updateEmailList) or !$this->mysqli->query($updateEmailList))
                throw new \Exception($updateEmailList);
            $this->mysqli->commit();
            $this->inserirRelatorio("Alterou lista de e-mails [" . $data['emaillist']->nome . "] id [$idEmailList]");
            $response->query = $idEmailList;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function importEmailByList($data = array())
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {
            $query = $this->DB_fetch_array("SELECT id_email FROM hbrd_cms_email_cms_listas A INNER JOIN hbrd_cms_listas B ON B.id = A.id_lista WHERE A.id_lista = {$data['id_lista_import']}");
            if ($query->num_rows) {
                foreach ($query->rows as $email) {
                    $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email_cms_listas WHERE id_email = {$email['id_email']} AND id_lista = {$data['id_lista']}");
                    if (!$verifica->num_rows) {
                        $insertData [] = "({$data['id_lista']}, {$email['id_email']})";
                    }
                }
                $importEmails = "INSERT INTO hbrd_cms_email_cms_listas (id_lista,id_email) VALUES " . implode(',', $insertData);
            }
            if (empty($importEmails) or !$this->mysqli->query($importEmails))
                throw new \Exception($importEmails);
            $this->mysqli->commit();
            $this->inserirRelatorio("Importou lista [" . $data['id_lista'] . "] para lista [" . $data['id_lista_import'] . "]");
            $response->query = true;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            return $response;
        }
    }

    public function importEmail($formulario, $upload)
    {
        $response = new \stdClass();
        $this->DB_connect();
        $response->query = false;
        try {
            $dir = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR . 'main/uploads/';
            $handle = @fopen($dir . $upload->file_uploaded, "r");
            $flag = true;
            // SE OS CAMPOS DO ARQUIVO CSV FOREM SEPARADOS POR VÍRGULA
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $nascimento = "NULL";
                if (isset($data [0]) && isset($data [1])) {
                    if ($this->validaEmail($data [0]) != 0) {
                        $emailCSV = $data [0];
                        $nomeCSV = $data [1];
                    } else {
                        $nomeCSV = $data [0];
                        $emailCSV = $data [1];
                    }
                    if (isset($data [2]) && $data[2] != "") {
                        if ($this->checkdate($data [2]))
                            $nascimento = $data [2];
                    }
                    if ($nascimento != "" and $nascimento != "NULL") {
                        $nascimento = $this->formataDataDeMascara($nascimento);
                    }
                    if ($this->validaEmail($emailCSV)) {
                        // VERIFICA SE E-MAIL EXISTE, CASO NÃO EXISTA INSERE, CASO EXISTA ATRIBUI SUA ID A $idEmail
                        $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email WHERE email = '$emailCSV'");
                        if (!$verifica->num_rows) {
                            $objetoEmail['nome'] = $nomeCSV;
                            $objetoEmail['email'] = $emailCSV;
                            $objetoEmail['nascimento'] = $nascimento;
                            $insertEmail = $this->DB_sqlInsert('hbrd_cms_email', $objetoEmail);
                            if (empty($insertEmail) or !$this->mysqli->query($insertEmail))
                                throw new \Exception($insertEmail);
                            $idEmail = $this->mysqli->insert_id;
                        } else {
                            $idEmail = $verifica->rows [0] ['id'];
                        }
                        if (isset($formulario->id_lista) and $formulario->id_lista != '') {
                            // VERIFICA SE EMAIL JÁ ESTÁ RELACIONADO A LISTA ATUAL, CASO ESTEJA NÃO FAZ NADA, CASO NÃO ESTEJA RELACIONA
                            $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email_cms_listas WHERE id_email = $idEmail AND id_lista = $formulario->id_lista");
                            if (!$verifica->num_rows) {
                                $objetoLista['id_lista'] = $formulario->id_lista;
                                $objetoLista['id_email'] = $idEmail;
                                $insertEmailLista = $this->DB_sqlInsert('hbrd_cms_email_cms_listas', $objetoLista);
                                if (empty($insertEmailLista) or !$this->mysqli->query($insertEmailLista))
                                    throw new \Exception($insertEmailLista);
                            }
                        }
                        unset($nomeCSV, $emailCSV);
                    }
                } else {
                    // SE FALHAR VERIFICAREMOS SE OS CAMPOS SÃO SEPARADOS POR PONTO E VÍRGULA
                    $flag = false;
                }
            }
            @fclose($handle);
            // SE OS CAMPOS DO ARQUIVO CSV FOREM SEPARADOS POR PONTO E VÍRGULA
            $handle = @fopen($dir . $upload->file_uploaded, "r");
            if ($flag == false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (isset($data [0]) && isset($data [1])) {
                        if ($this->validaEmail($data [0]) != 0) {
                            $emailCSV = $data [0];
                            $nomeCSV = $data [1];
                        } else {
                            $nomeCSV = $data [0];
                            $emailCSV = $data [1];
                        }
                        if (isset($data [2]) && $data[2] != "") {
                            if ($this->checkdate($data [2]))
                                $nascimento = $data [2];
                        }
                        if ($nascimento != "" and $nascimento != "NULL") {
                            $nascimento = $this->formataDataDeMascara($nascimento);
                        }
                        if ($this->validaEmail($emailCSV)) {
                            // VERIFICA SE E-MAIL EXISTE, CASO NÃO EXISTA INSERE, CASO EXISTA ATRIBUI SUA ID A $idEmail
                            $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email WHERE email = '$emailCSV'");
                            if (!$verifica->num_rows) {
                                $objetoEmail['nome'] = $nomeCSV;
                                $objetoEmail['email'] = $emailCSV;
                                $objetoEmail['nascimento'] = $nascimento;
                                $insertEmail = $this->DB_sqlInsert('hbrd_cms_email', $objetoEmail);
                                if (empty($insertEmail) or !$this->mysqli->query($insertEmail))
                                    throw new \Exception($insertEmail);
                                $idEmail = $this->mysqli->insert_id;
                            } else {
                                $idEmail = $verifica->rows [0] ['id'];
                            }
                            if (isset($formulario->id_lista) and $formulario->id_lista != '') {
                                // VERIFICA SE EMAIL JÁ ESTÁ RELACIONADO A LISTA ATUAL, CASO ESTEJA NÃO FAZ NADA, CASO NÃO ESTEJA RELACIONA
                                $verifica = $this->DB_fetch_array("SELECT * FROM hbrd_cms_email_cms_listas WHERE id_email = $idEmail AND id_lista = $formulario->id_lista");
                                if (!$verifica->num_rows) {
                                    $objetoLista['id_lista'] = $formulario->id_lista;
                                    $objetoLista['id_email'] = $idEmail;
                                    $insertEmailLista = $this->DB_sqlInsert('hbrd_cms_email_cms_listas', $objetoLista);
                                    if (empty($insertEmailLista) or !$this->mysqli->query($insertEmailLista))
                                        throw new \Exception($insertEmailLista);
                                }
                            }
                            unset($nomeCSV, $emailCSV);
                        }
                    }
                }
                @fclose($handle);
            }
            $this->mysqli->commit();
            $this->inserirRelatorio("Importou lista de emails do arquivo [" . $_FILES ['csv'] ['name'] . "]");
            $response->query = true;
        } catch (\Exception $e) {
            $this->inserirBug($this->mysqli->error, __service__, __classe__, $e);
            $this->mysqli->rollback();
        } finally {
            @unlink($this->upload_folder . $upload->file_uploaded);
            return $response;
        }
    }
}
