/* Booking card: calendar -> your details -> Stripe */
(function(){
  const card=document.getElementById("card"); if(!card) return;
  const API=card.dataset.api||"/api/";
  const notice=card.querySelector(".err"); const noticeHTML=notice?notice.outerHTML:"";
  let A=null, blocked=new Set(), view, start=null, end=null, guests=2, pets=0, stepNow="pick", sentDates=false, busy=false, errMsg="";
  const det={name:"",email:"",phone:"",arrival_time:"",message:""};

  const pad=n=>String(n).padStart(2,"0");
  const ymd=d=>d.getFullYear()+"-"+pad(d.getMonth()+1)+"-"+pad(d.getDate());
  const parse=s=>{const [y,m,d]=s.split("-").map(Number);return new Date(y,m-1,d)};
  const add=(d,n)=>{const x=new Date(d);x.setDate(x.getDate()+n);return x};
  const fmt=d=>d.toLocaleDateString("en-IE",{weekday:"short",day:"numeric",month:"short"});
  const esc=s=>String(s).replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
  const rate=d=>{const w=d.getDay();return (w===5||w===6||w===0)?A.rate_weekend:A.rate_weekday};
  const nights=()=>{const a=[];if(!start||!end)return a;for(let d=new Date(start);d<end;d=add(d,1))a.push(new Date(d));return a};
  const free=(a,b)=>{for(let d=new Date(a);d<b;d=add(d,1))if(blocked.has(ymd(d)))return false;return true};
  const total=()=>{const n=nights();return n.reduce((s,d)=>s+rate(d),0)+(pets?A.pet_fee*n.length:0)};
  const beacon=e=>{try{navigator.sendBeacon?navigator.sendBeacon(API+"event.php?e="+e):fetch(API+"event.php?e="+e)}catch(x){}};

  function load(){
    fetch(card.dataset.avail||API+"availability.php",{cache:"no-store"}).then(r=>r.json()).then(j=>{
      A=j; blocked=new Set(j.blocked); A.firstD=parse(j.first); A.lastD=parse(j.last);
      if(!view) view=new Date(A.firstD.getFullYear(),A.firstD.getMonth(),1);
      if(start&&end&&!free(start,end)){start=end=null;errMsg="Sorry, some of those dates have just been booked."}
      render();
    }).catch(()=>{card.innerHTML='<div class="err">Sorry, the calendar didn\'t load. Please refresh, or message us on WhatsApp.</div>'});
  }

  function pick(d){
    errMsg="";
    if(start&&!end&&d>start&&free(start,d)){end=d}
    else if(!blocked.has(ymd(d))&&d<A.lastD){start=d;end=null}
    render();
    if(start&&end&&!sentDates){sentDates=true;beacon("dates_chosen")}
  }

  function calendar(){
    const y=view.getFullYear(),m=view.getMonth(),first=(new Date(y,m,1).getDay()+6)%7,days=new Date(y,m+1,0).getDate();
    const minView=new Date(A.firstD.getFullYear(),A.firstD.getMonth(),1), maxView=new Date(A.lastD.getFullYear(),A.lastD.getMonth(),1);
    let h='<div class="cal-head"><button type="button" id="prev" aria-label="Previous month"'+(view<=minView?' disabled':'')+'>‹</button><b>'+view.toLocaleDateString("en-IE",{month:"long",year:"numeric"})+'</b><button type="button" id="next" aria-label="Next month"'+(view>=maxView?' disabled':'')+'>›</button></div><div class="grid">';
    ["M","T","W","T","F","S","S"].forEach(x=>h+='<div class="dow" aria-hidden="true">'+x+'</div>');
    for(let i=0;i<first;i++)h+='<div></div>';
    const choosingEnd=start&&!end;
    for(let i=1;i<=days;i++){
      const d=new Date(y,m,i),k=ymd(d),bk=blocked.has(k);let c="",dis=d<A.firstD||d>A.lastD,label=fmt(d);
      if(choosingEnd&&d>start&&free(start,d)){dis=false;if(bk)c="co",label+=", available to leave"}
      else if(bk){c="bk";dis=true;label+=", booked"}
      if(start&&+d===+start||end&&+d===+end)c="sel";else if(start&&end&&d>start&&d<end)c="in";
      if(d.getTime()===A.lastD.getTime()&&!choosingEnd)dis=true;
      h+='<button type="button" data-k="'+k+'" class="'+c+'"'+(dis?' disabled':'')+' aria-label="'+label+'">'+i+'</button>';
    }
    return h+'</div><div class="legend"><span><i style="background:var(--booked)"></i>Booked</span><span><i style="background:var(--plum)"></i>Your stay</span></div>';
  }

  function summary(n){
    const g={};n.forEach(d=>{const r=rate(d);g[r]=(g[r]||0)+1});
    let s="";Object.keys(g).sort().forEach(r=>s+='<div><span>€'+r+' × '+g[r]+' night'+(g[r]>1?'s':'')+'</span><span>€'+r*g[r]+'</span></div>');
    if(pets)s+='<div><span>Pet fee €'+A.pet_fee+' × '+n.length+' nights</span><span>€'+A.pet_fee*n.length+'</span></div>';
    return s+'<div><span>Cleaning</span><span>Included</span></div><div class="tot"><span>Total</span><span>€'+total()+'</span></div>';
  }

  function render(){
    const n=nights(), t=total();
    if(stepNow==="pick"){
      const tooShort=end&&n.length<A.min_nights, tooLong=end&&n.length>A.max_nights;
      const msg=errMsg||(!start?"Tap your arrival date.":!end?"Now tap your departure date.":tooShort?"Minimum stay is "+A.min_nights+" nights.":tooLong?"For stays over "+A.max_nights+" nights, please message us on WhatsApp.":"");
      const ok=end&&!tooShort&&!tooLong;
      card.innerHTML=noticeHTML+'<div class="price">from €'+A.rate_weekday+' <span>/ night</span></div><div class="direct">Book direct: no booking-site service fee</div>'+calendar()+
        '<div class="row"><div class="field"><label>Arrive</label><div>'+(start?fmt(start):'—')+'</div></div><div class="field"><label>Leave</label><div>'+(end?fmt(end):'—')+'</div></div></div>'+
        '<div class="row"><div class="field"><label for="g">Guests</label><select id="g"><option'+(guests===1?' selected':'')+'>1</option><option'+(guests===2?' selected':'')+'>2</option></select></div><div></div></div>'+
        '<label class="check"><input type="checkbox" id="pt"'+(pets?' checked':'')+'> Bringing pets (€'+A.pet_fee+' per night)</label>'+
        '<div class="sum">'+(ok?summary(n):'')+'</div><div class="msg" role="status">'+esc(msg)+'</div>'+
        '<button type="button" class="btn" id="go"'+(ok?'':' disabled')+'>Continue</button><div class="fine">You won\'t be charged until the next step.</div>';
      card.querySelectorAll(".grid button").forEach(b=>b.onclick=()=>pick(parse(b.dataset.k)));
      card.querySelector("#prev").onclick=()=>{view=new Date(view.getFullYear(),view.getMonth()-1,1);render()};
      card.querySelector("#next").onclick=()=>{view=new Date(view.getFullYear(),view.getMonth()+1,1);render()};
      card.querySelector("#g").onchange=e=>{guests=+e.target.value};
      card.querySelector("#pt").onchange=e=>{pets=e.target.checked?1:0;render()};
      card.querySelector("#go").onclick=()=>{stepNow="pay";errMsg="";beacon("details_step");render();card.scrollIntoView({block:"start",behavior:"smooth"})};
    } else {
      const times=["3pm","4pm","5pm","6pm","7pm","8pm","9pm"];
      card.innerHTML='<h2 style="font-size:22px">Your details</h2><div class="sum" style="margin-bottom:12px"><div><span>'+fmt(start)+' → '+fmt(end)+'</span><span>'+n.length+' nights</span></div><div><span>'+guests+' guest'+(guests>1?'s':'')+(pets?' + pets':'')+'</span><span></span></div><div class="tot"><span>To pay now</span><span>€'+t+'</span></div></div>'+
        '<form class="pay" id="pf" novalidate>'+
        '<label class="lbl" for="nm">Full name</label><input id="nm" name="name" autocomplete="name" required value="'+esc(det.name)+'">'+
        '<label class="lbl" for="em">Email</label><input id="em" name="email" type="email" autocomplete="email" required value="'+esc(det.email)+'">'+
        '<label class="lbl" for="ph">Mobile number</label><input id="ph" name="phone" type="tel" autocomplete="tel" required value="'+esc(det.phone)+'">'+
        '<label class="lbl" for="at">Roughly what time will you arrive?</label><select id="at" name="arrival_time"><option value="">Not sure yet</option>'+times.map(x=>'<option'+(det.arrival_time===x?' selected':'')+'>'+x+'</option>').join("")+'</select>'+
        '<label class="lbl" for="ms">Anything we should know? (optional)</label><textarea id="ms" name="message">'+esc(det.message)+'</textarea>'+
        '<input name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">'+
        '<label class="check"><input type="checkbox" id="agree"> I agree to the house rules and cancellation policy</label>'+
        (errMsg?'<div class="err" role="alert">'+esc(errMsg)+'</div>':'')+
        '<button class="btn" type="submit"'+(busy?' disabled':'')+'>'+(busy?'Please wait…':(A.demo?'Pay €'+t+' (preview: no real payment)':'Pay €'+t+' by card'))+'</button></form>'+
        '<div class="stripe">🔒 Secure card payment by Stripe'+(A.test&&!A.demo?' · TEST MODE: use card 4242 4242 4242 4242':'')+'</div><button type="button" class="btn ghost" id="back">Back</button>';
      const f=card.querySelector("#pf");
      f.oninput=()=>{det.name=f.name.value;det.email=f.email.value;det.phone=f.phone.value;det.arrival_time=f.arrival_time.value;det.message=f.message.value};
      card.querySelector("#back").onclick=()=>{stepNow="pick";errMsg="";render()};
      f.onsubmit=e=>{
        e.preventDefault(); if(busy) return; f.oninput();
        if(!det.name.trim()||!/^\S+@\S+\.\S+$/.test(det.email)||det.phone.replace(/\D/g,"").length<7){errMsg="Please fill in your name, email and mobile number.";return render()}
        if(!card.querySelector("#agree").checked){errMsg="Please tick to agree to the house rules and cancellation policy.";return render()}
        busy=true;errMsg="";render();
        if(card.dataset.thanks){location.href=card.dataset.thanks;return} // static preview copy only
        fetch(API+"checkout.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({arrive:ymd(start),depart:ymd(end),guests,pets,agree:1,website:f.website.value,...det})})
          .then(r=>r.json().catch(()=>({error:"Something went wrong. Please try again."})))
          .then(j=>{ if(j.url){location.href=j.url;return}
            busy=false;errMsg=j.error||"Something went wrong. Please try again.";
            if(/booked/.test(errMsg)){stepNow="pick";start=end=null;load()} else render(); })
          .catch(()=>{busy=false;errMsg="Couldn't connect. Please check your signal and try again.";render()});
      };
    }
  }
  load();
})();
