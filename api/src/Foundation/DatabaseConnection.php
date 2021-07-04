<?php

namespace App\Foundation;

use PDO;

final class DatabaseConnection 
{
    private ?PDO $db = null;

    public function __construct()
    {
    }

    private function getDb(): PDO 
    {
        if ($this->db == null) {
            $this->db = new PDO('mysql:host=localhost;dbname=todo', "root", "root", [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]); 
        }
        return $this->db;
    }

    public function execute(string $query, array $params = []) 
    {
        $command = $this->getDb()->prepare($query);
        foreach($params as $key => $value) {
            $command->bindValue($key, $value);
        }
        $command->execute();
    }

    public function lastInsertId() {
        return $this->getDb()->lastInsertId();
    }

    public function queryAll(string $query, array $params = []) 
    {
        $command = $this->getDb()->prepare($query);
        foreach($params as $key => $value) {
            $command->bindValue($key, $value);
        }
        $command->execute();
        return $command->fetchAll(\PDO::FETCH_ASSOC);
    }
}