<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/layout.php';
$p = [
    'path' => '/things-to-do-ballina/',
    'title' => 'Things to Do near Ballina, Co. Mayo | An Bothán Dubh',
    'description' => 'Walks from the door, salmon and coarse fishing, Dark Sky Park stargazing, Enniscrone surf and Wild Atlantic Way day trips from our off-grid cabin near Ballina.',
    'image' => '/photos/16.webp',
    'jsonld' => [],
];
page_head($p);
?>
<div class="wrap">
<div class="pagehead"><h1>Things to do near Ballina, Co. Mayo</h1><p>The cabin is in Attymass, 7 km south of Ballina, at the foot of the Ox Mountains. Hiking, salmon fishing and heritage are on the doorstep, with the beaches, surf and cliffs of the Wild Atlantic Way an easy day trip.</p></div>

  <section>
    <div class="split">
      <div>
        <h2>Walks from the door in Attymass</h2>
        <p><b>Straight from the door.</b> Walks start right outside the cabin, on quiet country roads and lanes with views to the Ox Mountains. No car needed.</p>
        <p><b>Attymass Loop Walks.</b> The waymarked Attymass loop passes the cabin door. Bog, mountain and history walks of 3 to 10 miles, on lanes and an off-road mountain track.</p>
        <p><b>Ballymore Lough loop.</b> An easy 6.8 km walk around the lake, overlooked by the ruins of Kildermot Abbey, with the Ox Mountains on one side and the River Moy valley on the other.</p>
        <p><b>The Ox Mountains.</b> The Sligo Way follows the line of the range, and Lough Talt sits up in the hills on protected bogland.</p>
      </div>
      <img src="/photos/16-s.webp" alt="Fields and trees around the cabin in Attymass" loading="lazy">
    </div>
  </section>

  <section>
    <h2>Local favourites near Ballina</h2>
    <div class="places">
      <div><h3>Fr. Peyton Centre: garden, mountain views, café</h3><span>3 km</span></div>
      <div><h3>Salmon fishing, River Moy (Attymass waters)</h3><span>nearby</span></div>
      <div><h3>Ballina town, pubs and restaurants</h3><span>7 km</span></div>
      <div><h3>Ballina Golf Club</h3><span>7 km</span></div>
      <div><h3>Humbert Monument</h3><span>9 km</span></div>
      <div><h3>Belleek Woods and Castle</h3><span>11 km</span></div>
      <div><h3>Foxford Woollen Mills and loop walks</h3><span>13 km</span></div>
      <div><h3>Moyne Abbey</h3><span>17 km</span></div>
      <div><h3>Ireland West Airport Knock</h3><span>35 km</span></div>
    </div>
    <p class="small" style="margin-top:12px">Ballina is known as Ireland's salmon capital and holds a salmon festival every July. Belleek Woods covers about 1,000 acres along the River Moy, one of the largest town forests in Europe.</p>
  </section>

  <section>
    <h2>Stargazing and dark skies in Mayo</h2>
    <p>With no street lights nearby, clear nights at the cabin are properly dark, and the stars come out in full. For the darkest skies in Ireland, drive west to <b>Mayo Dark Sky Park</b> in <b>Wild Nephin National Park</b>, an International Dark Sky Park. The national park visitor centre at Ballycroy has trails, views across the Nephin Beg mountains and bogland, and stargazing events through the year.</p>
  </section>

  <section>
    <h2>Sea swimming, surf and saunas at Enniscrone</h2>
    <p>Enniscrone, on the Sligo side of Killala Bay, has 5 km of sandy beach, good sea swimming, surf lessons and board hire, and saunas on the seafront to warm up after a swim. The seaweed baths there have been going for generations.</p>
  </section>

  <section>
    <h2>Fishing near Ballina: salmon and coarse</h2>
    <p>Ballina is known as Ireland's salmon capital. The River Moy runs close to the cabin, and the Attymass waters on its east bank are a well-known salmon fishery. For coarse fishing, Ballymore Lough is about a 10-minute walk from the cabin. Lough Conn and Lough Cullin, south-west of Ballina, are popular for trout. Check permits and seasons locally before you fish.</p>
  </section>

  <section>
    <h2>Day trips on the Wild Atlantic Way</h2>
    <div class="places">
      <div><h3>Downpatrick Head and its blowhole</h3><span>approx. 42 km</span></div>
      <div><h3>Céide Fields, the world's oldest known field systems</h3><span>approx. 45 km</span></div>
      <div><h3>Enniscrone: beach, surf, sea swimming, saunas</h3><span>approx. 20 km</span></div>
      <div><h3>Sligo town, Strandhill and Benbulben</h3><span>approx. 65 km</span></div>
      <div><h3>Belmullet and the Erris peninsula</h3><span>approx. 72 km</span></div>
      <div><h3>Wild Nephin National Park and Mayo Dark Sky Park, Ballycroy</h3><span>approx. 75 km</span></div>
      <div><h3>Achill Island: cliffs, beaches, Keem Bay</h3><span>approx. 85 km</span></div>
      <div><h3>North Mayo Heritage Centre, Enniscoe</h3><span>approx. 22 km</span></div>
    </div>
    <p class="small" style="margin-top:12px">Distances are approximate, by road from the cabin.</p>
  </section>

  <section class="band">
    <div><h2>Stay in the heart of north Mayo</h2><p>Book direct for the best price.</p></div>
    <a href="/book/" class="cta">Check dates</a>
  </section>
</div>
<?php page_foot($p);
