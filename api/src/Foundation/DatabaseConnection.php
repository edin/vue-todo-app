<?php

namespace App\Foundation;

use PDO;

final class DatabaseConnection
{
    private ?PDO $db = null;
    private string $driver;
    private string $host;
    private string $database;
    private string $user;
    private string $password;

    public function __construct(
        string $driver,
        string $host,
        string $database,
        string $user,
        string $password,
    ) {
        $this->driver = $driver;
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
    }

    private function getDb(): PDO
    {
        if ($this->db == null) {
            $dsn = "{$this->driver}:host={$this->host};dbname={$this->database}";

            $this->db = new PDO($dsn, $this->user, $this->password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        }
        return $this->db;
    }

    public function execute(string $query, array $params = [])
    {
        $command = $this->getDb()->prepare($query);
        foreach ($params as $key => $value) {
            $command->bindValue($key, $value);
        }
        $command->execute();
    }

    public function lastInsertId()
    {
        return $this->getDb()->lastInsertId();
    }

    public function queryAll(string $query, array $params = [])
    {
        $command = $this->getDb()->prepare($query);
        foreach ($params as $key => $value) {
            $command->bindValue($key, $value);
        }
        $command->execute();
        return $command->fetchAll(\PDO::FETCH_ASSOC);
    }
}
