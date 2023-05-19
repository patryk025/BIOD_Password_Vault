<?php

foreach (glob("../models/*.php") as $filename)
{
    require_once $filename;
}

require(__DIR__."/../vendor/autoload.php");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

class DbAdapter {
    private static $dbConnection;

    public static function getDbConnection() {
        if (self::$dbConnection === null) {
            self::$dbConnection = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME'], intval($_ENV['DB_PORT']), $_ENV['DB_ENC']);
        }

        return self::$dbConnection;
    }

    public static function insertObject($table, $object) {
        $db = self::getDbConnection();

        $reflector = new ReflectionClass(get_class($object));
        $properties = $reflector->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED);

        $columns = [];
        $values = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            if($propertyName != "yubikeyData" && $propertyName != "OTPData") {
                $columns[] = $propertyName;
                $values[] = $property->getValue($object);
            }
            $property->setAccessible(false);
        }

        $columnList = implode(',', $columns);
        $paramList = implode(',', array_fill(0, count($values), '?'));

        $query = "INSERT INTO {$table} ({$columnList}) VALUES ({$paramList})";
        $statement = $db->prepare($query);
        try {
            $statement->execute($values);

            $last_id = $db->insert_id;

            $object->setId($last_id);
            return true;
        }
        catch(Exception $e) {
            return $e->getCode();
        }
    }

    public static function editAttributeInObject($table, $attr, $val, $id, $where_key) {
        $db = self::getDbConnection();

        $query = "UPDATE {$table} SET {$attr} = ? WHERE {$where_key} = ?";
        $statement = $db->prepare($query);
        $statement->bind_param('si', $val, $id);
        try {
            $statement->execute();
            return true;
        }
        catch(Exception $e) {
            return $e->getCode();
        }
    }

    public static function queryObject($table, $id) {
        $db = self::getDbConnection();

        $query = "SELECT * FROM {$table} WHERE id = ?";
        $statement = $db->prepare($query);

        $statement->bind_param('i', $id);

        try {
            $statement->execute();
            $result = $statement->get_result()->fetch_assoc();

            $className = ucfirst($table);
            if (!class_exists($className)) {
                throw new Exception("Klasa {$className} nie istnieje.");
            }

            $object = new $className($result);
            return $object;
        }
        catch(Exception $e) {
            return null;
        }
    }

    public static function queryObjects($table, $id, $foreign_key) {
        $db = self::getDbConnection();

        $query = "SELECT * FROM {$table} WHERE {$foreign_key} = ?";
        $statement = $db->prepare($query);
        $statement->bind_param('s', $id);

        try {
            $statement->execute();
            $result = $statement->get_result();

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
        catch(Exception $e) {
            return [];
        }
    }

    public static function removeObject($table, $object) {
        $db = self::getDbConnection();

        $id = $object->getId();

        $query = "DELETE FROM {$table} WHERE id = ?";
        $statement = $db->prepare($query);
        $statement->bind_param('i', $id);
        try {
            $statement->execute();
            return true;
        }
        catch(Exception $e) {
            return $e->getCode();
        }
    }

    private static function snakeToCamel($input) {
        return ucfirst(str_replace('_', '', ucwords($input, '_')));
    }
}

?>