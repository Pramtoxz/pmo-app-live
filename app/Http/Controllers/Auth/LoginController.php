<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private function generateCaptcha(): void
    {
        $a = rand(1, 9);
        $b = rand(1, 9);
        session(['captcha_answer' => $a + $b, 'captcha_question' => "{$a} + {$b}"]);
    }

    public function showLoginForm()
    {
        $this->generateCaptcha();
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'   => 'required|email',
            'password' => 'required|string',
            'captcha' => 'required|integer',
        ]);

        if ((int) $request->captcha !== (int) session('captcha_answer')) {
            $this->generateCaptcha();
            throw ValidationException::withMessages([
                'captcha' => ['Jawaban verifikasi salah.'],
            ]);
        }

        session()->forget(['captcha_answer', 'captcha_question']);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is admin
            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->with('error', 'Anda tidak memiliki akses ke halaman admin.');
            }

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang, ' . $user->name . '!');
        }

        return back()->with('error', 'Email atau password salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showKebijakanPrivasi(){
        return view('auth.kebijakan_privasi');
    }
}
