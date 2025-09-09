<?php
/*
$ TEAM    : https://instagram.com/darkxcode_
$ AUTHOR  : https://t.me/zlaxtert 
$ CODE    : https://t.me/zexkings 
$ DESIGN  : https://t.me/danielsmt 
$ SITE    : https://darkxcode.site/
$ VERSION : 1.0
*/

// Read helpers
require_once "function/helpers.php";

// Read settings
$configFile = 'settings.ini';
$defaultConfig = [
    'SETTINGS' => [
        'BOT_TOKEN' => 'PASTE YOUR BOT TOKEN HERE'
    ]
];

if (!file_exists($configFile)) {
    $config = $defaultConfig;
    file_put_contents($configFile, implode("\n", [
        "[SETTINGS]",
        "BOT_TOKEN = " . $defaultConfig['SETTINGS']['BOT_TOKEN']
    ]));
} else {
    $config = parse_ini_file($configFile, true);
    if ($config === false) {
        $config = $defaultConfig;
    }
}

if ($config['SETTINGS']['BOT_TOKEN'] === 'PASTE YOUR BOT TOKEN HERE') {
    die("\n\n[!] INCORRECT BOT TOKEN! [!]\n\n");
}

// Configuration
$botToken = $config['SETTINGS']['BOT_TOKEN'];
$website = "https://api.telegram.org/bot" . $botToken;


// Error reporting - consider logging instead of suppressing all errors
error_reporting(1);

// Get and parse the update
$update = json_decode(file_get_contents('php://input'), true);

// Validate update structure
if (!isset($update['message'])) {
    exit;
}

// Extract message data with null coalescing for safety
$message = $update['message']['text'] ?? '';
$chatId = $update['message']['chat']['id'] ?? '';
$userId = $update['message']['from']['id'] ?? '';
$firstname = $update['message']['from']['first_name'] ?? '';
$username = $update['message']['from']['username'] ?? '';
$message_id = $update['message']['message_id'] ?? '';
$ownerUsername = "@zlaxtert";

// Command routing
switch (true) {
    case startsWith($message, ['!start', '/start']):
        handleStartCommand($chatId, $message_id);
        break;
        
    case startsWith($message, ['!menu', '/menu']):
        handleMenuCommand($chatId, $message_id);
        break;
        
    case startsWith($message, ['!info', '/info']):
        handleInfoCommand($chatId, $userId, $firstname, $username, $message_id);
        break;
        
    case startsWith($message, ['!help', '/help']):
        handleHelpCommand($chatId, $userId, $message_id);
        break;
        
    case startsWith($message, ['!bin', '/bin']):
        handleBinCommand($message, $chatId, $username, $message_id);
        break;
        
    case startsWith($message, ['!stripe', '/stripe']):
        handleStripeCommand($message, $chatId, $message_id);
        break;
        
    case startsWith($message, ['!braintree', '/braintree']):
        handleBraintreeCommand($message, $chatId, $message_id);
        break;
        
    case startsWith($message, ['!vbv', '/vbv']):
        handleVbvCommand($message, $chatId, $message_id);
        break;
        
    case startsWith($message, ['!sk', '/sk']):
        handleSkCommand($message, $chatId, $message_id);
        break;
        
    default:
        handleUnknownCommand($chatId, $message_id);
        break;
}
