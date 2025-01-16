<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tower;

class TowerController extends Controller
{
    private const WIN_CHANCE = 100;

    public function init()
    {
        $user = auth('api')->user();
        $towers = Tower::where('user_id', $user->id)->where('status', 'active')->first();

        return response()->json(['success' => true, 'game' => $towers]);
    }


    public function startGame(Request $request)
    {
        $user = auth('api')->user();
        $game = Tower::where('user_id', $user->id)->where('status', 'active')->first();

        if ($game) {
            return response()->json(['success' => false, 'message' => 'You already have a game']);
        }

        if ($user->wallet->balance < $request->bet) {
            return response()->json(['success' => false, 'message' => 'You do not have enough balance']);
        }

        $result = $this->generateResult();

        // Сразу вычитаем ставку при начале игры
        $user->wallet->balance -= $request->bet;
        $user->wallet->save();

        $game = Tower::create([
            'user_id' => $user->id,
            'bet' => $request->bet,
            'result' => $result,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'game' => $game,
            'next_win' => $this->calculateNextWin($request->bet, 1)
        ]);
    }
    private function getRow($result)
    {
        $row = array_column($result, 'picked');
        return $row;
    }
    private function generateResult()
    {
        $result = [];
        $rows = 8;
        $cells = 4;

        for ($row = 0; $row < $rows; $row++) {
            $rowResult = [];

            for ($cell = 0; $cell < $cells; $cell++) {
                $rowResult[$cell] = [
                    'position' => $cell,
                    'picked' => false,
                    'bomb' => false
                ];
            }
            $result[] = $rowResult;
        }

        return $result;
    }

    public function stopGame(Request $request)
    {
        $user = auth('api')->user();
        $game = Tower::where('user_id', $user->id)->where('status', 'active')->first();
        $game->status = 'win';
        $game->save();

        $user->wallet->balance += $game->bet;
        $user->wallet->save();

        return response()->json(['success' => true, 'game' => $game]);
    }
    private function multiplyBet($bet, $mines, $picked = 0)
    {
        $totalCells = 36;
        $safeCells = $totalCells - $mines;
        $remainingSafeCells = $safeCells - $picked;
        $remainingCells = $totalCells - $picked;

        if ($remainingCells <= 0 || $remainingSafeCells <= 0) {
            return 0;
        }


        $multiplier = 0.99 * ($totalCells / $safeCells);

        for ($i = 0; $i < $picked; $i++) {
            $currentSafeCells = $safeCells - $i;
            $currentTotalCells = $totalCells - $i;
            $multiplier *= ($currentTotalCells / $currentSafeCells);
        }

        return round($bet * $multiplier, 2);
    }

    private function getNextWin($bet, $mines)
    {
        $game = Tower::where('user_id', auth('api')->user()->id)
            ->where('status', 'active')
            ->first();

        $picked = $game ? $this->countPicked($game->result) : 0;
        return $this->multiplyBet($bet, $mines, $picked);
    }
    private function countPicked($result)
    {
        return array_sum(array_column($result, 'picked'));
    }

    public function pick(Request $request)
    {
        $user = auth('api')->user();
        $game = Tower::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$game) {
            return response()->json(['success' => false, 'message' => 'Нет активной игры']);
        }

        $result = $game->result;
        $row = $request->row;
        $position = $request->position;

        if ($result[$row][$position]['picked']) {
            return response()->json(['success' => false, 'message' => 'Ячейка уже выбрана']);
        }

        $isBomb = rand(1, 100) > self::WIN_CHANCE;
        $result[$row][$position]['picked'] = true;
        $result[$row][$position]['bomb'] = $isBomb;

        $game->result = $result;

        if ($isBomb) {
            $game->status = 'lose';
            $game->save();
            // При проигрыше не возвращаем ставку, она уже была вычтена при старте
            return response()->json([
                'success' => false,
                'result' => $result,
                'game' => $game
            ]);
        }

        if ($row === 0) {
            $winAmount = $this->calculateNextWin($game->bet, 8 - $row);
            $user->wallet->balance += $winAmount;
            $user->wallet->save();

            $game->status = 'win';
            $game->save();
        }

        $game->save();

        return response()->json([
            'success' => true,
            'result' => $result,
            'next_win' => $this->calculateNextWin($game->bet, 8 - $row)
        ]);
    }

    private function calculateNextWin($bet, $level)
    {
        $multiplier = 1 + ($level * 0.3); // Теперь будет: 1.3, 1.6, 1.9, 2.2, 2.5 и т.д.
        return round($bet * $multiplier, 2);
    }

    public function stop(Request $request)
    {
        $user = auth('api')->user();
        $game = Tower::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$game) {
            return response()->json(['success' => false, 'message' => 'Нет активной игры']);
        }

        // Подсчитываем количество успешных выборов
        $pickedCount = 0;
        foreach ($game->result as $row) {
            foreach ($row as $cell) {
                if ($cell['picked'] && !$cell['bomb']) {
                    $pickedCount++;
                }
            }
        }

        // Рассчитываем выигрыш
        $winAmount = $this->calculateNextWin($game->bet, $pickedCount);

        // Начисляем выигрыш
        $user->wallet->balance += $winAmount;
        $user->wallet->save();

        // Завершаем игру
        $game->status = 'win';
        $game->save();

        return response()->json([
            'success' => true,
            'game' => $game,
            'win_amount' => $winAmount
        ]);
    }
}