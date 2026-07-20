<?php
// chat.php
// Receives { "prompt": "..." } from app.js, calls the Cohere API server-side
// (so the API key is never exposed in browser JavaScript), and returns { "reply": "..." }.

// ------------------------------------------------------------------
// 0. CORS: allow this PHP file to be called from a different origin
//    (e.g. your GitHub Pages site, since GitHub Pages can't run PHP
//    itself). Replace "*" with your exact GitHub Pages URL for better
//    security once everything is working, e.g.:
//    header("Access-Control-Allow-Origin: https://yourusername.github.io");
// ------------------------------------------------------------------
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Browsers send a preflight OPTIONS request before the real POST when
// calling a different origin. Just acknowledge it and stop here.
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

header("Content-Type: application/json");

// ------------------------------------------------------------------
// 1. Put your Cohere API key here, or better: read it from an
//    environment variable so it's never committed to GitHub.
//    Get a free trial key at: https://dashboard.cohere.com/api-keys
// ------------------------------------------------------------------
$COHERE_API_KEY = getenv("COHERE_API_KEY") ?: "PASTE_YOUR_COHERE_API_KEY_HERE";

if ($COHERE_API_KEY === "PASTE_YOUR_COHERE_API_KEY_HERE") {
    http_response_code(500);
    echo json_encode(["error" => "Server is missing a Cohere API key. Set COHERE_API_KEY."]);
    exit;
}

// ------------------------------------------------------------------
// 2. Read the incoming JSON body { "prompt": "..." }
// ------------------------------------------------------------------
$input = json_decode(file_get_contents("php://input"), true);
$prompt = isset($input["prompt"]) ? trim($input["prompt"]) : "";

if ($prompt === "") {
    http_response_code(400);
    echo json_encode(["error" => "No prompt provided."]);
    exit;
}

// ------------------------------------------------------------------
// 3. Call the Cohere Chat API
//    Docs: https://docs.cohere.com/reference/chat
// ------------------------------------------------------------------
$url = "https://api.cohere.ai/v1/chat";

$payload = json_encode([
    "model"   => "command-r",
    "message" => $prompt
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $COHERE_API_KEY",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(["error" => "Request to Cohere failed: $curlError"]);
    exit;
}

$data = json_decode($response, true);

// ------------------------------------------------------------------
// 4. Extract the reply text and send it back to app.js
// ------------------------------------------------------------------
if ($httpCode !== 200 || !isset($data["text"])) {
    http_response_code(500);
    echo json_encode([
        "error" => "Cohere API error",
        "details" => $data
    ]);
    exit;
}

echo json_encode(["reply" => $data["text"]]);
