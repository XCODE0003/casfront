<?php

namespace App\Http\Service;

use Illuminate\Http\Request;

class GatoTgates
{
    public function handleGame(Request $request)
    {
        $action = $request->input('action');
        $symbol = $request->input('symbol');
        $coin_value = (float)$request->input('c', 0.1);
        $lines = (int)$request->input('l', 20);
        $index = $request->input('index');
        
        switch($action) {
            case 'doInit':
                return $this->generateInitResponse($symbol);
                
            case 'doSpin':
                return $this->generateSpinResponse($coin_value, $lines, $index);
                
            default:
                return response('Неизвестное действие');
        }
    }

    private function generateInitResponse($symbol)
    {
        $token = $_COOKIE['token'] ?? null;
        $user = auth('api')->setToken($token)->user();
        $user_balance = $user->wallet->balance;
        
        $config = $this->getGameConfig($symbol);
        if (!$config) {
            return 'Неизвестная игра';
        }
        
        $symbols = $this->generateRandomSymbols($config['symbolsCount']);
        $sa = $this->generateRandomSymbols($config['saCount']);
        $sb = $this->generateRandomSymbols($config['sbCount']);
        
        $params = [
            'def_s' => implode(',', $symbols),
            'balance' => number_format($user_balance, 2),
            'cfgs' => '1',
            'ver' => $config['version'],
            'index' => '1',
            'balance_cash' => number_format($user_balance, 2),
            'reel_set_size' => $config['reelSetSize'],
            'def_sb' => implode(',', $sb),
            'def_sa' => implode(',', $sa),
            'balance_bonus' => '0.00',
            'na' => 's',
            'scatters' => $config['scatters'],
            'rt' => 'd',
            'stime' => time() . '896',
            'sa' => implode(',', $sa),
            'sb' => implode(',', $sb),
            'sc' => $config['coins'],
            'defc' => $config['defaultCoin'],
            'sh' => $config['height'],
            'wilds' => '2~0,0,0,0,0~1,1,1,1,1',
            'bonuses' => '0',
            'c' => $config['defaultCoin'],
            'sver' => '5',
            'gmb' => '0,0,0',
            'purInit' => '[{type:"fs",bet:2000,fs_count:10}]',
            't' => 'stack',
            'reel_set' => '0',
            'reel_set_size' => '8',
            'counter' => '2',
            'ntp' => '0.00',
            'paytable' => $this->getPaytable(),
            'l' => '20',
            's' => implode(',', $symbols),
        ];
        
        // Добавляем reelSets
        for ($i = 0; $i < 8; $i++) {
            $params['reel_set' . $i] = $this->getReelSet($i);
        }
        
        // Добавляем дополнительные параметры если есть
        if (isset($config['extraParams'])) {
            $params = array_merge($params, $config['extraParams']);
        }
        
        $response = '';
        foreach ($params as $key => $value) {
            if ($value !== '') {
                $response .= $key . '=' . $value . '&';
            }
        }
        
        return rtrim($response, '&');
    }
    private function getGameConfig($symbol) 
    {
        $configs = [
            'symbolsCount' => 49,
            'saCount' => 7,
            'sbCount' => 7,
            'version' => '2',
            'reelSetSize' => '5',
            'height' => '7',
            'defaultCoin' => '0.10',
            'coins' => '0.01,0.02,0.03,0.04,0.05,0.10,0.20,0.30,0.40,0.50,0.75,1.00,2.00,3.00,4.00,5.00',
            'scatters' => '1~0,0,0,0,0,0,0~10,10,10,10,10,0,0~1,1,1,1,1,1,1',
            'extraParams' => [
                'gmb' => '0,0,0',
                'purInit' => '[{type:"fs",bet:2000,fs_count:10}]',
                't' => 'stack',
                'reel_set' => '0',
                'total_bet_min' => '0.01',
                'total_bet_max' => '10000.00',
                'rtp' => '96.47'
            ]
        ];
        
        return $configs ?? null;
    }
    private function generateSpinResponse($coin_value, $lines, $index)
    {
        $token = $_COOKIE['token'] ?? null;
        $user = auth('api')->setToken($token)->user();
        $user_balance = $user->wallet->balance;
        
        $bet = $coin_value * $lines;
        $hasWin = (rand(1, 100) <= ($user->win_chance ?? 25)); // Уменьшен шанс выигрыша до 25%
        
        if ($hasWin) {
            $win = $bet * $this->getWinMultiplier();
            $newBalance = $user_balance - $bet + $win;
        } else {
            $win = 0;
            $newBalance = $user_balance - $bet;
        }
        
        $user->wallet->balance = $newBalance;
        $user->wallet->save();

        // Генерируем выигрышные позиции если есть выигрыш
        $tmb = '';
        $l0 = '';
        $rs = '';
        $rs_p = '';
        $rs_c = '';
        $rs_m = '';
        
        if ($hasWin) {
            // Генерируем случайные позиции для выигрыша
            $positions = $this->generateWinningPositions();
            $tmb = implode('~', array_map(function($pos) {
                return $pos . ',5'; // 5 - символ для выигрыша
            }, $positions));
            
            $l0 = '0~' . number_format($win, 2) . '~' . implode('~', $positions);
            $rs = 't';
            $rs_p = '0';
            $rs_c = '1';
            $rs_m = '1';
        }

        $params = [
            'tw' => number_format($win, 2),
            'tmb' => $tmb,
            'balance' => number_format($newBalance, 2),
            'index' => $index,
            'balance_cash' => number_format($newBalance, 2),
            'balance_bonus' => '0.00',
            'na' => 's',
            'rs' => $rs,
            'tmb_win' => number_format($win, 2),
            'l0' => $l0,
            'rs_p' => $rs_p,
            'stime' => time() . rand(100,999),
            'sa' => implode(',', $this->generateRandomSymbols(7)),
            'sb' => implode(',', $this->generateRandomSymbols(7)), 
            'rs_c' => $rs_c,
            'sh' => '7',
            'rs_m' => $rs_m,
            'c' => $coin_value,
            'sver' => '5',
            'counter' => rand(20, 40),
            'l' => $lines,
            's' => implode(',', $this->generateRandomSymbols(49)),
            'w' => number_format($win, 1),
            'sw' => '7',
            'st' => 'rect'
        ];

        $response = '';
        foreach ($params as $key => $value) {
            if ($value !== '') {
                $response .= $key . '=' . $value . '&';
            }
        }
        return rtrim($response, '&');
    }

    private function getWinMultiplier()
    {
        $rand = rand(1, 100);
        
        if ($rand <= 40) return rand(2, 5);      // 40% шанс на x2-x5
        if ($rand <= 70) return rand(5, 10);     // 30% шанс на x5-x10
        if ($rand <= 85) return rand(10, 20);    // 15% шанс на x10-x20
        if ($rand <= 95) return rand(20, 30);    // 10% шанс на x20-x30
        return rand(30, 40);                     // 5% шанс на x30-x40
    }

    private function generateRandomSymbols($count)
    {
        $symbols = [];
        for ($i = 0; $i < $count; $i++) {
            $symbols[] = rand(3, 9); // Символы от 3 до 9
        }
        return $symbols;
    }

    private function getReelSet($index) 
    {
        // Определяем базовые наборы барабанов
        $reelSets = [
            '3,1,10,11,9,7,5,11,11,11,4,10,10,10,8,6,8,8,8,1,8,5,4,8,5,1,10,1,8,4,10,1,8~' .
            '11,11,11,5,7,8,4,1,10,10,10,10,11,9,3,9,9,9,6,4,10,4,9,4,10,4,10,4,6,1,9,3,9,4,3,4,9,6,9,3,10,1,10,1,3,8,10,3,10,1,9,6,3,6~' .
            '5,5,5,5,1,4,8,6,8,8,8,3,7,7,7,9,7,11,10,7,8,6,7,4,6,8,11,10,4,11,7,8,6,4,11,8,7,11,10,8,4,9,6,7,8,11,7,4,9,11,8,6,8,11,7,8~' .
            '4,9,7,10,10,10,6,3,3,3,10,8,7,7,7,1,11,3,5,6,6,6,10,7,11,10,5,6,10,7,10,1,7,6,3,6,1,3,1,7,10~' .
            '7,8,5,10,3,9,11,8,8,8,4,9,9,9,6,1,4,4,4,7,7,7,11,11,11,8,11,9,3,1,8,3,9,6,8,6,9,6,9,11,9,11,8,6,4,9,8,9,1,8,9,8,9,4,1,11,9,8,1,6,8,9,8~' .
            '6,6,6,6,9,9,9,11,5,8,3,10,10,10,7,4,10,11,11,11,9,1,4,4,4,8,10,9,11,4,8,11,4,10,11,9,5,8,4,8,4,10,9,4,9,5,9,11,7,11,9',

            '6,5,8,3,11,1,7,10,4,9,5,10,4,9,10,9,7,10,5,9,5,10,7,10,4,10,7,4,5,9,5,10~' .
            '5,1,6,7,8,10,9,3,4,11,8,9,4,7,9,4,6,8,11,3,6,10,3,9,8,10,8,11,6,9,1,7,6,7,11,6,11,4,11,7,11,9,6,4,8,3,11,1,9,7,10,11,4,11,3,4,9,11,9,8~' .
            '10,1,8,11,9,5,6,4,3,7,8,11,6,11,4,11,6,7,5,8,7,11,3~' .
            '10,4,5,8,8,8,1,8,3,7,11,9,6,9,3,8,4,7,8,3,8,3,9,1,9,7,8,9,8,11,9,1,9,7,8,11,3,7,8,1,7,8~' .
            '3,9,8,6,5,10,11,1,4,7,4,5,4,1,4,8,5,4,5,7,5,9,11,10,5,10,6,10,4,10,7,5,10,6,7,9,10,5,10,7,1,11,5,10,9,4,8,10,1,4,5,4,10~' .
            '9,9,9,9,3,1,8,4,10,7,5,11,6,3,8,3,4,3,5,8,3,11,3,5,3,11,3,11,3,1,3,5,3,7,4,5,3,5,8,11',

            '4,5,7,11,11,11,6,9,10,10,10,3,8,8,8,8,10,11,12,10,8,6,11,6,11,6,10,11~' .
            '6,9,9,9,12,10,11,11,11,4,9,7,5,4,4,4,8,11,3,10,10,10,3,8,7,11,9,11,3,11,4,8,3,4,9,12,8,11,9,11,9,12,11,10,3,9,12,9,8,10,4,11,9,12,7,9,7,8,11,4~' .
            '8,8,8,7,5,5,5,5,7,7,7,12,4,11,3,6,9,8,10,7,5,10,5,12,7,10,7,3,10,7,5,7,11,10,6,5,10,5,7,12,10,12,11,10,3,5,10,5,11,5,7,10,5,11,5,7,12,3,7,3,7,10~' .
            '12,6,6,6,8,6,10,11,7,7,7,7,9,3,3,3,3,4,5,10,10,10,6,10,3,10~' .
            '12,10,8,9,11,11,11,11,5,6,7,9,9,9,3,4,8,8,8,7,7,7,4,4,4,9,11,9,7,9,7,6~' .
            '4,4,4,4,8,11,11,11,12,7,10,10,10,9,5,3,10,6,11,6,6,6,9,9,9,7,5,6,10,6,5,9,10,12,6,3,7,10,11,10,7,9,12,6',

            '8,8,8,7,9,10,10,10,11,8,11,11,11,4,12,6,1,5,3,10,1,10,9,10,11,10,11,1,11,10,11,4,1,4,11,6,12,11,10,11,10~' .
            '4,9,9,9,9,6,11,11,11,1,3,10,10,10,5,12,8,10,7,11,12,1,8,9,11,12,9,11,10,12,9,6,9,11,5,12,9,11,12,11,12,9,11,10,11,5,9,1,10,9,11,5,11,5,12,9,11,5,1,12,9,11~' .
            '5,5,5,6,8,8,8,12,7,7,7,8,7,10,4,11,3,5,9,1,11,9,7~' .
            '1,5,12,6,6,6,4,7,7,7,9,11,3,3,3,10,10,10,10,8,6,3,7,6,9,3,10,3,9,7,11,3,10,9,10,6,11,10,7,3,6,3,6,7,10,6,3,8,3,9,7,10~' .
            '4,1,7,11,11,11,10,8,9,9,9,6,9,11,12,3,5,8,8,8,7,7,7,4,4,4,1,7,9,5,9,7,12,6,8,12,9,8,11,7,8,10,11,8,11,7~' .
            '10,6,5,9,6,6,6,1,4,8,7,12,3,11,9,9,9,10,10,10,4,4,4,11,11,11,4,5,3,6,1',

            '10,11,11,11,8,10,10,10,7,8,8,8,3,11,4,9,6,12,5,7,8,11,8,11,7,8,11,4,11,4,7,8,5,8,7,6,7,11~' .
            '3,10,10,10,11,9,9,9,4,12,11,11,11,5,7,8,6,10,9,10,11,9,11,10,6,10,9,11,9,10,11,5~' .
            '5,11,8,8,8,9,12,7,7,7,6,3,5,5,5,7,8,4,10,7,12,8,7,6,7,10,8,12,7,8,12,11,7,8,10,8,7~' .
            '6,6,6,5,10,6,11,10,10,10,8,7,4,12,3,7,7,7,9,3,3,3,8,12,10,7,3,9,3,4,3,8,7,5,3,10,4,10,3,5,3,10,12,11,7,10,3~' .
            '5,7,9,9,9,3,12,6,10,11,4,8,9,11,11,11,8,8,8,4,4,4,7,7,7,9,6,7,11,4,10,12,11,8,9,7,4,3,4,7,9,4,11,4,12,9,4,12~' .
            '9,10,10,10,6,4,4,4,12,7,5,6,6,6,8,4,9,9,9,11,10,3,11,11,11,7,4,8,4,12,7,11,3,11,12,6,11,5,3,7,5,10,12,4,10,5,12,10,6,11',

            '10,10,10,9,10,7,12,4,3,5,5,5,5,11,8,6,6,6,6,4,4,4,3,3,3,7,7,7,9,9,9,8,8,8,11,11,11,4,5,6,5,4,3,7,6,3,7,4,6,4,5,3,11,5,6,5,4,6,3,5,4,6,4,3,11,4,5,4,5,4,5,7,5,11,4,11,3,4,6,7,6,9,12~' .
            '4,4,4,9,5,10,9,9,9,11,11,11,11,3,6,6,6,7,6,3,3,3,4,7,7,7,12,8,8,8,8,10,10,10,5,5,5,3,5,6,3~' .
            '10,6,6,6,11,8,5,9,6,4,12,3,7,7,7,7,3,3,3,5,5,5,9,9,9,10,10,10,8,8,8,11,11,11,4,4,4,3,5,3,9,6,3,6,8,5,11,4,7,4,3,11,6,5,11,3,6,9,3,11,3,5,11,4,9,6,3,4,6,5,3,7,5,7,11,8,4,11,6,3,5,4~' .
            '5,12,3,3,3,10,4,6,8,7,11,9,3,9,9,9,7,7,7,8,8,8,5,5,5,10,10,10,11,11,11,6,6,6,4,4,4,8,4,3,10,11,3,10,9,10,3,6,8,10,9,11,9,6,9,8,3,4,8,11,10,8,11,8,11,3,9,3,9,8,10,7,9,8,10,8,11,8,7,3,4,3,11,3,8,6,10,3,4,3,8~' .
            '3,7,7,7,7,9,5,3,3,3,6,4,12,10,10,10,8,10,9,9,9,11,11,11,11,4,4,4,6,6,6,5,5,5,8,8,8,9,4~' .
            '8,4,3,3,3,3,5,6,6,6,11,9,9,9,7,6,8,8,8,9,10,10,10,12,10,11,11,11,5,5,5,7,7,7,4,4,4,11,4,6,9,3,5,3,7,3,12,5,3,9,11,3,9,6,4,6,3,4,3,5,4,5,7,4,11,3,7,5,3,9,6,9,4,12',

            '7,8,4,9,3,6,11,10,5,6,10,6,11,3,5,6,10,6,3,10,11,8,4,11,6,10,4,8,3,6,9~' .
            '10,9,4,3,6,8,7,5,11,5,11,4,3,4~' .
            '11,5,3,6,9,10,8,7,4,9,3,9,4,9~' .
            '4,7,5,6,9,3,8,11,10,8,10,8,10,5,10,9,10~' .
            '11,9,7,8,10,4,5,6,3,8,9,5,7,9,5,4,10,6,4~' .
            '10,4,5,8,6,7,3,11,9,8,3'
        ];

        return $reelSets[$index] ?? '';
    }

    private function getPaytable()
    {
        // Обновляем таблицу выплат согласно новым требованиям
        return '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0;' .
               '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0;' .
               '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0;' .
               '1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,500,500,200,200,0,0,0,0,0,0,0';
    }

    private function generateWinningPositions() 
    {
        // Генерируем от 5 до 7 случайных позиций для выигрышной линии
        $count = rand(5, 7);
        $positions = [];
        
        for ($i = 0; $i < $count; $i++) {
            $positions[] = rand(1, 40); // Позиции от 1 до 40
        }
        
        sort($positions); // Сортируем позиции по возрастанию
        return $positions;
    }
}
    

