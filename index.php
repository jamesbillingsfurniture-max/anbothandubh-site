<?php
require __DIR__ . '/app/bootstrap.php';
require APP_DIR . '/layout.php';
$p = [
    'path' => '/',
    'title' => 'Bothán Dubh | Off-Grid Cabin near Ballina, Co. Mayo',
    'description' => 'Solar-powered off-grid cabin for two in Attymass, 7 km from Ballina, Mayo. Dark skies, walks from the door, pets welcome. Book direct, no service fee.',
    'jsonld' => [lodging_jsonld()],
];
page_head($p);
?>
<div class="wrap">
  <div class="hero">
    <img src="/photos/15.webp" alt="The cabin deck with a gravel path curving towards the Ox Mountains" width="1600" height="1200" fetchpriority="high">
    <div class="hero-text">
      <h1>Silence, darkness and tranquillity under the Ox Mountains</h1>
      <p>A solar-powered, off-grid cabin for two, 7 km from Ballina in Co. Mayo. Pets welcome.</p>
      <div class="hero-cta"><a href="/book/" class="cta">Check dates &amp; book</a><a href="/the-cabin/" class="cta ghost">See the cabin</a></div>
    </div>
  </div>
  <p class="small" style="margin:14px 0 0"><b>Bothán Dubh</b> (say it <i>“bo-HAWN duv”</i>) is Irish for <i>the black cabin</i>.</p>
  <div class="ratings" style="margin-top:12px"><span class="rating"><b>★ 4.97</b> 34 Airbnb reviews · Superhost</span><span class="rating"><b>9.5</b> Exceptional · 17 Booking.com reviews</span></div>

  <section class="hl">
    <div><img src="/photos/38-s.webp" alt="Sunset over the fields beside the cabin" loading="lazy"><h2 style="font-size:21px;margin:0 0 4px">Silence and real darkness</h2><p>No traffic, no street lights. Birdsong by day, a sky full of stars by night, and Mayo Dark Sky Park within reach.</p></div>
    <div><img src="/photos/39-s.webp" alt="The black cabin with solar panels on the roof" loading="lazy"><h2 style="font-size:21px;margin:0 0 4px">Off-grid, on solar</h2><p>Powered by the sun and heated by a gas stove. No Wi-Fi or TV, but good phone signal if you need it.</p></div>
    <div><img src="/photos/16-s.webp" alt="View over the fields towards the mountains" loading="lazy"><h2 style="font-size:21px;margin:0 0 4px">A base for the Wild Atlantic Way</h2><p>Walk from the door, hike the Ox Mountains, fish the Moy, or drive to Enniscrone, Belmullet, Achill or Sligo.</p></div>
  </section>

  <section>
    <h2>Who comes to stay</h2>
    <div class="who">
      <div><h3>People who want quiet</h3><p>Switch off, read, sleep well and watch the stars. Most guests come for the peace.</p></div>
      <div><h3>Hikers</h3><p>Walks start right outside the door, with the Ox Mountains, the Attymass loops and the Sligo Way all close by.</p></div>
      <div><h3>Anglers</h3><p>Salmon on the River Moy close by, and coarse fishing on Ballymore Lough, a 10-minute walk away.</p></div>
      <div><h3>Sea swimmers and surfers</h3><p>Enniscrone has a long sandy beach, surf, sea swimming and saunas to warm up after.</p></div>
      <div><h3>Wild Atlantic Way explorers</h3><p>A quiet base for day trips to Belmullet, Achill Island, Downpatrick Head and Sligo.</p></div>
    </div>
  </section>

  <section>
    <h2>What guests say about Bothán Dubh</h2>
    <div class="reviews">
      <figure><blockquote>“The location was beautiful, so peaceful. The cabin was spotlessly clean and lovely decor. The host made us feel so welcome with a nice hand written note and fresh lemon water on arrival!”</blockquote><figcaption>Caroline, United Kingdom · Booking.com</figcaption></figure>
      <figure><blockquote>“The cabin was extremely cosy and had fabulous facilities! The shower was amazing, the bed was very very comfy. The decking is a beautiful spot for coffee in the morning.”</blockquote><figcaption>Ryan, United Kingdom · Booking.com</figcaption></figure>
      <figure><blockquote>“It was a lovely spot, quiet and serene. We loved Sadie! The decor was great, good selection of books.”</blockquote><figcaption>Kirstine, Australia · Booking.com</figcaption></figure>
    </div>
  </section>

  <section class="band">
    <div><h2>Book direct and save</h2><p>No booking-site service fee. Pay securely by card and get instant confirmation.</p></div>
    <a href="/book/" class="cta">Check dates</a>
  </section>
</div>
<?php page_foot($p);
