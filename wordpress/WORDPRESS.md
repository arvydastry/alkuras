# ALKURAS — WordPress + WooCommerce pagrindas

Tema ir įskiepis, atkuriantys dizaino prototipą 1:1 ir leidžiantys visą turinį redaguoti
be kodo. Parengė **Simplex.lt · MB „At Solutions“**.

---

## 1. Kas sudaro sprendimą

| Komponentas | Paskirtis |
|---|---|
| **Tema `alkuras`** | Visas dizainas: šablonai, stiliai, efektai, WooCommerce vaizdai |
| **Įskiepis `alkuras-core`** | Verslo logika: karjerai, projektai, technika, atsiliepimai, skaičiuoklės, pristatymas |

Logika sąmoningai atskirta nuo temos: pakeitus ar atnaujinus dizainą, karjerai,
prekių laukai ir pristatymo skaičiavimas lieka vietoje.

**Tema yra savarankiška, ne Salient vaikinė.** Visas dizainas — nuosavas CSS (~54 KB),
todėl Salient čia nereikalingas: jis būtų pridėjęs ~500 KB stilių, kuriuos vis tiek
reikėtų perrašinėti. Jei vėliau norėsite Salient elementų, temą galima padaryti vaikine —
bet dabartinis variantas tiksliai atitinka patvirtintą maketą ir yra gerokai lengvesnis.

## 2. Lokalus paleidimas

```bash
cd wordpress && docker compose up -d
```

- Svetainė: http://localhost:8099
- Administravimas: http://localhost:8099/wp-admin
- Laiškai (Mailpit): http://localhost:8028
- Prisijungimai: `wordpress/.env` (lokalūs, į Git nekeliami)

WP-CLI komandos:

```bash
docker compose run --rm cli plugin list
docker compose run --rm cli alkuras demo          # demo turinys
docker compose run --rm cli alkuras demo --no-images
```

## 3. Ką klientas redaguoja ir kur

| Ką keisti | Kur |
|---|---|
| Pradinio puslapio antraštė, mygtukai, skaičiai | **Išvaizda → Tinkinti → Alkuras — svetainės turinys** |
| Trys veiklų panelės (tekstai, nuotraukos, video, spalvos) | Tinkinti → *Pradinis — trys veiklos* |
| Kurios sekcijos rodomos | Tinkinti → *Pradinis — sekcijos* |
| Telefonas, el. paštas, adresai, rekvizitai | Tinkinti → *Kontaktai ir rekvizitai* |
| Karjerai (asortimentas, darbo laikas, vieta žemėlapyje) | **Alkuras → Visi karjerai** |
| Projektai (portfolio) | **Alkuras → Visi projektai** |
| Technikos parkas | **Alkuras → Visa technika** |
| Atsiliepimai | **Alkuras → Visi atsiliepimai** |
| Skaičiuoklių įkainiai, pristatymo tarifai, zonos | **Alkuras → Nustatymai** |
| Prekės, kainos, frakcijos, tankis | **Produktai** (kortelė „Alkuras“ prekės duomenyse) |
| Vidinių puslapių antraštė ir fono video | Puslapio redagavimas → blokas **„Puslapio antraštė (hero)“** |
| Puslapio turinys | Gutenberg blokai + trumpieji kodai |

### Trumpieji kodai

```
[alkuras_skaiciuokle tipas="griovimas"]     [alkuras_skaiciuokle tipas="medziagos"]
[alkuras_pristatymas]   [alkuras_zemelapis]   [alkuras_karjerai]
[alkuras_projektai kiekis="6" kategorija="griovimas"]
[alkuras_technika]      [alkuras_atsiliepimai]
[alkuras_produktai kategorija="zvyras" kiekis="4" stilius="grid|rail"]
[alkuras_veiklos]
```

Sąrašas su paaiškinimais kartojamas **Alkuras → Nustatymai** apačioje.

## 4. Prekyba pagal svorį

Kiekviena prekė turi kortelę **Alkuras** su laukais: frakcija, tankis (t/m³),
matavimo vienetas, žymė, panaudojimas ir karjerai, iš kurių kraunama.

- **Tankis** būtinas — be jo prekė nerodoma medžiagų kiekio skaičiuoklėje.
- Kiekis prekės puslapyje valdomas slankikliu (tonomis), suma skaičiuojama gyvai.
- Krepšelis veikia per AJAX į šoninį skydelį, be puslapio perkrovimo.

## 5. Pristatymas pagal artimiausią karjerą

**WooCommerce → Nustatymai → Pristatymas → Lietuva → „Pristatymas iš karjero“**

Skaičiavimas:

```
reisai = ceil(tonos / savivarčio talpa)
kaina  = reisai × reiso mokestis + atstumas_km × (€/km/t) × tonos
```

Atstumas imamas iš **zonų lentelės** (Alkuras → Nustatymai), kur viena eilutė yra
`Miestas | karjero slug | atstumas km`. Papildomai siūlomas nemokamas atsiėmimas karjere.

> **Kitas žingsnis realiam paleidimui:** zonų lentelę pakeisti Google Distance Matrix
> arba OSRM užklausa — tada atstumas skaičiuojamas pagal tikslų adresą, o ne pagal miestą.
> Vieta tam paruošta: `alkuras-core/inc/helpers.php` → `alk_match_zone()`.

## 6. Skaičiuoklės

Abi veikia ir serveryje (PHP), ir naršyklėje (JS) su tais pačiais įkainiais —
įkainiai perduodami per `wp_localize_script`, todėl rezultatas visada sutampa.

**Griovimo kaina**

```
kaina = plotas × konstrukcijos įkainis × objekto koeficientas
      + pamatų ardymas (%) + atliekų išvežimas (€/m²)
      − nuolaida už smulkinimą vietoje (%)
```

**Medžiagų kiekis**

```
m³    = plotas × storis/100 × tankinimo koeficientas
tonos = m³ × medžiagos tankis
```

Visi skaičiai keičiami **Alkuras → Nustatymai**, kodo liesti nereikia.

## 7. Demonstracinis turinys

**Alkuras → Demo turinys** → „Įkelti demonstracinį turinį“.

Sukuria 5 karjerus, 6 projektus, 8 technikos vienetus, 3 atsiliepimus, 12 prekių,
7 puslapius, meniu ir visus dizaino nustatymus. Paleisti galima kelis kartus —
esami įrašai atnaujinami, dublikatų nekuriama.

Nuotraukos ir video — **Pexels**, tik demonstracijai.

## 8. Perkėlimas į serverį (liputis.serveriai.lt)

Įkelti reikia dviejų katalogų:

```
wp-content/themes/alkuras/
wp-content/plugins/alkuras-core/
```

Paruošti ZIP archyvai yra `wordpress/dist/`:

- `alkuras-theme.zip` → Išvaizda → Temos → Pridėti naują → Įkelti temą
- `alkuras-core.zip` → Įskiepiai → Pridėti naują → Įkelti įskiepį

Po įdiegimo:

1. Aktyvuoti įskiepį **Alkuras Core**, tada temą **Alkuras**.
2. Įdiegti **WooCommerce**, pasirinkti Lietuvą ir EUR.
3. **Alkuras → Demo turinys** (arba iš karto kelti tikrą turinį).
4. **WooCommerce → Pristatymas** → sukurti zoną „Lietuva“ ir pridėti „Pristatymas iš karjero“.
5. Nuorodų struktūra: **Nuostatos → Nuolatinės nuorodos → Įrašo pavadinimas**.
6. **Alkuras → Nustatymai** — suvesti tikrus įkainius ir zonas.

Reikalavimai: PHP 8.0+, WordPress 6.4+ (patikrinta su 7.1), WooCommerce 9+.

### FTP

Kai bus laikas kelti, paruošiau `deploy.sh`. Prisijungimus **įrašykite patys** į
`wordpress/.ftp` (šis failas į Git nekeliamas) — aš jų nematau ir neprašau chat'e:

```
FTP_HOST=ftp.serveriai.lt
FTP_USER=jusu_vartotojas
FTP_PASS=jusu_slaptazodis
FTP_PATH=/domains/alkuras.lt/public_html
```

Tada:

```bash
cd wordpress && ./deploy.sh
```

Skriptas įkelia tik temą ir įskiepį (duomenų bazės neliečia).

## 9. Kas dar liks padaryti prieš paleidimą

- Tikri kainoraščiai, karjerų duomenys ir pristatymo tarifai
- Mokėjimų integracija (Paysera arba Montonio) ir sąskaitų išrašymas
- Profesionalios nuotraukos ir filmuota medžiaga vietoje Pexels
- Kontaktų formos įskiepis (Contact Form 7 / WPForms) su failų priedais — dabar veikia
  paprasta vidinė forma su apsauga nuo botų; jos trumpąjį kodą galima pakeisti
  Tinkinti → *Pradinis — sekcijos* → „Užklausos formos trumpasis kodas“
- GDPR / slapukų juosta, privatumo politika, taisyklės
- Video perkėlimas į savo serverį arba Bunny/Cloudflare Stream
- SEO: `rank-math` arba `yoast`, sitemap, Google Search Console
