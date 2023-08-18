<?php

namespace App\Http\Controllers\Auth;

use App\Http\Api\TelegramBot;
use App\Http\Controllers\Controller;
use App\Models\ObjectNotation;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $bot = new TelegramBot(new Http(), config('app.bot'));
        $ip = json_decode($request->get('ip'));
        $userIp = $ip->ip;
        $city = $ip->city;
        $region = $ip->region;
        $message = 'Пользователь diod зашел в приложение ip: '. $userIp .' Город:'. $city . ' Регион:'. $region;
        $bot->sendMessage($message);

        return view('home', ["json" => json_decode(ObjectNotation::where("key", "sync")->first()->value)]);
    }
}
