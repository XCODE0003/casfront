<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class NewVisit
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
            $message = "🔔 Новое посещение!\n\n";
            $message .= "Новый посетитель зашел на сайт.\n";
            $message .= "IP: {$ip}\n";
            $message .= "User-Agent: {$userAgent}\n";

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            
            $response = Http::withoutVerifying()
                ->post($url, [
                    'chat_id' => $userId,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);
                
            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Telegram notification error: ' . $e->getMessage());
            return false;
        }
    }
}
