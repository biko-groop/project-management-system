<html xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        td, th { border: 1px solid #cbd5e1; padding: 6px 10px; font-family: 'Cairo', Tahoma, Arial; font-size: 12px; mso-number-format:"\@"; }
        th { background-color: #4f46e5; color: #ffffff; font-weight: bold; text-align: center; }
        .title { background-color: #eef2ff; color: #4338ca; font-weight: bold; font-size: 15px; text-align: center; }
        .meta { background-color: #f8fafc; color: #64748b; font-size: 11px; text-align: center; }
        td { text-align: right; }
    </style>
</head>
<body>
    @php $cols = max(count($report['headers']), 1); @endphp
    <table dir="rtl" border="1">
        <tr><td class="title" colspan="{{ $cols }}">نظام إدارة المشاريع — {{ $report['title'] }}</td></tr>
        <tr><td class="meta" colspan="{{ $cols }}">تاريخ التقرير: {{ now()->format('Y-m-d H:i') }} — عدد السجلات: {{ count($report['rows']) }}</td></tr>
        <tr>
            @foreach ($report['headers'] as $h)
                <th>{{ $h }}</th>
            @endforeach
        </tr>
        @foreach ($report['rows'] as $row)
            <tr>
                @foreach ($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>
