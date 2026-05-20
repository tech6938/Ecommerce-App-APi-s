<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Mail};
use App\Models\{Role, User};

class AuthController extends Controller
{

    function dashboard()
    {
        return view('dashboard');
    }
    // signup
    public function signup()
    {
        $role = Role::all();
        return view('auth.signup', compact('role'));
    }
    //insert signup
    public function insert_signup(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required',
            'password' => 'required|confirmed|max:8',
        ]);

        $insert = User::create([
            'name' => $request->first_name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);
        if ($insert) {
            auth()->login($insert);
            return redirect()->route('dashboard');
        }
    }
    // login
    public function login()
    {
        return view('auth.login');
    }
    // math login
    public function match_login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|max:8',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('dashboard');
        }

        return redirect()->back()->with('error', 'Admin Not Found');
    }
    // logout
    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect()->route('login')->with('info', 'Are you sure you want to logout?');
    }
    // forgot
    public function forget()
    {
        return view('auth.forget');
    }
    // forgot password message ====
    public function forget_message(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
        ]);

        $message = random_int(1000, 9999);
        $messageText = (string) $message;

        // Store OTP in session
        session(['otp' => $message, 'otp_email' => $request->email]);

        Mail::raw($messageText, function ($mail) use ($request) {
            $mail->to($request->email)
                ->subject('Direct Message');
        });

        return redirect()->route('otp')->with('success', 'Message sent successfully');
    }

    // otp
    function otp()
    {
        return view('auth.otp');
    }
    public function matching_route(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $sessionOtp = session('otp');
        $email = session('otp_email');

        if ($request->otp == $sessionOtp) {
            // OTP is correct, clear it from session
            session()->forget(['otp']);

            // Redirect to reset password page
            return redirect()->route('reset')->with('success', 'OTP verified successfully');
        } else {
            return redirect()->back()->with('error', 'Invalid OTP');
        }
    }


    // reset
    public function reset()
    {
        return view('auth.reset');
    }
    public function update_password(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|max:8',
        ]);

        $email = session('otp_email'); // email stored during OTP

        if (!$email) {
            return redirect()->route('forget')->with('error', 'Session expired, please try again.');
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            // Clear OTP session just in case
            session()->forget(['otp', 'otp_email']);

            return redirect()->route('login')->with('success', 'Password reset successfully!');
        }

        return redirect()->route('login')->with('error', 'User not found.');
    }
}
