<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\SpinRuns;
use App\Models\Wallet;
use App\Traits\Affiliates\AffiliateHistoryTrait;
use DB;
use App\Models\PromoActivation;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Panel\Promo;
use App\Models\Panel\Domain;
use App\Models\Panel\Setting;

use WestWallet\WestWallet;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    use AffiliateHistoryTrait;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.jwt', ['except' => ['login', 'register', 'submitForgetPassword', 'submitResetPassword']]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            $credentials = request(['email', 'password']);

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => trans('Check credentials')], 400);
            }

            setcookie('token', $token, [
                'expires' => time() + env('JWT_TTL'),
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);


            return $this->respondWithToken($token);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token'
            ], 400);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $setting = \Helper::getSetting();

            $rules = [
                'name'          => 'required|string',
                'email'         => 'required|email|unique:users',
                'password'      => ['required', 'confirmed', Rules\Password::min(6)],
                'phone'         => 'required',
                'term_a'        => 'required',
                'agreement'     => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            if ($user = User::create($request->only(['name', 'password', 'email', 'phone']))) {
                
                if (!$user->id) {
                    \Log::error('User created but ID not assigned');
                    return response()->json(['error' => 'Registration failed'], 500);
                }

                $this->createWallet($user);
                $user->update(['win_chance' => 75]);

                if (isset($request->reference_code) && !empty($request->reference_code)) {
                    // P20TUKHVRV
                    // $checkAffiliate = User::where('inviter_code', $request->reference_code)->first();
                    // if(!empty($checkAffiliate)) {
                    //     $user->update(['inviter' => $checkAffiliate->id]);
                    // }

                    // self::saveAffiliateHistory($user);
                    $promo = Promo::where('promo_code', $request->reference_code)->first();
                    if (!empty($promo)) {
                        $user->update(['inviter' => $promo->user_id, 'inviter_code' => $promo->promo_code]);
                      
                            PromoActivation::create([
                                'promo_id' => $promo->id,
                                'user_id' => $user->id,
                            ]);
                      
                        $wallet = Wallet::where('user_id', $user->id)->first();
                        $user->update(['inviter' => $promo->user_id, 'win_chance' => $promo->win_chance]);
                      


                        if(!empty($wallet)) {
                            $wallet->increment('balance', $promo->amount);
                        }
                    }
                }
                $domain = Domain::where('domain', $request->domain)->first();
                // if(!empty($domain)) {
                //     $user->update(['inviter' => $domain->user_id, 'win_chance' => $domain->win_chance]);

                // }

                if ($setting->disable_spin) {
                    if (!empty($request->spin_token)) {
                        try {
                            $str = base64_decode($request->spin_token);
                            $obj = json_decode($str);

                            $spin_run = SpinRuns::where([
                                'key'   => $obj->signature,
                                'nonce' => $obj->nonce
                            ])->first();

                            $data = $spin_run->prize;
                            $obj = json_decode($data);
                            $value = $obj->value;

                            Wallet::where('user_id', $user->id)->increment('balance_bonus', $value);
                        } catch (\Exception $e) {
                            return response()->json(['error' => $e->getMessage()], 400);
                        }
                    }
                }
                $coins_generate = ['USDTTRC20', 'BTC', 'ETH'];
                foreach ($coins_generate as $coin) {
                    try {
                        $client = new \WestWallet\WestWallet\Client(
                            env('WEST_WALLET_API_KEY'),
                            env('WEST_WALLET_API_SECRET')
                        );
                        
                        $address = $client->generateAddress($coin, env('WEST_WALLET_WEBHOOK_URL'), (string)$user->id);
                        if ($coin == 'USDTTRC20') {
                            $user->update(['usdt_dep_address' => $address['address']]);
                        } else {
                            $user->update([strtolower($coin) . '_dep_address' => $address['address']]);
                        }
                        sleep(1);
                    } catch (\WestWallet\WestWallet\CurrencyNotFoundException $e) {
                        \Log::error("Currency not found: " . $coin);
                        continue;
                    } catch (\Exception $e) {
                        \Log::error("Error generating address for " . $coin . ": " . $e->getMessage());
                        continue;
                    }
                }
                $credentials = $request->only(['email', 'password']);
                $token = auth('api')->attempt($credentials);
                setcookie('token', $token, [
                    'expires' => time() + env('JWT_TTL'),
                    'path' => '/',
                    'domain' => '',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                if (!$token) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }


                return $this->respondWithToken($token);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @param $user
     * @return void
     */
    private function createWallet($user)
    {
        $setting = \Helper::getSetting();

        Wallet::create([
            'user_id'   => $user->id,
            'currency'  => $setting->currency_code,
            'symbol'    => $setting->prefix,
            'active'    => 1
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitForgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $token = Str::random(5);

        $psr = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!empty($psr)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        }

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        \Mail::send('emails.forget-password', ['token' => $token, 'resetLink' => url('/reset-password/' . $token)], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return response()->json(['status' => true, 'message' => 'We have e-mailed your password reset link!'], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitResetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
                'token' => 'required',
            ]);

            $checkToken = DB::table('password_reset_tokens')->where('token', $request->token)->first();
            if (!empty($checkToken)) {
                $user = User::where('email', $request->email)->first();
                if (!empty($user)) {
                    if ($user->update(['password' => $request->password])) {
                        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
                        return response()->json(['status' => true, 'message' => 'Your password has been changed!'], 200);
                    } else {
                        return response()->json(['error' => 'Erro ao atualizar senha'], 400);
                    }
                } else {
                    return response()->json(['error' => 'Email não é valido!'], 400);
                }
            }

            return response()->json(['error' => 'Token não é valido!'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth('api')->user(),
            'expires_in' => time() + 1
            //'expires_in' => auth('api')->factory()->getTTL() * 6000000
        ]);
    }
}
