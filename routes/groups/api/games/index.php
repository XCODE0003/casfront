<?php

use App\Http\Controllers\Api\Games\GameController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MineController;
use App\Http\Controllers\TowerController;
use App\Http\Controllers\CoinController;

Route::prefix('games')
    ->group(function () {
        Route::get('all', [GameController::class, 'index']);
        Route::get('single/{id}', [GameController::class, 'show']);
        Route::post('favorite/{id}', [GameController::class, 'toggleFavorite']);
        Route::post('like/{id}', [GameController::class, 'toggleLike']);


        Route::prefix('mines')
            ->group(function () {
                Route::get('init', [MineController::class, 'init']);
                Route::post('start', [MineController::class, 'start']);
                Route::post('pick', [MineController::class, 'pick']);
                Route::post('stop', [MineController::class, 'stop']);
            });

        Route::prefix('tower')
            ->group(function () {
                Route::get('init', [TowerController::class, 'init']);
                Route::post('start', [TowerController::class, 'startGame']);
                Route::post('pick', [TowerController::class, 'pick']);
                Route::post('stop', [TowerController::class, 'stop']);
            });


        Route::prefix('coin')
            ->group(function () {
            
                Route::post('start', [CoinController::class, 'play']);
             
            });
    });

Route::prefix('featured')
    ->group(function () {
        Route::any('/games', [GameController::class, 'featured']);
    });

Route::prefix('vgames')
    ->group(function () {
        Route::any('/{token}/{action}', [GameController::class, 'sourceProvider']);
    });

Route::prefix('casinos')
    ->group(function () {
        Route::get('games', [GameController::class, 'allGames']);
    });