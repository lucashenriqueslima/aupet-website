<?php
require_once dirname(__DIR__).'/Core/Config.php';
use System\Base\{BadRequest};
class ConnectorBase extends Config {
    public $mysqli;
    public function __construct() {
        parent::__construct();
    }
    public function DB_connect() {
        $this->mysqli = new \mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_database);
        if ($this->mysqli->connect_errno) throw new \Exception($this->mysqli->connect_error);
        $this->mysqli->autocommit(false);
        mysqli_set_charset($this->mysqli, "utf8");
        $this->mysqli->query("SET sql_mode=''");
    }
    public function DB_num_rows($query) {
        $query = $this->mysqli->query($query);
        if (!$query) throw new \Exception($this->mysqli->error);
        if (isset($query) && @$query->num_rows) return $query->num_rows;
    }
    public function DB_insertData($table, $fields, $values) {
        $response = new \stdClass();
        $queryin = "INSERT INTO " . $table . " (" . $fields . ") VALUES(" . $values . ")";
        $response->query = $this->mysqli->query($queryin);
        if (!$response->query) {
            throw new \Exception($this->mysqli->error);
        } else {
            $response->insert_id = $this->mysqli->insert_id;
        }
        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        return $response;
    }
    public function DB_insertWithId($table, $data) {
        if(is_array($data)) unset($data['delete_at']);
        else if(is_object($data)) unset($data->delete_at);
        foreach ($data as $key => $value) {
            $fields[] = $key;
            if ($value === 'NULL' || $value === null) $values[] = "NULL";
            else $values[] = '"'.addslashes($value).'"';
        }
        $sql = "INSERT INTO ".$table." (".implode(',', $fields).") VALUES(".implode(',', $values).")";
        if (!$this->mysqli->query($sql)) throw new \Exception($this->mysqli->error);
        return $this->mysqli->insert_id;
    }
    public function DBInsertOrUpdate($table, $data, $id = 'id') {
        if(is_object($data)) $data = (array)$data;
        unset($data['delete_at']);
        foreach ($data as $key => $value) {
            $fields[] = $key;
            if ($value === 'NULL' || $value === null) $values[] = "NULL";
            else $values[] = '"'.addslashes($value).'"';
        }
        $duplicate = " ON DUPLICATE KEY UPDATE ";
        foreach ($fields as $row) { $duplicate .= "$row=VALUES($row),"; };
        $duplicate = substr_replace($duplicate ,"", -1).';';
        $sql = "INSERT INTO ".$table." (".implode(',', $fields).") VALUES(".implode(',', $values).")". $duplicate;
        if (!$this->mysqli->query($sql)) throw new \Exception($this->mysqli->error);
        return $this->mysqli->insert_id ?: $data[$id];
    }
    public function DBUpdate($table, $data, $where, $params = []) {
        return $this->DB_update($table, $data, $where, $params);
    }
    public function DBInsert($table, $data, $params = []) {
        return $this->DB_insert($table, $data, $params = []);
    }
    public function DB_insert($table, $data, $params = []) {
        $data = (array)$data;
        unset($data['delete_at']);
        unset($data['id']);
        $fields = $values = $object = [];
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $values[] = "?";
            $object[] = $value;
        }
        $sql = "INSERT INTO ".$table." (".implode(',', $fields).") VALUES(".implode(',', $values).")";
        $refValues = array_merge($object, $params);
        $this->DB_exec($sql, $refValues);
        return $this->mysqli->insert_id;
    }
    public function DB_update($table, $data, $where, $params = []) {
        $fields = $object = [];
        $data = (array)$data;
        unset($data['id']);
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $object[] = $value;
        }
        $sql = "UPDATE ".$table." SET  ".implode('=?, ', $fields)."=? $where";
        $refValues = array_merge($object, $params);
        $this->DB_exec($sql, $refValues);
    }
    public function DB_update2($table, $fields_values) {
        $query = $this->mysqli->query("UPDATE " . $table . " SET " . $fields_values);
        if(!(bool)$query) {
            throw new Exception("<br>Error: Query failed: (".$this->mysqli->errno.") ".$this->mysqli->error);
        }
        return $query;
    }
    public function DB_exec($queryin, $params = []) {
        $stmt = $this->mysqli->prepare($queryin);
        if(!(bool)$stmt) throw new \Exception($this->mysqli->error);
        call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
        $stmt->execute();
        if((bool)$stmt->error) throw new \Exception($stmt->error);
        return $stmt;
    }
    public function DBDelete($table, $where, $params = []) {
        $sql = "DELETE FROM ".$table." ".$where;
        $this->DB_exec($sql, $params);
    }
    public function DB_delete($table, $where, $params = []) {
        $sql = "DELETE FROM ".$table." WHERE ".$where;
        $this->DB_exec($sql, $params);
    }
    private function refValues($arr) {
        $types = '';
        foreach ($arr as $a) {
            // i	corresponde a uma variável de tipo inteiro
            // d	corresponde a uma variável de tipo double
            // s	corresponde a uma variável de tipo string
            // b	corresponde a uma variável que contém dados para um blob e enviará em pacotes
            $types .= 's'; 
        }
        array_unshift($arr, $types);
        return $arr;
    }
    #Qualquer valor passado por $_GET ou $_POST usar parametros para evitar sql injection
    public function DB_fetch_array($queryin, $params = []) {
        if(!(bool)$queryin) return;
        $return = new \stdClass();
        $return->query = true;
        $stmt = $this->DB_exec($queryin, $params);
        $result = $stmt->get_result();
        $return->num_rows = $result->num_rows;
        $return->rows = $result->fetch_all(MYSQLI_ASSOC);
        $return->total = (int)$this->mysqli->query("SELECT FOUND_ROWS() count")->fetch_assoc()['count'];
        return $return;
    }
    public function DB_sqlInsert($table, $data) {
        foreach ($data as $key => $value) {
            if ($key != "id") {
                $fields[] = $key;
                if ($value == "NULL")
                    $values[] = "$value";
                else
                    $values[] = '"'.($value).'"';
            }
        }
        $sql = "INSERT INTO " . $table . " (" . implode(',', $fields) . ") VALUES(" . implode(',', $values) . ")";
        if(!$this->mysqli->prepare($sql)) throw new Exception("<br>Error: Query failed: (".$this->mysqli->errno.") ".$this->mysqli->error);
        return $sql;
    }
    public function DB_sqlUpdate($table, $data, $where) {
        foreach ($data as $key => $value) {
            if ($value == "NULL")
                $fields_values[] = "$key=$value";
            else
                $fields_values[] = $key.'='.'\''.($value).'\'';
        }
        $sql = "UPDATE  " . $table . " SET  " . implode(',', $fields_values) . " $where";
        if(!$this->mysqli->prepare($sql)) throw new Exception("<br>Error: Query failed: (".$this->mysqli->errno.") ".$this->mysqli->error);
        return $sql;
    }
    public function DB_sqlDelete($table, $where) {
        if($where != '')
            $sql = "DELETE FROM " . $table . " WHERE " . $where;
        else
            $sql = "DELETE FROM " . $table;
        if(!$this->mysqli->prepare($sql)) throw new Exception("<br>Error: Query failed: (".$this->mysqli->errno.") ".$this->mysqli->error);
        return $sql;
    }
    public function DBUpdateId($table, $data) {
        $id = $data['id'];
        if(!$id) throw new Exception("Error Processing Request", 1);
        $fields = $object = [];
        $data = (array)$data;
        unset($data['id']);
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $object[] = $value;
        }
        $sql = "UPDATE ".$table." SET  ".implode('=?, ', $fields)."=? WHERE id = $id";
        $this->DB_exec($sql, $object);
        return $id;
    }
}