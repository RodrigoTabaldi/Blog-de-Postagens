<?php

declare(strict_types=1);

class Connection
{
    public static function create(array $config): PDO
    {
        $host = $config['host'] ?? null;
        $port = $config['port'] ?? null;
        $database = $config['database'] ?? null;
        $charset = $config['charset'] ?? null;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        if (!is_string($host) || !preg_match('/^[a-zA-Z0-9.:-]+$/D', $host)
            || !is_int($port) || $port < 1 || $port > 65535
            || !is_string($database) || !preg_match('/^[a-zA-Z0-9_]+$/D', $database)
            || !is_string($charset) || !preg_match('/^[a-zA-Z0-9_]+$/D', $charset)
            || !is_string($username) || !is_string($password)) {
            throw new InvalidArgumentException('Configuração de banco de dados inválida.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Não foi possível conectar ao banco de dados.');
        }
    }
}
