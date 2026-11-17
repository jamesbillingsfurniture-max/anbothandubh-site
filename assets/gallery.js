/* Cabin page: photo mosaic, gallery and viewer */
(function(){
/* ---------- Photos ---------- */
const P=[
 [37,"out","The deck and gravel path into the garden, with the Ox Mountains beyond"],[0,"bed","Double bed by the big window"],[29,"live","Living area with sofa, rug and stove"],[35,"kit","Kitchen with copper hood and gas cooker"],[25,"live","Evening by the stove"],
 [39,"out","The cabin with solar panels on the roof"],[38,"out","Sunset over the fields"],[36,"out","Sadie on the lawn, with the Ox Mountains beyond"],[40,"out","Evening sun through the trees"],[16,"out","View over the fields"],[21,"out","Bistro table on the deck"],[22,"out","Deck with a view"],[20,"out","Parking space and driveway"],
 [26,"live","Cheese board by the stove"],[27,"live","Table set for two"],[30,"live","Living area at dusk"],[31,"live","Stove and glass doors"],[34,"live","The gas stove lit"],[10,"live","Morning light through the curtain"],
 [1,"bed","Bedroom corner"],[5,"bed","Bed nook with curtain"],[18,"bed","View from the bed"],[19,"bed","Waking up to the garden"],[28,"bed","Morning coffee in bed"],[32,"bed","Bed nook and step"],[33,"bed","Bed nook"],[24,"bed","Hallway"],
 [23,"kit","Gas cooker and sink"],[6,"kit","Bathroom"],[7,"kit","Walk-in shower"],[8,"kit","Toilet and basin"]
];
const CATS=[["all","All"],["out","Outside"],["live","Living room"],["bed","Bedroom"],["kit","Kitchen & bathroom"]];
const src=(n,s)=>"/photos/"+String(n).padStart(2,"0")+(s?"-s":"")+".webp";
const $=id=>document.getElementById(id);
let cat="all", list=P, idx=0, lastFocus=null;

$("mosaic").innerHTML=P.slice(0,5).map((p,i)=>'<button class="'+(i?"":"big")+'" data-i="'+i+'" aria-label="Open photo: '+p[2]+'"><img src="'+src(p[0],i>0)+'" alt="'+p[2]+'"'+(i?' loading="lazy"':'')+'></button>').join("")+
 '<button class="allbtn" id="allBtn"><svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>See all '+P.length+' photos</button>';
$("cats").innerHTML=CATS.slice(1).map(c=>'<button data-c="'+c[0]+'">'+c[1]+' · '+P.filter(p=>p[1]===c[0]).length+'</button>').join("");

function openGal(c){ lastFocus=document.activeElement; cat=c||"all"; renderGal(); $("gal").hidden=false; document.body.style.overflow="hidden"; $("galClose").focus(); }
function closeGal(){ $("gal").hidden=true; document.body.style.overflow=""; lastFocus&&lastFocus.focus(); }
function renderGal(){
  list=cat==="all"?P:P.filter(p=>p[1]===cat);
  $("galTitle").textContent=(CATS.find(c=>c[0]===cat)[1])+(cat==="all"?" photos":"")+" ("+list.length+")";
  $("tabs").innerHTML=CATS.map(c=>'<button data-c="'+c[0]+'" aria-pressed="'+(c[0]===cat)+'">'+c[1]+'</button>').join("");
  $("galGrid").innerHTML=list.map((p,i)=>'<button data-i="'+i+'" aria-label="Open photo: '+p[2]+'"><img src="'+src(p[0],1)+'" alt="'+p[2]+'" loading="lazy"></button>').join("");
  $("gal").scrollTop=0;
}
function openLb(i){ idx=i; showLb(); $("lb").hidden=false; document.body.style.overflow="hidden"; $("lbClose").focus(); }
function showLb(){ const p=list[idx]; $("lbImg").src=src(p[0]); $("lbImg").alt=p[2]; $("lbCap").textContent=p[2]; $("lbCount").textContent=(idx+1)+" / "+list.length; }
function closeLb(){ $("lb").hidden=true; if($("gal").hidden) document.body.style.overflow=""; }
function step(d){ idx=(idx+d+list.length)%list.length; showLb(); }

$("mosaic").addEventListener("click",e=>{ const b=e.target.closest("button"); if(!b) return; if(b.id==="allBtn") return openGal("all"); list=P; openLb(+b.dataset.i); });
$("cats").addEventListener("click",e=>{ const b=e.target.closest("button"); if(b) openGal(b.dataset.c); });
$("tabs").addEventListener("click",e=>{ const b=e.target.closest("button"); if(b){cat=b.dataset.c; renderGal();} });
$("galGrid").addEventListener("click",e=>{ const b=e.target.closest("button"); if(b) openLb(+b.dataset.i); });
$("galClose").onclick=closeGal; $("lbClose").onclick=closeLb; $("lbPrev").onclick=()=>step(-1); $("lbNext").onclick=()=>step(1);
document.addEventListener("keydown",e=>{
  if(!$("lb").hidden){ if(e.key==="Escape")closeLb(); if(e.key==="ArrowLeft")step(-1); if(e.key==="ArrowRight")step(1); return; }
  if(!$("gal").hidden && e.key==="Escape") closeGal();
});
let tx=null; $("lb").addEventListener("touchstart",e=>{tx=e.touches[0].clientX},{passive:true});
$("lb").addEventListener("touchend",e=>{ if(tx===null)return; const dx=e.changedTouches[0].clientX-tx; if(Math.abs(dx)>40) step(dx<0?1:-1); tx=null; });

})();
