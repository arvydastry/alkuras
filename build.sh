#!/bin/bash
# ALKURAS — surenka puslapius iš _parts/ + _body/
cd "$(dirname "$0")"
build () {
  local slug="$1" title="$2" desc="$3"
  python3 - "$slug" "$title" "$desc" <<'PY'
import sys, io, os
slug, title, desc = sys.argv[1], sys.argv[2], sys.argv[3]
head = open("_parts/head.html", encoding="utf-8").read()
foot = open("_parts/foot.html", encoding="utf-8").read()
body = open("_body/%s.html" % slug, encoding="utf-8").read()
head = head.replace("{{TITLE}}", title).replace("{{DESC}}", desc)
open("%s.html" % slug, "w", encoding="utf-8").write(head + body + foot)
print("  ->", slug + ".html")
PY
}
echo "Renkami puslapiai:"
build index        "ALKURAS — karjerai, griovimo darbai ir inertinės medžiagos"        "Smėlis, žvyras ir skalda iš nuosavų karjerų Vakarų Lietuvoje. Griovimo darbai, statybinių atliekų smulkinimas ir pristatymas iki objekto."
build karjerai     "Smėlio ir žvyro karjerai — ALKURAS"                                 "Keturi nuosavi karjerai Kretingos ir Šilutės rajonuose. Sijota produkcija, krovimas vietoje, didmena ir mažmena."
build griovimas    "Griovimo darbai — ALKURAS"                                          "Pastatų ir statinių griovimas, teritorijų paruošimas, atliekų tvarkymas. Preliminari kaina — per 30 sekundžių."
build smulkinimas  "Smulkinimas ir frakcijos — ALKURAS"                                 "Betono ir mūro atliekų smulkinimas objekte. Mobilus smulkintuvas iki 250 t/val."
build produkcija   "Produkcija — ALKURAS"                                               "Žvyras, smėlis, skalda ir perdirbtos frakcijos. Kaina už toną su PVM, pristatymas pagal lokaciją."
build preke        "Prekė — ALKURAS"                                                    "Medžiagos aprašymas, frakcija, tankis ir pristatymo skaičiavimas."
build skaiciuokles "Skaičiuoklės — ALKURAS"                                             "Griovimo darbų kainos ir medžiagų kiekio skaičiuoklės."
build projektai    "Projektai — ALKURAS"                                                "Įvykdyti griovimo, karjerų ir tiekimo projektai Vakarų Lietuvoje."
build apie         "Apie įmonę — ALKURAS"                                               "UAB „Alkuras“ nuo 2009 m. Technikos parkas, komanda, sertifikatai."
build kontaktai    "Kontaktai — ALKURAS"                                                "Medvalakio g. 27, Palanga. Karjerų lokacijos ir darbo laikas."
build krepselis    "Krepšelis ir užsakymas — ALKURAS"                                   "Užsakymo apžvalga su pristatymo skaičiavimu pagal artimiausią karjerą."
echo "Baigta."
