<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #141B2E; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #4A5670; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #E7EAF1; }
        .summary { display: block; margin-top: 12px; }
        .badge { display: inline-block; background: #FFF1CF; color: #DD8F00; padding: 2px 8px; border-radius: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>KerjaKu &mdash; Laporan Mingguan</h1>
    <p class="muted">{{ $week_range[0] }} &ndash; {{ $week_range[1] }}</p>

    <div class="summary">
        <span class="badge">{{ intdiv($total_minutes, 60) }} jam {{ $total_minutes % 60 }} menit total</span>
        &nbsp; <span class="badge">{{ $photos_count }} foto</span>
    </div>

    <table>
        <thead>
            <tr><th>Proyek</th><th>Jam Kerja</th><th>Task Selesai</th></tr>
        </thead>
        <tbody>
            @forelse ($by_project as $row)
                <tr>
                    <td>{{ $row['project'] ?? '-' }}</td>
                    <td>{{ intdiv($row['minutes'], 60) }}j {{ $row['minutes'] % 60 }}m</td>
                    <td>{{ $row['tasks_completed'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Tidak ada aktivitas minggu ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
