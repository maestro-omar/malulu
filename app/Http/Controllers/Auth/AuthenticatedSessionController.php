<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\UserContextService;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'rememberedEmail' => request()->cookie('remembered_email'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $response = redirect()->intended(RouteServiceProvider::HOME);

        // Set remember me cookie for email if remember is checked
        if ($request->boolean('remember')) {
            $response->cookie('remembered_email', $request->email, 60 * 24 * 30); // 30 days
        } else {
            // Clear the cookie if remember is not checked
            $response->cookie('remembered_email', '', -1);
        }

        return $response;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        UserContextService::forget();
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $response = redirect('/');

        // DO NOT Clear the remembered email cookie on logout
        // $response->cookie('remembered_email', '', -1);

        return $response;
    }
}
