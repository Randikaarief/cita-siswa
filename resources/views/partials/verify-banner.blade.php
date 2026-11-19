@auth
    @if (auth()->user()->isStudent() && ! auth()->user()->hasVerifiedEmail() && ! request()->routeIs('verification.notice'))
        <div class="notice notice-warn flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between" role="status">
            <p>Email kamu belum diverifikasi. Verifikasi dulu supaya bisa mengunggah berkas dan minta masukan mentor.</p>
            <form method="POST" action="{{ route('verification.send') }}" class="shrink-0">
                @csrf
                <button type="submit" class="btn btn-soft btn-sm">Kirim ulang email</button>
            </form>
        </div>
    @endif
@endauth
