@php
    /** @var \Illuminate\Support\Collection $flagged */
    /** @var \Illuminate\Support\Collection $byCategory */
    /** @var callable $isFlagged */
    $card = function ($p) use ($isFlagged) {
        $flag = $isFlagged($p);
        $before = trim(strip_tags((string) $p->description)) ?: '(fără descriere)';
        $after = trim((string) $p->description_draft) ?: '(fără draft)';
        ob_start(); ?>
        <div class="card {{ $flag ? 'flagged' : '' }}">
            <div class="head">
                <strong>{{ $p->name }}</strong>
                <span class="code">{{ $p->code ?: '—' }}</span>
                @if ($flag)<span class="badge">⚠️ sursă subțire</span>@endif
            </div>
            @if (! empty($p->specs))
                <div class="specs">
                    @foreach ($p->specs as $k => $v)
                        <span class="chip"><b>{{ $k }}:</b> {{ is_array($v) ? implode(', ', $v) : $v }}</span>
                    @endforeach
                </div>
            @else
                <div class="specs"><span class="chip empty">fără specs extrase</span></div>
            @endif
            <div class="cols">
                <div class="col before"><h4>Înainte (live)</h4><p>{{ $before }}</p></div>
                <div class="col after"><h4>După (draft AI)</h4><p>{!! nl2br(e($after)) !!}</p></div>
            </div>
        </div>
        <?php return ob_get_clean();
    };
@endphp
<!DOCTYPE html>
<html lang="ro"><head><meta charset="utf-8"><title>Review îmbogățire conținut</title>
<style>
  body{font-family:ui-sans-serif,system-ui,sans-serif;max-width:1100px;margin:2rem auto;padding:0 1rem;color:#1a1a1a;background:#fafaf9}
  h1{font-size:1.5rem} h2{margin-top:2.5rem;border-bottom:2px solid #1f6f78;padding-bottom:.3rem;color:#1f6f78}
  .summary{display:flex;gap:1rem;flex-wrap:wrap;background:#fff;border:1px solid #e7e5e4;border-radius:12px;padding:1rem}
  .summary div{font-size:.9rem} .summary b{display:block;font-size:1.4rem;color:#1f6f78}
  .card{background:#fff;border:1px solid #e7e5e4;border-radius:12px;padding:1rem;margin:1rem 0}
  .card.flagged{border-color:#f5b301;background:#fffdf5}
  .head{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap} .code{color:#8a857f;font-size:.85rem}
  .badge{background:#f5b301;color:#000;font-size:.7rem;padding:.1rem .5rem;border-radius:99px;font-weight:700}
  .specs{margin:.6rem 0;display:flex;gap:.4rem;flex-wrap:wrap}
  .chip{background:#eef4f5;border:1px solid #d7e6e8;border-radius:6px;padding:.15rem .5rem;font-size:.78rem}
  .chip.empty{background:#f3f2f0;color:#8a857f}
  .cols{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:.5rem}
  .col h4{margin:0 0 .3rem;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#8a857f}
  .col p{margin:0;font-size:.9rem;line-height:1.5}
  .before p{color:#57534e} .after{background:#f7faf9;border-radius:8px;padding:.6rem}
  .note{background:#e7f0f1;border-radius:8px;padding:.8rem;font-size:.9rem;margin:1rem 0}
  @media(max-width:700px){.cols{grid-template-columns:1fr}}
</style></head><body>
  <h1>Review îmbogățire conținut — Decor Urban</h1>
  <div class="summary">
    <div><b>{{ $summary['total'] }}</b> produse</div>
    <div><b>{{ $summary['cu_draft'] }}</b> cu draft AI</div>
    <div><b>{{ $summary['cu_specs'] }}</b> cu specs</div>
    <div><b>{{ $summary['flagged'] }}</b> sursă subțire (flag)</div>
    <div><b style="font-size:.95rem">{{ $summary['model'] }}</b> model</div>
  </div>
  <div class="note">
    <strong>Regula de aur:</strong> verifică în special că drafturile NU conțin dimensiuni/materiale
    inventate — doar ce apare în „Înainte" + chip-urile de specs. Cele galbene = sursă subțire
    (descriere generică de categorie, fără specs). Nimic nu e live până nu zici „promovează".
  </div>

  <h2>⚠️ Toate produsele cu sursă subțire ({{ $flagged->count() }})</h2>
  @forelse ($flagged as $p)
    {!! $card($p) !!}
  @empty
    <p>Niciun produs flag-uit.</p>
  @endforelse

  @foreach ($byCategory as $group)
    @if ($group['products']->isNotEmpty())
      <h2>{{ $group['category']->name }} — eșantion ({{ $group['products']->count() }})</h2>
      @if ($group['category']->intro)
        <div class="note"><strong>Intro categorie:</strong> {{ $group['category']->intro }}</div>
      @endif
      @foreach ($group['products'] as $p)
        {!! $card($p) !!}
      @endforeach
    @endif
  @endforeach
</body></html>
