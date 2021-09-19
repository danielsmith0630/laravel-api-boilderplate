<?php

namespace App\Http\Controllers;

use App\Events\Registered;
use App\Http\Requests\Auth\{
    RegisterRequest,
    ForgotPasswordRequest,
    ResetPasswordRequest
};
use App\Mail\ResetPassword;
use App\Models\{
    User,
    PasswordReset
};
use Spatie\QueryBuilder\{
  QueryBuilder,
  AllowedFilter
};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Hash,
    Mail
};
use Illuminate\Support\Str;

/**
 * @group Authentication
 *
 * APIs for authentication
 */
class AuthController extends Controller
{
    /**
     * Register
     *
     * Register a new user with name, email and password and send a verification email.
     *
     * @responseFile responses/Auth/register.json
     * @unauthenticated
     */
    public function register(RegisterRequest $request) {
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());

        Registered::dispatch($user);

        return $this->jsonItem(
            $user->fresh()
        );
    }

    /**
     * Get User
     *
     * Get the authenticated user
     *
     * @responseFile responses/Auth/user.json
     */
    public function getUser(Request $request) {
        return $this->jsonItem(
            QueryBuilder::for(User::class)
                ->allowedIncludes([
                    'profile',
                    'setting',
                    'privacySetting',
                    'linkedSocialAccounts',
                    'createdSpaces',
                    'ownedSpaces',
                    'spaces',
                    'createdChannels',
                    'ownedChannels',
                    'channels'
                ])
                ->allowedFilters(User::getFields())
                ->findOrFail($request->user()->id)
        );
    }

    /**
     * Logout
     *
     * revoke the current access_token and refresh_token(s)
     *
     * @response {
     *  "message": "You have been successfully logged out!"
     * }
     */
    public function logout(Request $request) {
        $token = $request->user()->token();
        $token->revoke();

        return [
            'message' => __('auth.logout_success')
        ];
    }

    /**
     * Forgot Password
     *
     * Send Reset Password email to the user with the specified email address
     *
     * @response {
     *  "status": "success"
     * }
     * @unauthenticated
     */
    public function forgot(ForgotPasswordRequest $request) {
        $user = User::where('email', $request->email)->first();

        $token = md5(time() . $request->email);

        $pReset = PasswordReset::create([
            'email' => $request->email,
            'token' => $token
        ]);

        Mail::to($user->email)->send(new ResetPassword($user, $token));

        return [
            'status' => 'success'
        ];
    }

    /**
     * Reset Password
     *
     * Check the token validity and reset the user password
     *
     * @response {
     *  "status": "success"
     * }
     * @response 403 scenario="token expired" {
     *  "status": "error",
     *  "errors": {
     *      "token": ["Token was expired."]
     *  }
     * }
     * @unauthenticated
     */
    public function resetPassword(ResetPasswordRequest $request) {
        $pr = PasswordReset::where("token", $request->token)->first();

        $time = Carbon::now()
            ->subHours(config('mail.reset_password.expires_in_hours'))
            ->format('Y-m-d H:i:s');

        if ($time > $pr->created_at) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'token' => [ 'Token was expired.' ]
                ]
            ], 403);
        }

        $user = User::where('email', $pr->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        PasswordReset::where('email', $user->email)->delete();

        return [
            'status' => 'success'
        ];
    }
}
