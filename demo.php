<?php  
$api_key = "AIzaSyCdA3Pz8VkX7Ib--gYd-Gdi8_QKFFOB5DM";
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$api_key";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Get user input
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['message'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Define the system message
$system_message = "You are NoteMate, the dedicated AI assistant for NoteHub - an educational document management platform. Your role is to help users with NoteHub features, document management, user roles (students, teachers, colleges, admin), educational queries, and platform features like ratings and search. IMPORTANT: Always keep your responses to 2-3 lines maximum, being concise but informative. You cannot modify data or perform system actions - your role is purely informational and advisory.";

// Combine system message with user input
$user_message = trim($input['message']);
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $system_message],  // First message to set chatbot behavior
                ["text" => $user_message]  // User query
            ]
        ]
    ]
];

// Call Gemini API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


if ($http_code !== 200) {
    echo json_encode(['error' => 'Google Gemini API error']);
    exit;
}

// Extract AI response
$response_data = json_decode($response, true);
if (!isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(['error' => 'Unexpected API response format']);
    exit;
}

$ai_response = trim($response_data['candidates'][0]['content']['parts'][0]['text']);
echo json_encode(['response' => $ai_response]);
