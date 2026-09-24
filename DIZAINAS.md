# ALKURAS — svetainės dizaino sistema

Interaktyvus dizaino prototipas UAB „Alkuras“ svetainei ir el. parduotuvei.
Parengė **Simplex.lt · MB „At Solutions“** pagal 2026-08-14 komercinį pasiūlymą.

Dizaino kryptis paimta iš **Salient „eCommerce Robust“** demo (redakcinis minimalizmas,
pilno pločio vaizdai, didelė tipografija, santūrūs pabraukti CTA), pritaikyta sunkiajai pramonei.

---

## 1. Kaip paleisti

Prototipas — statiniai HTML failai. Nereikia jokio serverio, tačiau dėl `fetch`/vaizdų
patogiau paleisti per lokalų serverį:

```bash
cd alkuras && python3 -m http.server 8788
```

Tada naršyklėje atidarykite `http://localhost:8788/index.html`.
Failus galima atidaryti ir tiesiogiai (`index.html`) — viskas veikia be `file://` apribojimų.

**Puslapiai**

| Failas | Turinys | Atitikmuo WordPress'e |
|---|---|---|
| `index.html` | Pagrindinis — visa dizaino kalba | Front page (Salient / Elementor šablonas) |
| `karjerai.html` | Karjerų veikla, gavybos ciklas, lokacijų žemėlapis | Page + CPT `karjeras` |
| `griovimas.html` | Griovimo paslaugos + kainos skaičiuoklė | Page + shortcode `[alk_calc_demo]` |
| `smulkinimas.html` | Smulkinimo paslauga, eiga, rezultatas | Page |
| `produkcija.html` | Katalogas su filtru | WooCommerce shop archive |
| `preke.html` | Prekės puslapis (`?p=<id>`) | WooCommerce single product |
| `krepselis.html` | Krepšelis + pristatymo skaičiavimas | Woo cart / checkout |
| `skaiciuokles.html` | Abi skaičiuoklės vienoje vietoje | Page + shortcodes |
| `projektai.html` | Portfolio | CPT `projektas` |
| `apie.html` | Apie įmonę, technikos parkas, rekvizitai | Page |
| `kontaktai.html` | Kontaktai, forma, karjerų žemėlapis | Page + CF7 / WPForms |

Puslapiai surenkami iš `_parts/` (antraštė, poraštė) ir `_body/` (turinys) — `./build.sh`.
Redaguodami keiskite `_body/*.html`, po to paleiskite `./build.sh`.

---

## 2. Spalvos

| Rolė | Reikšmė | Naudojimas |
|---|---|---|
| `--ink` | `#0B0C0D` | pagrindinis tekstas, tamsios sekcijos |
| `--bone` | `#EFEBE3` | pagrindinis fonas (šiltas klinčių atspalvis) |
| `--bone-2` | `#E4DED3` | antrinės sekcijos |
| `--signal` | `#D8F250` | vienintelis akcentas — CTA, skaičiai, aktyvios būsenos |
| `--sand` / `--silt` | `#C6B296` / `#8A8478` | antriniai medžiagų tonai, pagalbinis tekstas |

**Veiklų spalvos** — kiekviena veikla turi savo akcentą, matomą hover metu:

| Veikla | Spalva |
|---|---|
| Karjerai | `#D9A521` (ochra) |
| Griovimas | `#D9541E` (rūdys) |
| Smulkinimas | `#D8F250` (signalas) |

Kontrastas: `--ink` ant `--bone` = 15.8:1; `--signal-ink` ant `--signal` = 9.4:1 (AAA).

## 3. Tipografija

| Rolė | Šriftas | Pastabos |
|---|---|---|
| Displėjus | **Archivo** (variable, `wdth` 75–125) | antraštės, skaičiai; naudojamas platusis ašies galas (`wdth` 104–125) |
| Tekstas | **Inter Tight** | pastraipos, formos |
| Duomenys | **JetBrains Mono** | frakcijos, kainos, etiketės, „eyebrow“ |

Visi trys palaiko lietuviškus rašmenis (ą č ę ė į š ų ū ž).
Dydžiai — `clamp()`, todėl skaluojasi be lūžio taškų: `--t-hero` iki 9.5rem, `--t-h2` iki 4rem.

## 4. Efektų biblioteka

Visi efektai gerbia `prefers-reduced-motion: reduce` — įjungus judesio mažinimą jie išjungiami.

**Hover**

1. **„Iškasimo" atidengimas** (`.exc`) — po pilku, tamsintu sluoksniu paslėptas tikrasis vaizdas
   ar video; pelė „iškasa" apvalią angą, kuri seka žymeklį (radialinė `mask-image` + rAF lerp).
   Naudojama: prekės galerijoje, gavybos cikle, griovimo dvinaryje.
2. **Video paleidimas užvedus** (`.act`) — veiklos panelėje nuotrauka pakeičiama video,
   spalvos grįžta iš pilkos, aprašymas išsiskleidžia, viršuje nubėga akcento linija.
3. **Sieto efektas** (`.prod`) — ant produkto nuotraukos iš viršaus „nukrenta" sieto tinklelis,
   antra nuotrauka persilieja, byra grūdeliai.
4. **3D pakrypimas su gyliu** (`data-tilt`) — kortelė pasisuka pagal žymeklį, vidiniai sluoksniai
   juda skirtingu `translateZ`, viršuje slysta blizgesys (`.tilt__sheen`).
5. **Raidžių ritinys** (`data-roll`) — meniu nuorodų raidės pakyla po vieną su kaskada.
6. **Magnetiniai mygtukai** (`data-magnet`) — mygtukas traukiasi link žymeklio su tamprumu.
7. **Technikos peržiūra** — užvedus ant technikos eilutės, šalia žymeklio išplaukia video langelis.
8. **Dvigubas pabraukimas** (`.ulink`) — linija išnyksta iš dešinės ir grįžta iš kairės.
9. **Užpildo mygtukai** (`.btn__fill`) — spalva pakyla iš apačios.
10. **Poraštės kontūrinis logotipas** — `-webkit-text-stroke` užsipildo užvedus.

**Paralaksas ir gylis**

11. **Sluoksniuotas hero** — video juda lėčiau už turinį (`data-px`), antraštė reaguoja į pelę
    (`data-mx`), virš viso to — dulkių dalelės (`.dust`) ir grūdėtumas (`.grain`).
12. **Paralakso scenos** (`.pxs`) — fono video slenka `data-px` greičiu po turiniu.
13. **Prilipusios pakopos** (`.steps`) — kortelės kraunasi viena ant kitos, apatinės mažėja
    ir tamsėja — matomas realus gylis.
14. **Video per tekstą** (`.vtext`) — video matomas tik per raides (`mix-blend-mode: lighten`).
15. **Plytelių atidengimas** (`.fitem__tiles`) — portfolio nuotraukos atsidengia 6×4 plytelėmis.
16. **Juosta pagal slinkties greitį** (`.mq`) — greičiau slenkant juosta įsibėgėja ir pasvyra.
17. **Sluoksniuoti šešėliai** (`--sh-1…--sh-lift`) — 3 sluoksnių šešėliai vietoj vieno.

**Kita**

18. Krovimo ekranas su „užuolaida", progreso juosta viršuje, individualus žymeklis su etikete,
    antraštė, slepiama slenkant žemyn, mega meniu, apskritimu atsidarantis mobilus meniu.

## 5. Skaičiuoklės (logika prototipe)

**Griovimo kaina**
```
kaina = plotas × konstrukcijos_įkainis × objekto_koeficientas
        + pamatų_ardymas (18 %)  + atliekų_išvežimas (4,20 €/m²)
        − nuolaida už smulkinimą vietoje (10 %)
```
Konstrukcijos įkainiai: medinė 9, mūrinė 14, gelžbetonis 22, mišri 17 €/m².
Objekto koeficientai: gyvenamasis 1,00 · ūkinis 0,85 · gamybinis 1,15 · inž. 1,30.
Rodomas ir nuokrypio intervalas (−12 % … +14 %) bei preliminari trukmė.

**Medžiagų kiekis**
```
m³   = plotas × storis/100 × tankinimo_koef.
tonos = m³ × medžiagos_tankis
```
Tankinimo koeficientai: 1,00 / 1,20 / 1,35. Rezultatas — tonos, m³ ir savivarčio reisų skaičius,
su mygtuku „Į krepšelį".

**Pristatymas pagal lokaciją**
```
kaina = reisai × 28 € (pakrovimas) + atstumas_km × 0,16 €/km/t × tonos
reisai = ceil(tonos / 25)
```
Miestas → artimiausias karjeras nustatomas iš `cities[].near`. **Realizacijoje** tai keičiama
Google Distance Matrix arba OSRM užklausa + karjerų zonų lentele.

> Visi įkainiai prototipe yra **demonstraciniai**. Prieš paleidimą juos reikia pakeisti
> tikrais Alkuras kainoraščiais (`assets/js/data.js`).

## 6. Perkėlimas į WordPress + WooCommerce

| Prototipo elementas | WP realizacija |
|---|---|
| Produkto kortelė | Woo product; frakcija, tankis, karjerai — produkto atributai |
| Kaina už toną | Woo kaina; matavimo vnt. per **Measurement Price Calculator** arba custom |
| Kiekio slankiklis | custom `add_to_cart` laukas (min/max/step iš atributų) |
| Pristatymas pagal lokaciją | custom Woo Shipping Method: adresas → artimiausias karjeras → įkainis |
| Karjerai | CPT `karjeras` (ACF: koordinatės, asortimentas, darbo laikas, kainoraštis) |
| Projektai | CPT `projektas` (ACF: kategorija, vieta, metai, faktai, galerija) |
| Technikos parkas | CPT `technika` (ACF: pavadinimas, specifikacija, nuotrauka, video) |
| Skaičiuoklės | 2 shortcode'ai; užklausa keliauja į CF7/WPForms su užpildytais laukais |
| Užklausų formos | Contact Form 7 arba WPForms + failų priedai + reCAPTCHA/honeypot |
| Mokėjimai | Paysera arba Montonio plugin + pavedimas (BACS) |
| Sąskaitos | pvz. „B1.lt" / „Sąskaitos.lt" integracija arba Woo PDF Invoices |

**Greitaveika.** Prototipe fono video tingiai kraunami (`IntersectionObserver`), už ekrano ribų
stabdomi, 720p naudojami visur, išskyrus hero. Realizacijoje video rekomenduojama laikyti
savo serveryje arba Bunny/Cloudflare Stream, o ne YouTube — kad nebūtų valdiklių ir sekimo.

## 7. Failų struktūra

```
alkuras/
├── index.html … kontaktai.html   ← paruošti puslapiai
├── assets/
│   ├── css/base.css              ← tokenai, tipografija, tinklelis, prieinamumas
│   ├── css/components.css        ← visi komponentai ir efektai
│   ├── js/app.js                 ← efektų variklis, krepšelis, formos
│   ├── js/page.js                ← turinio atvaizdavimas, skaičiuoklės
│   └── js/data.js                ← katalogas, karjerai, projektai, technika
├── _parts/ _body/                ← šaltiniai
├── build.sh                      ← surinkimas
└── DIZAINAS.md
```

## 8. Nuotraukos ir video

Visa vaizdinė medžiaga — **Pexels** (nemokama licencija, komerciniam naudojimui, be priskyrimo).
Ji naudojama **tik dizaino demonstracijai**. Prieš paleidimą rekomenduojama profesionali
fotosesija ir dronu filmuota medžiaga Alkuras karjeruose bei objektuose — tai vienintelis
dalykas, kurio šis dizainas negali „pasiskolinti".

## 9. Ką dar reikia patikslinti su klientu

- Tikras karjerų sąrašas, jų koordinatės, asortimentas ir darbo laikas
- Kainoraščiai pagal frakciją ir karjerą (ar kainos skiriasi tarp karjerų?)
- Pristatymo tarifai: fiksuotas reiso mokestis, €/km, minimalus kiekis
- Ar reikalingas atsiėmimo savo transportu srautas su laiko rezervacija
- Griovimo skaičiuoklės įkainiai ir kokie objektų tipai realiai vykdomi
- Logotipas vektoriniu formatu (prototipe naudojamas tekstinis ženklas)
- Įmonės rekvizitai patikslinimui: prototipe naudojami vieši duomenys
  (įm. k. 302312279, PVM LT100005138216, Medvalakio g. 27, Palanga; tel. +370 460 32097)
