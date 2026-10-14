<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
            ],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where(
            'username',
            $credentials['username']
        )->first();

        if (
            !$user ||
            !$user->is_active ||
            !Hash::check(
                $credentials['password'],
                $user->password
            )
        ) {
            AuditLog::create([
                'user_id' => $user?->id,
                'action' => 'login_failed',
                'entity_type' => 'User',
                'entity_id' => $user?->id,
                'description' => 'Percobaan login gagal.',
            ]);

            return back()
                ->withInput(
                    $request->only('username')
                )
                ->withErrors([
                    'username' =>
                        'Username atau password salah.',
                ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login_success',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'description' => 'Admin berhasil login.',
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'entity_type' => 'User',
                'entity_id' => $user->id,
                'description' => 'Admin logout.',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}