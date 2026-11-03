<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 28px 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #1E293B;
        }

        h2 {
            margin: 0 0 2px 0;
            font-size: 14px;
            color: #1E3A5F;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 8px;
            color: #64748B;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #1E3A5F;
            color: #FFFFFF;
            text-align: left;
            padding: 5px 4px;
            border: 1px solid #1E3A5F;
        }

        td {
            padding: 4px;
            border: 1px solid #E2E8F0;
            vertical-align: top;
            word-wrap: break-word;
        }

        tr:nth-child(even) td {
            background-color: #F8FAFC;
        }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>
    <div class="meta">SIMEWO - Dinas Komunikasi dan Informatika Kabupaten Tapin | Dicetak: {{ $printedAt }} | Total: {{ $total }} baris</div>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                @foreach ($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headings) }}" style="text-align: center;">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>