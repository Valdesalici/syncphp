<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbHost = $_ENV['DB_HOST'] ?? '';
$dbName = $_ENV['DB_NAME'] ?? '';
$dbUser = $_ENV['DB_USER'] ?? '';
$dbPass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("pgsql:host=$dbHost;port=5432;dbname=$dbName", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';
$code = $_GET['code'] ?? '';
header('Content-Type: application/json');

if ($action === 'load') {
    if ($code === '') {
        echo json_encode(['error' => 'Missing code']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT contenuto FROM liste WHERE code = :code");
    $stmt->execute([':code' => $code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row ? $row['contenuto'] : []);
    exit;
}

if ($action === 'save') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['code']) || !isset($input['items'])) {
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO liste (code, contenuto, updated)
        VALUES (:code, :contenuto, now())
        ON CONFLICT (code)
        DO UPDATE SET contenuto = EXCLUDED.contenuto, updated = now()
    ");
    $stmt->execute([
        ':code' => $input['code'],
        ':contenuto' => json_encode($input['items'])
    ]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);
