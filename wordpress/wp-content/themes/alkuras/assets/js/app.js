/* ==========================================================================
   ALKURAS — sąveikos ir efektų variklis
   ========================================================================== */
(function(){
"use strict";

const RM = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const TOUCH = window.matchMedia("(hover: none), (pointer: coarse)").matches;
const $  = (s,c)=> (c||document).querySelector(s);
const $$ = (s,c)=> Array.from((c||document).querySelectorAll(s));
const lerp = (a,b,t)=> a+(b-a)*t;
const clamp = (v,a,b)=> Math.min(b,Math.max(a,v));
const eur = n => new Intl.NumberFormat("lt-LT",{style:"currency",currency:"EUR"}).format(n);
const num = (n,d)=> new Intl.NumberFormat("lt-LT",{minimumFractionDigits:d||0,maximumFractionDigits:d||0}).format(n);

/* ---------- rAF ciklas ---------- */
const ticks = [];
const onTick = fn => ticks.push(fn);
let scrollY = window.scrollY, scrollYs = scrollY, vel = 0, vels = 0;
function frame(){
  scrollY = window.scrollY;
  const prev = scrollYs;
  scrollYs = lerp(scrollYs, scrollY, .11);
  vel = scrollYs - prev;
  vels = lerp(vels, vel, .12);
  for(let i=0;i<ticks.length;i++) ticks[i]();
  requestAnimationFrame(frame);
}
requestAnimationFrame(frame);

/* ==========================================================
   1. KROVIMO EKRANAS
   ========================================================== */
(function loader(){
  const el = $(".loader"); if(!el) return;
  const bar = $(".loader__bar i", el), pct = $(".loader__pct", el);
  let p = 0;
  const t = setInterval(()=>{
    p = Math.min(100, p + Math.random()*16 + 5);
    if(bar) bar.style.transform = "scaleX("+(p/100)+")";
    if(pct) pct.textContent = String(Math.round(p)).padStart(3,"0");
    if(p>=100){
      clearInterval(t);
      setTimeout(()=>{ el.classList.add("done"); document.body.classList.remove("is-locked");
        document.dispatchEvent(new Event("alk:ready")); }, 260);
    }
  }, RM ? 40 : 130);
  document.body.classList.add("is-locked");
})();

/* ==========================================================
   2. ŽYMEKLIS SU ETIKETE
   ========================================================== */
(function cursor(){
  if(TOUCH || RM) return;
  const dot = document.createElement("div"); dot.className = "cursor";
  const lab = document.createElement("span"); lab.className = "cursor__label"; dot.appendChild(lab);
  const ring = document.createElement("div"); ring.className = "cursor-ring";
  document.body.append(dot, ring);
  let mx=innerWidth/2, my=innerHeight/2, dx=mx, dy=my, rx=mx, ry=my;
  addEventListener("mousemove", e=>{ mx=e.clientX; my=e.clientY; }, {passive:true});
  onTick(()=>{
    dx=lerp(dx,mx,.9); dy=lerp(dy,my,.9);
    rx=lerp(rx,mx,.16); ry=lerp(ry,my,.16);
    dot.style.transform  = "translate3d("+dx+"px,"+dy+"px,0)";
    ring.style.transform = "translate3d("+rx+"px,"+ry+"px,0)";
  });
  document.addEventListener("mouseover", e=>{
    const t = e.target.closest("[data-cursor]");
    if(t){ lab.textContent = t.dataset.cursor; dot.classList.add("cursor--big"); ring.style.opacity=0; }
    else if(e.target.closest("a,button,input,select,textarea,[role=button]")){
      dot.classList.remove("cursor--big"); lab.textContent=""; ring.style.opacity=1;
      ring.style.transform += " scale(1.5)";
    } else { dot.classList.remove("cursor--big"); lab.textContent=""; ring.style.opacity=1; }
  });
})();

/* ==========================================================
   3. ANTRAŠTĖ / MENIU / PROGRESAS
   ========================================================== */
(function header(){
  const hdr = $(".hdr"); const prog = $(".prog");
  /* Šviesiuose puslapiuose (be tamsios antraštės) antraštė turi būti tamsi */
  const firstBlock = $("main > section, main > div");
  if(hdr && firstBlock && !firstBlock.matches(".hero, .phead, .pxs, .depth, .vtext")){
    hdr.classList.remove("hdr--over");
  }
  let last = 0;
  onTick(()=>{
    const y = scrollY;
    if(hdr){
      hdr.classList.toggle("hdr--stuck", y > 40);
      if(!$(".mega.on") && !$(".mnav.on")) hdr.classList.toggle("hdr--hidden", y > last && y > 420);
    }
    if(prog){
      const h = document.documentElement.scrollHeight - innerHeight;
      prog.style.width = (h>0 ? (y/h)*100 : 0) + "%";
    }
    last = y;
  });

  /* Mega meniu */
  const mega = $(".mega"), trig = $$("[data-mega]");
  let mt;
  const closeMega = ()=>{ if(mega) mega.classList.remove("on"); };
  trig.forEach(t=>{
    t.addEventListener("mouseenter", ()=>{ clearTimeout(mt); if(mega) mega.classList.add("on"); });
    t.addEventListener("mouseleave", ()=>{ mt = setTimeout(closeMega, 260); });
  });
  if(mega){
    mega.addEventListener("mouseenter", ()=> clearTimeout(mt));
    mega.addEventListener("mouseleave", closeMega);
  }
  addEventListener("keydown", e=>{ if(e.key==="Escape"){ closeMega(); closeM(); } });

  /* Mobilus meniu */
  const burger = $(".burger"), mnav = $(".mnav");
  function closeM(){ if(burger) burger.classList.remove("on"); if(mnav) mnav.classList.remove("on"); document.body.classList.remove("is-locked"); }
  if(burger && mnav){
    burger.addEventListener("click", ()=>{
      const on = burger.classList.toggle("on");
      mnav.classList.toggle("on", on);
      document.body.classList.toggle("is-locked", on);
    });
    $$("a", mnav).forEach(a=> a.addEventListener("click", closeM));
  }
})();

/* ==========================================================
   4. TEKSTO SKAIDYMAS
   ========================================================== */
function splitChars(el){
  if(el.dataset.split==="done") return;
  const walk = node => {
    const out = [];
    node.childNodes.forEach(n=>{
      if(n.nodeType===3){
        n.textContent.split("").forEach(c=>{
          const s = document.createElement("span");
          s.className = "ch"; s.textContent = c === " " ? " " : c;
          out.push(s);
        });
      } else if(n.nodeType===1){
        const clone = n.cloneNode(false);
        walk(n).forEach(c=> clone.appendChild(c));
        out.push(clone);
      }
    });
    return out;
  };
  const kids = walk(el);
  el.textContent = "";
  kids.forEach((k,i)=>{ if(k.classList && k.classList.contains("ch")) k.style.transitionDelay = (i*16)+"ms"; el.appendChild(k); });
  el.dataset.split = "done";
}
$$("[data-split=chars]").forEach(splitChars);

/* Nuorodų raidžių ritinys */
$$("[data-roll]").forEach(el=>{
  const txt = el.textContent.trim();
  el.classList.add("roll"); el.textContent = "";
  const mk = cls => {
    const w = document.createElement("span"); w.className = "roll__in " + (cls||"");
    txt.split("").forEach((c,i)=>{
      const s = document.createElement("span"); s.className="roll__ch";
      s.textContent = c===" " ? " " : c;
      s.style.transition = "transform .5s cubic-bezier(.16,1,.3,1) " + (i*22) + "ms";
      w.appendChild(s);
    });
    return w;
  };
  const a = mk(), b = mk("roll__clone");
  el.append(a,b);
  el.addEventListener("mouseenter", ()=>{
    $$(".roll__ch", a).forEach(s=> s.style.transform = "translateY(-100%)");
    $$(".roll__ch", b).forEach(s=> s.style.transform = "translateY(-100%)");
  });
  el.addEventListener("mouseleave", ()=>{
    $$(".roll__ch", a).forEach(s=> s.style.transform = "");
    $$(".roll__ch", b).forEach(s=> s.style.transform = "");
  });
});

/* ==========================================================
   5. ATIDENGIMAS SLENKANT
   ========================================================== */
const io = new IntersectionObserver((es)=>{
  es.forEach(e=>{
    if(e.isIntersecting){
      e.target.classList.add("in");
      if(e.target.dataset.rvOnce !== "no") io.unobserve(e.target);
    }
  });
},{ threshold:.02, rootMargin:"0px 0px -6% 0px" });
function observeReveals(root){
  $$("[data-rv], .fitem, .fig", root||document).forEach(el=>{
    if(!el.dataset.rvBound){ el.dataset.rvBound="1"; io.observe(el); }
  });
}
observeReveals();

/* Atsarginis mechanizmas: jei stebėtojas nesuveikė (pvz., elementas užpildytas vėliau),
   atidengiame viską, kas jau matoma ekrane. */
function rvKick(root){
  $$("[data-rv]", root || document).forEach(el=>{
    if(el.classList.contains("in")) return;
    const r = el.getBoundingClientRect();
    if(r.height > 0 && r.top < innerHeight * 0.96 && r.bottom > 0) el.classList.add("in");
  });
  $$(".fitem:not(.in), .fig:not(.in)", root || document).forEach(el=>{
    const r = el.getBoundingClientRect();
    if(r.height > 0 && r.top < innerHeight * 0.96 && r.bottom > 0) el.classList.add("in");
  });
}
addEventListener("load", ()=> setTimeout(rvKick, 120));
document.addEventListener("alk:ready", ()=> setTimeout(rvKick, 80));

/* Pakopinis uždelsimas grupėse */
$$("[data-stagger]").forEach(g=>{
  const step = parseInt(g.dataset.stagger,10) || 90;
  Array.from(g.children).forEach((c,i)=>{
    const t = c.querySelector("[data-rv]") || (c.hasAttribute("data-rv") ? c : null);
    if(t) t.style.transitionDelay = (i*step)+"ms";
  });
});

/* ==========================================================
   6. PARALAKSAS (slinktis + pelė)
   ========================================================== */
(function parallax(){
  if(RM) return;
  const items = $$("[data-px]").map(el=>({
    el, k: parseFloat(el.dataset.px)||.15, cur:0,
    rot: parseFloat(el.dataset.pxRot)||0,
    sc:  parseFloat(el.dataset.pxScale)||0
  }));
  if(!items.length) return;
  onTick(()=>{
    const vh = innerHeight;
    items.forEach(it=>{
      const r = it.el.getBoundingClientRect();
      if(r.bottom < -400 || r.top > vh+400) return;
      const mid = r.top + r.height/2;
      const p = (mid - vh/2) / vh;              // -1 .. 1
      const y = -p * it.k * vh * .55;
      it.cur = lerp(it.cur, y, .14);
      let t = "translate3d(0,"+it.cur.toFixed(2)+"px,0)";
      if(it.rot) t += " rotate("+(p*it.rot).toFixed(2)+"deg)";
      if(it.sc)  t += " scale("+(1 + Math.abs(p)*it.sc).toFixed(4)+")";
      it.el.style.transform = t;
    });
  });
})();

(function mouseParallax(){
  if(RM || TOUCH) return;
  const items = $$("[data-mx]").map(el=>({ el, k: parseFloat(el.dataset.mx)||14, x:0, y:0, tx:0, ty:0 }));
  if(!items.length) return;
  addEventListener("mousemove", e=>{
    const nx = (e.clientX/innerWidth - .5)*2, ny = (e.clientY/innerHeight - .5)*2;
    items.forEach(it=>{ it.tx = -nx*it.k; it.ty = -ny*it.k; });
  }, {passive:true});
  onTick(()=>{
    items.forEach(it=>{
      it.x = lerp(it.x, it.tx, .07); it.y = lerp(it.y, it.ty, .07);
      it.el.style.setProperty("--mpx", it.x.toFixed(2)+"px");
      it.el.style.setProperty("--mpy", it.y.toFixed(2)+"px");
      const base = it.el.dataset.mxBase || "";
      it.el.style.transform = base + " translate3d("+it.x.toFixed(2)+"px,"+it.y.toFixed(2)+"px,0)";
    });
  });
})();

/* ==========================================================
   7. 3D PAKRYPIMAS SU GYLIU
   ========================================================== */
function initTilt(root){
  if(RM || TOUCH) return;
  $$("[data-tilt]", root).forEach(card=>{
    if(card.dataset.tiltInit) return; card.dataset.tiltInit="1";
    const inner = $(".tilt__in", card) || card.firstElementChild;
    if(!inner) return;
    const max = parseFloat(card.dataset.tilt) || 8;
    let rx=0, ry=0, trx=0, try_=0, active=false;
    card.addEventListener("mousemove", e=>{
      const r = card.getBoundingClientRect();
      const px = (e.clientX - r.left)/r.width, py = (e.clientY - r.top)/r.height;
      trx = (py - .5) * -2 * max; try_ = (px - .5) * 2 * max;
      card.style.setProperty("--mx", (px*100)+"%");
      card.style.setProperty("--my", (py*100)+"%");
      active = true;
    });
    card.addEventListener("mouseleave", ()=>{ trx=0; try_=0; });
    onTick(()=>{
      if(!active) return;
      rx = lerp(rx, trx, .13); ry = lerp(ry, try_, .13);
      inner.style.transform = "rotateX("+rx.toFixed(2)+"deg) rotateY("+ry.toFixed(2)+"deg)";
      if(trx===0 && try_===0 && Math.abs(rx)<.02 && Math.abs(ry)<.02){ active=false; inner.style.transform=""; }
    });
  });
}

/* ==========================================================
   8. „IŠKASIMO" ATIDENGIMAS — žymeklio kaukė
   ========================================================== */
function initExc(root){
  $$(".exc", root).forEach(el=>{
    if(el.dataset.excInit) return; el.dataset.excInit="1";
    let r=0, tr=0;
    if(!TOUCH && !RM){
      el.addEventListener("mousemove", e=>{
        const b = el.getBoundingClientRect();
        el.style.setProperty("--mx", ((e.clientX-b.left)/b.width*100)+"%");
        el.style.setProperty("--my", ((e.clientY-b.top)/b.height*100)+"%");
        tr = parseFloat(el.dataset.exc) || 90;
      });
      el.addEventListener("mouseleave", ()=>{ tr = 0; });
      onTick(()=>{
        if(Math.abs(r-tr) < .3) return;
        r = lerp(r, tr, .12);
        el.style.setProperty("--r", r.toFixed(1)+"px");
      });
    } else {
      el.style.setProperty("--r","2000px");
    }
  });
}

/* ==========================================================
   9. VIDEO: tingus krovimas + rodymas užvedus
   ========================================================== */
const VIO = new IntersectionObserver(es=>{
  es.forEach(e=>{
    const v = e.target;
    if(e.isIntersecting){
      if(v.dataset.src && !v.src){ v.src = v.dataset.src; v.load(); }
      if(v.dataset.hoverOnly !== "1"){ const p = v.play(); if(p && p.catch) p.catch(()=>{}); }
    } else if(!v.paused){ v.pause(); }
  });
},{ rootMargin:"320px 0px" });

function initVideo(root){
  $$("video[data-src]", root).forEach(v=>{
    if(v.dataset.vInit) return; v.dataset.vInit="1"; VIO.observe(v);
  });
  $$("[data-hover-video]", root).forEach(box=>{
    if(box.dataset.hvInit) return; box.dataset.hvInit="1";
    const v = $("video", box.querySelector(".act__video") || box);
    if(!v) return;
    v.dataset.hoverOnly = "1";
    const on = ()=>{
      if(v.dataset.src && !v.src){ v.src = v.dataset.src; v.load(); }
      box.classList.add("playing"); const p = v.play(); if(p&&p.catch) p.catch(()=>{});
    };
    const off = ()=>{ box.classList.remove("playing"); v.pause(); };
    box.addEventListener("mouseenter", on);
    box.addEventListener("mouseleave", off);
    box.addEventListener("focusin", on);
    box.addEventListener("focusout", off);
  });
}

/* ==========================================================
   10. SKAITIKLIAI
   ========================================================== */
const CIO = new IntersectionObserver(es=>{
  es.forEach(e=>{
    if(!e.isIntersecting) return;
    const el = e.target; CIO.unobserve(el);
    const to = parseFloat(el.dataset.count);
    const dec = parseInt(el.dataset.dec,10)||0;
    const suf = el.dataset.suffix||"";
    const dur = 1500; const t0 = performance.now();
    (function step(t){
      const k = clamp((t-t0)/dur,0,1);
      const e2 = 1 - Math.pow(1-k, 3);
      el.textContent = num(to*e2, dec) + suf;
      if(k<1) requestAnimationFrame(step);
    })(t0);
  });
},{ threshold:.5 });
function initCounters(root){
  $$("[data-count]", root).forEach(el=>{
    if(el.dataset.cInit) return; el.dataset.cInit="1"; CIO.observe(el);
  });
}

/* ==========================================================
   11. MAGNETINIAI ELEMENTAI
   ========================================================== */
function initMagnet(root){
  if(RM || TOUCH) return;
  $$("[data-magnet]", root).forEach(el=>{
    if(el.dataset.mgInit) return; el.dataset.mgInit="1";
    const k = parseFloat(el.dataset.magnet) || .3;
    let x=0,y=0,tx=0,ty=0,live=false;
    el.addEventListener("mousemove", e=>{
      const r = el.getBoundingClientRect();
      tx = (e.clientX - (r.left+r.width/2))*k;
      ty = (e.clientY - (r.top+r.height/2))*k;
      live = true;
    });
    el.addEventListener("mouseleave", ()=>{ tx=0; ty=0; });
    onTick(()=>{
      if(!live) return;
      x = lerp(x,tx,.18); y = lerp(y,ty,.18);
      el.style.transform = "translate3d("+x.toFixed(2)+"px,"+y.toFixed(2)+"px,0)";
      if(Math.abs(x)<.05 && Math.abs(y)<.05 && tx===0 && ty===0){ live=false; el.style.transform=""; }
    });
  });
}

/* ==========================================================
   12. HORIZONTALUS BĖGIS
   ========================================================== */
(function rails(){
  $$(".rail").forEach(rail=>{
    const track = $(".rail__track", rail);
    const prev = $("[data-rail=prev]", rail), next = $("[data-rail=next]", rail);
    if(!track) return;
    const step = ()=> (track.firstElementChild ? track.firstElementChild.getBoundingClientRect().width + 20 : 320);
    const sync = ()=>{
      if(prev) prev.disabled = track.scrollLeft < 6;
      if(next) next.disabled = track.scrollLeft > track.scrollWidth - track.clientWidth - 6;
    };
    if(prev) prev.addEventListener("click", ()=> track.scrollBy({left:-step()*1.6, behavior:"smooth"}));
    if(next) next.addEventListener("click", ()=> track.scrollBy({left: step()*1.6, behavior:"smooth"}));
    track.addEventListener("scroll", sync, {passive:true}); sync();

    /* vilkimas pele */
    let down=false, sx=0, sl=0, moved=0;
    track.addEventListener("pointerdown", e=>{
      if(e.pointerType==="mouse"){ down=true; sx=e.clientX; sl=track.scrollLeft; moved=0; track.classList.add("dragging"); }
    });
    addEventListener("pointermove", e=>{
      if(!down) return; const d = e.clientX - sx; moved = Math.abs(d);
      track.scrollLeft = sl - d;
    });
    addEventListener("pointerup", ()=>{ if(down){ down=false; track.classList.remove("dragging"); } });
    track.addEventListener("click", e=>{ if(moved>8){ e.preventDefault(); e.stopPropagation(); } }, true);
  });
})();

/* ==========================================================
   13. BĖGANTI JUOSTA su slinkties greičiu
   ========================================================== */
(function marquee(){
  $$(".mq").forEach(mq=>{
    const row = $(".mq__row", mq); if(!row) return;
    const base = parseFloat(mq.dataset.speed) || .6;
    /* dubliuojame, kad užpildytų */
    let need = 2;
    while(row.scrollWidth * need < innerWidth*2.4 && need < 8) need++;
    for(let i=1;i<need;i++) mq.appendChild(row.cloneNode(true));
    let x = 0, w = 0;
    const measure = ()=>{ w = row.scrollWidth; };
    measure(); addEventListener("resize", measure);
    onTick(()=>{
      if(RM) return;
      const boost = clamp(Math.abs(vels)*.16, 0, 7);
      x -= base + boost;
      if(w && x <= -w) x += w;
      const skew = clamp(vels*-.16, -8, 8);
      mq.style.transform = "translate3d("+x.toFixed(2)+"px,0,0) skewX("+skew.toFixed(2)+"deg)";
    });
  });
})();

/* ==========================================================
   14. TECHNIKOS SĄRAŠO PERŽIŪRA (deleguota)
   ========================================================== */
(function peek(){
  if(TOUCH || RM) return;
  const box = document.createElement("div"); box.className = "mach__peek";
  document.body.appendChild(box);
  let x=innerWidth/2, y=innerHeight/2, tx=x, ty=y, cur=null;
  onTick(()=>{ x=lerp(x,tx,.13); y=lerp(y,ty,.13); box.style.left=x+"px"; box.style.top=y+"px"; });
  document.addEventListener("mousemove", e=>{
    const row = e.target.closest(".mach__row");
    if(row){
      tx = e.clientX + 150; ty = e.clientY;
      if(row !== cur){
        cur = row; tx = x = e.clientX+150; ty = y = e.clientY;
        const v = row.dataset.vid, i = row.dataset.img;
        box.innerHTML = v
          ? '<video muted loop playsinline autoplay preload="none" src="'+v+'" poster="'+(i||"")+'"></video>'
          : '<img src="'+(i||"")+'" alt="">';
        box.classList.add("on");
      }
    } else if(cur){ cur = null; box.classList.remove("on"); box.innerHTML = ""; }
  }, {passive:true});
})();

/* ==========================================================
   15. PRILIPUSIOS PAKOPOS — mastelis pagal gylį
   ========================================================== */
(function stickySteps(){
  if(RM) return;
  const steps = $$(".steps .step"); if(!steps.length) return;
  onTick(()=>{
    steps.forEach((s,i)=>{
      const r = s.getBoundingClientRect();
      const top = parseFloat(getComputedStyle(s).top) || 100;
      const over = clamp((top - r.top)/420, 0, 1);
      s.style.transform = "scale("+(1 - over*.055).toFixed(4)+") translateY("+(over*-14).toFixed(1)+"px)";
      s.style.filter = "brightness("+(1 - over*.1).toFixed(3)+")";
      s.style.zIndex = i+1;
    });
  });
})();

/* ==========================================================
   16. DULKĖS (gylio sluoksnis)
   ========================================================== */
function initDust(root){
  if(RM) return;
  $$(".dust", root).forEach(d=>{
    if(d.dataset.dInit) return; d.dataset.dInit="1";
    const n = parseInt(d.dataset.n,10) || 26;
    let h = "";
    for(let i=0;i<n;i++){
      const sz = (Math.random()*3+1).toFixed(1);
      h += '<i style="left:'+(Math.random()*100).toFixed(1)+'%;width:'+sz+'px;height:'+sz+'px;'
        + 'animation-duration:'+(Math.random()*16+13).toFixed(1)+'s;'
        + 'animation-delay:-'+(Math.random()*22).toFixed(1)+'s;'
        + '--dx:'+((Math.random()-.5)*180).toFixed(0)+'px;'
        + 'opacity:'+(Math.random()*.5+.25).toFixed(2)+'"></i>';
    }
    d.innerHTML = h;
  });
  $$(".prod__grains", root).forEach(g=>{
    if(g.dataset.dInit) return; g.dataset.dInit="1";
    let h="";
    for(let i=0;i<10;i++){
      h += '<i style="left:'+(Math.random()*100).toFixed(0)+'%;top:'+(Math.random()*30).toFixed(0)
        + '%;animation-delay:'+(Math.random()*.9).toFixed(2)+'s"></i>';
    }
    g.innerHTML = h;
  });
}

/* Bendras pakartotinis skenavimas po dinaminio turinio */
function rescan(root){
  root = root || document;
  initTilt(root); initExc(root); initVideo(root); initCounters(root);
  initMagnet(root); initDust(root);
  $$(".range", root).forEach(r=>{ if(!r.dataset.rInit){ r.dataset.rInit="1"; bindRange(r); } });
  requestAnimationFrame(()=> rvKick(root));
  setTimeout(()=> rvKick(root), 420);
  $$(".field select", root).forEach(sel=>{
    if(sel.dataset.sInit) return; sel.dataset.sInit="1";
    const upd = ()=> sel.classList.toggle("filled", !!sel.value);
    sel.addEventListener("change", upd); upd();
  });
  observeReveals(root);
}

/* ==========================================================
   17. KREPŠELIO SKYDELIS (turinį pateikia WooCommerce)
   ========================================================== */
function openDrawer(){ const d=$(".drawer"); if(d) d.classList.add("on"); }
function closeDrawer(){ const d=$(".drawer"); if(d) d.classList.remove("on"); }
document.addEventListener("click", e=>{
  if(e.target.closest("[data-open-cart]")){ e.preventDefault(); openDrawer(); }
  if(e.target.closest("[data-close-cart]") || e.target.classList.contains("drawer__bg")) closeDrawer();
});

/* Pranešimas */
let toastT;
function toast(html){
  let t = $(".toast");
  if(!t){ t = document.createElement("div"); t.className="toast"; document.body.appendChild(t); }
  t.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D8F250" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>' + html;
  t.classList.add("on"); clearTimeout(toastT);
  toastT = setTimeout(()=> t.classList.remove("on"), 3400);
}
window.alkToast = toast;
window.alkOpenCart = openDrawer;
window.alkCloseCart = closeDrawer;

/* ==========================================================
   18. FORMOS
   ========================================================== */
/* Select „filled" būsena etiketei */
$$(".field select").forEach(s=>{
  const upd = ()=> s.classList.toggle("filled", !!s.value);
  s.addEventListener("change", upd); upd();
});

/* Slankiklių užpildas */
function bindRange(r){
  const upd = ()=>{
    const p = ((r.value - r.min) / (r.max - r.min))*100;
    r.style.setProperty("--p", p+"%");
  };
  r.addEventListener("input", upd); upd();
}
$$(".range").forEach(bindRange);
window.alkBindRange = bindRange;

/* Sklandi vidinių nuorodų slinktis */
document.addEventListener("click", e=>{
  const a = e.target.closest('a[href^="#"]:not([href="#"])');
  if(!a) return;
  const t = document.querySelector(a.getAttribute("href"));
  if(!t) return;
  e.preventDefault();
  window.scrollTo({ top: t.getBoundingClientRect().top + scrollY - 90, behavior: RM ? "auto" : "smooth" });
});

/* Eksportas puslapių scenarijams */
rescan(document);
window.ALKUI = { $, $$, lerp, clamp, eur, num, onTick, observeReveals, splitChars, toast, bindRange, rescan, openDrawer, closeDrawer, RM, TOUCH };
document.dispatchEvent(new Event("alk:ui"));
})();
