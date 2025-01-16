<?php

namespace App\Http\Service;

use Illuminate\Http\Request;

class DogHouse
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
                    'scatters' => '1~2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,2000,100,60,0,0,0~0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0~1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1',
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
        private function getDogHousePaytable()
        {
            return implode(';', [
                '0,0,0,0,0',        // 0
                '0,0,0,0,0',        // 1
                '0,0,0,0,0',        // 2
                '750,150,50,0,0',   // 3 - Wild Dog House
                '500,100,35,0,0',   // 4 - Lady
                '300,60,25,0,0',    // 5 - Man
                '200,40,20,0,0',    // 6 - Dog House
                '150,25,12,0,0',    // 7 - Rottweiler
                '100,20,8,0,0',     // 8 - Pug
                '50,10,5,0,0',      // 9 - Shih Tzu
                '50,10,5,0,0',      // 10 - Collar
                '25,5,2,0,0',       // 11 - Bone
                '25,5,2,0,0',       // 12 - Bowl
                '25,5,2,0,0'        // 13 - Ball
            ]);
        }
        
        private function getDogHouseReelSet0()
        {
            return '9,8,12,8,10,7,5,11,4,1,3,7,10,13,1,6,9,13,6,11,12~' .
                   '3,6,8,13,7,10,9,11,10,9,6,5,12,2,4,8,11,12,13,7~' .
                   '4,9,13,12,13,7,8,12,6,1,2,10,11,7,5,11,3,10,8,9,6~' .
                   '2,6,10,7,11,13,12,5,9,3,6,7,12,9,13,8,10,11,4,8~' .
                   '8,11,7,6,13,9,10,5,1,12,6,3,8,4,7,10,13,12,11,9';
        }
        
        private function getDogHouseReelSet1()
        {
            return '12,5,11,9,13,8,13,3,3,3,10,12,11,10,13,11,8,8,9,6,9,10,12,6,3,7,4,7,5~' .
                   '13,11,7,9,4,12,7,3,10,9,8,13,11,10,13,5,6,9,2,7,6,10,12,8,11~' .
                   '6,12,10,13,7,12,5,10,8,7,2,13,3,6,9,8,11,8,5,12,9,4,11,10,9,13~' .
                   '13,9,5,7,13,6,12,11,6,10,13,12,9,7,8,10,4,2,8,7,5,9,11,3,12,8,6,10,11~' .
                   '13,12,11,7,10,11,7,13,4,9,12,6,10,3,3,3,8,6,11,8,9,13,7,9,5,8,12';
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
            $rand = rand(1, 100);
            
            if ($rand <= 25) return rand(2, 5);      // 25% шанс на x2-x5
            if ($rand <= 40) return rand(5, 10);     // 15% шанс на x5-x10
            if ($rand <= 55) return rand(10, 20);    // 15% шанс на x10-x20
            if ($rand <= 65) return rand(20, 30);    // 10% шанс на x20-x30
            if ($rand <= 70) return rand(30, 50);    // 5% шанс на x30-x50
            return rand(50, 60);                     // 30% шанс на x50-x60
        }
        
        private function generateSpinResponse($symbol, $bet, $lines, $index)
        {
            $user = auth('api')->setToken($_COOKIE['token'])->user();
            $user_balance = $user->wallet->balance;
            
            if ($symbol == 'vs20doghouse') {
                $config = $this->getSpinConfig($symbol);
                $symbols = $this->generateRandomSymbols($config['symbolsCount']);
                $hasWin = (rand(1, 100) <= ($user->win_chance ?? 30));
                
                if ($hasWin) {
                    $multiplier = $this->getWinMultiplier();
                    $win = $bet * $multiplier;
                    
                    $height = rand($config['winPositions']['heightRange'][0], $config['winPositions']['heightRange'][1]);
                    $posCount = rand($config['winPositions']['minCount'], $config['winPositions']['maxCount']);
                    
                    $positions = [];
                    $availablePositions = range(0, $config['symbolsCount'] - 1);
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
                    
                    $sa = $this->generateRandomSymbols($config['saCount']);
                    $sb = $this->generateRandomSymbols($config['sbCount']);
                    
                    $newBalance = $user_balance - $bet + $win;
                    $user->wallet->balance = $newBalance;
                    $user->wallet->save();
                    
                    $params = array_merge([
                        'tw' => number_format($win, 2),
                        'balance' => number_format($newBalance, 2),
                        'index' => $index,
                        'balance_cash' => number_format($newBalance, 2),
                        'reel_set' => '0',
                        'balance_bonus' => '0.00',
                        'na' => 'c',
                        'tmb_win' => number_format($win, 2),
                        'bl' => '0',
                        'stime' => time() . '377',
                        'sa' => implode(',', $sa),
                        'sb' => implode(',', $sb),
                        'sh' => $config['height'],
                        'c' => $bet,
                        'sver' => '5',
                        'counter' => '4',
                        'l' => $lines,
                        's' => implode(',', $symbols),
                        'w' => number_format($win, 1),
                        'st' => 'rect',
                        'sw' => $config['sw'],
                        'tmb' => $tmb,
                        'rs' => 't',
                        'rs_p' => '0',
                        'rs_m' => '1',
                        'rs_c' => '1',
                        'trail' => 'nmwin~' . number_format($win, 2),
                        's_mark' => 'tmb~' . str_replace("~", ",", $tmb),
                        'l0' => $l0
                    ], $config['extraParams']);
                } else {
                    $win = 0;
                    $sa = $this->generateRandomSymbols($config['saCount']);
                    $sb = $this->generateRandomSymbols($config['sbCount']);
                    
                    $newBalance = $user_balance - $bet;
                    $user->wallet->balance = $newBalance;
                    $user->wallet->save();
                    
                    $params = array_merge([
                        'tw' => '0.00',
                        'balance' => number_format($newBalance, 2),
                        'index' => $index,
                        'balance_cash' => number_format($newBalance, 2),
                        'reel_set' => '0',
                        'balance_bonus' => '0.00',
                        'na' => 'c',
                        'tmb_win' => '0',
                        'bl' => '0',
                        'stime' => time() . '377',
                        'sa' => implode(',', $sa),
                        'sb' => implode(',', $sb),
                        'sh' => $config['height'],
                        'c' => $bet,
                        'sver' => '5',
                        'counter' => '4',
                        'l' => $lines,
                        's' => implode(',', $symbols),
                        'w' => '0',
                        'st' => 'rect',
                        'sw' => $config['sw']
                    ], $config['extraParams']);
                }
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
    
        private function getSpinConfig($symbol) 
        {
            $configs = [
                'vs20doghouse' => [
                    'symbolsCount' => 15,
                    'saCount' => 5,
                    'sbCount' => 5,
                    'height' => 3,
                    'sw' => 5,
                    'extraParams' => [
                        'mbri' => '1,2,3',
                        'mbr' => '2,2,2'
                    ],
                    'winPositions' => [
                        'minCount' => 3,
                        'maxCount' => 5,
                        'heightRange' => [2, 3]
                    ]
                ]
            ];
            
            return $configs[$symbol] ?? null;
        }
    }
    

