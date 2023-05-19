<?php

namespace db;

class DbAdapter {
    private static $dbConnection;

    public static function getDbConnection() {
        if (self::$dbConnection === null) {
            self::$dbConnection = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_PORT'], $_ENV['DB_ENC']);
        }

        return self::$dbConnection;
    }

    public static function insertObject($table, $object) {
        $db = self::getDbConnection();

        $objectVars = get_object_vars($object);
        $columns = array_keys($objectVars);
        $values = array_values($objectVars);

        $columnList = implode(',', $columns);
        $paramList = implode(',', array_fill(0, count($values), '?'));

        $query = "INSERT INTO {$table} ({$columnList}) VALUES ({$paramList})";
        $statement = $db->prepare($query);
        $statement->execute($values);

        $last_id = $db->insert_id;

        $object->id = $last_id;
    }

    public static function editAttributeInObject($table, $attr, $val, $id, $where_key) {
        $db = self::getDbConnection();

        $query = "UPDATE {$table} SET {$attr} = ? WHERE {$where_key} = ?";
        $statement = $db->prepare($query);
        $statement->bind_param('si', $val, $id);
        $statement->execute();
    }

    public static function queryObject($table, $id) {
        $db = self::getDbConnection();

        $query = "SELECT * FROM {$table} WHERE id = ?";
        $statement = $db->prepare($query);

        $statement->bind_param('i', $id);

        $statement->execute();

        $result = $statement->get_result()->fetch_assoc();

        $className = ucfirst($table);
        if (!class_exists($className)) {
            throw new Exception("Klasa {$className} nie istnieje.");
        }

        $object = new $className($result);
        return $object;
    }

    public static function queryObjects($table, $id, $foreign_key) {
        $db = self::getDbConnection();

        $query = "SELECT * FROM {$table} WHERE {$foreign_key} = ?";
        $statement = $db->prepare($query);
        $statement->bind_param('i', $id);
        $statement->execute();
        
        $results = $statement->get_result();

        $className = ucfirst($table);
        if (!class_exists($className)) {
            throw new Exception("Klasa {$className} nie istnieje.");
        }
        
        $dataObjects = [];
        while ($row = $results->fetch_assoc()) {
            $dataObjects[] = new $className($row);
        }
        
        return $dataObjects;
    }

    public static function removeObject($table, $object) {
        $db = self::getDbConnection();

        $id = $object->getId();

        $query = "DELETE FROM {$table} WHERE id = ?";
        $statement = $db->prepare($query);
        $statement->bind_param('i', $id);
        $statement->execute();
    }

    private static function snakeToCamel($input) {
        return ucfirst(str_replace('_', '', ucwords($input, '_')));
    }
}

?>