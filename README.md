# ALKURAS — svetainės dizaino prototipas

Interaktyvus dizaino prototipas UAB „Alkuras“ svetainei ir el. parduotuvei
(karjerai · griovimo darbai · statybinių atliekų smulkinimas).

**Peržiūra:** https://arvydastry.github.io/alkuras/

Parengė **Simplex.lt · MB „At Solutions“** pagal 2026-08-14 komercinį pasiūlymą.
Dizaino kryptis — Salient „eCommerce Robust“, pritaikyta sunkiajai pramonei.

## Kas čia yra

11 puslapių statinis prototipas: pagrindinis, trys veiklų puslapiai, produkcijos katalogas
su filtru, prekės puslapis, krepšelis su pristatymo skaičiavimu, dvi interaktyvios
skaičiuoklės, portfolio, apie įmonę ir kontaktai.

Veikianti logika: griovimo kainos ir medžiagų kiekio skaičiuoklės, artimiausio karjero
parinkimas pagal miestą, krepšelis (`localStorage`).

Išsami dizaino sistemos, efektų ir perkėlimo į WordPress + WooCommerce dokumentacija —
[`DIZAINAS.md`](DIZAINAS.md).

## Paleidimas lokaliai

```bash
python3 -m http.server 8788
```

Puslapius redaguokite `_body/` kataloge, po to paleiskite `./build.sh` — jis surenka
galutinius HTML failus iš `_parts/` (antraštė, poraštė) ir `_body/` (turinys).

## Svarbu

- Nuotraukos ir video — **Pexels** (nemokama licencija), naudojami tik dizaino demonstracijai.
  Prieš paleidimą reikalinga fotosesija ir filmavimas dronu Alkuras objektuose.
- **Kainos, frakcijų kainoraščiai ir pristatymo tarifai prototipe yra demonstraciniai.**
  Tikrieji duomenys keičiami `assets/js/data.js`.
