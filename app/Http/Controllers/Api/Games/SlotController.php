<?php

namespace App\Http\Controllers\Api\Games;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Service\SweetBananza;
use App\Http\Service\DogHouse;
use App\Http\Service\Olympus;
use App\Http\Service\Fruit;
use App\Http\Service\GatoTgates;
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
        $service = null;
        if($symbol == 'vs20fruitswx') {
            $service = new SweetBananza();
        } else if($symbol == 'vs20doghouse') {
            $service = new DogHouse();
        }
        else if($symbol == 'vs20olympx') {
            $service = new Olympus();
        }   
         else if($symbol == 'vs20fruitparty') {
            $service = new Fruit();
        }
        else if($symbol == 'vs20gatotgates') {
            $service = new GatoTgates();
        }
        return $service->handleGame($request);
    }
    
}
