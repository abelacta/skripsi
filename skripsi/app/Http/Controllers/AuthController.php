<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function masukCustomer()
    {
        return view('masukCustomer');
    }

    public function tampilanLoginAdmin()
    {
        return view('auth.login');
    }

    public function LoginAdmin(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // Retrieve the user by the provided username
        $user = User::where('username', $credentials['username'])->first();

        // Check if the user exists and the provided password matches
        if ($user && $user->password === $credentials['password']) {
            // User is authenticated, log them in
            // Note: This is NOT secure in production. Use proper password encryption like bcrypt.
            auth()->login($user);

            // Redirect the user to the desired location after login
            return redirect()->route('woTable', ['title' => 'BMW OFFICE']);
        } else {
            // Authentication failed, redirect back to the login page with an error message.
            return redirect()->route('login_admin')->with('error', 'Invalid username or password.');
        }
    }

    public function logoutAdmin(Request $request)
    {
        Auth::logout();
        return redirect('/login/admin');
    }

    public function loginCustomer(Request $request)
    {
        $noPol = $request->input('no_polisi');

        // Find the user by the given no_polisi
        $user = \App\Models\User::where('no_polisi', $noPol)->first();

        if (!$user) {
            // If the user is not found, redirect back with an error message
            return redirect()->back()->withInput()->with('error', 'User not found.');
        }

        // Authenticate the user without requiring a password
        Auth::login($user);

        // Check if the authenticated user has the role 'user'
        if (auth()->user()->role === 'customer') {
            // Authentication successful for a regular user
            return redirect()->route('indexOnBooking', $noPol); // Replace 'user.dashboard' with your user dashboard route name
        } else {
            // Authentication successful, but the user is not a regular user

            Auth::logout();
            return redirect()->back()->withInput()->with('error', 'You are not a regular user.');
        }
    }


    public function logoutCustomer(Request $request)
    {
        Auth::logout();
        return redirect('/');
    }
}
