<?php
require __DIR__ . '/app/bootstrap.php';
require APP_DIR . '/layout.php';
$p = [
    'path' => '/',
    'title' => 'An Bothán Dubh | Off-Grid Cabin near Ballina, Co. Mayo',
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
  <p class="small" style="margin:14px 0 0"><b>An Bothán Dubh</b> (say it <i>“un bo-HAWN duv”</i>) is Irish for <i>the black cabin</i>.</p>
  <?php $R = require APP_DIR . '/reviews.php'; $ra = $R['ratings']; ?>
  <div class="ratings" style="margin-top:12px"><span class="rating"><b>★ <?= h($ra['airbnb']['score']) ?></b> <?= (int) $ra['airbnb']['count'] ?> Airbnb reviews · <?= h($ra['airbnb']['extra']) ?></span><span class="rating"><b><?= h($ra['booking']['score']) ?></b> <?= h($ra['booking']['label']) ?> · <?= (int) $ra['booking']['count'] ?> Booking.com reviews</span></div>

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
    <h2>What guests say about An Bothán Dubh</h2>
    <div class="reviews">
      <?php $pool = $R['reviews']; shuffle($pool); foreach (array_slice($pool, 0, 3) as $rv): ?>
      <figure><blockquote>“<?= h($rv[0]) ?>”</blockquote><figcaption><?= h($rv[1]) ?>, <?= h($rv[2]) ?> · <?= h($rv[3]) ?></figcaption></figure>
      <?php endforeach; ?>
    </div>
    <?php if (cfg('google_review_url')): ?><p style="margin-top:16px"><a class="cta ghost" href="<?= h(cfg('google_review_url')) ?>" target="_blank" rel="noopener">Stayed with us? Leave a Google review</a></p><?php endif; ?>
  </section>

  <section class="band">
    <div><h2>Book direct and save</h2><p>No booking-site service fee. Pay securely by card and get instant confirmation.</p></div>
    <a href="/book/" class="cta">Check dates</a>
  </section>
</div>
<?php page_foot($p);
