# Audit date pentru filtre de listare

> Generat pe 21.07.2026 din dump-ul de producție (`decorurb_baza.sql`, 127 produse active,
> 11 categorii active). Doar analiză — nicio modificare de date sau cod.
> Sursa: `products.specs` (chei existente: `material`, `dimensiuni`, `montaj`, `finisaj`) + coloane.

## Tabel sinteză

| Filtru candidat | Acoperire | Nr. valori / format | Viabil? | Efort | Recomandare |
|---|---|---|---|---|---|
| **Material** | 113/127 (89%) | 6 valori curate | **DA** | Mic (facete există deja în /catalog) | **Implementează acum** |
| **Categorie** | 127/127 (100%) | 11 categorii | DA | Zero (există) | Păstrează |
| **Promoție** („doar reduceri") | 8/127 (6%) | boolean (`hasSalePrice`) | Da, dar nișat | Mic | Acum, ca **toggle**, nu facetă |
| **Preț (interval)** | 10/127 (8%) | 579–1.159 lei, doar bănci | NU încă | Mic tehnic, dar irelevant | **După** ce mai multe produse au preț |
| **Dimensiuni** | 41/127 (32%) | 19 valori unice, 3 formate | **NU** | Mare + date greșite semantic | **Nu implementa** (vezi detalii) |
| **Montaj** | 28/127 (22%) | 1 (una!) valoare | NU | — | Nu — o singură valoare nu filtrează nimic |
| **Finisaj** | 15/127 (12%) | 2 valori | NU | — | Nu — acoperire prea mică |
| **SEAP / transport gratuit** | 0% | coloanele nu există | NU | — | Nu există date; de colectat întâi |

## 1. Material — filtrul câștigător

Acoperire 89%, valori deja normalizate de extracția de specs (nu e nevoie de curățare):

| Valoare | Produse |
|---|---|
| metal | 101 |
| lemn | 71 |
| beton | 28 |
| alucobond | 7 |
| inox | 4 |
| policarbonat | 3 |

(Un produs poate avea mai multe materiale — ex. „lemn + metal".)

**Verdict: viabil acum.** Facetele de material există deja în `/catalog` (CatalogBrowser); efortul
real e doar extinderea pe paginile de categorie, dacă se dorește. Cele 14 produse fără material
rămân vizibile când nu e activ niciun filtru.

## 2. Dimensiuni — capcană semantică, nu implementa

Acoperire 32% (41 produse, concentrate în Bănci 31/47; Pergole, Stații, Locuri de joacă = zero).
19 valori unice, 3 formate: `LxlxH` (41%), `Φ diametru` (39%), `Lxl` (20%). Toate valorile brute:

```
Φ26,9x2,6mm · Φ48x2,6mm · 40x40x2mm · 40x20mm · 40x40mm · 30x30mm · 20x20x2mm
1800x90x45mm · 900x1800x450mm · 950x2000x450mm · 800x1800x450mm · 950x1800x450mm
450x1880x400mm · 1000x400mm · 1600x500mm · 2000x1000mm · 2000x2000mm · 3000x1200mm · 4500x1200mm
```

**Problema nu e parsarea — e sensul.** Valorile parsează tehnic aproape 100%, dar majoritatea
descriu **profile de material, nu gabaritul produsului**:

- `Φ48x2,6mm` = țeava picioarelor (diametru × grosime perete) — apare la 13 bănci;
- `40x40x2mm` = profilul cadrului metalic;
- `1800x90x45mm` = lamela de lemn a șezutului, nu banca întreagă.

Doar ~11 valori arată ca dimensiuni reale de produs (băncile `900x1800x450`, totemurile
`3000x1200`, taraba `2000x1000`). Un filtru „lungime < 1,5 m" ar băga banca cu țeavă Φ48 la
„sub 5 cm" sau ar amesteca lamela cu gabaritul — **rezultate greșite garantat**.

**Verdict: nerealist acum ȘI după parsare.** Parsarea nu poate distinge automat „profil" de
„gabarit" — distincția nu există în date. Singura cale onestă: câmpuri noi dedicate
(`lungime/lățime/înălțime` produs) completate manual sau extrase din sursă cu validare umană,
apoi filtru pe intervale. Până atunci, dimensiunile rămân ce sunt: informație de fișă tehnică.

## 3. Montaj / Finisaj — date insuficiente

- **Montaj**: 28/127 (22%) și **o singură valoare** — „fixare în beton" (28×). Un filtru cu o
  opțiune nu filtrează nimic. Nu.
- **Finisaj**: 15/127 (12%) — „vopsit" (15), „zincat" (4, unele ambele). Listă scurtă și curată,
  dar acoperirea de 12% ar ascunde 88% din catalog la orice selecție. Nu acum; re-evaluează dacă
  extracția de specs se îmbogățește.

## 4. Preț, promoție, flags B2B

- **Preț real afișabil**: 10/127 (8%) — toate bănci: B202 (629), B203 (579), B204 (1.159→999),
  B209 (749→649), B235–B240 (1.159→999). Interval 579–1.159 lei, median 999.
  Un slider de preț global ar afișa 8% din catalog; în categoria Bănci ar fi 10/47. Prematur.
- **Promoție**: 8 produse cu `sale_price` valid. Ca facetă e slab, dar ca **toggle „Doar
  promoții"** e ieftin, onest și util comercial chiar și cu 8 produse.
- **SEAP / transport gratuit**: nu există nici coloane, nici chei în specs. Fără date, fără filtru.

### Observație colaterală (nu ține de filtre, dar am văzut-o în date)

Toate cele 127 de produse au `quote_only=1` și `feed_enabled=0` — inclusiv cele 10 cu preț real și
`price_on_request=0`. Adică produsele cu preț sunt afișate pe site, dar **excluse din feed-urile
Google/Meta** de flag-uri. Dacă vrei băncile cu preț în Merchant Center: bifează `feed_enabled` +
debifează `quote_only` pentru cele 10, din Filament (validarea există deja).

## Recomandare finală

**Acum (date curate, efort mic):**
1. **Material** — facetă multi-select; datele-s gata (89% acoperire, 6 valori).
2. **Toggle „Doar promoții"** — boolean pe `hasSalePrice`, zero curățare de date.
3. Categoria rămâne filtrul principal (există).

**După normalizare / mai multe date:**
- **Preț (interval)** — abia când >30–40% din produse au preț real; tehnic banal, azi inutil.
- **Finisaj** — doar dacă extracția de specs ajunge la >50% acoperire.

**Nu merită (spus sincer):**
- **Dimensiuni** — datele existente descriu profile de material, nu gabarit; orice filtru ar
  minți. Necesită câmpuri noi + completare manuală înainte să devină subiect.
- **Montaj** — o singură valoare distinctă în toată baza.
- **SEAP / transport gratuit** — nu există date deloc.
