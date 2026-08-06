<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>محادثة الجلسة — أمان</title>
    @vite(['resources/css/app.css'])
</head>
<body class="booking-chat-page">
@php
    $isCounselor = auth()->id() === $booking->counselor?->user_id;
    $otherName = $isCounselor ? $booking->user->alias : $booking->counselor->name;
    $returnUrl = $isCounselor ? route('counselor.dashboard') : route('dashboard').'#bookings';
@endphp
<main class="booking-chat-shell">
    <header class="booking-chat-head">
        <a class="booking-chat-back" href="{{ $returnUrl }}" aria-label="العودة إلى لوحة التحكم">→ <span>العودة</span></a>
        <a class="booking-chat-brand" href="{{ $returnUrl }}">
            <img src="{{ asset('images/aman/logo.png') }}" alt="شعار أمان">
            <span><strong>أمان</strong><small>SAFETY</small></span>
        </a>
        <div class="booking-chat-person">
            <span class="booking-chat-avatar">{{ mb_substr($otherName, 0, 1) }}</span>
            <div><h1>{{ $otherName }}</h1><small><i></i> محادثة الجلسة متاحة</small></div>
        </div>
        <div class="booking-chat-reference"><span>رقم الحجز</span><b>#{{ $booking->id }}</b></div>
    </header>

    <section class="booking-chat-card" aria-label="محادثة خاصة">
        <div class="booking-chat-context">
            <div><span>الجلسة الإرشادية</span><b>{{ $booking->service?->name ?? $booking->supportProgram?->name ?? 'استشارة عامة' }}</b></div>
            <p><span>⌾</span> هذه مساحة خاصة وآمنة، ولا تظهر رسائلها إلا لك وللطرف الآخر في هذا الحجز.</p>
        </div>
        <div class="booking-chat-messages" id="chatMessages" aria-live="polite" aria-busy="true">
            <div class="chat-loading"><img src="{{ asset('images/aman/logo.png') }}" alt=""><b>مساحتك الآمنة جاهزة</b><span>جارٍ تحميل الرسائل...</span></div>
        </div>
        <p class="booking-chat-error" id="chatError" role="alert" hidden></p>
        <form class="booking-chat-form" id="chatForm">
            @csrf
            <textarea name="body" maxlength="2000" rows="1" aria-label="نص الرسالة" placeholder="اكتب رسالتك هنا بكل أريحية..." required></textarea>
            <button type="submit"><span>إرسال</span> ←</button>
        </form>
        <small class="booking-chat-security">🔒 رسائلك مرتبطة برقم الحجز ولا تظهر خارج هذه الجلسة.</small>
    </section>
</main>
<script>
const box=document.getElementById('chatMessages'),form=document.getElementById('chatForm'),error=document.getElementById('chatError');
const messagesUrl=@json(route('bookings.messages',$booking)),sendUrl=@json(route('bookings.messages.store',$booking));let lastSignature='';
const esc=value=>{const node=document.createElement('div');node.textContent=value;return node.innerHTML};
const emptyState=()=>`<div class="chat-loading"><img src="{{ asset('images/aman/logo.png') }}" alt=""><b>ابدأ المحادثة بأمان</b><span>أرسل رسالة ترحيبية عندما تكون مستعدًا.</span></div>`;
async function loadMessages(scroll=false){try{const response=await fetch(messagesUrl,{headers:{Accept:'application/json'}});if(!response.ok)throw new Error();const data=await response.json(),signature=data.messages.map(m=>m.id).join(',');box.setAttribute('aria-busy','false');if(signature===lastSignature)return;lastSignature=signature;box.innerHTML=data.messages.length?data.messages.map(m=>`<article class="booking-chat-message ${m.mine?'mine':'theirs'}"><b>${esc(m.sender)}</b><p>${esc(m.body)}</p><small>${esc(m.time)}</small></article>`).join(''):emptyState();if(scroll||data.messages.length)box.scrollTop=box.scrollHeight;error.hidden=true}catch{box.setAttribute('aria-busy','false');error.textContent='تعذّر تحديث الرسائل. تحقق من الاتصال.';error.hidden=false}}
form.body.addEventListener('input',()=>{form.body.style.height='auto';form.body.style.height=Math.min(form.body.scrollHeight,120)+'px'});
form.addEventListener('keydown',event=>{if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();form.requestSubmit()}});
form.addEventListener('submit',async event=>{event.preventDefault();const button=form.querySelector('button'),body=form.body.value.trim();if(!body)return;button.disabled=true;try{const response=await fetch(sendUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':form.querySelector('[name=_token]').value},body:JSON.stringify({body})});if(!response.ok)throw new Error();form.body.value='';form.body.style.height='auto';lastSignature='';await loadMessages(true)}catch{error.textContent='لم تُرسل الرسالة. حاول مرة أخرى.';error.hidden=false}finally{button.disabled=false;form.body.focus()}});
loadMessages(true);setInterval(()=>loadMessages(),4000);
</script>
</body>
</html>
