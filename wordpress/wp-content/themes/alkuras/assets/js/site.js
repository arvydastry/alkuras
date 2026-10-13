/* ==========================================================================
   ALKURAS — skaičiuoklės, pristatymas ir krepšelis (WooCommerce)
   ========================================================================== */
(function(){
"use strict";
if(!window.ALKUI || !window.ALKURAS) return;
const U = window.ALKUI, C = window.ALKURAS;
const { $, $$, bindRange, toast, rescan, openDrawer } = U;
const R = C.rates;

const eur = n => new Intl.NumberFormat("lt-LT",{style:"currency",currency:C.currency||"EUR"}).format(n);
const num = (n,d) => new Intl.NumberFormat("lt-LT",{minimumFractionDigits:d||0,maximumFractionDigits:d||0}).format(n);
const tick = el => { if(!el) return; el.classList.remove("tick"); void el.offsetWidth; el.classList.add("tick"); };

/* ---------- Bendras segmentinio pasirinkimo valdiklis ---------- */
function seg(box, cb){
  if(!box) return;
  box.addEventListener("click", e=>{
    const b = e.target.closest("button"); if(!b) return;
    if(box.dataset.multi === "1"){ b.classList.toggle("on"); }
    else { $$("button", box).forEach(x=> x.classList.remove("on")); b.classList.add("on"); }
    cb(b.dataset.k, b);
  });
}

/* ==========================================================
   GRIOVIMO SKAIČIUOKLĖ
   ========================================================== */
$$("[data-calc-demo]").forEach(box=>{
  const area = $("[data-c-area]", box);
  if(!area) return;
  bindRange(area);

  let type = "gyv", cons = "mur";
  const extras = { pam:false, isv:true, smu:false };

  seg($("[data-c-type]", box), k => { type = k; upd(); });
  seg($("[data-c-cons]", box), k => { cons = k; upd(); });
  seg($("[data-c-extra]", box), (k,b) => { extras[k] = b.classList.contains("on"); upd(); });
  area.addEventListener("input", upd);

  const LAB = {
    med:"medinė", mur:"mūrinė", gzb:"gelžbetonis", mis:"mišri"
  };

  function upd(){
    const a = parseFloat(area.value);
    $("[data-c-area-l]", box).textContent = num(a) + " m²";

    const base = a * (R[cons] || R.mur) * (R[type] || 1);
    const pam  = extras.pam ? base * (R.pamatai/100) : 0;
    const isv  = extras.isv ? a * R.isvezimas : 0;
    const smu  = extras.smu ? -((base + isv) * (R.smulk/100)) : 0;
    const tot  = base + pam + isv + smu;
    const dev  = R.nuokrypis/100;

    const res = $("[data-c-res]", box);
    res.childNodes[0].nodeValue = eur(Math.round(tot/10)*10);
    tick(res);

    $("[data-c-range]", box).textContent =
      C.i18n.deviation + " " + eur(Math.round(tot*(1-dev)/10)*10) + " – " + eur(Math.round(tot*(1+dev)/10)*10);

    $("[data-c-break]", box).innerHTML =
        '<div><span>Griovimo darbai · ' + (LAB[cons]||"") + '</span><b>' + eur(base) + '</b></div>'
      + (pam ? '<div><span>Pamatų ardymas</span><b>' + eur(pam) + '</b></div>' : "")
      + (isv ? '<div><span>Atliekų išvežimas ir tvarkymas</span><b>' + eur(isv) + '</b></div>' : "")
      + (smu ? '<div><span>Nuolaida — smulkinimas objekte</span><b>' + eur(smu) + '</b></div>' : "")
      + '<div><span>Trukmė (preliminari)</span><b>' + Math.max(2, Math.round(a / R.tempas)) + ' ' + C.i18n.days + '</b></div>';
  }
  upd();
});

/* ==========================================================
   MEDŽIAGŲ KIEKIO SKAIČIUOKLĖ
   ========================================================== */
$$("[data-calc-mat]").forEach(box=>{
  const sel = $("[data-m-prod]", box),
        ar  = $("[data-m-area]", box),
        th  = $("[data-m-thick]", box);
  if(!sel || !ar || !th) return;
  bindRange(ar); bindRange(th);

  let comp = 1.2, tons = 0;
  seg($("[data-m-comp]", box), k => { comp = parseFloat(k); upd(); });
  [sel, ar, th].forEach(el => el.addEventListener("input", upd));
  sel.addEventListener("change", upd);

  function upd(){
    const opt     = sel.options[sel.selectedIndex];
    const density = parseFloat(opt.dataset.tankis) || 1.6;
    const price   = parseFloat(opt.dataset.kaina) || 0;
    const a = parseFloat(ar.value), t = parseFloat(th.value);

    $("[data-m-area-l]", box).textContent  = num(a) + " m²";
    $("[data-m-thick-l]", box).textContent = t + " cm";

    const m3 = a * (t/100) * comp;
    tons = m3 * density;

    const res = $("[data-m-res]", box);
    res.childNodes[0].nodeValue = num(tons,1) + " " + C.i18n.tons;
    tick(res);

    $("[data-m-sub]", box).textContent =
      num(m3,1) + " m³ · " + Math.ceil(tons / R.talpa) + " " + C.i18n.trips;

    $("[data-m-break]", box).innerHTML =
        '<div><span>' + opt.dataset.pav + '</span><b>' + eur(price) + ' / t</b></div>'
      + '<div><span>Tankis</span><b>' + num(density,2) + ' t/m³</b></div>'
      + '<div><span>Medžiagų vertė</span><b>' + eur(tons*price) + '</b></div>';
  }

  const add = $("[data-m-add]", box);
  if(add){
    add.addEventListener("click", ()=> addToCart(sel.value, Math.max(1, Math.round(tons)), add));
  }
  upd();
});

/* ==========================================================
   PRISTATYMO SKAIČIUOKLĖ
   ========================================================== */
$$("[data-deliv]").forEach(box=>{
  const sel = $("[data-dv-city]", box), rng = $("[data-dv-tons]", box);
  if(!sel || !rng) return;
  bindRange(rng);

  let timer;
  const run = ()=>{
    const t = parseFloat(rng.value);
    $("[data-dv-tons-l]", box).textContent = t + " " + C.i18n.tons;

    /* Greitas vietinis skaičiavimas — kad slankiklis reaguotų iškart */
    const z = (C.zones || []).find(x => x.city === sel.value);
    if(z){
      const loads = Math.ceil(t / R.talpa);
      const price = Math.max(R.delMin, loads * R.reisas + z.km * R.kmT * t);
      $("[data-dv-q]", box).textContent     = z.quarry;
      $("[data-dv-km]", box).textContent    = z.km + " km";
      $("[data-dv-loads]", box).textContent = loads + " " + C.i18n.loads;
      const p = $("[data-dv-price]", box);
      p.textContent = eur(price); tick(p);
      $$(".pin", box).forEach(pin => pin.classList.toggle("on", pin.dataset.q === z.slug));
    }

    /* Patvirtinimas serveryje (ten galioja tie patys įkainiai) */
    clearTimeout(timer);
    timer = setTimeout(()=>{
      const fd = new FormData();
      fd.append("action", "alkuras_delivery");
      fd.append("nonce", C.nonce);
      fd.append("city", sel.value);
      fd.append("tons", t);
      fetch(C.ajax, { method:"POST", body:fd, credentials:"same-origin" })
        .then(r => r.json())
        .then(j => {
          if(!j || !j.success) return;
          const d = j.data;
          $("[data-dv-q]", box).textContent     = d.quarry;
          $("[data-dv-km]", box).textContent    = d.km + " km";
          $("[data-dv-loads]", box).textContent = d.loads + " " + C.i18n.loads;
          $("[data-dv-price]", box).textContent = d.priceHtml;
          $$(".pin", box).forEach(pin => pin.classList.toggle("on", pin.dataset.q === d.quarrySlug));
        })
        .catch(()=>{});
    }, 420);
  };

  sel.addEventListener("change", run);
  rng.addEventListener("input", run);

  /* Paspaudus smeigtuką žemėlapyje — parenkamas to karjero miestas */
  box.addEventListener("click", e=>{
    const pin = e.target.closest(".pin"); if(!pin) return;
    const z = (C.zones || []).find(x => x.slug === pin.dataset.q);
    if(z){ sel.value = z.city; run(); }
  });

  run();
});

/* ==========================================================
   KREPŠELIS (WooCommerce AJAX)
   ========================================================== */
function applyFragments(fragments){
  if(!fragments) return;
  Object.keys(fragments).forEach(sel=>{
    const el = document.querySelector(sel);
    if(el) el.outerHTML = fragments[sel];
  });
  rescan(document);
}

function addToCart(productId, qty, btn){
  if(!productId) return;
  if(btn){ btn.disabled = true; btn.classList.add("is-busy"); }

  const fd = new FormData();
  fd.append("action", "alkuras_add_to_cart");
  fd.append("nonce", C.nonce);
  fd.append("product_id", productId);
  fd.append("qty", qty || 1);

  fetch(C.ajax, { method:"POST", body:fd, credentials:"same-origin" })
    .then(r => r.json())
    .then(j => {
      if(!j || !j.success){
        toast((j && j.data && j.data.message) || C.i18n.error);
        return;
      }
      applyFragments(j.data.fragments);
      toast(C.i18n.added + ": <b>" + j.data.name + (j.data.frac ? " " + j.data.frac : "") + "</b>");
      openDrawer();
    })
    .catch(()=> toast(C.i18n.error))
    .finally(()=>{ if(btn){ btn.disabled = false; btn.classList.remove("is-busy"); } });
}
window.alkAddToCart = addToCart;

document.addEventListener("click", e=>{
  const add = e.target.closest("[data-alk-add]");
  if(add){
    e.preventDefault();
    addToCart(add.dataset.alkAdd, parseFloat(add.dataset.qty) || 1, add);
    return;
  }

  const q = e.target.closest("[data-alk-q]");
  if(q){
    e.preventDefault();
    updateCart(q.dataset.key, q.dataset.alkQ);
    return;
  }

  const rm = e.target.closest("[data-alk-rm]");
  if(rm){
    e.preventDefault();
    updateCart(rm.dataset.alkRm, "remove");
  }
});

function updateCart(key, op){
  const fd = new FormData();
  fd.append("action", "alkuras_update_cart");
  fd.append("nonce", C.nonce);
  fd.append("key", key);
  fd.append("op", op);

  fetch(C.ajax, { method:"POST", body:fd, credentials:"same-origin" })
    .then(r => r.json())
    .then(j => { if(j && j.success) applyFragments(j.data.fragments); })
    .catch(()=> toast(C.i18n.error));
}

/* WooCommerce savo fragmentai (pvz. iš prekės puslapio) */
jQueryReady(function($j){
  $j(document.body).on("added_to_cart", function(_e, fragments){
    applyFragments(fragments);
    openDrawer();
  });
  $j(document.body).on("updated_wc_div wc_fragments_refreshed", function(){ rescan(document); });
});

function jQueryReady(fn){
  if(window.jQuery){ window.jQuery(fn); }
  else { document.addEventListener("DOMContentLoaded", ()=>{ if(window.jQuery) window.jQuery(fn); }); }
}

/* ==========================================================
   PREKĖS PUSLAPIO KIEKIO SLANKIKLIS
   ========================================================== */
$$("[data-s-qty]").forEach(rng=>{
  bindRange(rng);
  const price = parseFloat(rng.dataset.price) || 0;
  const lab   = $("[data-s-qty-l]");
  const sum   = $("[data-s-sum]");
  const input = document.querySelector('form.cart input.qty, form.cart input[name="quantity"]');

  const upd = ()=>{
    const t = parseFloat(rng.value);
    if(lab) lab.textContent = t + " " + C.i18n.tons;
    if(sum) sum.textContent = eur(t * price);
    if(input){ input.value = t; input.dispatchEvent(new Event("change", { bubbles:true })); }
  };
  rng.addEventListener("input", upd);
  upd();
});
})();
