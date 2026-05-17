<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        $title = 'login';
        return view('auth.login', compact('title'));
    }

    /**
     * =========================================================================
     * TAMBAHAN METHOD BARU: Halaman Login Pegawai (desain berbeda)
     * =========================================================================
     */
    public function showLoginPegawai()
    {
        $title = 'Login Pegawai';
        return view('auth.login-pegawai', compact('title'));
    }

    public function loginAction(Request $request)
    {
        $request->validate([
            'login' => 'required', // Bisa email atau nip
            'password' => 'required',
        ]);

        // Cek apakah input login berupa email atau nip
        $loginType = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        // Ambil hanya login dan password
        $credentials = [
            $loginType => $request->input('login'),
            'password' => $request->input('password'),
        ];

        try {
            if (!Auth::attempt($credentials)) {
                return back()->with('error', 'Invalid email, NIP, or password');
            }

            return redirect()->route('dashboard');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function register()
    {
        $title = 'register';
        return view('auth.register', compact('title'));
    }

    public function registerAction(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nip' => 'required|unique:pegawai',
            'jabatan' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $pegawai = Pegawai::create([
            'nama' => $request->name,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'foto' => null,
            'tanggal_lahir' => null,
            'is_pejabat' => 0,
            'jenis_pejabat' => null,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
            'pegawai_id' => $pegawai->id,
            'role' => 'admin',
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $role = Auth::user()->role;
        Auth::logout();
        
        // Redirect berdasarkan role
        if ($role === 'admin') {
            return redirect()->route('login');
        } else {
            return redirect()->route('login.pegawai');
        }
    }
}