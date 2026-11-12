<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Alumni Data Report</title>
    <style>
        @page { margin: 18pt; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 7pt; color: #111827; }
        h1 { margin: 0 0 4pt; font-size: 16pt; color: #800000; }
        p { margin: 0 0 12pt; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 0.5pt solid #d1d5db; padding: 4pt; vertical-align: top; overflow-wrap: anywhere; }
        th { background: #800000; color: #ffffff; text-align: left; font-size: 6pt; }
        .empty { padding: 18pt; text-align: center; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Alumni Data Report</h1>
    <p>{{ $title }}</p>
    <table>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th style="width: {{ [0 => 9, 1 => 3, 2 => 5, 3 => 5, 4 => 6, 7 => 11, 8 => 8, 9 => 8, 10 => 9, 11 => 5, 12 => 5, 13 => 5, 14 => 4, 15 => 5, 16 => 6, 17 => 6][$column] }}%">{{ $headings[$column] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($columns as $column)
                        <td>{{ $row[$column] }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ count($columns) }}">No alumni records match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
