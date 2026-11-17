/* Footer share buttons */
const $=id=>document.getElementById(id);
/* ---------- Share buttons ---------- */
(function(){
  const SITE=document.getElementById("share").dataset.site||location.origin;
  const TEXT="An Bothán Dubh: a solar-powered off-grid cabin under the Ox Mountains, Co. Mayo";
  const u=encodeURIComponent(SITE), t=encodeURIComponent(TEXT), img=encodeURIComponent(SITE+"/photos/15.webp");
  const ic={
    wa:'<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.2.1.7-.1 1.3z"/></svg>',
    fb:'<svg viewBox="0 0 24 24"><path d="M13.5 22v-8h2.7l.4-3.2h-3.1v-2c0-.9.3-1.5 1.6-1.5h1.7V4.4c-.3 0-1.3-.1-2.5-.1-2.5 0-4.1 1.5-4.1 4.2v2.3H7.4V14h2.8v8h3.3z"/></svg>',
    x:'<svg viewBox="0 0 24 24"><path d="M17.8 3h3l-6.6 7.6L22 21h-6.1l-4.8-6.2L5.6 21h-3l7.1-8.1L2.2 3h6.2l4.3 5.7L17.8 3zm-1 16.2h1.7L7.3 4.7H5.5l11.3 14.5z"/></svg>',
    pin:'<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.8-2.5.9 0 1.3.6 1.3 1.4 0 .9-.5 2.1-.8 3.3-.2 1 .5 1.8 1.5 1.8 1.8 0 3.1-1.9 3.1-4.6 0-2.4-1.7-4.1-4.2-4.1-2.9 0-4.5 2.1-4.5 4.4 0 .9.3 1.8.8 2.3l.1.4-.3 1.1c0 .2-.2.2-.4.1-1.3-.6-2.1-2.5-2.1-4 0-3.3 2.4-6.3 6.9-6.3 3.6 0 6.4 2.6 6.4 6 0 3.6-2.3 6.5-5.4 6.5-1.1 0-2.1-.6-2.4-1.2l-.7 2.5c-.2.9-.9 2.1-1.3 2.8A10 10 0 1 0 12 2z"/></svg>',
    em:'<svg viewBox="0 0 24 24"><path d="M3 5h18v14H3V5zm2 2v.5l7 4.5 7-4.5V7H5zm14 2.9-7 4.5-7-4.5V17h14V9.9z"/></svg>',
    ln:'<svg viewBox="0 0 24 24"><path d="M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a3 3 0 1 1 4.2 4.2l-2 2-1.4-1.4 2-2a1 1 0 1 0-1.4-1.4L12 13.4a1 1 0 0 1-1.4 0zm2.8-2.8a1 1 0 0 1 0 1.4l-3.5 3.5a3 3 0 1 1-4.2-4.2l2-2 1.4 1.4-2 2a1 1 0 1 0 1.4 1.4l3.5-3.5a1 1 0 0 1 1.4 0z"/></svg>'
  };
  const links=[
    ["wa","WhatsApp","https://wa.me/?text="+t+"%20"+u],
    ["fb","Facebook","https://www.facebook.com/sharer/sharer.php?u="+u],
    ["x","X","https://twitter.com/intent/tweet?text="+t+"&url="+u+"&hashtags=AnBothanDubh"],
    ["pin","Pinterest","https://pinterest.com/pin/create/button/?url="+u+"&media="+img+"&description="+t]
  ];
  const el=$("share");
  el.insertAdjacentHTML("beforeend", links.map(l=>'<a href="'+l[2]+'" target="_blank" rel="noopener">'+ic[l[0]]+l[1]+'</a>').join("")+
    '<a href="mailto:?subject='+t+'&body='+u+'">'+ic.em+'Email</a><button type="button" id="copyLink">'+ic.ln+'<span>Copy link</span></button>');
  $("copyLink").onclick=function(){ const s=this.querySelector("span");
    navigator.clipboard.writeText(SITE).then(()=>{s.textContent="Link copied"}).catch(()=>{s.textContent=SITE});
    setTimeout(()=>{s.textContent="Copy link"},2500); };
})();


