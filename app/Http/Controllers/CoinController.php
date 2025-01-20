<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoinController extends Controller
{
    public function play(Request $request)
    {
        $user = auth('api')->user();
        $win_chance = (int)$user->win_chance;
        $bet = $request->bet;
        $coin = $request->coin; 

        $will_win = mt_rand(1, 100) <= $win_chance;

        $result = [
            'win' => false,
            'coin_result' => null
        ];

        if ($will_win) {
            $result['coin_result'] = $coin;
        } else {
            $result['coin_result'] = $coin === 'heads' ? 'tails' : 'heads';
        }

        $result['win'] = ($result['coin_result'] === $coin);

        \Log::info('Coin flip debug:', [
            'user_id' => $user->id,
            'win_chance' => $win_chance,
            'will_win' => $will_win,
            'user_choice' => $coin,
            'result' => $result
        ]);

        if ($result['win']) {
            $user->wallet->balance += $bet * 2;
            $user->wallet->save();
        } else {
            $user->wallet->balance -= $bet;
            $user->wallet->save();
        }

        return response()->json($result);
    }
}
