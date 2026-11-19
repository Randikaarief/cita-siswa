<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScholarshipController as PublicScholarshipController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Daftar siswa: HANYA nama dan email. Tidak ada data pribadi, berkas, atau riwayat. */
class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $like = '%'.PublicScholarshipController::escapeLike($q).'%';

        $items = User::query()
            ->select('id', 'name', 'email', 'created_at')
            ->where('role', User::ROLE_USER)
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', $like)->orWhere('email', 'like', $like)))
            ->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.students', ['items' => $items, 'q' => $q]);
    }

    /** Tidak ada fitur lupa password, jadi admin yang mereset. */
    public function resetPassword(Request $request, int $student): RedirectResponse
    {
        $user = User::where('role', User::ROLE_USER)->findOrFail($student);

        $data = $request->validate(['password' => ['required', Password::min(8)]]);

        $user->password = $data['password'];
        $user->setRememberToken(null);
        $user->save();

        return back()->with('status', 'Password '.$user->name.' diatur ulang. Sampaikan password barunya ke siswa.');
    }
}
