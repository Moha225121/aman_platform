<!doctype html>
<html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>محادثة الجلسة — أمان</title>@vite(['resources/css/app.css'])</head>
<body class="booking-chat-page">
@php $isCounselor=auth()->user()->role==='counselor'; $otherName=$isCounselor?$booking->user->alias:$booking->counselor->name; @endphp
<main class="booking-chat-shell">
    <header class="booking-chat-head"><a href="{{ $isCounselor?route('counselor.dashboard'):route('dashboard').'#bookings' }}">→ العودة</a><img src="{{ asset('images/aman/logo.png') }}" alt="أمان"><div><span>محادثة خاصة وآمنة</span><h1>{{ $otherName }}</h1><small>الحجز #{{ $booking->id }} · {{ $booking->service?->name ?? $booking->supportProgram?->name ?? 'استشارة عامة' }}</small></div></header>
    <section class="booking-chat-card">
        <div class="booking-chat-notice">لا تظهر هذه المحادثة إلا للمسترشد والمرشد المعيّن لهذا الحجز.</div>
        <div class="booking-chat-messages" id="chatMessages"><div class="chat-loading">جارٍ تحميل الرسائل...</div></div>
        <form class="booking-chat-form" id="chatForm">@csrf<textarea name="body" maxlength="2000" rows="1" placeholder="اكتب رسالتك هنا..." required></textarea><button type="submit">إرسال</button></form>
        <p class="booking-chat-error" id="chatError" hidden></p>
    </section>
</main>
<script>
const box=document.getElementById('chatMessages'),form=document.getElementById('chatForm'),error=document.getElementById('chatError');
const messagesUrl=@json(route('bookings.messages',$booking)), sendUrl=@json(route('bookings.messages.store',$booking));let lastSignature='';
function esc(value){const node=document.createElement('div');node.textContent=value;return node.innerHTML}
async function loadMessages(scroll=false){try{const response=await fetch(messagesUrl,{headers:{Accept:'application/json'}});if(!response.ok)throw new Error();const data=await response.json(),signature=data.messages.map(m=>m.id).join(',');if(signature===lastSignature)return;lastSignature=signature;box.innerHTML=data.messages.length?data.messages.map(m=>`<article class="booking-chat-message ${m.mine?'mine':'theirs'}"><b>${esc(m.sender)}</b><p>${esc(m.body)}</p><small>${esc(m.time)}</small></article>`).join(''):'<div class="chat-loading">ابدأ المحادثة برسالة ترحيبية.</div>';if(scroll||data.messages.length)box.scrollTop=box.scrollHeight;error.hidden=true}catch{error.textContent='تعذّر تحديث الرسائل. تحقق من الاتصال.';error.hidden=false}}
form.addEventListener('submit',async event=>{event.preventDefault();const button=form.querySelector('button'),body=form.body.value.trim();if(!body)return;button.disabled=true;try{const response=await fetch(sendUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':form.querySelector('[name=_token]').value},body:JSON.stringify({body})});if(!response.ok)throw new Error();form.body.value='';lastSignature='';await loadMessages(true)}catch{error.textContent='لم تُرسل الرسالة. حاول مرة أخرى.';error.hidden=false}finally{button.disabled=false;form.body.focus()}});
loadMessages(true);setInterval(()=>loadMessages(),4000);
</script></body></html>
