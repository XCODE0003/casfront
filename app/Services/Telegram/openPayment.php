<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class openPayment
{
    
    

    public function send(string $botToken, string $userId): void
    {
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $message = "🔔 Пользователь перешел на страницу пополнения баланса!\n\n";
        $message .= "IP: {$ip}\n";
        $message .= "User-Agent: {$userAgent}\n";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        Http::post($url, [
            'chat_id' => $userId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);
    }
}
