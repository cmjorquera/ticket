<?php

require_once __DIR__ . '/ConfigPrivada.php';

class Conexion
{
    private static array $instancias = [];
    private \PDO $pdo;

    public function __construct(string $bd)
    {
        // Credenciales desde la configuración privada (fuera del repositorio).
        // Ver clases/ConfigPrivada.php y config_privada.example.php
        $cfg = ConfigPrivada::get('db');

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $cfg['host'] ?? 'localhost',
            ($cfg['prefijo'] ?? '') . $bd,
            $cfg['charset'] ?? 'utf8'
        );

        $this->pdo = new \PDO($dsn, (string)($cfg['usuario'] ?? ''), (string)($cfg['password'] ?? ''), [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(string $bd): self
    {
        if (!isset(self::$instancias[$bd])) {
            self::$instancias[$bd] = new self($bd);
        }
        return self::$instancias[$bd];
    }

    public function getPDO(): \PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
?>