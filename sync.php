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

// === Percorso file dati ===
$code = $_GET["code"] ?? "";
if (!$code) {
  http_response_code(400);
  echo "Missing code";
  exit;
}

$filename = "liste/" . preg_replace("/[^A-Z0-9]/", "", strtoupper($code)) . ".json";

// === Se è POST, salva la lista ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $data = file_get_contents("php://input");
  if (!is_dir("liste")) mkdir("liste");
  file_put_contents($filename, $data);
  echo "✅ Lista salvata";
  exit;
}

// === Se è GET, restituisci la lista ===
if (file_exists($filename)) {
  header("Content-Type: application/json");
  readfile($filename);
} else {
  echo "[]";
}
