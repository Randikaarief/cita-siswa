<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Ganti password untuk mentor dan admin (wajib setelah login dengan password sementara). */
class PanelPasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('panel.password', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari yang sementara.',
        ]);

        $user = $request->user();
        $user->password = $data['password'];
        $user->must_change_password = false;
        $user->save();

        return redirect($user->homeUrl())->with('status', 'Password diganti.');
    }
}
