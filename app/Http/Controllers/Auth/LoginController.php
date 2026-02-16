<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * index
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // return inertia page Auth/Login
        return inertia('Auth/Login');
    }

    /**
     * store
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // set validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // get email and password from request
        $credentials = $request->only('email', 'password');

        // attempt to login
        if (auth()->attempt($credentials)) {

            // Cek apakah user aktif
            if (!auth()->user()->is_active) {
                // Logout immediately
                auth()->logout();

                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect back with error
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ])->withInput($request->only('email'));
            }

            // regenerate session
            $request->session()->regenerate();

            // redirect route dashboard dengan flash message success
            return redirect()->route('dashboard')
                ->with('success', 'Selamat Datang Kembali!');
        }

        // if login fails
        return back()->withErrors([
            'email' => 'Informasi akun yang Anda masukkan tidak cocok dengan data kami.',
        ]);
    }

    /**
     * logout
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // logout user
        auth()->logout();

        // invalidate session
        $request->session()->invalidate();

        // regenerate token
        $request->session()->regenerateToken();

        // redirect ke halaman login
        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar.');
    }
}