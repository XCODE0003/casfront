<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Panel\Worker;
use Illuminate\Http\Request;
use App\Models\Panel\Promo;
use App\Models\User;
use App\Services\Telegram\activatePromo;
use App\Models\Panel\NotifySetting;
use App\Models\PromoActivation;
class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalEarnings = Order::where('user_id', auth('api')->id())->where('type', 'win')->sum('amount');
        $totalBets = Order::where('user_id', auth('api')->id())->where('type', 'bet')->count();
        $sumBets = Order::where('user_id', auth('api')->id())->where('type', 'bet')->sum('amount');

        return response()->json([
            'status' => true,
            'user' => auth('api')->user(),
            'totalEarnings' => \Helper::amountFormatDecimal($totalEarnings),
            'totalBets' => \Helper::amountFormatDecimal($totalBets),
            'sumBets' => $sumBets,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function updateName(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if(auth('api')->user()->update(['name' => $request->name])) {
            return response()->json(['status' => true, 'message' => trans('Name was updated successfully')]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function uploadAvatar(Request $request)
    {
        $rules = [
            'avatar' => ['required','image','mimes:jpg,png,jpeg'],
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $avatar = \Helper::upload($request->avatar)['path'];
        if(auth('api')->user()->update(['avatar' => $avatar])) {
            return response()->json(['status' => true, 'message' => trans('Avatar has been updated successfully')]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLanguage(Request $request)
    {
        if(auth('api')->check()) {
            $user = auth('api')->user();

            $user->language = $request->input('language');
            $user->save();

            return response()->json(['message' => 'Idioma atualizado com sucesso']);
        }
        return response()->json(['message' => 'Idioma atualizado com sucesso, mas com dados salvo na sessão, faça login para salvar em seu perfil']);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLanguage(Request $request)
    {
        $browserLanguages = $request->getLanguages();

        $preferredLanguage = $browserLanguages[0] ?? 'en';
        if(auth('api')->check()) {
            return response()->json(['language' => auth('api')->user()->language]);
        }

        return response()->json(['language' => $preferredLanguage]);
    }

    public function applyPromo(Request $request)
    {
        $promo = Promo::where('promo_code', $request->code)->first();
        $promoActivation = PromoActivation::where('promo_id', $promo->id)->where('user_id', auth('api')->id())->first();
        if($promoActivation) {
            return response()->json(['status' => false, 'message' => 'Promo code already applied'], 400);
        }

        if($promo) {
            $user = auth('api')->user();
            $user->wallet->balance += $promo->amount;
            $user->wallet->save();
            $user->inviter = $promo->user_id;
            $user->inviter_code = $promo->promo_code;
            $user->save();
            PromoActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $user->id,
            ]);
            $worker = Worker::query()->where('id', $user->inviter)->first();
            if($worker){
                $notify = NotifySetting::query()->where('user_id', $worker->id)->first();
                if($notify->notify_activate_promo) {
                    (new activatePromo())->send($notify->bot_token, $worker->tg_id, $promo->promo_code, $user);
                }
            }
            return response()->json(['status' => true, 'message' => 'Promo code applied successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Promo code not found'], 404);
    }
}
