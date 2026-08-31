<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Tentativo di login dal backend: consentito solo agli utenti con
     * app = false. Gli utenti registrati dalla webapp (app = true) possono
     * accedere esclusivamente dalla webapp.
     */
    protected function attemptLogin(Request $request)
    {
        $user = User::where($this->username(), $request->input($this->username()))->first();

        if ($user && $user->app) {
            return false;
        }

        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    /**
     * Risposta in caso di login fallito: messaggio specifico se l'account
     * esiste ma è un utente della webapp (app = true).
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::where($this->username(), $request->input($this->username()))->first();

        $message = ($user && $user->app)
            ? 'Questo account non è abilitato all\'accesso dal backend.'
            : trans('auth.failed');

        throw ValidationException::withMessages([
            $this->username() => [$message],
        ]);
    }
}

