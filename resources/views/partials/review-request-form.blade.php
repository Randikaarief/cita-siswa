{{-- Kartu "Minta masukan mentor". Hanya tampil kalau ada mentor yang tersedia. --}}
@if ($mentors->isNotEmpty())
<div class="card card-pad space-y-4">
    <div>
        <h3 class="h3">Minta masukan mentor</h3>
        <p class="mt-1 text-xs text-muted">
            Mentor membantu secara sukarela, balasan tidak dijamin.
            Mentor yang kamu pilih bisa membuka berkas yang kamu centang, sampai dia membalas atau pengajuan ditutup.
        </p>
    </div>

    @if ($mySubmissions->isEmpty())
        <p class="text-sm">Kamu belum punya berkas. <a href="/settings/personal#berkas" class="link">Unggah CV atau esai dulu</a>.</p>
    @else
        <form action="{{ route('review-requests.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="target_type" value="{{ $targetType }}">
            <input type="hidden" name="target_id" value="{{ $targetId }}">

            <div>
                <label for="mentor_profile_id" class="label">Pilih mentor</label>
                <select id="mentor_profile_id" name="mentor_profile_id" required class="select">
                    <option value="" disabled selected>Pilih mentor alumni</option>
                    @foreach ($mentors as $mentor)
                        <option value="{{ $mentor->id }}" @selected((int) old('mentor_profile_id') === $mentor->id)>
                            {{ $mentor->nama }}{{ $mentor->is_sample ? ' (contoh, belum ada balasan)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <fieldset class="space-y-2">
                <legend class="label">Berkas yang dibagikan <span class="font-semibold text-muted">(pilih 1 sampai 3)</span></legend>
                @foreach ($mySubmissions as $submission)
                    <label class="flex items-center gap-2.5 rounded-xl border border-line px-3 py-2 text-sm hover:bg-brand-50 cursor-pointer">
                        <input type="checkbox" name="submissions[]" value="{{ $submission->id }}"
                               @checked(in_array($submission->id, (array) old('submissions', [])))
                               class="size-4 rounded border-line text-brand-500">
                        <span class="min-w-0 flex-1 truncate">{{ $submission->title }}</span>
                        <span class="badge {{ $submission->type === 'essay' ? 'badge-yellow' : 'badge-blue' }}">{{ $submission->type === 'essay' ? 'Esai' : 'CV' }}</span>
                    </label>
                @endforeach
            </fieldset>

            <div>
                <label for="note" class="label">Catatan <span class="font-semibold text-muted">(opsional)</span></label>
                <textarea id="note" name="note" rows="2" maxlength="500" class="textarea" placeholder="Contoh: tolong cek bagian pembuka esaiku">{{ old('note') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary w-full">Minta masukan</button>
        </form>
    @endif
</div>
@endif
