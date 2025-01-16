<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mine;

class MineController extends Controller
{
    public function init()
    {
        $user = auth('api')->user();
        $mines = Mine::where('user_id', $user->id)->where('status', 'active')->first();
        if ($mines) {
            $mines->result = $this->resultHidden($mines->result);
            $mines->next_win = $this->getNextWin($mines->bet, $mines->mines);
            return response()->json(['success' => true, 'game' => $mines]);
        }

        return response()->json(['success' => true, 'game' => null]);
    }

    public function start(Request $request)
    {
        $user = auth('api')->user();

        $game = Mine::where('user_id', $user->id)->where('status', 'active')->first();
        if ($game) {
            return response()->json(['success' => false, 'message' => 'You already have a game']);
        }
        if ($user->wallet->balance < $request->bet) {
            return response()->json(['success' => false, 'message' => 'You do not have enough balance']);
        }
        if ($request->mines > 25 || $request->mines < 1) {
            return response()->json(['success' => false, 'message' => 'Invalid mines count']);
        }


        $bet = $request->bet;
        $mines = $request->mines;
        $result = $this->generateResult($mines);
        $next_win = $this->getNextWin($bet, $mines);
        $game = Mine::create([
            'user_id' => $user->id,
            'bet' => $bet,
            'mines' => $mines,
            'result' => $result,
            'status' => 'active',
        ]);
        $user->wallet->balance -= $bet;

        $user->wallet->save();
        $game->result = $this->resultHidden($result);


        return response()->json(['success' => true, 'game' => $game, 'result' => $game->result, 'next_win' => $next_win]);
    }

    public function pick(Request $request)
    {
        $position = $request->id;
        $user = auth('api')->user();
        $game = Mine::where('user_id', $user->id)->where('status', 'active')->first();
        if (!$game) {
            return response()->json(['success' => false, 'message' => 'Game not found']);
        }
        if ($game->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Game is not active']);
        }

        $result = $game->result;
        if ($result[$position]['picked']) {
            return response()->json(['success' => false, 'message' => 'Cell already picked']);
        }
        $result[$position]['picked'] = true;

        $game->result = $result;
        $game->save();
        $game->result = $this->resultHidden($game->result);

        if ($result[$position]['mine'] === 1) {
            $game->status = 'lost';
            $game->save();
            return response()->json(['success' => false, 'message' => 'You lost', 'result' => $game->result, 'game' => $game]);
        }


        return response()->json(['success' => true, 'game' => $game->result, 'next_win' => $this->getNextWin($game->bet, $game->mines), 'result' => $this->resultHidden($game->result)]);
    }

    public function stop(Request $request)
    {
        $user = auth('api')->user();
        $game = Mine::where('user_id', $user->id)->where('status', 'active')->first();
        if (!$game) {
            return response()->json(['success' => false, 'message' => 'Game not found']);
        }
        $game->status = 'win';
        $game->save();
        $picked = $this->countPicked($game->result);

        if ($picked > 0) {
            $user->wallet->balance += $this->multiplyBet($game->bet, $game->mines, $picked - 1);
        } else {
            $user->wallet->balance += $game->bet;
        }

        $user->wallet->save();
        return response()->json(['success' => true, 'game' => $game->result]);
    }
    private function resultHidden($result)
    {
        foreach ($result as $key => $value) {
            if (!$value['picked']) {
                $result[$key]['mine'] = 0;
            }
        }
        return $result;
    }
    private function generateResult($mineCount)
    {
        $result = [];
        $totalCells = 25; // 5x5 grid

        for ($i = 0; $i < $totalCells; $i++) {
            $result[] = [
                "picked" => false,
                "mine" => 0
            ];
        }

        $placedMines = 0;
        while ($placedMines < $mineCount) {
            $position = rand(0, $totalCells - 1);
            if ($result[$position]["mine"] === 0) {
                $result[$position]["mine"] = 1;
                $placedMines++;
            }
        }

        return $result;
    }

    private function checkWin($result)
    {
        foreach ($result as $key => $value) {
            if ($value['mine'] === 1 && $value['picked'] === false) {
                return false;
            }
        }
        return true;
    }


    private function multiplyBet($bet, $mines, $picked = 0)
    {
        // Вычисляем вероятность выигрыша
        $totalCells = 25;
        $safeCells = $totalCells - $mines;
        $remainingSafeCells = $safeCells - $picked;
        $remainingCells = $totalCells - $picked;

        // Базовый множитель на основе вероятности
        if ($remainingCells <= 0 || $remainingSafeCells <= 0) {
            return 0;
        }

        // Формула: (общее количество ячеек) / (количество безопасных ячеек)
        // С учетом house edge (0.99 для 1% преимущества казино)
        $multiplier = 0.99 * ($totalCells / $safeCells);

        // Умножаем на количество успешных выборов
        for ($i = 0; $i < $picked; $i++) {
            $currentSafeCells = $safeCells - $i;
            $currentTotalCells = $totalCells - $i;
            $multiplier *= ($currentTotalCells / $currentSafeCells);
        }

        return round($bet * $multiplier, 2);
    }

    private function getNextWin($bet, $mines)
    {
        $game = Mine::where('user_id', auth('api')->user()->id)
            ->where('status', 'active')
            ->first();

        $picked = $game ? $this->countPicked($game->result) : 0;
        return $this->multiplyBet($bet, $mines, $picked);
    }

    private function countPicked($result)
    {
        return array_sum(array_column($result, 'picked'));
    }
}
