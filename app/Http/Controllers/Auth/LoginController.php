<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Models\User;

class LoginController extends Controller {

    public function login(Request $request) {

        $this->isTooManyFailedAttempts();

        $this->validateLogin($request);

        $user = User::where('email', $request->email)
                ->first();

        if (!is_null($user)) {
            if ($user->disabled == 1) {
                throw ValidationException::withMessages([
                    $this->username() => [
                        'This account is currently disabled.'
                    ],
                ]);
            }

            $hashCheck = Hash::check($request->password, $user->password);

            if ($hashCheck) {
                Auth::login($user, 1);
            } else {
                $md5_password = md5($user->email . $request->password);

                if (strcmp($user->password, $md5_password) == 0) {
                    $user->old_password = $md5_password;
                    $user->password = Hash::make($request->password);

                    Auth::login($user, $request->remember);
                } else {                 
                    return $this->sendFailedLoginResponse($request);
                }
            }

            $token = self::token($user);

            $jwt = JWT::encode($token, JWT_KEY);

            $currentDate =  strtotime(date("Y-m-d H:i:s"));
            $experationDate = date("Y-m-d 23:59:59", strtotime("+1 month", $currentDate));

            $sessionId = Session::getId();

            UserSession::updateOrCreate([
                'user_id' => $user->user_id,
                'token' => $sessionId,
                'expired_at' => $experationDate
            ]);

            return $this->sendLoginResponse($request, $jwt);
        }

        return $this->sendFailedLoginResponse($request);

    }

    protected function sendLoginResponse(Request $request) {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $this->authenticated($request, $this->guard()->user());

        return [
            'success' => true,
            'redirectTo' => self::redirectView(null, false, false)
        ];
    }

    public function throttleKey() {
        return Str::lower(request('email')) . '|' . request()->ip();
    }

    public function isTooManyFailedAttempts() {

        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return false;
        }

        throw new Exception('Too many attempts. Please try again later');
    }

}

