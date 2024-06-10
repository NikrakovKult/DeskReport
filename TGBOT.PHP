<?php
define('BOT_TOKEN', '7019580590:AAEmPJy5iiWEEWFMH3YWFROVp6GFR6YwAR4');

$input = file_get_contents('php://input');
$json = json_decode($input, true);

if (json_last_error() === JSON_ERROR_NONE) {
    if ($json) {
        // Обработка входящих сообщений
        foreach ($json as $update) {
            $message = $update['message'];
            $chat_id = $message['chat']['id'];
            $text = $message['text'];

            // Отправка ответа
            $response = json_encode(['chat_id' => $chat_id, 'text' => 'Привет, я бот!']);
            file_get_contents('https://api.telegram.org/bot'. BOT_TOKEN. '/sendMessage?'. http_build_query($response));
        }
    }
} else {
    // Handle invalid JSON input
    error_log('Invalid JSON input: ' . $input);
}
?>