/* ==========================================================================
   ALKURAS — turinio atvaizdavimas ir įrankiai
   ========================================================================== */
(function(){
"use strict";
const U = window.ALKUI, D = window.ALK;
if(!U || !D) return;
const { $, $$, eur, num, observeReveals, Cart, toast, deliveryQuote, bindRange, rescan } = U;
const q = id => D.quarries.find(x=> x.id===id);

/* ---------- MEGA MENIU KORTELĖS ---------- */
(function(){
  const box = $("[data-mega-cards]"); if(!box) return;
  const cats = [
    { t:"Žvyras",     img:D.products[0].img, href:"produkcija.html#zvyras" },
    { t:"Smėlis",     img:D.products[2].img, href:"produkcija.html#smelis" },
    { t:"Skalda",     img:D.products[4].img, href:"produkcija.html#skalda" },
    { t:"Perdirbta",  img:D.products[6].img, href:"produkcija.html#perdirbta" }
  ];
  box.innerHTML = cats.map(c=>
    '<a class="mega__card" href="'+c.href+'"><img src="'+c.img+'" alt="'+c.t+'" loading="lazy"><span>'+c.t+'</span></a>'
  ).join("");
})();

/* ---------- PRODUKTO KORTELĖ ---------- */
function prodCard(p){
  const tag = p.tags.length
    ? '<span class="prod__tag'+(p.tags[0]==="Nauja"?" prod__tag--new":"")+'">'+p.tags[0]+'</span>' : "";
  return '<article class="prod rv-up" data-tilt="5" data-rv>'
    + '<div class="tilt__in">'
    + '<div class="prod__top">'
    +   '<a class="prod__fig" href="preke.html?p='+p.id+'" data-cursor="Žiūrėti">'
    +     '<img class="prod__img" src="'+p.img+'" alt="'+p.name+' '+p.frac+'" loading="lazy">'
    +     '<img class="prod__img2" src="'+p.img2+'" alt="" loading="lazy">'
    +     '<span class="prod__sieve"></span><span class="prod__grains"></span>'
    +     tag
    +   '</a>'
    +   '<div class="prod__quick">'
    +     '<button class="btn btn--solid btn--sm" data-add="'+p.id+'" data-qty="1" style="flex:1;justify-content:center"><span class="btn__fill"></span>Į krepšelį</button>'
    +   '</div>'
    + '</div>'
    + '<div class="prod__body">'
    +   '<span class="prod__frac">'+p.frac+'</span>'
    +   '<h3 class="prod__name">'+p.name+'</h3>'
    +   '<p class="prod__meta">'+p.desc+'</p>'
    +   '<div class="prod__price"><b class="num">'+eur(p.price)+'</b><span>/ tona su PVM</span></div>'
    + '</div>'
    + '<span class="tilt__sheen"></span>'
    + '</div></article>';
}

$$("[data-products]").forEach(box=>{
  const cat = box.dataset.cat, lim = parseInt(box.dataset.limit,10)||99;
  const list = D.products.filter(p=> !cat || cat==="*" || p.cat===cat).slice(0,lim);
  box.innerHTML = list.map(prodCard).join("");
});

/* Katalogo filtras */
(function(){
  const seg = $("[data-prod-filter]"), grid = $("[data-products-filterable]");
  if(!seg || !grid) return;
  const draw = cat =>{
    const list = cat==="*" ? D.products : D.products.filter(p=> p.cat===cat);
    grid.style.opacity = 0;
    setTimeout(()=>{
      grid.innerHTML = list.map(prodCard).join("");
      grid.style.opacity = 1;
      rescan(grid);
    }, 200);
  };
  seg.addEventListener("click", e=>{
    const b = e.target.closest("button"); if(!b) return;
    $$("button", seg).forEach(x=> x.classList.remove("on"));
    b.classList.add("on"); draw(b.dataset.cat);
  });
  draw("*");
  if(location.hash){
    const h = location.hash.slice(1);
    const b = $('[data-cat="'+h+'"]', seg);
    if(b){ $$("button", seg).forEach(x=> x.classList.remove("on")); b.classList.add("on"); draw(h); }
  }
})();

/* ---------- PROJEKTAI ---------- */
$$("[data-projects]").forEach(box=>{
  const lim = parseInt(box.dataset.limit,10)||99;
  box.innerHTML = D.projects.slice(0,lim).map(p=>
    '<a class="fitem fitem--'+p.cls+'" href="projektai.html" data-cursor="Projektas">'
    + '<img class="fitem__img" src="'+p.img+'" alt="'+p.t+'" loading="lazy" data-px=".07">'
    + '<span class="fitem__tiles">'+ Array(24).fill('<i></i>').join("") +'</span>'
    + '<span class="fitem__meta">'
    +   '<span class="fitem__cat">'+p.cat+' · '+p.loc+' · '+p.year+'</span>'
    +   '<span class="fitem__t">'+p.t+'</span>'
    +   '<span class="fitem__facts">'+ p.facts.map(f=>'<span>'+f+'</span>').join("") +'</span>'
    + '</span></a>'
  ).join("");
  observeReveals(box);
  /* plytelių kaskada */
  $$(".fitem", box).forEach(f=>{
    $$(".fitem__tiles i", f).forEach((t,i)=>{
      t.style.transitionDelay = ((i%6)*45 + Math.floor(i/6)*70) + "ms";
    });
  });
});

/* ---------- TECHNIKA ---------- */
$$("[data-machines]").forEach(box=>{
  box.classList.add("mach");
  box.innerHTML = D.machines.map((m,i)=>
    '<div class="mach__row" data-img="'+m.img+'" data-vid="'+m.vid+'">'
    + '<span class="mach__idx">'+String(i+1).padStart(2,"0")+'</span>'
    + '<span class="mach__n">'+m.n+'</span>'
    + '<span class="mach__s">'+m.s+'</span>'
    + '<span class="mach__arrow">↗</span></div>'
  ).join("");
});

/* ---------- KARJERŲ SĄRAŠAS ---------- */
$$("[data-quarry-list]").forEach(box=>{
  box.innerHTML = D.quarries.map((k,i)=>
    '<article id="'+k.id+'" class="rv-up" data-rv style="transition-delay:'+(i*70)+'ms;padding:clamp(1.2rem,2.4vw,1.9rem) 0;border-bottom:1px solid var(--line)">'
    + '<div style="display:flex;justify-content:space-between;gap:1.5rem;align-items:baseline">'
    +   '<h3 class="t-h4" style="font-family:var(--f-display);font-weight:700;letter-spacing:-.03em">'+k.name+'</h3>'
    +   '<span class="eyebrow eyebrow--plain">'+k.region+'</span></div>'
    + '<p class="soft" style="margin-top:.7rem;font-size:.95rem">'+k.materials+'</p>'
    + '<div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem">'
    +   '<span class="chip">'+k.hours+'</span><span class="chip">'+k.load+'</span></div>'
    + '</article>'
  ).join("");
  observeReveals(box);
});

/* ---------- GRIOVIMO PASLAUGŲ SĄRAŠAS ---------- */
$$("[data-services]").forEach(box=>{
  const S = [
    { n:"Pastatų griovimas", s:"Gyvenamieji, ūkiniai, gamybiniai", img:D.photo.demo4, vid:D.video.sm.demoHouse },
    { n:"Selektyvus demontažas", s:"Vidaus konstrukcijos, angos", img:D.photo.demo6, vid:D.video.sm.demoPower },
    { n:"Gelžbetonio ardymas", s:"Hidraulinis kūjis ir žirklės", img:D.photo.demo3, vid:D.video.sm.demoPower },
    { n:"Teritorijos paruošimas", s:"Grunto keitimas, lyginimas", img:D.photo.demo9, vid:D.video.sm.excavator },
    { n:"Atliekų išvežimas ir apskaita", s:"Su dokumentais tvarkytojui", img:D.photo.truck5, vid:D.video.sm.unload },
    { n:"Smulkinimas objekte", s:"Betonas virsta 0/40 skalda", img:D.photo.crusher1, vid:D.video.sm.crusher }
  ];
  box.innerHTML = S.map((m,i)=>
    '<div class="mach__row" data-img="'+m.img+'" data-vid="'+m.vid+'">'
    + '<span class="mach__idx">'+String(i+1).padStart(2,"0")+'</span>'
    + '<span class="mach__n">'+m.n+'</span>'
    + '<span class="mach__s">'+m.s+'</span>'
    + '<span class="mach__arrow">↗</span></div>'
  ).join("");
});

/* ---------- ATSILIEPIMAI ---------- */
$$("[data-quotes]").forEach(box=>{
  box.innerHTML = D.testimonials.map(t=>
    '<article class="quote rv-up" data-rv>'
    + '<p class="quote__t">'+t.t+'</p>'
    + '<div class="quote__by"><img class="quote__av" src="'+t.av+'" alt="" loading="lazy">'
    + '<span><span class="quote__n">'+t.n+'</span><br><span class="quote__r">'+t.r+'</span></span></div>'
    + '</article>'
  ).join("");
  observeReveals(box);
});

/* ---------- KARJERŲ ŽEMĖLAPIS (schema) ---------- */
function mapSVG(active){
  const contours = Array.from({length:8},(_,i)=>{
    const r = 10 + i*6.5;
    return '<ellipse cx="40" cy="48" rx="'+(r*1.4).toFixed(1)+'" ry="'+r.toFixed(1)+'" fill="none" stroke="rgba(239,235,227,'+(0.15 - i*0.014).toFixed(3)+')" stroke-width=".28"/>';
  }).join("");
  const grid = Array.from({length:21},(_,i)=>
    '<line x1="'+(i*5)+'" y1="0" x2="'+(i*5)+'" y2="100" stroke="rgba(239,235,227,.045)" stroke-width=".18"/>'
    +'<line x1="0" y1="'+(i*5)+'" x2="100" y2="'+(i*5)+'" stroke="rgba(239,235,227,.045)" stroke-width=".18"/>').join("");
  const label = k => k.name.replace(" karjeras","").replace("Smulkinimo aikštelė","Smulkinimas");
  const pins = D.quarries.map(k=>
    '<g class="pin'+(k.id===active?" on":"")+'" data-q="'+k.id+'" transform="translate('+k.x+','+k.y+')">'
    + '<circle class="halo" r="3.5" fill="none" stroke="#D8F250" stroke-width=".3"/>'
    + '<circle class="dot" r="1.15" fill="#EFEBE3"/>'
    + '<circle r="5" fill="transparent"/>'
    + '<text x="2.4" y="0.7">'+label(k)+'</text>'
    + '</g>').join("");
  return '<svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice" role="img" aria-label="Karjerų žemėlapio schema">'
    + '<rect x="-10" y="-10" width="120" height="120" fill="#131517"/>' + grid + contours
    + '<g opacity=".5"><path d="M6 10 h5 M6 10 v5" stroke="#D8F250" stroke-width=".3" fill="none"/>'
    + '<text x="6" y="17" font-family="ui-monospace,monospace" font-size="2" fill="rgba(239,235,227,.4)" letter-spacing=".3">VAKARŲ LIETUVA</text></g>'
    + pins + '</svg>';
}

/* ---------- PRISTATYMO MODULIS ---------- */
$$("[data-deliv]").forEach(box=>{
  box.innerHTML =
    '<div class="deliv__map" data-map>'+mapSVG("imbare")+'</div>'
  + '<div class="deliv__out">'
  +   '<p class="eyebrow">Pristatymas pagal lokaciją</p>'
  +   '<h3 class="t-h3" style="margin:.6rem 0 1.2rem">Sistema pati parenka artimiausią karjerą</h3>'
  +   '<label class="field field--sel"><select data-dv-city>'
  +     D.cities.map(c=>'<option'+(c.name==="Klaipėda"?" selected":"")+'>'+c.name+'</option>').join("")
  +   '</select><label>Pristatymo vieta</label></label>'
  +   '<div><div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:1.4rem">'
  +     '<span class="eyebrow eyebrow--plain">Kiekis</span><b class="num" data-dv-tons-l>20 t</b></div>'
  +     '<input class="range" type="range" min="5" max="200" step="5" value="20" data-dv-tons></div>'
  +   '<dl style="margin:1.2rem 0 0">'
  +     '<div class="deliv__row"><dt>Artimiausias karjeras</dt><dd data-dv-q>—</dd></div>'
  +     '<div class="deliv__row"><dt>Atstumas</dt><dd data-dv-km>—</dd></div>'
  +     '<div class="deliv__row"><dt>Reisai (25 t savivartis)</dt><dd data-dv-loads>—</dd></div>'
  +   '</dl>'
  +   '<div class="deliv__total"><span class="eyebrow eyebrow--plain" style="color:rgba(239,235,227,.6)">Pristatymas</span><b class="num flip" data-dv-price>—</b></div>'
  +   '<p class="muted" style="font-size:.8rem">Preliminaru. Galutinė kaina patvirtinama užsakymo metu — priklauso nuo privažiavimo ir iškrovimo sąlygų.</p>'
  + '</div>';

  const sel = $("[data-dv-city]", box), rng = $("[data-dv-tons]", box);
  bindRange(rng);
  function upd(){
    const t = parseFloat(rng.value);
    $("[data-dv-tons-l]", box).textContent = t + " t";
    const r = deliveryQuote(sel.value, t);
    if(!r) return;
    $("[data-dv-q]", box).textContent = r.quarry ? r.quarry.name : "—";
    $("[data-dv-km]", box).textContent = r.km + " km";
    $("[data-dv-loads]", box).textContent = r.loads + " vnt.";
    const pEl = $("[data-dv-price]", box);
    pEl.textContent = eur(r.price);
    pEl.classList.remove("tick"); void pEl.offsetWidth; pEl.classList.add("tick");
    $$(".pin", box).forEach(p=> p.classList.toggle("on", p.dataset.q === (r.quarry&&r.quarry.id)));
  }
  sel.addEventListener("change", upd);
  rng.addEventListener("input", upd);
  box.addEventListener("click", e=>{
    const pin = e.target.closest(".pin"); if(!pin) return;
    const city = D.cities.find(c=> c.near === pin.dataset.q);
    if(city){ sel.value = city.name; upd(); }
  });
  upd();
});

/* Atskiras žemėlapis (kontaktai / karjerai) */
$$("[data-map-only]").forEach(b=> b.innerHTML = mapSVG(b.dataset.mapOnly||"imbare"));

/* ---------- GRIOVIMO SKAIČIUOKLĖ ---------- */
$$("[data-calc-demo]").forEach(box=>{
  const TYPES = [
    { k:"gyv",  n:"Gyvenamasis namas", m:1.00 },
    { k:"ukio", n:"Ūkinis pastatas",   m:0.85 },
    { k:"gam",  n:"Gamybinis pastatas",m:1.15 },
    { k:"inz",  n:"Inž. konstrukcijos",m:1.30 }
  ];
  const CONS = [
    { k:"med", n:"Medinė",      r:9  },
    { k:"mur", n:"Mūrinė",      r:14 },
    { k:"gzb", n:"Gelžbetonis", r:22 },
    { k:"mis", n:"Mišri",       r:17 }
  ];
  box.innerHTML =
    '<div class="calc__in">'
  +   '<p class="eyebrow">Skaičiuoklė 01</p>'
  +   '<h3 class="t-h3">Griovimo darbų kaina</h3>'
  +   '<div><p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem">Objekto tipas</p>'
  +     '<div class="seg" data-c-type>'+TYPES.map((t,i)=>'<button class="'+(i===0?"on":"")+'" data-k="'+t.k+'">'+t.n+'</button>').join("")+'</div></div>'
  +   '<div><p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem">Konstrukcija</p>'
  +     '<div class="seg" data-c-cons>'+CONS.map((c,i)=>'<button class="'+(i===1?"on":"")+'" data-k="'+c.k+'">'+c.n+'</button>').join("")+'</div></div>'
  +   '<div><div style="display:flex;justify-content:space-between;align-items:baseline">'
  +     '<span class="eyebrow eyebrow--plain">Plotas</span><b class="num" data-c-area-l>250 m²</b></div>'
  +     '<input class="range" type="range" min="20" max="4000" step="10" value="250" data-c-area></div>'
  +   '<div><p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem">Papildomai</p>'
  +     '<div class="seg" data-c-extra>'
  +       '<button data-k="pam">Pamatų ardymas</button>'
  +       '<button class="on" data-k="isv">Atliekų išvežimas</button>'
  +       '<button data-k="smu">Smulkinimas vietoje</button>'
  +     '</div></div>'
  + '</div>'
  + '<div class="calc__out">'
  +   '<video muted loop playsinline preload="none" data-src="'+D.video.sm.demoPower+'"></video>'
  +   '<div><p class="eyebrow eyebrow--plain" style="color:rgba(239,235,227,.55)">Preliminari kaina</p>'
  +     '<div class="calc__result flip" data-c-res>—<small data-c-range></small></div></div>'
  +   '<div class="calc__break" data-c-break></div>'
  +   '<a class="btn btn--signal" href="kontaktai.html" data-magnet=".25" style="justify-content:center"><span class="btn__fill"></span>Pateikti užklausą <span class="btn__ico">→</span></a>'
  + '</div>';

  let type = TYPES[0], cons = CONS[1];
  const extras = { pam:false, isv:true, smu:false };
  const area = $("[data-c-area]", box); bindRange(area);

  function seg(sel, cb){
    const el = $(sel, box);
    el.addEventListener("click", e=>{
      const b = e.target.closest("button"); if(!b) return;
      if(el.dataset.multi === "1"){ b.classList.toggle("on"); }
      else { $$("button", el).forEach(x=> x.classList.remove("on")); b.classList.add("on"); }
      cb(b.dataset.k, b);
    });
  }
  $("[data-c-extra]", box).dataset.multi = "1";
  seg("[data-c-type]", k => { type = TYPES.find(t=>t.k===k); upd(); });
  seg("[data-c-cons]", k => { cons = CONS.find(c=>c.k===k); upd(); });
  seg("[data-c-extra]", (k,b) => { extras[k] = b.classList.contains("on"); upd(); });
  area.addEventListener("input", upd);

  function upd(){
    const a = parseFloat(area.value);
    $("[data-c-area-l]", box).textContent = num(a) + " m²";
    const base = a * cons.r * type.m;
    const pam  = extras.pam ? base*0.18 : 0;
    const isv  = extras.isv ? a*4.2     : 0;
    const smu  = extras.smu ? -(base+isv)*0.10 : 0;
    const tot  = base + pam + isv + smu;
    const res = $("[data-c-res]", box);
    res.firstChild.nodeValue = eur(Math.round(tot/10)*10);
    res.classList.remove("tick"); void res.offsetWidth; res.classList.add("tick");
    $("[data-c-range]", box).textContent = "Galimas nuokrypis " + eur(Math.round(tot*0.88/10)*10) + " – " + eur(Math.round(tot*1.14/10)*10);
    $("[data-c-break]", box).innerHTML =
        '<div><span>Griovimo darbai · '+cons.n.toLowerCase()+'</span><b>'+eur(base)+'</b></div>'
      + (pam ? '<div><span>Pamatų ardymas</span><b>'+eur(pam)+'</b></div>' : "")
      + (isv ? '<div><span>Atliekų išvežimas ir tvarkymas</span><b>'+eur(isv)+'</b></div>' : "")
      + (smu ? '<div><span>Nuolaida — smulkinimas objekte</span><b>'+eur(smu)+'</b></div>' : "")
      + '<div><span>Trukmė (preliminari)</span><b>'+Math.max(2, Math.round(a/160))+' d. d.</b></div>';
  }
  upd();
});

/* ---------- MEDŽIAGŲ KIEKIO SKAIČIUOKLĖ ---------- */
$$("[data-calc-mat]").forEach(box=>{
  box.innerHTML =
    '<div class="calc__in">'
  +   '<p class="eyebrow">Skaičiuoklė 02</p>'
  +   '<h3 class="t-h3">Medžiagų kiekis</h3>'
  +   '<label class="field field--sel"><select data-m-prod>'
  +     D.products.map((p,i)=>'<option value="'+p.id+'"'+(i===0?" selected":"")+'>'+p.name+' · '+p.frac+'</option>').join("")
  +   '</select><label>Medžiaga</label></label>'
  +   '<div><div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:.8rem">'
  +     '<span class="eyebrow eyebrow--plain">Plotas</span><b class="num" data-m-area-l>100 m²</b></div>'
  +     '<input class="range" type="range" min="5" max="2000" step="5" value="100" data-m-area></div>'
  +   '<div><div style="display:flex;justify-content:space-between;align-items:baseline">'
  +     '<span class="eyebrow eyebrow--plain">Sluoksnio storis</span><b class="num" data-m-thick-l>15 cm</b></div>'
  +     '<input class="range" type="range" min="2" max="80" step="1" value="15" data-m-thick></div>'
  +   '<div><p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem">Tankinimas</p>'
  +     '<div class="seg" data-m-comp>'
  +       '<button data-k="1">Be tankinimo</button>'
  +       '<button class="on" data-k="1.2">Vidutinis ×1,2</button>'
  +       '<button data-k="1.35">Stiprus ×1,35</button>'
  +     '</div></div>'
  + '</div>'
  + '<div class="calc__out">'
  +   '<video muted loop playsinline preload="none" data-src="'+D.video.sm.conveyorG+'"></video>'
  +   '<div><p class="eyebrow eyebrow--plain" style="color:rgba(239,235,227,.55)">Reikiamas kiekis</p>'
  +     '<div class="calc__result flip" data-m-res>—<small data-m-sub></small></div></div>'
  +   '<div class="calc__break" data-m-break></div>'
  +   '<button class="btn btn--signal" data-m-add data-magnet=".25" style="justify-content:center;width:100%"><span class="btn__fill"></span>Įdėti į krepšelį <span class="btn__ico">→</span></button>'
  + '</div>';

  const sel = $("[data-m-prod]", box), ar = $("[data-m-area]", box), th = $("[data-m-thick]", box);
  bindRange(ar); bindRange(th);
  let comp = 1.2, tons = 0;
  $("[data-m-comp]", box).addEventListener("click", e=>{
    const b = e.target.closest("button"); if(!b) return;
    $$("button", $("[data-m-comp]", box)).forEach(x=> x.classList.remove("on"));
    b.classList.add("on"); comp = parseFloat(b.dataset.k); upd();
  });
  [sel, ar, th].forEach(el=> el.addEventListener("input", upd));
  sel.addEventListener("change", upd);

  function upd(){
    const p = D.products.find(x=> x.id === sel.value) || D.products[0];
    const a = parseFloat(ar.value), t = parseFloat(th.value);
    $("[data-m-area-l]", box).textContent = num(a) + " m²";
    $("[data-m-thick-l]", box).textContent = t + " cm";
    const m3 = a * (t/100) * comp;
    tons = m3 * p.density;
    const res = $("[data-m-res]", box);
    res.firstChild.nodeValue = num(tons,1) + " t";
    res.classList.remove("tick"); void res.offsetWidth; res.classList.add("tick");
    $("[data-m-sub]", box).textContent = num(m3,1) + " m³ · " + Math.ceil(tons/25) + " savivarčio reisai";
    $("[data-m-break]", box).innerHTML =
        '<div><span>'+p.name+' '+p.frac+'</span><b>'+eur(p.price)+' / t</b></div>'
      + '<div><span>Tankis</span><b>'+num(p.density,2)+' t/m³</b></div>'
      + '<div><span>Medžiagų vertė</span><b>'+eur(tons*p.price)+'</b></div>';
    box.dataset.pid = p.id;
  }
  $("[data-m-add]", box).addEventListener("click", ()=>{
    Cart.add(box.dataset.pid, Math.max(1, Math.round(tons)));
  });
  upd();
});

/* ---------- PREKĖS PUSLAPIS ---------- */
(function single(){
  const box = $("[data-single]"); if(!box) return;
  const id = new URLSearchParams(location.search).get("p") || D.products[0].id;
  const p = D.products.find(x=> x.id===id) || D.products[0];
  document.title = p.name + " " + p.frac + " — ALKURAS";

  $$("[data-s-name]").forEach(e=> e.textContent = p.name);
  $$("[data-s-frac]").forEach(e=> e.textContent = p.frac);
  $$("[data-s-desc]").forEach(e=> e.textContent = p.desc);
  $$("[data-s-price]").forEach(e=> e.textContent = eur(p.price));
  const g = $("[data-s-gallery]");
  if(g) g.innerHTML =
      '<div class="exc" data-exc="150" style="aspect-ratio:4/3">'
    +   '<div class="exc__base"><img src="'+p.img2+'" alt="'+p.name+'"></div>'
    +   '<div class="exc__over" style="background-image:url('+p.img+')"></div>'
    +   '<div class="exc__ring"></div>'
    + '</div>'
    + '<p class="eyebrow" style="margin-top:1rem">Užveskite pelę — medžiaga atidengiama</p>';

  const specs = $("[data-s-specs]");
  if(specs) specs.innerHTML =
      '<div class="deliv__row"><dt>Frakcija</dt><dd>'+p.frac+'</dd></div>'
    + '<div class="deliv__row"><dt>Tankis</dt><dd>'+num(p.density,2)+' t/m³</dd></div>'
    + '<div class="deliv__row"><dt>Matavimo vnt.</dt><dd>tona (t)</dd></div>'
    + '<div class="deliv__row"><dt>Panaudojimas</dt><dd>'+p.use.join(", ")+'</dd></div>'
    + '<div class="deliv__row"><dt>Karjerai</dt><dd>'+p.quarry.map(k=>{ const x=q(k); return x?x.name.replace(" karjeras",""):k; }).join(", ")+'</dd></div>';

  const addBtn = $("[data-s-add]");
  const qt = $("[data-s-qty]");
  if(qt && addBtn){
    bindRange(qt);
    const lab = $("[data-s-qty-l]"), sum = $("[data-s-sum]");
    const upd = ()=>{
      const t = parseFloat(qt.value);
      if(lab) lab.textContent = t + " t";
      if(sum) sum.textContent = eur(t*p.price);
    };
    qt.addEventListener("input", upd); upd();
    addBtn.addEventListener("click", ()=> Cart.add(p.id, parseFloat(qt.value)));
  }
  const rel = $("[data-s-related]");
  if(rel) rel.innerHTML = D.products.filter(x=> x.cat===p.cat && x.id!==p.id).slice(0,4).map(prodCard).join("")
    || D.products.slice(0,4).map(prodCard).join("");
  observeReveals(box);
})();

/* ---------- KREPŠELIO PUSLAPIS ---------- */
(function cartPage(){
  const box = $("[data-cartpage]"); if(!box) return;
  const draw = ()=>{
    const items = Cart.items();
    const list = $("[data-cp-list]", box);
    if(!items.length){
      list.innerHTML = '<p class="muted" style="padding:3rem 0">Krepšelis tuščias. <a class="ulink" href="produkcija.html">Peržiūrėti produkciją</a></p>';
    } else {
      list.innerHTML = items.map(i=>{
        const p = D.products.find(x=>x.id===i.id); if(!p) return "";
        return '<div class="citem" style="grid-template-columns:96px 1fr auto">'
          + '<img src="'+p.img+'" alt="" style="width:96px;height:96px">'
          + '<div><div class="citem__n">'+p.name+' '+p.frac+'</div>'
          + '<div class="citem__m">'+eur(p.price)+' / t · '+p.quarry.map(k=>{const x=q(k);return x?x.name.replace(" karjeras",""):k;}).join(" · ")+'</div>'
          + '<div class="qty" style="margin-top:.6rem"><button data-q="-" data-id="'+p.id+'">–</button><span>'+i.qty+' t</span><button data-q="+" data-id="'+p.id+'">+</button></div></div>'
          + '<div style="text-align:right"><div class="num" style="font-weight:600;font-size:1.1rem">'+eur(p.price*i.qty)+'</div>'
          + '<button class="ulink" data-rm="'+p.id+'" style="margin-top:.7rem;font-size:10px">Šalinti</button></div></div>';
      }).join("");
    }
    calc();
  };
  const city = $("[data-cp-city]", box);
  function calc(){
    const tons = Cart.items().reduce((s,i)=> s+i.qty, 0);
    const goods = Cart.total();
    const d = deliveryQuote(city ? city.value : "Klaipėda", tons || 1);
    const del = tons ? (d ? d.price : 0) : 0;
    $("[data-cp-goods]", box).textContent = eur(goods);
    $("[data-cp-del]", box).textContent = tons ? eur(del) : "—";
    $("[data-cp-q]", box).textContent = d && tons ? d.quarry.name : "—";
    $("[data-cp-km]", box).textContent = d && tons ? d.km+" km" : "—";
    $("[data-cp-tons]", box).textContent = num(tons,1)+" t";
    const tot = $("[data-cp-total]", box);
    tot.textContent = eur(goods + del);
    tot.classList.remove("tick"); void tot.offsetWidth; tot.classList.add("tick");
  }
  if(city) city.addEventListener("change", calc);
  document.addEventListener("alk:cart", draw);
  draw();
})();

/* Pakartotinis efektų prijungimas prie dinaminio turinio */
rescan(document);
Cart.render();
})();
