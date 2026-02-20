<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Entities\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LookupUserByDniController extends Controller
{
    /**
     * Display the DNI lookup form (public, guest only).
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/LookupUserByDni', [
            'result' => [
                'found' => $request->session()->get('dni_lookup_found'),
                'email' => $request->session()->get('dni_lookup_email'),
            ],
        ]);
    }

    /**
     * Look up a user by DNI (id_number). Returns email if found, otherwise instructs to contact support.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'id_number' => ['required', 'string', 'max:50'],
        ]);

        $idNumber = trim($request->input('id_number'));
        $user = User::where('id_number', $idNumber)->first();

        if ($user) {
            return back()->with([
                'dni_lookup_found' => true,
                'dni_lookup_email' => $user->email,
            ]);
        }

        return back()->with([
            'dni_lookup_found' => false,
            'dni_lookup_email' => null,
        ]);
    }
}
