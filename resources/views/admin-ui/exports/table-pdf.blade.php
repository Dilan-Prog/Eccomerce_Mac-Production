<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; color: #111a2b; font-size: 11px; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #6d7a8d; font-size: 10px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e7f0; padding: 5px 7px; text-align: left; }
        th { background: #fafbfd; font-weight: 600; }
        tr:nth-child(even) td { background: #fafbfd; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Exportado el {{ $generatedAt }} · {{ count($rows) }} registro(s)</div>
    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
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
</body>
</html>
