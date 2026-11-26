<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    private function recordActivity(?int $userId, string $action, string $description): void
    {
        if (!$userId) {
            return;
        }

        try {
            if (Schema::hasTable('activity_logs')) {
                $data = [
                    'user_id'    => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('activity_logs', 'action')) {
                    $data['action'] = $action;
                }
                if (Schema::hasColumn('activity_logs', 'activity')) {
                    $data['activity'] = $description;
                }
                if (Schema::hasColumn('activity_logs', 'description')) {
                    $data['description'] = $description;
                }
                if (Schema::hasColumn('activity_logs', 'details')) {
                    $data['details'] = $description;
                }
                if (Schema::hasColumn('activity_logs', 'ip_address')) {
                    $data['ip_address'] = request()->ip();
                }

                DB::table('activity_logs')->insert($data);
            }
        } catch (\Throwable $e) {
            // Abaikan jika gagal mencatat log agar login/logout tetap lancar
        }
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->recordActivity($user->id, 'Register', 'Mendaftarkan akun baru & masuk ke sistem');

        return redirect('/dashboard');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $this->recordActivity(Auth::id(), 'Login', 'Login ke aplikasi GameBacklog');

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        $this->recordActivity($userId, 'Logout', 'Logout dari aplikasi GameBacklog');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
