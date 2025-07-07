<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Generate 6-digit OTP as string
        $otp = (string) rand(100000, 999999);

        // Hash OTP before saving
        $hashedOtp = Hash::make($otp);

        // Create or update the OTP record
        PasswordReset::updateOrCreate(
            ['email' => $request->email],
            [
                'code_hash' => $hashedOtp,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Send OTP email (using Laravel mail)
        $userEmail = $request->email;
        Mail::raw("Your password reset OTP is: $otp. It expires in 10 minutes.", function ($message) use ($userEmail) {
            $message->to($userEmail)
                    ->subject('Email Verification OTP');
        });

        return response()->json(['message' => 'OTP sent to your email.', 'email' => $request->email]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:password_resets,email',
            'otp' => 'required|numeric|digits:6',
        ]);

        $email = $request->email;
        $otp = $request->otp;

        // Fetch OTP record
        $record = PasswordReset::where('email', $email)->first();

        if (!$record || !Hash::check($otp, $record->code_hash)) {
            return response()->json(['message' => 'Invalid OTP or email.'], 400);
        }

        // Check if the OTP is expired (10 minutes)
        if ($record->updated_at->diffInMinutes(now()) > 10) {
            return response()->json(['message' => 'OTP has expired.'], 400);
        }

        $token = Str::random(60);
        $hash = bcrypt($token);
        PasswordReset::updateOrCreate(
            ['email' => $request->email],
            [
                'token' => $hash,
            ]
        );

        return response()->json(['message' => 'OTP verified successfully.', 'token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'email' => 'required|email|exists:password_resets,email',
        ]);

    
        $value = PasswordReset::where('email', $request->email )->firstOrFail();
        
        if (!$value) {
            return response()->json(['message' => 'Invalid email.'], 400);
        }

        if (!Hash::check($request->token, $value->token)) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        User::where('email', $value->email)->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
