<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/layout.php';
$p = [
    'path' => '/the-cabin/',
    'title' => 'The Cabin | Pet-Friendly Off-Grid Cabin, Ballina, Mayo',
    'description' => 'Inside An Bothán Dubh: a 19 m² solar-powered cabin for two with hot shower, gas stove, deck and private garden. Dogs welcome, €10 a night however many pets.',
    'image' => '/photos/29.webp',
    'jsonld' => [lodging_jsonld()],
];
page_head($p);
?>
<div class="wrap">
  <div class="pagehead"><h1>The Cabin</h1><p>A 19 m² off-grid cabin for two, with its own deck, garden and parking.</p></div>
  <div class="mosaic" id="mosaic"></div>
  <div class="cats" id="cats"></div>

  <section>
    <div class="split">
      <div>
        <h2>A small, solar-powered cabin for two</h2>
        <p class="lede">Guests come for the silence, the darkness and the tranquillity. Powered by solar, this is the place to wind down. Set in quiet countryside with views of the Ox Mountains, there's plenty here to slow you down: peaceful walks, or just sitting on the deck listening to the birds.</p>
        <p>There's no Wi-Fi and no TV, but phone reception is good, so you can stay in touch or tether a laptop if you need to work.</p>
        <div class="facts"><span>2 guests</span><span>1 double bed</span><span>1 bathroom</span><span>19 m²</span><span>Pets welcome</span></div>
      </div>
      <img src="/photos/29-s.webp" alt="Living area with sofa, rug and stove" loading="lazy">
    </div>
  </section>

  <section>
    <h2>What's included in the cabin</h2>
    <ul class="am">
      <li>Comfortable double cabin bed</li><li>Toilet and hot shower</li><li>Gas hob and oven</li><li>Fridge with freezer box</li>
      <li>Kettle for the gas hob, coffee, cooking basics</li><li>Crockery, cutlery, wine glasses</li><li>Gas stove heating</li><li>Towels, linen, extra blankets</li>
      <li>Room-darkening blinds</li><li>Sofa and seating area</li><li>Board games, puzzles and books</li><li>Sound system</li>
      <li>Slippers</li><li>Clothes drying rack and fan</li><li>Deck with bistro table and chairs</li><li>Fire pit and BBQ on request</li>
      <li>Private garden</li><li>Free parking for 1 car</li><li>Self check-in with lockbox</li><li>Smoke and CO alarms, extinguisher, first aid</li>
      <li class="no">Wi-Fi</li><li class="no">TV</li><li class="no">Washing machine</li>
    </ul>
  </section>

  <section id="power">
    <div class="split">
      <img src="/photos/39-s.webp" alt="The black cabin with its solar panels on the roof" loading="lazy">
      <div>
        <h2>Running on sunshine: off-grid solar power</h2>
        <p>The cabin has one socket, good for up to 500 watts. That's plenty for what you'll need:</p>
        <table class="t">
          <tr><td>Phone charging</td><td>about 15 W</td></tr>
          <tr><td>Laptop</td><td>about 65 W</td></tr>
          <tr><td>Hair dryer, kettle and other high-power items</td><td>over 1,000 W · not allowed</td></tr>
        </table>
        <div class="meter" aria-hidden="true"><span></span></div>
        <p class="small">A phone and laptop together use about 16% of the socket's limit. Cooking and heating run on gas.</p>
      </div>
    </div>
  </section>

  <section>
    <h2>Dog-friendly: pets welcome, and meet Sadie</h2>
    <p>Pets are welcome. The pet fee is €10 per night, however many pets you bring. Dogs must stay off the bed, and a charge applies for soiling or damage.</p>
    <div class="split sadie-row" style="margin-top:18px"><img src="/photos/41-s.webp" alt="Sadie, the friendly black and white sheepdog" loading="lazy" style="width:200px;max-width:100%;aspect-ratio:1;border-radius:50%;justify-self:center;object-fit:cover"><p class="sadie" style="margin:0"><b>Meet Sadie.</b> Our friendly sheepdog likes to meet new people and is eager to play. She may bark, but she just wants a friend. Our cat may visit too. Please don't feed them or let them in. We can keep Sadie away if you'd prefer.</p></div>
  </section>

  <section class="band">
    <div><h2>Like what you see?</h2><p>Check availability and book direct.</p></div>
    <a href="/book/" class="cta">Check dates</a>
  </section>
</div>

<div class="gal" id="gal" hidden role="dialog" aria-modal="true" aria-label="Photo gallery">
  <div class="gal-top"><div class="wrap">
    <h2 id="galTitle">All photos</h2><button class="x" id="galClose" aria-label="Close gallery">×</button>
    <div class="tabs" id="tabs" style="flex-basis:100%"></div>
  </div></div>
  <div class="wrap"><div class="gal-grid" id="galGrid"></div></div>
</div>
<div class="lb" id="lb" hidden role="dialog" aria-modal="true" aria-label="Photo viewer">
  <div class="lb-top"><span id="lbCount"></span><button class="x" id="lbClose" aria-label="Close photo">×</button></div>
  <div class="lb-stage"><button class="lb-nav lb-prev" id="lbPrev" aria-label="Previous photo">‹</button><img id="lbImg" alt=""><button class="lb-nav lb-next" id="lbNext" aria-label="Next photo">›</button></div>
  <div class="lb-cap" id="lbCap"></div>
</div>
<?php page_foot($p, ['/assets/gallery.js']);
