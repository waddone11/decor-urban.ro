<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Ops runner</title>
    <style>
        body { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; max-width: 720px; margin: 2rem auto; padding: 0 1rem; color: #1a1a1a; background: #fafafa; }
        h1 { font-size: 1.2rem; }
        .warn { background: #fff4e5; border: 1px solid #ffb74d; padding: .75rem 1rem; border-radius: 6px; font-size: .85rem; }
        ul { list-style: none; padding: 0; }
        li { margin: .35rem 0; }
        a { color: #0a58ca; text-decoration: none; }
        a:hover { text-decoration: underline; }
        code { background: #eee; padding: .1rem .35rem; border-radius: 4px; font-size: .85rem; }
        .danger a { color: #b02a37; font-weight: 700; }
        .muted { color: #777; font-size: .8rem; }
    </style>
</head>
<body>
    <h1>⚙️ Ops runner</h1>
    <div class="warn">
        Plasă pentru hosting fără SSH. Rulează comenzi artisan din whitelist.
        <strong>Dezactivează</strong> (<code>OPS_ENABLED=false</code>) și rotește token-ul când termini.
    </div>

    <ul>
        @foreach ($commands as $key => $cmd)
            @php $isDanger = in_array($key, $destructive, true); @endphp
            <li @class(['danger' => $isDanger])>
                <a href="{{ url('/ops/'.$key).'?token='.urlencode($token).($isDanger ? '&confirm=YES' : '') }}">
                    {{ $key }}
                </a>
                <span class="muted">→ <code>php artisan {{ $cmd }}</code></span>
                @if ($isDanger)
                    <span class="muted">⚠️ distructiv — necesită confirm=YES</span>
                @endif
            </li>
        @endforeach
    </ul>
</body>
</html>
