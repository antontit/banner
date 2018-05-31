<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use App\Entity\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    use ThrottlesLogins;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        $aunthenticate = Auth::attempt([
            $request->only(['email', 'password']),
            $request->filled('remember')
        ]);

        if ($aunthenticate) {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            $user = Auth::user();
            if (!$user->status !== User::STATUS_ACTIVE) {
                Auth::logout();
                return back()->with('error', 'You need to confirm your account. Please check email.');
            }
            return redirect()->intended(route('cabinet'));
        }

        $this->incrementLoginAttempts($request);
        throw ValidationException::withMessages(['email' => [trans('auth.failed')]]);
    }

    public function logout(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        return redirect()->route('home');
    }

    protected function username()
    {
        return 'name';
    }
}
