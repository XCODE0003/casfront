<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class openPayment
{
    
    

    public function send(string $botToken, string $userId): bool
    {
        try {
          
            $ip = Http::withoutVerifying()
                ->get('https://api.ipify.org?format=json')
                ->json('ip');
            
            if (!$ip) {
                $ip = request()->header('X-Forwarded-For') ?? request()->ip();
            }
            
            $userAgent = request()->userAgent();
            $domain = request()->getHost();
            $message = "<b>🔔 Пользователь перешел на страницу пополнения баланса!</b>\n\n";
            $message .= "<b>Domain:</b> {$domain}\n";
            $message .= "<b>IP:</b> {$ip}\n";
            $message .= "<b>User-Agent:</b> {$userAgent}\n";

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            
            $response = Http::withoutVerifying()
                ->post($url, [
                    'chat_id' => $userId,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Telegram payment notification error: ' . $e->getMessage());
            return false;
        }
    }
}
