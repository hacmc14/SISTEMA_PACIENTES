<?php

final class CompatResult
{
    private array $rows;
    private int $position = 0;
    public int $num_rows;

    public function __construct(array $rows)
    {
        $this->rows = array_map(static function (array $row): array {
            foreach ($row as $column => $value) {
                if (strtolower((string)$column) !== 'suministrada') {
                    continue;
                }

                if ($value === true || $value === 't' || $value === 'true') {
                    $row[$column] = 1;
                } elseif ($value === false || $value === 'f' || $value === 'false') {
                    $row[$column] = 0;
                }
            }

            return $row;
        }, $rows);
        $this->num_rows = count($this->rows);
    }

    public function fetch_assoc(): array|false
    {
        if (!array_key_exists($this->position, $this->rows)) {
            return false;
        }

        return $this->rows[$this->position++];
    }
}

final class CompatStatement
{
    private array $parameters = [];
    private bool $executed = false;
    public int $affected_rows = 0;
    public int $insert_id = 0;

    public function __construct(
        private PDO $pdo,
        private PDOStatement $statement,
        private string $sql
    ) {
    }

    public function bind_param(string $types, mixed ...$parameters): bool
    {
        $this->parameters = $parameters;
        return true;
    }

    public function execute(): bool
    {
        try {
            $this->executed = $this->statement->execute($this->parameters);
            $this->affected_rows = $this->statement->rowCount();

            if ($this->executed && preg_match('/^\s*INSERT\b/i', $this->sql)) {
                $lastInsertId = $this->pdo->lastInsertId();
                $this->insert_id = is_numeric($lastInsertId) ? (int)$lastInsertId : 0;
            }

            return $this->executed;
        } catch (PDOException) {
            $this->executed = false;
            $this->affected_rows = 0;
            return false;
        }
    }

    public function get_result(): CompatResult
    {
        if (!$this->executed) {
            return new CompatResult([]);
        }

        return new CompatResult($this->statement->fetchAll(PDO::FETCH_ASSOC));
    }
}

final class CompatConnection
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function prepare(string $sql): CompatStatement
    {
        return new CompatStatement($this->pdo, $this->pdo->prepare($sql), $sql);
    }

    public function query(string $sql): CompatResult|bool
    {
        try {
            $statement = $this->pdo->query($sql);

            if ($statement === false || $statement->columnCount() === 0) {
                return true;
            }

            return new CompatResult($statement->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException) {
            http_response_code(500);
            exit('No se pudo completar la consulta a la base de datos.');
        }
    }
}

$databaseUrl = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
if (!$databaseUrl) {
    http_response_code(500);
    exit('La base de datos no está configurada en este entorno.');
}

$databaseParts = parse_url($databaseUrl);
$databaseName = is_array($databaseParts) && isset($databaseParts['path'])
    ? ltrim($databaseParts['path'], '/')
    : '';
if (
    !is_array($databaseParts) ||
    empty($databaseParts['host']) ||
    $databaseName === '' ||
    !isset($databaseParts['user'], $databaseParts['pass'])
) {
    http_response_code(500);
    exit('La configuración de la base de datos no es válida.');
}

$dsn = sprintf(
    'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
    $databaseParts['host'],
    $databaseParts['port'] ?? 5432,
    rawurldecode($databaseName)
);

try {
    $pdo = new PDO(
        $dsn,
        rawurldecode($databaseParts['user']),
        rawurldecode($databaseParts['pass']),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $conexion = new CompatConnection($pdo);
} catch (PDOException) {
    http_response_code(503);
    exit('No se pudo conectar con PostgreSQL. Comprueba la conexión de Neon.');
}
?>
