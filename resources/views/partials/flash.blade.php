@if (session('status'))
    <div class="notice notice-info" role="status">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="notice notice-error" role="alert">
        <ul class="space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
