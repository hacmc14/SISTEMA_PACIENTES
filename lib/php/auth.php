<?php
require_once __DIR__ . '/conexion.php';

final class PostgresSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $statement = $this->pdo->prepare(
            'SELECT session_data FROM php_sessions WHERE session_id = ? AND updated_at >= ?'
        );
        $statement->execute([$id, time() - (int)ini_get('session.gc_maxlifetime')]);
        $sessionData = $statement->fetchColumn();

        return $sessionData === false ? '' : (string)$sessionData;
    }

    public function write(string $id, string $data): bool
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO php_sessions (session_id, session_data, updated_at) VALUES (?, ?, ?) '
            . 'ON CONFLICT (session_id) DO UPDATE '
            . 'SET session_data = EXCLUDED.session_data, updated_at = EXCLUDED.updated_at'
        );

        return $statement->execute([$id, $data, time()]);
    }

    public function destroy(string $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM php_sessions WHERE session_id = ?');
        return $statement->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->pdo->prepare('DELETE FROM php_sessions WHERE updated_at < ?');
        $statement->execute([time() - $max_lifetime]);

        return $statement->rowCount();
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('pacientes_session');
    session_set_save_handler(new PostgresSessionHandler($conexion->getPdo()), true);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function usuario_autenticado(): bool
{
    return isset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['usuario_rol']);
}

function exigir_login(): void
{
    if (!usuario_autenticado()) {
        header('Location: login.php');
        exit;
    }
}

function exigir_master(): void
{
    exigir_login();
    if ($_SESSION['usuario_rol'] !== 'master') {
        http_response_code(403);
        exit('Acceso denegado. Solo el usuario Master puede administrar cuentas. <a href="admin.php">Volver</a>');
    }
}

function token_csrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validar_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Solicitud no válida. Recarga la página e inténtalo de nuevo.');
    }
}
?>
