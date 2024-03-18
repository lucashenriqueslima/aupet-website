<?php
require_once __DIR__."/Config.php";
class Connector extends Config {
    public $mysqli;
    function __construct() {
        parent::__construct();
        $this->DB_connect();
    }
    public function DB_connect() {
        // $this->mysqli = new \mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_database);
        // mysqli_set_charset($this->mysqli, "utf8");
        // if ($this->mysqli->connect_errno) throw new \Exception("Erro ao conectar ao banco: ". $this->mysqli->connect_error);
        // $this->mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");

        $this->mysqli = new \MySQLi($this->db_host, $this->db_user, $this->db_pwd, $this->db_database);
        mysqli_set_charset($this->mysqli, "utf8");
        if ($this->mysqli->connect_errno) {
            printf("Erro ao conectar ao banco: %s\n", $this->mysqli->connect_error);
            exit();
        } else {
            $this->db_connected = true;
        }
        
    }
    public function DB_fetch_object($queryin, $origem = "echo") {
        $return = new \stdClass();
        if ($query = $this->mysqli->query($queryin)) {
            $return->query = true;
            $return->num_rows = $query->num_rows;
            $i = 0;
            while ($result = $query->fetch_object()) {
                $return->rows[$i] = new \stdClass();
                $return->rows[$i] = $this->trataArray($result, $origem);
                $i++;
            }
        } else throw new \Exception($this->mysqli->error);
        return $return;
    }
    public function DB_fetch_array($queryin, $option = "echo") {
        // $return = new \stdClass();
        // if ($query = $this->mysqli->query($queryin)) {
        //     $return->query = true;
        //     $return->num_rows = $query->num_rows;
        //     $return->total = $this->mysqli->query("SELECT FOUND_ROWS() count")->fetch_assoc()['count'];
        //     $i = 0;
        //     while ($result = $query->fetch_assoc()) {
        //         $return->rows[$i] = $this->trataArray($result, $option);
        //         $i++;
        //     }
        // } else {
        //     throw new \Exception($this->mysqli->error);
        // }
        // return $return;

         //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
         $justonpened = false;
         if (!$this->db_connected)
             $justonpened = true;
         $this->DB_connect();
 
 
         $return = new \stdClass();
 
         if ($query = $this->mysqli->query($queryin)) {
             $return->query = true;
             $return->num_rows = $query->num_rows;
             $i = 0;
             while ($result = $query->fetch_assoc()) {
                 $return->rows[$i] = $this->trataArray($result, $option);
                 $i++;
             }
             $query->close();
         } else {
             $this->LOG_errors("QUERY ERROR:($queryin) ERRO:(" . $this->mysqli->error . ")");
             $return->query = false;
         }
 
 
         //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
         if ($justonpened)
             $this->DB_disconnect();
 
 
 
         return $return;
    }
    public function DB_num_rows($query) {
        // $query = $this->mysqli->query($query);
        // if(!$query) throw new \Exception($this->mysqli->error);
        // if(isset($query) && @$query->num_rows) {
        //     return $query->num_rows;
        // }

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $query = $this->mysqli->query($query);


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();


        return $query->num_rows;
    }
    public function DB_sqlInsert($table, $data) {
        // foreach ($data as $key => $value) {
        //     $value = trim($value);
        //     if ($key != "id") {
        //         $fields[] = $key;
        //         if ($value == "NULL")
        //             $values[] = "$value";
        //         else if($value == '')
        //             $values[] = "NULL";
        //         else
        //             $values[] = "'{$value}'";
        //     }
        // }
        // $sql = "INSERT INTO " . $table . " (" . implode(',', $fields) . ") VALUES(" . implode(',', $values) . ")";
        // $stmt = $this->mysqli->prepare($sql);
        // if (!$stmt) throw new \Exception($this->mysqli->error);
        // return $sql;

        foreach ($data as $key => $value) {
            if ($key != "id") {
                $fields[] = $key;
                if ($value == "NULL")
                    $values[] = "$value";
                else
                    $values[] = "'$value'";
            }
        }

        $sql = "INSERT INTO " . $table . " (" . implode(',', $fields) . ") VALUES(" . implode(',', $values) . ")";

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();

        $stmt = $this->mysqli->prepare($sql);

        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();

        if (!$stmt) {
            return false;
        } else {
            return $sql;
        }
    }

    public function DB_sqlUpdate($table, $data, $where, $extra = null) {
        foreach ($data as $key => $value) {
            if ($value == "NULL") {
                $fields_values[] = "{$key}={$value}";
            } else if ($value === '') {
                $fields_values[] = "{$key}=NULL";
            } else {
                $fields_values[] = "{$key}='{$value}'";
            }
        }
        $sql = "UPDATE  " . $table . " SET  " . implode(',', $fields_values) . " $extra $where";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) throw new \Exception($this->mysqli->error);
        return $sql;
    }

    public function DB_sqlDelete($table, $where) {
        // $sql = "DELETE FROM " . $table . " WHERE " . $where;
        // $stmt = $this->mysqli->prepare($sql);
        // if (!$stmt) throw new \Exception($this->mysqli->error);
        // return $sql;

        if($where != '')
            $sql = "DELETE FROM " . $table . " WHERE " . $where;
        else
            $sql = "DELETE FROM " . $table;

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();

        $stmt = $this->mysqli->prepare($sql);

        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();

        if (!$stmt) {
            return false;
        } else {
            return $sql;
        }
    }

    public function DB_exec($queryin) {
        if (!$this->mysqli->query($queryin)) throw new \Exception($this->mysqli->error);
    }

    public function DB_insert($table, $fields, $values) {
        $response = new \stdClass();
        $queryin = "INSERT INTO " . $table . " (" . $fields . ") VALUES(" . $values . ")";
        $response->query = $this->mysqli->query($queryin);
        if (!$response->query) 
            throw new Exception("<br>Error: Query failed: (".$this->mysqli->errno.") ".$this->mysqli->error);
        $response->insert_id = $this->mysqli->insert_id;
        return $response;
    }

    public function DB_insertData($table, $fields, $values) {
        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();

        $response = new \stdClass();
        $queryin = "INSERT INTO " . $table . " (" . $fields . ") VALUES(" . $values . ")";

        $response->query = $this->mysqli->query($queryin);

        if (!$response->query) {
            throw new \Exception($this->mysqli->error);
        } else {
            $response->insert_id = $this->mysqli->insert_id;
        }
        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();


        return $response;
    }

    public function DB_update($table, $fields_values) {
        // $sql = $this->DB_sqlUpdate($table, $data, $where, $extra);
        // $this->DB_exec($sql);

         //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
         $justonpened = false;
         if (!$this->db_connected)
             $justonpened = true;
         $this->DB_connect();
 
 
         $query = $this->mysqli->query("UPDATE " . $table . " SET " . $fields_values);
 
 
         //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
         if ($justonpened)
             $this->DB_disconnect();
 
 
         return $query;
    }
    public function DB_delete($table, $where) {
        // $sql = $this->DB_sqlDelete($table, $where);
        // $this->DB_exec($sql);

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        if ($where == "")
            $query = $this->mysqli->query("DELETE FROM " . $table);
        else
            $query = $this->mysqli->query("DELETE FROM " . $table . " WHERE " . $where);


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();



        return $query;
    }
    public function DB_disconnect() {
        $this->mysqli->close();
    }

    function DB_anti_injection($value) {

        //VERIFICA SE JÁ ESTÁ CONECTADO OU NÃO
        $justonpened = false;
        if (!$this->db_connected)
            $justonpened = true;
        $this->DB_connect();


        $query = $this->mysqli->real_escape_string($value);

        $query = str_replace(array('\\n', '\\r'), '', $query);


        //VERIFICA SE A CONEXÃO FOI ABERTA AGORA PARA DESCONECTAR
        if ($justonpened)
            $this->DB_disconnect();

        return $query;
    }
}