<?php
// === CORS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// === Risposta automatica al preflight CORS ===
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(204);
  exit;
}

// === Connessione al database ===
$db_host = getenv("DB_HOST");     // Es: db.abcd.supabase.co
$db_port = getenv("DB_PORT") ?: "5432";
$db_name = getenv("DB_NAME");
$db_user = getenv("DB_USER");
$db_pass = getenv("DB_PASS");

$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
try {
  $pdo = new PDO($dsn, $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo "Errore di connessione al database";
  exit;
}

// === Gestione codice lista ===
$code = $_GET["code"] ?? "";
if (!$code) {
  http_response_code(400);
  echo "Missing code";
  exit;
}

// === Se è POST: salva o aggiorna la lista ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $data = file_get_contents("php://input");

  $stmt = $pdo->prepare("INSERT INTO liste (code, contenuto, updated)
                         VALUES (:code, :contenuto, now())
                         ON CONFLICT (code)
                         DO UPDATE SET contenuto = EXCLUDED.contenuto, updated = now()");
  $stmt->execute([
    ":code" => strtoupper($code),
    ":contenuto" => $data
  ]);
  echo "✅ Lista salvata";
  exit;
}

// === Se è GET: restituisci la lista ===
$stmt = $pdo->prepare("SELECT contenuto FROM liste WHERE code = :code");
$stmt->execute([":code" => strtoupper($code)]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
  header("Content-Type: application/json");
  echo $row["contenuto"];
} else {
  echo "[]";
}
