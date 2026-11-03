<div class="text-start">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Preview: {{ $title }}</strong>
        <small class="text-muted">
            @if ($total > count($rows))
            Menampilkan {{ count($rows) }} dari {{ $total }} baris
            @else
            {{ $total }} baris
            @endif
        </small>
    </div>

    @if ($total === 0)
    <div class="text-muted text-center py-4">Tidak ada data untuk ditampilkan.</div>
    @else
    <div style="max-height: 55vh; overflow: auto; border: 1px solid #E2E8F0; border-radius: 6px;">
        <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                    <th style="position: sticky; top: 0; background: #F1F5F9; white-space: nowrap;">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                    <td>{{ $cell }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($total > count($rows))
    <div class="text-muted mt-2" style="font-size: 0.75rem;">
        Preview dibatasi {{ count($rows) }} baris pertama. File yang diunduh berisi seluruh {{ $total }} baris.
    </div>
    @endif
    @endif
</div>