<?php

namespace App\Http\Controllers\Api\Games;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function handleGame(Request $request)
    {
        $action = $request->input('action');
        $symbol = $request->input('symbol');
        $coin_value = (float)$request->input('c', 0.1);
        $lines = (int)$request->input('l', 20);
        $bet_multiplier = (int)$request->input('b', 1);

        // Расчет реальной ставки в долларах
        $bet = $coin_value * $lines;
    

        $index = $request->input('index');
        
        switch($action) {
            case 'doInit':
                return $this->generateInitResponse($symbol);
                
            case 'doSpin':
                return $this->generateSpinResponse($symbol, $bet, $lines, $index);
                
            default:
                return response('Неизвестное действие');
        }
    }
    
    private function getGameConfig($symbol) 
    {
        $configs = [
            'vs20fruitswx' => [
                'symbolsCount' => 30,
                'saCount' => 6,
                'sbCount' => 6,
                'height' => 5,
                'version' => 3,
                'reelSetSize' => 5,
                'defaultCoin' => 0.10,
                'scatters' => '1~2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,100,60,0,0,0~0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0~1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1',
                'coins' => '0.01,0.02,0.03,0.04,0.05,0.10,0.20,0.30,0.40,0.50,0.75,1.00,2.00,3.00,4.00,5.00,6.00,7.00,8.00,9.00,10.00,11.00,12.00',
                'getPaytable' => 'getPaytable',
                'getReelSets' => [
                    'getReelSet0',
                    'getReelSet1',
                    'getReelSet2',
                    'getReelSet3',
                    'getReelSet4'
                ]
            ],
            'vs20doghouse' => [
                'symbolsCount' => 15,
                'saCount' => 5,
                'sbCount' => 5,
                'height' => 3,
                'version' => 2,
                'reelSetSize' => 2,
                'defaultCoin' => 0.01,
                'scatters' => '1~0,0,5,0,0~0,0,0,0,0~1,1,1,1,1',
                'coins' => '0.01,0.02,0.05,0.10,0.25,0.50,1.00,3.00,5.00',
                'getPaytable' => 'getDogHousePaytable',
                'getReelSets' => [
                    'getDogHouseReelSet0',
                    'getDogHouseReelSet1'
                ],
                'extraParams' => [
                    'gmb' => '0,0,0',
                    'mbri' => '1,2,3',
                    'fsbonus' => '',
                    'mbr' => '1,1,1',
                    'n_reel_set' => '0'
                ]
            ]
        ];
        
        return $configs[$symbol] ?? null;
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
            'counter' => '2',
            'ntp' => '0.00',
            'paytable' => $this->{$config['getPaytable']}(),
            'l' => '20',
            's' => implode(',', $symbols),
        ];
        
        // Добавляем reelSets
        foreach ($config['getReelSets'] as $i => $method) {
            $params['reel_set' . $i] = $this->{$method}();
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
    
    private function getPaytable()
    {
        return implode(';', [
            '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
            '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
            '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
            '1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,1000,500,500,200,200,0,0,0,0,0,0,0',
            '500,500,500,500,500,500,500,500,500,500,500,500,500,500,500,500,500,500,500,200,200,50,50,0,0,0,0,0,0,0',
            '300,300,300,300,300,300,300,300,300,300,300,300,300,300,300,300,300,300,300,100,100,40,40,0,0,0,0,0,0,0',
            '240,240,240,240,240,240,240,240,240,240,240,240,240,240,240,240,240,240,240,40,40,30,30,0,0,0,0,0,0,0',
            '200,200,200,200,200,200,200,200,200,200,200,200,200,200,200,200,200,200,200,30,30,20,20,0,0,0,0,0,0,0',
            '160,160,160,160,160,160,160,160,160,160,160,160,160,160,160,160,160,160,160,24,24,16,16,0,0,0,0,0,0,0',
            '100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,20,20,10,10,0,0,0,0,0,0,0',
            '80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,80,18,18,8,8,0,0,0,0,0,0,0',
            '40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,40,15,15,5,5,0,0,0,0,0,0,0',
            '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0'
        ]);
    }
    
    private function getReelSet0()
    {
        return '8,11,6,8,4,3,9,11,5,10,6,10,6,10,10,10,9,11,9,11,10,11,8,7,5,3,7,10,9,5,7,7,7,11,1,4,6,8,4,7,8,10,10,9,10,11,7,10~9,11,7,8,7,9,11,6,8,10,4,9,10,8,5,10,8,10,3,4,1,8,11,11,11,7,10,7,1,11,6,11,10,5,5,9,11,9,11,9,6,11,3,8,6,11,10,9,4~6,11,11,3,5,3,9,3,7,9,8,6,8,6,7,7,7,6,10,5,8,10,10,8,4,10,4,10,11,5,10,7,3,3,3,4,10,8,8,11,7,4,8,11,7,1,9,9,11,7,11~7,8,10,6,7,3,6,9,4,6,5,5,10,10,10,11,1,5,10,10,9,9,11,9,10,11,9,11,3,10,11,11,11,5,11,8,11,4,11,10,11,3,4,8,6,11,11,6,7~5,8,9,6,5,9,10,3,9,9,8,4,8,7,7,7,6,9,9,4,9,10,5,11,6,8,1,7,11,9,9,9,8,11,5,11,10,7,11,3,10,4,11,7,9,6,10~5,10,6,11,5,10,6,11,4,6,10,1,9,7,11,8,8,6,4,11,8,11,8,4,4,4,11,7,7,8,4,9,6,10,9,4,9,7,10,11,9,9,5,9,10,3,4,8,4,9,6';
    }
    
    private function getReelSet1()
    {
        return '11,6,8,9,11,8,10,5,6,12,8,11,8,7,4,10,10,10,3,10,9,10,9,10,11,10,9,5,11,8,1,4,6,11,9,7,11~8,10,9,11,8,9,7,7,5,9,9,10,6,8,10,9,10,11,5,7,4,8,9,10,10,11,10,10,4,1,12,8,11,4,6,11,8,6,11,11,9,6,9,10,11,4,12,11,8,9,5,6,10,11,9,5,3,6,8,3,7~11,9,6,3,9,11,4,11,4,10,8,5,4,8,10,10,11,6,6,3,12,9,11,3,8,4,7,7,7,10,4,1,8,8,11,7,10,8,10,5,9,3,6,9,8,10,11,8,7,8,7,7,5,5,6,7~10,11,10,5,6,3,9,8,3,7,11,9,8,4,10,4,11,11,8,1,9,11,4,9,9,10,9,6,7,10,10,10,7,11,6,12,3,8,10,10,6,6,5,5,11,10,4,11,5,5,10,11,11,7,3,10,10,9,6,10,5,9,11,11~7,9,8,7,5,11,6,9,10,9,11,1,8,8,6,3,10,9,11,7,8,7,7,7,4,10,3,9,6,12,11,7,11,8,5,8,10,9,10,10,5,4,11,9,4,11,9,6~11,9,10,12,9,6,9,9,8,4,7,3,5,11,6,11,10,11,5,11,10,9,7,7,11,10,10,8,8,5,9,1,4,6,11,9,5,3,12,6,4,11,9,8,9,8,9,9,8,6,4,11,10,7,8,10,11,8,10,9,6';
    }
    
    private function getReelSet2()
    {
        return '5,4,10,10,9,11,7,10,8,6,3,5,6,9,10,8,10,7,10,10,10,4,11,11,9,3,8,10,10,7,4,11,11,1,8,10,9,4,8,7,7,7,8,7,8,10,11,6,11,5,6,9,6,5,11,10,11,9,9,11,10,10~10,9,5,10,5,7,9,11,4,7,5,7,9,3,10,9,10,11,10,11,6,9,4,6,9,4,8,6,6,8,9,11,11,11,10,7,11,10,11,8,4,11,11,10,6,10,8,5,7,11,9,10,11,3,11,9,1,11,8,8,6,1,7,9,11,8,8,11~8,8,6,9,6,11,4,1,5,1,8,3,11,7,7,7,11,8,10,9,5,11,9,4,4,3,9,10,10,8,3,3,3,7,7,3,7,5,4,10,11,11,6,8,6,10,10,7~5,11,6,8,6,6,10,6,11,11,3,9,8,11,5,3,10,7,9,11,10,10,10,9,10,9,9,11,7,10,3,6,4,5,11,9,10,4,11,9,5,6,5,11,3,9,9,9,4,10,11,9,8,4,1,9,6,11,10,5,11,10,10,7,9,7,10,8,10,6,9~10,8,9,4,7,8,3,9,10,10,4,5,6,8,6,11,10,1,3,7,7,7,11,10,5,9,11,11,8,4,8,1,9,7,7,11,5,9,6,9,6,11,5,9~10,4,10,11,5,6,7,4,11,8,7,6,9,11,9,4,10,8,8,9,11,4,7,8,4,11,8,8,1,4,4,4,11,6,3,10,7,9,6,9,5,9,3,4,4,6,10,11,6,9,9,6,9,6,11,9,10,5,10,11,9,10,5,8,8';
    }
    
    private function getReelSet3()
    {
        return '5,4,9,11,8,11,10,6,10,4,11,11,6,10,3,8,11,10,6,11,9,8,6,7,10,10,10,11,8,10,9,6,7,1,4,10,10,9,11,5,9,10,11,3,10,9,9,11,8,5,12,7,8,8~4,11,10,5,4,11,11,7,11,7,9,4,3,9,10,8,9,8,10,8,3,12,10,6,9,8,11,6,11,5,10,5,12,6,10,8,6,9,9,7,1~11,11,10,8,6,9,8,3,12,8,4,11,9,11,7,11,4,3,7,3,11,10,8,7,10,10,11,7,7,7,4,8,5,9,6,9,6,10,1,8,4,8,4,5,8,9,7,8,5,11,7,3,6,6,7,10,5,10,10~10,11,11,5,1,7,10,6,9,5,7,10,10,5,9,9,11,10,11,6,4,11,3,5,6,10,11,10,10,10,3,10,3,6,10,8,9,7,4,9,8,9,5,8,3,10,11,4,12,8,11,11,4,11,10,11,9,6,9,7~11,11,6,11,8,6,7,1,4,5,8,9,12,3,8,8,4,10,10,9,11,10,10,11,8,9,6,6,9,7,7,7,6,3,10,7,9,9,10,10,6,9,11,11,9,5,11,9,4,8,9,4,8,8,10,11,5,7,5,9,11,7,7,9~9,7,11,6,10,9,11,12,8,1,8,9,10,4,8,10,12,6,3,11,9,5,8,3,7,7,10,9,10,6,11,11,5,10,4,4,11,9,11,9,6,10,11,9,5,8,9,8,6,9,8';
    }
    
    private function getReelSet4()
    {
        return '5,9,6,8,10,9,11,10,10,11,7,7,6,8,7,10,10,10,7,6,10,8,4,10,8,11,10,11,11,5,10,7,10,10,11,7,7,7,3,4,9,5,11,9,10,8,6,11,11,9,6,10,4,8,9,3~10,6,4,5,8,7,6,7,5,4,4,4,8,8,11,3,6,9,5,10,11,11,8,8,8,9,10,9,11,10,6,11,4,11,11,11,7,10,7,4,8,8,11,9,9,10,9,3~8,3,7,8,10,10,9,10,10,10,4,6,9,10,4,10,11,5,6,7,7,7,8,8,10,9,11,4,11,3,3,3,8,5,7,11,6,11,10,7,3,7~11,4,5,4,10,3,11,10,6,9,9,9,5,6,11,9,9,10,7,8,3,7,10,10,10,3,4,10,9,8,5,11,9,6,11,11,11,8,7,11,6,9,10,10,11,6,11,11,5~9,7,7,6,10,4,9,8,11,10,9,7,9,5,11,11,11,9,10,11,3,5,4,5,11,11,9,10,9,10,9,11,7,7,7,3,5,9,9,11,6,4,8,9,6,6,5,8,8,7,9,9,9,6,8,9,8,11,9,5,6,8,9,11,11,4,10,11,10~9,11,6,11,4,11,10,11,9,5,6,6,4,10,9,7,10,8,4,11,4,4,4,11,9,9,3,6,9,5,4,8,9,4,4,9,6,5,7,11,6,8,3,7,11,6,6,6,10,5,9,7,8,10,6,8,6,10,6,8,11,4,4,8,9,8,7,7,9,10,10';
    }
    private function getWinMultiplier() {
        $rand = rand(1, 1000);
        
        if ($rand <= 600) { // 60% шанс маленького выигрыша
            return rand(1, 3); // минимальные выигрыши
        }
        else if ($rand <= 850) { // 25% шанс среднего выигрыша
            return rand(4, 8); // небольшие выигрыши
        }
        else if ($rand <= 950) { // 10% шанс хорошего выигрыша
            return rand(9, 15); // средние выигрыши
        }
        else if ($rand <= 990) { // 4% шанс крупного выигрыша
            return rand(16, 25); // хорошие выигрыши
        }
        else { // 1% шанс джекпота
            return rand(26, 50); // максимальные выигрыши
        }
    }
    private function generateSpinResponse($symbol, $bet, $lines, $index)
    {
        $symbols = $this->generateRandomSymbols(30);
        $user = auth('api')->setToken($_COOKIE['token'])->user();
        $user_balance = $user->wallet->balance;
        $hasWin = (rand(1, 100) <= $user->win_chance ?? 40);
        
        if ($hasWin) {
            $multiplier = $this->getWinMultiplier();
            $win = $bet * $multiplier;
            
            $height = rand(7, 8);
            
            $positions = [];
            $posCount = rand(6, 9);
            
            $availablePositions = range(0, 26);
            shuffle($availablePositions);
            $selectedPositions = array_slice($availablePositions, 0, $posCount);
            sort($selectedPositions);
            
            foreach ($selectedPositions as $pos) {
                $positions[] = $pos . "," . $height;
            }
            
            $tmb = implode("~", $positions);
            
            $l0Positions = array_map(function($pos) {
                return explode(",", $pos)[0];
            }, $positions);
            $l0 = "0~" . number_format($win, 2) . "~" . implode("~", $l0Positions);
        } else {
            $tmb = "";
            $win = 0;
        }

        $sa = $this->generateRandomSymbols(6);
        $sb = $this->generateRandomSymbols(6);
        
        $newBalance = $user_balance - $bet + $win;
        
        $user->wallet->balance = $newBalance;
        $user->wallet->save();
        
        $params = [
            'tw' => number_format($win, 2),
            'balance' => number_format($newBalance, 2),
            'index' => $index,
            'balance_cash' => number_format($newBalance, 2),
            'reel_set' => '0',
            'balance_bonus' => '0.00',
            'na' => 's',
            'tmb_win' => number_format($win, 2),
            'bl' => '0',
            'stime' => time() . '377',
            'sa' => implode(',', $sa),
            'sb' => implode(',', $sb),
            'sh' => '5',
            'c' => $bet,
            'sver' => '5',
            'counter' => '4',
            'l' => $lines,
            's' => implode(',', $symbols),
            'w' => $hasWin ? number_format($win, 1) : '0',
            'st' => 'rect',
            'sw' => '6'
        ];

        if ($hasWin) {
            $params['tmb'] = $tmb;
            $params['rs'] = 't';
            $params['rs_p'] = '0';
            $params['rs_m'] = '1';
            $params['rs_c'] = '1';
            $params['trail'] = 'nmwin~' . number_format($win, 2);
            $params['s_mark'] = 'tmb~' . str_replace("~", ",", $tmb);
            $params['l0'] = $l0;
        }

        $response = '';
        foreach ($params as $key => $value) {
            if ($value !== '') {
                $response .= $key . '=' . $value . '&';
            }
        }
        
        return rtrim($response, '&');
    }
    
    private function generateRandomSymbols($count)
    {
        $symbols = [];
        for ($i = 0; $i < $count; $i++) {
            $symbols[] = rand(3, 11); 
        }
        return $symbols;
    }
}
