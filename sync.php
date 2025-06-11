// Abilita CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Risposta immediata alle richieste preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(204);
  exit;
}


<?php
$dir = "liste";
if (!file_exists($dir)) mkdir($dir, 0755, true);

$code = isset($_GET["code"]) ? $_GET["code"] : ($_POST["code"] ?? null);
$code = strtoupper(preg_replace("/[^A-Z0-9]/", "", $code));

if (!$code) {
  http_response_code(400);
  echo "Codice lista mancante o non valido.";
  exit;
}

$file = "$dir/$code.json";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $json = file_get_contents("php://input");
  if (isset($_POST["json"])) {
    $json = $_POST["json"];
  }
  if (json_decode($json) === null) {
    http_response_code(400);
    echo "Formato JSON non valido.";
    exit;
  }

  file_put_contents($file, $json);
  echo "✅ Lista salvata con successo.";
} else {
  if (file_exists($file)) {
    header("Content-Type: application/json");
    echo file_get_contents($file);
  } else {
    echo json_encode([]);
  }
}
?>
