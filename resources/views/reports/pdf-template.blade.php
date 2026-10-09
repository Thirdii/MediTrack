<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }} — MediTrack</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18px; color: #059669; }
        .header p { margin: 2px 0; color: #6b7280; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 9px; font-weight: 600; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #d1d5db; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #d1d5db; text-align: center; color: #9ca3af; font-size: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MediTrack — {{ $reportTitle }}</h1>
        <p>Pharmacy: {{ $pharmacyName }}</p>
        @if(isset($dateFrom) && isset($dateTo))
            <p>Period: {{ $dateFrom }} to {{ $dateTo }}</p>
        @endif
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    @if(count($rows) > 0)
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($rows[0]) as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #6b7280; padding: 40px 0;">No data available for this report.</p>
    @endif

    <div class="footer">
        MediTrack Pharmacy Inventory Management System · Report generated automatically
    </div>
</body>
</html>
