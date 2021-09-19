<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Events\Remind;

/**
 * @group Authentication
 */
class VerificationController extends Controller
{
    /**
     * Verify Email Address
     * 
     * Verify the email address of a user with specified "id".
     * This route will not be called in the frontend.
     * It will be contained in the verification email.
     * Once the user email is verified, it will be directed to 
     * a specific front end screen specified by "EMAIL_VERIFY_SUCCESS_URL" env variable.
     *
     * @urlParam id int required The ID of the user. Example: 6
     * @response a specific front-end screen specified by "EMAIL_VERIFY_SUCCESS_URL" env variable.
     * 
     * @unauthenticated
     */
    public function verify(Request $request) {
        $userID = $request['id'];

        $user = User::findOrFail($userID);

        $date = date("Y-m-d g:i:s");
        $user->email_verified_at = $date;
        $user->save();

        return redirect(env('EMAIL_VERIFY_SUCCESS_URL'));
    }

    /**
     * Resend Verification Email
     * 
     * Resend verification email to the user email address.
     *
     * @urlParam id int required The ID of the user. Example: 6
     * @response {
     *  "data": {
     *      "message": ["The notification has been resubmitted"]
     *  }
     * }
     * @response 422 scenario="Email already verified" {
     *  "status": "error",
     *  "errors": {
     *      "message": ["Your email have alreay been verified!"]
     *  }
     * }
     */
    public function resend(Request $request) {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'message' => [ __('auth.resend_verify_email_denied') ]
                ]
            ], 422);
        }
        
        Remind::dispatch($user);

        return [
            'data' => [
                'message' => __('auth.resend_verify_email_success')
            ]
        ];
    }
}
