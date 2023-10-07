<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

switch ($keyboard) {
    case 2:
        $keyboard = [[[ 'text' => 'Тестовая кнопка', 'callback_data' => 'test_data']]];
        break;

    default:
        throw new \Exception("Invalid test keyboard.");
}

$markup = new InlineKeyboardMarkup($keyboard);

echo serialize($markup);
