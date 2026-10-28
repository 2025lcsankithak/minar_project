<?php
// Update these values for your local MySQL installation.
define('DB_HOST', 'localhost');
define('DB_NAME', 'mentorship_portal');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/minar_project');

/** MySQLi-backed adapter for the app's query interface. */
class MysqlStatement
{
    private mysqli_stmt $statement;

    public function __construct(mysqli_stmt $statement) { $this->statement = $statement; }

    public function execute(array $params = []): bool
    {
        if ($params) {
            $types = '';
            foreach ($params as $value) $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : (is_null($value) ? 's' : 's'));
            $values = array_values($params);
            $references = [];
            foreach ($values as $key => &$value) $references[$key] = &$value;
            unset($value);
            $this->statement->bind_param($types, ...$references);
        }
        return $this->statement->execute();
    }

    public function fetch(): array|false { return $this->statement->get_result()->fetch_assoc() ?: false; }
    public function fetchAll(int $mode = MYSQLI_ASSOC): array { return $this->statement->get_result()->fetch_all($mode); }
    public function fetchColumn(int $column = 0): mixed
    {
        $row = $this->statement->get_result()->fetch_row();
        return $row === null ? false : ($row[$column] ?? false);
    }
    public function rowCount(): int { return $this->statement->affected_rows; }
}

class MysqlDatabase
{
    private mysqli $connection;

    public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->connection->set_charset('utf8mb4');
    }

    public function prepare(string $sql): MysqlStatement { return new MysqlStatement($this->connection->prepare($sql)); }
    public function query(string $sql): MysqlStatement
    {
        $statement = $this->connection->prepare($sql);
        $result = new MysqlStatement($statement);
        $result->execute();
        return $result;
    }
    public function beginTransaction(): bool { return $this->connection->begin_transaction(); }
    public function commit(): bool { return $this->connection->commit(); }
    public function rollBack(): bool { return $this->connection->rollback(); }
    public function lastInsertId(): string { return (string) $this->connection->insert_id; }
}

function db(): MysqlDatabase
{
    static $connection;
    if (!$connection) $connection = new MysqlDatabase();
    return $connection;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_valid_document_url($url): bool
{
    $url = trim((string) $url);
    if ($url === '' || strlen($url) > 255 || !filter_var($url, FILTER_VALIDATE_URL)) return false;
    return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        exit('Invalid request token. Reload the page and try again.');
    }
}

function user_error_message(Throwable $exception): string
{
    error_log($exception::class . ': ' . $exception->getMessage());
    if ($exception instanceof mysqli_sql_exception) return 'A database operation failed. Please try again or contact the administrator.';
    return $exception->getMessage();
}

function countOf(string $table, string $filter = ''): int
{
    $allowed = ['users' => ['student' => "role='student'", 'mentor' => "role='mentor'"], 'course_requests' => ['pending' => "status='pending'"]];
    if ($table === 'courses') return (int) db()->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    if (!isset($allowed[$table][$filter])) throw new InvalidArgumentException('Unsupported count filter.');
    return (int) db()->query("SELECT COUNT(*) FROM $table WHERE {$allowed[$table][$filter]}")->fetchColumn();
}
