/* ALKURAS — turinio duomenys (prototipe atstoja WooCommerce / ACF laukus) */
window.ALK = (function(){

  const V = "https://videos.pexels.com/video-files/";
  const P = "https://images.pexels.com/photos/";
  const img = (id,slug,w)=> P+id+"/"+(slug||("pexels-photo-"+id))+".jpeg?auto=compress&cs=tinysrgb&w="+(w||1200);

  /* --- Video biblioteka (Pexels, nemokama licencija) --- */
  const video = {
    heroQuarry:   V+"30911567/13223367_1920_1080_30fps.mp4",
    aggregate:    V+"27130531/12081391_1920_1080_30fps.mp4",
    sandSite:     V+"35025189/14837860_1920_1080_25fps.mp4",
    conveyorQ:    V+"30911545/13223506_1920_1080_30fps.mp4",
    conveyorG:    V+"30911632/13223373_1920_1080_30fps.mp4",
    conveyorR:    V+"30911561/13223391_1920_1080_30fps.mp4",
    unload:       V+"30911560/13223285_1920_1080_30fps.mp4",
    factory:      V+"30911547/13223563_1920_1080_30fps.mp4",
    demoPower:    V+"34241190/14511141_1920_1080_60fps.mp4",
    demoHouse:    V+"10971670/10971670-hd_1920_1080_30fps.mp4",
    demoAerial:   V+"36752521/15576095_1920_1080_30fps.mp4",
    crusher:      V+"31232154/13340366_1920_1080_30fps.mp4",
    gravelProc:   V+"38814725/16498523_1920_1080_30fps.mp4",
    loadTruck:    V+"36251027/15373409_1920_1080_30fps.mp4",
    loadRocks:    V+"38889069/16535517_1920_1080_30fps.mp4",
    excavator:    V+"35649966/15107550_1920_1080_50fps.mp4",
    trucksQuarry: V+"17275464/17275464-hd_1920_1080_30fps.mp4",
    sandProc:     V+"35223044/14922523_1920_1080_25fps.mp4",
    /* lengvesnės 720p versijos smulkiems langeliams */
    sm: {
      crusher:   V+"31232154/13340365_1280_720_30fps.mp4",
      demoHouse: V+"10971670/10971670-hd_1280_720_60fps.mp4",
      loadTruck: V+"36251027/15373408_1280_720_30fps.mp4",
      conveyorG: V+"30911632/13223372_1280_720_30fps.mp4",
      excavator: V+"35649966/15107549_1280_720_50fps.mp4",
      sandSite:  V+"35025189/14837858_1280_720_25fps.mp4",
      demoPower: V+"34241190/14511140_1280_720_60fps.mp4",
      unload:    V+"30911560/13223284_1280_720_30fps.mp4"
    }
  };

  /* --- Nuotraukos (Pexels, patikrintos pagal aprašymus) --- */
  const photo = {
    quarryAerial: img(33122152),   /* dronu: smėlio karjeras su transporteriais */
    quarryPit:    img(33122148),   /* atviros kasyklos vaizdas iš viršaus */
    quarryWide:   img(33122146),   /* didelis karjeras su technika */
    quarryMachine:img(4993793),    /* rotorinis ekskavatorius kasykloje */
    quarryTrucks: img(5125104),    /* geltonas karjerinis savivartis */
    quarryDeep:   img(2892618),    /* terasinė kasykla iš oro */
    quarryHill:   img(32507739),   /* smėlio ir žvyro perdirbimo aikštelė */
    quarryConv:   img(31096912),   /* transporteriai su žvyru */
    quarryLake:   img(28758765),   /* karjeras su vandeniu */
    quarryLoad:   img(14484386),   /* savivarčiai ir transporteriai karjere */
    quarryLt:     img(31925763),   /* smėlio karjeras Lietuvoje */
    quarryPlant:  img(38862400),   /* smėlio perdirbimo linija */
    quarryScene:  img(38862399),   /* karjeras, transporteris, smėlio kalnai */
    quarryExc:    img(13130999),   /* ekskavatoriai smėlio karjere */
    quarryMine:   img(36236254),   /* plati kasybos panorama */

    demo1:  img(21914025),  /* griovimas su ekskavatoriumi mieste */
    demo2:  img(11461003),  /* iš dalies nugriautas pastatas */
    demo3:  img(14446390),  /* ekskavatorius valo nuolaužas */
    demo4:  img(11521973),  /* pastato griovimas ekskavatoriumi */
    demo5:  img(17727622),  /* ekskavatorius tarp griuvėsių */
    demo6:  img(38975495),  /* atidengtos konstrukcijos ir nuolaužos */
    demo7:  img(11818456),  /* nugriautas pastatas su technika */
    demo8:  img(27946397),  /* kranai ir ekskavatoriai griovimo objekte */
    demo9:  img(10451774),  /* buldozeris griauna seną pastatą */
    demo10: img(15304083),  /* atidengti pastato aukštai */
    demo11: img(36665324),  /* nespalvota griovimo scena */

    rubble1: img(28041688), /* nuolaužų krūva po griovimo */
    rubble2: img(31249540), /* betono ir plytų krūvos */
    rubble3: img(14562040), /* betono ir plytų nuolaužos iš arti */
    rubble4: img(36134823), /* sukrautos betono plokštės */

    crusher1: img(6890368),  /* mobilus smulkintuvas objekte */
    crusher2: img(17320062), /* smulkintuvas dulkėtame karjere */
    crusher3: img(38862558), /* karjero smulkintuvas */
    crusher4: img(6109687),  /* akmenų smulkinimo mašina */
    crusher5: img(4946886),  /* technika dulkėtame karjere */
    conveyor:  img(17320030),/* transporteris su žvyru iš arti */

    exc1: img(30751525), exc2: img(5125783),  exc3: img(27986721),
    exc4: img(38520033), exc5: img(31607374), exc6: img(28663727),
    loader1: img(12835355), loader2: img(28753112), loader3: img(33201219),
    dozer:  img(416988),

    truck1: img(27627498),  /* geltonas savivartis objekte */
    truck2: img(35177799),  /* savivartis iškrauna akmenis saulėlydyje */
    truck3: img(36506004),  /* savivartis ir ekskavatorius */
    truck4: img(15325401),  /* sunkiasvoris savivartis */
    truck5: img(29174547),  /* mėlynas savivartis iškrauna žvyrą */
    truck6: img(19820169),  /* savivartis iškrauna žvyrą kelyje */
    truck7: img(14206819),  /* didelis savivartis objekte */

    road1: img(26953857),   /* buldozeris ties žvyrkelio įrengimu */
    road2: img(19394246),   /* kelio atnaujinimo technika */
    road3: img(17605956),   /* savivartis miško kelyje */

    man1: img(8961524,null,900),   /* statybininko portretas */
    man2: img(34670926,null,900),  /* besišypsantis darbuotojas su šalmu */
    man3: img(8487760,null,900),   /* inžinierė su šalmu */
    man4: img(37556452,null,900),  /* darbuotojas su geltonu šalmu */
    man5: img(12576220,null,900),  /* barzdotas vyras su kepure */
    man6: img(8487783,null,900)    /* darbuotoja su atšvaitine liemene */
  };

  /* --- Medžiagos (WooCommerce prekės) --- */
  const products = [
    { id:"zvyras-0-16", name:"Žvyras", frac:"0/16 mm", cat:"zvyras",
      desc:"Universalus statybinis žvyras pagrindams, aikštelėms ir betonavimui.",
      price:12.90, unit:"t", density:1.75, tags:["Populiaru"],
      img:img(10921738), img2:img(12044663), quarry:["prudupis","imbare","ruigiai"],
      use:["Pagrindų įrengimas","Betonavimas","Aikštelės"] },

    { id:"zvyras-16-32", name:"Žvyras stambus", frac:"16/32 mm", cat:"zvyras",
      desc:"Stambesnė frakcija drenažui ir apkrovą laikantiems sluoksniams.",
      price:14.50, unit:"t", density:1.70, tags:[],
      img:img(7690440), img2:img(15186541), quarry:["imbare","katyciai"],
      use:["Drenažas","Kelio pagrindas"] },

    { id:"smelis-0-4", name:"Smėlis smulkus", frac:"0/4 mm", cat:"smelis",
      desc:"Sijotas smėlis mūrijimo ir tinkavimo skiediniams.",
      price:9.80, unit:"t", density:1.60, tags:["Sijotas"],
      img:img(36214462), img2:img(30520816), quarry:["prudupis","ruigiai"],
      use:["Mūro skiedinys","Tinkavimas"] },

    { id:"smelis-statybinis", name:"Smėlis statybinis", frac:"0/8 mm", cat:"smelis",
      desc:"Užpildams, grindinio pasluoksniui ir teritorijos lyginimui.",
      price:8.40, unit:"t", density:1.62, tags:[],
      img:img(9653855), img2:img(24182398), quarry:["prudupis","imbare","katyciai","ruigiai"],
      use:["Užpildas","Grindinio pasluoksnis"] },

    { id:"skalda-4-16", name:"Dolomito skalda", frac:"4/16 mm", cat:"skalda",
      desc:"Kietos uolienos skalda takams, aikštelėms ir betono mišiniams.",
      price:18.60, unit:"t", density:1.45, tags:["Populiaru"],
      img:img(5799727), img2:img(13367122), quarry:["imbare","katyciai"],
      use:["Takai","Betono mišiniai"] },

    { id:"skalda-32-63", name:"Dolomito skalda stambi", frac:"32/63 mm", cat:"skalda",
      desc:"Stambi skalda sunkiai apkrautiems pagrindams ir šlaitams.",
      price:17.20, unit:"t", density:1.42, tags:[],
      img:img(36235847), img2:img(12029157), quarry:["katyciai"],
      use:["Šlaitų tvirtinimas","Sunkūs pagrindai"] },

    { id:"betono-skalda-0-40", name:"Betono skalda", frac:"0/40 mm", cat:"perdirbta",
      desc:"Perdirbta betono atliekų skalda — ekonomiškas pagrindų sprendimas.",
      price:7.50, unit:"t", density:1.50, tags:["Perdirbta","Eco"],
      img:img(14562040), img2:img(31249540), quarry:["prudupis","kretinga"],
      use:["Kelio pagrindas","Aikštelės","Užpildas"] },

    { id:"plytu-skalda", name:"Plytų / mūro skalda", frac:"0/63 mm", cat:"perdirbta",
      desc:"Smulkintos mūro atliekos laikinų kelių ir aikštelių įrengimui.",
      price:6.20, unit:"t", density:1.35, tags:["Perdirbta"],
      img:img(32491406), img2:img(30991817), quarry:["kretinga"],
      use:["Laikini keliai","Teritorijos lyginimas"] },

    { id:"zvirgzdas", name:"Žvirgždas", frac:"5/20 mm", cat:"zvyras",
      desc:"Plautas žvirgždas apdailai, drenažui ir kraštovaizdžiui.",
      price:16.40, unit:"t", density:1.55, tags:["Plautas"],
      img:img(8657953), img2:img(9608836), quarry:["imbare"],
      use:["Kraštovaizdis","Drenažas"] },

    { id:"juodzemis", name:"Juodžemis sijotas", frac:"0/10 mm", cat:"gruntas",
      desc:"Sijotas derlingas gruntas vejoms ir želdiniams.",
      price:15.90, unit:"t", density:1.30, tags:[],
      img:img(29555030), img2:img(13931827), quarry:["prudupis","ruigiai"],
      use:["Vejos įrengimas","Želdiniai"] },

    { id:"akmenys-dekoratyviniai", name:"Dekoratyviniai akmenys", frac:"63/150 mm", cat:"skalda",
      desc:"Stambūs akmenys kraštovaizdžio akcentams ir gabionams.",
      price:29.00, unit:"t", density:1.40, tags:["Nauja"],
      img:img(37181458), img2:img(38960403), quarry:["imbare"],
      use:["Gabionai","Kraštovaizdis"] },

    { id:"smelis-zvyro-misinys", name:"Smėlio–žvyro mišinys", frac:"0/32 mm", cat:"zvyras",
      desc:"Kelių ir aikštelių pagrindams — optimalus tankinimas.",
      price:11.20, unit:"t", density:1.80, tags:[],
      img:img(4622069), img2:img(14091422), quarry:["prudupis","imbare","ruigiai","katyciai"],
      use:["Kelio pagrindas","Aikštelės"] }
  ];

  /* --- Karjerai (žemėlapio taškai — schema, ne tikslios koordinatės) --- */
  const quarries = [
    { id:"prudupis", name:"Prūdupio karjeras", region:"Kretingos r.", x:20, y:26,
      materials:"Smėlis · žvyras · juodžemis", hours:"I–V 8:00–17:00", load:"Krautuvas vietoje" },
    { id:"imbare", name:"Imbarės karjeras", region:"Kretingos r.", x:39, y:57,
      materials:"Žvyras · žvirgždas · skalda", hours:"I–V 8:00–17:00, VI 9:00–14:00", load:"Sijojimo įranga" },
    { id:"ruigiai", name:"Ruigių karjeras", region:"Kretingos r.", x:53, y:21,
      materials:"Smėlis · smėlio–žvyro mišinys", hours:"I–V 8:00–16:00", load:"Krautuvas vietoje" },
    { id:"katyciai", name:"Katyčių karjeras", region:"Šilutės r.", x:62, y:76,
      materials:"Skalda · stambus žvyras", hours:"I–V 8:00–17:00", load:"Krautuvas vietoje" },
    { id:"kretinga", name:"Smulkinimo aikštelė", region:"Kretinga", x:28, y:42,
      materials:"Betono ir mūro skalda", hours:"I–V 8:00–17:00", load:"Mobilus smulkintuvas" }
  ];

  /* Miestai — pristatymo demonstracijai */
  const cities = [
    { name:"Kretinga",   near:"prudupis", km:8 },
    { name:"Palanga",    near:"prudupis", km:16 },
    { name:"Klaipėda",   near:"imbare",   km:34 },
    { name:"Šilutė",     near:"katyciai", km:22 },
    { name:"Plungė",     near:"imbare",   km:41 },
    { name:"Skuodas",    near:"ruigiai",  km:38 },
    { name:"Telšiai",    near:"imbare",   km:63 },
    { name:"Šiauliai",   near:"ruigiai",  km:118 },
    { name:"Tauragė",    near:"katyciai", km:74 },
    { name:"Kaunas",     near:"katyciai", km:196 },
    { name:"Vilnius",    near:"katyciai", km:298 }
  ];

  /* --- Projektai --- */
  const projects = [
    { t:"Buvusio gamybinio pastato griovimas", cat:"Griovimas", loc:"Klaipėda",
      year:"2025", facts:["4 200 m²","18 d. d.","96 % perdirbta"], img:photo.demo8, cls:"a" },
    { t:"Imbarės karjero plėtra", cat:"Karjerai", loc:"Kretingos r.",
      year:"2025", facts:["12 ha","Naujas sijotuvas"], img:photo.quarryLt, cls:"b" },
    { t:"Betono atliekų smulkinimas objekte", cat:"Smulkinimas", loc:"Palanga",
      year:"2024", facts:["8 400 t","0/40 frakcija"], img:photo.crusher2, cls:"c" },
    { t:"Sandėlių komplekso demontažas", cat:"Griovimas", loc:"Šilutės r.",
      year:"2024", facts:["2 900 m²","Metalo atskyrimas"], img:photo.demo7, cls:"d" },
    { t:"Kelio pagrindo medžiagų tiekimas", cat:"Tiekimas", loc:"Kretingos r.",
      year:"2024", facts:["14 500 t","0/32 mišinys"], img:photo.road1, cls:"e" },
    { t:"Teritorijos paruošimas statyboms", cat:"Griovimas", loc:"Klaipėdos r.",
      year:"2023", facts:["6 ha","Grunto keitimas"], img:photo.demo9, cls:"f" }
  ];

  /* --- Technikos parkas --- */
  const machines = [
    { n:"Vikšrinis ekskavatorius", s:"30 t klasė · griovimo strėlė", img:photo.exc1, vid:video.sm.excavator },
    { n:"Mobilus žandikaulinis smulkintuvas", s:"iki 250 t/val. · 0/63 mm", img:photo.crusher1, vid:video.sm.crusher },
    { n:"Mobilus sijotuvas", s:"3 frakcijų atskyrimas", img:photo.quarryPlant, vid:video.sm.conveyorG },
    { n:"Frontalinis krautuvas", s:"4,2 m³ kaušas", img:photo.loader1, vid:video.sm.loadTruck },
    { n:"Savivarčiai", s:"20–30 t · tentai", img:photo.truck5, vid:video.sm.unload },
    { n:"Buldozeris", s:"Teritorijų lyginimas", img:photo.dozer, vid:video.sm.excavator },
    { n:"Hidraulinis kūjis ir žirklės", s:"Gelžbetonio ardymas", img:photo.demo3, vid:video.sm.demoPower },
    { n:"Vilkikas su tralu", s:"Technikos pervežimas", img:photo.truck7, vid:video.sm.loadTruck }
  ];

  const testimonials = [
    { t:"Užsakėme 400 t žvyro į du objektus — atvežė per parą, kiekiai sutapo su važtaraščiais. Skaičiuoklė padėjo iš karto suprasti biudžetą.",
      n:"Mindaugas Jurgutis", r:"UAB „Vakarų statyba“", av:photo.man1 },
    { t:"Griovimo darbai buvo atlikti tvarkingai, atliekos išvežtos ir perdirbtos. Gavome visus dokumentus atliekų apskaitai.",
      n:"Rasa Petraitienė", r:"Nekilnojamojo turto vystytoja", av:photo.man3 },
    { t:"Smulkintuvas atvažiavo į objektą — sutaupėme ir vežimo, ir naujos skaldos pirkimo. Pagrindui panaudojome savo betoną.",
      n:"Tomas Bagdonas", r:"Rangovas, Klaipėda", av:photo.man2 }
  ];

  return { video, photo, img, products, quarries, cities, projects, machines, testimonials };
})();
