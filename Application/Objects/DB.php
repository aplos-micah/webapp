<?php

class DB
{
    private PDO $connection;
    private string $driver;

    private static array $passwordFields = [
        'password', 'passwd', 'pass', 'pwd', 'user_password',
        'account_password', 'hashed_password', 'password_hash'
    ];

    public function __construct()
    {
        $config       = Config::db();
        $this->driver = strtolower($config['driver'] ?? 'mysql');
        $this->connection = $this->connect($config);
    }

    private function connect(array $config): PDO
    {
        $host     = $config['host']     ?? 'localhost';
        $port     = $config['port']     ?? null;
        $dbname   = $config['dbname']   ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $charset  = $config['charset']  ?? 'utf8mb4';

        if ($this->driver === 'sqlsrv') {
            $port = $port ?? 1433;
            $dsn  = "sqlsrv:Server={$host},{$port};Database={$dbname};TrustServerCertificate=1";
        } else {
            $port = $port ?? 3306;
            $dsn  = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Execute a SELECT query and return all matching rows.
     *
     * @param  string $sql    Parameterized SQL string
     * @param  array  $params Bound parameters
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return $this->stripPasswords($rows);
    }

    /**
     * Execute a SELECT query and return a single row.
     *
     * @param  string     $sql
     * @param  array      $params
     * @return array|null
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $this->stripPasswordsFromRow($row) : null;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE statement.
     * Returns the number of affected rows.
     *
     * @param  string $sql
     * @param  array  $params
     * @return int
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Execute an INSERT and return the last inserted ID.
     *
     * @param  string $sql
     * @param  array  $params
     * @return string
     */
    public function insert(string $sql, array $params = []): string
    {
        $this->execute($sql, $params);
        return $this->connection->lastInsertId();
    }

    /**
     * Wrap multiple operations in a transaction.
     * Automatically commits on success or rolls back on exception.
     *
     * @param  callable $callback  Receives this DB instance
     * @return mixed               Return value of the callback
     */
    public function transaction(callable $callback): mixed
    {
        $this->connection->beginTransaction();
        try {
            $result = $callback($this);
            $this->connection->commit();
            return $result;
        } catch (Throwable $e) {
            $this->connection->rollBack();
            Logger::getInstance()->error('Transaction rolled back', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute raw multi-statement SQL (migrations only — no parameter binding).
     * Throws PDOException on failure.
     */
    public function runRaw(string $sql): void
    {
        $this->connection->exec($sql);
    }

    // -------------------------------------------------------------------------
    // Password scrubbing
    // -------------------------------------------------------------------------

    private function stripPasswords(array $rows): array
    {
        return array_map([$this, 'stripPasswordsFromRow'], $rows);
    }

    private function stripPasswordsFromRow(array $row): array
    {
        foreach ($row as $key => $value) {
            if (in_array(strtolower((string) $key), self::$passwordFields, true)) {
                unset($row[$key]);
            }
        }
        return $row;
    }
}
