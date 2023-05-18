<?php

class DbAdapter {
    protected $dbConnection;

    public function __construct() {
        $this->dbConnection = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_PORT'], $_ENV['DB_ENC']);
    }

    public function insertObject($table, $object) {
        $objectVars = get_object_vars($object);
        $columns = array_keys($objectVars);
        $values = array_values($objectVars);

        $columnList = implode(',', $columns);
        $paramList = implode(',', array_fill(0, count($values), '?'));

        $query = "INSERT INTO {$table} ({$columnList}) VALUES ({$paramList})";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($values);
    }

    public function queryObject($table, $id) {
        // Przygotuj zapytanie
        $query = "SELECT * FROM {$table} WHERE id = ?";
        $statement = $this->dbConnection->prepare($query);
        
        // Bindowanie wartości do zapytania
        $statement->bind_param('i', $id);
    
        // Wykonaj zapytanie
        $statement->execute();
    
        // Pobierz wyniki
        $result = $statement->get_result()->fetch_assoc();
    
        // Przekształć wyniki w obiekt
        // Zakładam, że istnieje klasa o nazwie identycznej z nazwą tabeli
        $className = snakeToCamel($table);
        if (!class_exists($className)) {
            throw new Exception("Klasa {$className} nie istnieje.");
        }
    
        $object = new $className($result);
        return $object;
    }

    private function snakeToCamel($input) {
        return ucfirst(str_replace('_', '', ucwords($input, '_')));
    }
}

?>