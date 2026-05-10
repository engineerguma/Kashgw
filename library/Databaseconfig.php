<?php

class Databaseconfig extends PDO
{
    private static $instance = null;

    private function __construct()
    {
        $dsn = DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        parent::__construct($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false
        ]);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton database connection");
    }

    public function InsertData($table, $data, $return_id = false)
    {
        ksort($data);

        $fieldlog = '';
        $fieldNames = implode(', ', array_keys($data));
        $fieldInputs = ':' . implode(', :', array_keys($data));

        $sql_statement = "INSERT INTO $table
                    ($fieldNames)
            VALUES  ($fieldInputs)";

        $sth = $this->prepare($sql_statement);

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
            $fieldlog .= "$key = $value,";
        }

        $sth->execute();

        if ($return_id !== false) {
            return $this->lastInsertId();
        }

        $now = date('Y-m-d H:i:s');

        if (isset($_SESSION['uid'])) {
            $user_id = $_SESSION['uid'];
        } else {
            $user_id = 0;
        }

        $logdata = array(
            'log_time' => $now,
            'table_name' => $table,
            'query_executed' => $sql_statement,
            'data_set' => $fieldlog,
            'user_id' => $user_id
        );

        // $this->DBOperationLog($logdata);

        return $sth;
    }

    public function UpdateData($table, $data, $where)
    {
        ksort($data);

        $fieldlog = '';
        $fieldDetails = '';

        foreach ($data as $key => $value) {
            $fieldDetails .= "$key = :$key,";
            $fieldlog .= "$key = $value,";
        }

        $fieldDetails = rtrim($fieldDetails, ',');

        $sql_statement = "UPDATE $table SET $fieldDetails WHERE $where";

        $sth = $this->prepare($sql_statement);

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        $sth->execute();

        $now = date('Y-m-d H:i:s');

        if (isset($_SESSION['uid'])) {
            $user_id = $_SESSION['uid'];
        } else {
            $user_id = 0;
        }

        $logdata = array(
            'log_time' => $now,
            'table_name' => $table,
            'query_executed' => $sql_statement,
            'data_set' => $fieldlog,
            'user_id' => $user_id
        );

        // $this->DBOperationLog($logdata);

        return $sth;
    }

    public function SelectData($sql, $data = array(), $fetchMode = PDO::FETCH_ASSOC)
    {
        $sth = $this->prepare($sql);

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        $sth->execute();

        return $sth->fetchAll($fetchMode);
    }

    public function DeleteData($table, $where, $limit = 1)
    {
        $limit = (int) $limit;

        $sql_statement = "DELETE FROM $table WHERE $where LIMIT $limit";

        $result = $this->exec($sql_statement);

        $now = date('Y-m-d H:i:s');

        if (isset($_SESSION['uid'])) {
            $user_id = $_SESSION['uid'];
        } else {
            $user_id = 0;
        }

        $logdata = array(
            'log_time' => $now,
            'table_name' => $table,
            'query_executed' => $sql_statement,
            'user_id' => $user_id
        );

        // $this->DBOperationLog($logdata);

        return $result;
    }

    public function DBOperationLog($data)
    {
        $fieldNames = implode(', ', array_keys($data));
        $fieldInputs = ':' . implode(', :', array_keys($data));

        $sql_statement = "INSERT INTO sm_system_log
                    ($fieldNames)
            VALUES  ($fieldInputs)";

        $sth = $this->prepare($sql_statement);

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        return $sth->execute();
    }
}