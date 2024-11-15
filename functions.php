<?php
function generateShortURL($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $short_url = '';

    for ($i = 0; $i < $length; $i++) {
        $short_url .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $short_url;
}

function calculateExpiration($amount, $unit) {
    date_default_timezone_set('UTC');
    $current_time = new DateTime();

    switch ($unit) {
        case 'seconds':
            $current_time->modify("+{$amount} seconds");
            break;
        case 'minutes':
            $current_time->modify("+{$amount} minutes");
            break;
        case 'hours':
            $current_time->modify("+{$amount} hours");
            break;
        case 'days':
            $current_time->modify("+{$amount} days");
            break;
        case 'weeks':
            $current_time->modify("+{$amount} weeks");
            break;
        case 'months':
            $current_time->modify("+{$amount} months");
            break;
        default:
            $current_time->modify('+1 day');
            break;
    }

    return $current_time->format('Y-m-d H:i:s');
}

// Base62 encoding function
function base62Encode($number) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $base = strlen($characters);
    $encoded = '';

    while ($number > 0) {
        $remainder = $number % $base;
        $encoded = $characters[$remainder] . $encoded;
        $number = (int)($number / $base);
    }

    return $encoded;
}
?>
