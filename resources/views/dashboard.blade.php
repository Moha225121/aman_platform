<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>مساحتي — أمان</title>
    @vite(['resources/css/app.css', 'resources/js/chat-notifications.js'])
</head>
<body class="user-dashboard">
@php
    $pending = $bookings->where('status','pending')->count();
    $completed = $bookings->where('status','completed')->count();
    $nextBooking = $bookings->where('status','accepted')->sortBy('scheduled_at')->first();
@endphp

<aside class="user-sidebar" id="userSidebar">
    <a class="user-brand" href="/"><img src="{{ asset('images/aman/logo.png') }}" alt="أمان"><span><b>أمان</b><small>مساحتك الآمنة</small></span></a>
    <nav>
        <a class="active" href="#home" data-section="home"><span>⌂</span> الرئيسية</a>
        <a href="#bookings" data-section="bookings"><span>▣</span> حجوزاتي <i>{{ $bookings->count() }}</i></a>
        <a href="#counselors" data-section="counselors"><span>♧</span> المرشدون</a>
        <a href="#programs" data-section="programs"><span>♡</span> برامج الدعم</a>
    </nav>
    <div class="privacy-mini"><b>⌾ خصوصيتك أولويتنا</b><p>لا نعرض سوى اسمك المستعار، وبياناتك محفوظة بسرية.</p></div>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="sidebar-logout">↪ تسجيل الخروج</button></form>
</aside>

<main class="user-main">
    <header class="user-topbar">
        <button class="sidebar-toggle" type="button" aria-label="فتح القائمة">☰</button>
        <div><span class="online-dot"></span><small>مساحتك الخاصة آمنة</small></div>
        <button class="notification-button" type="button" aria-label="الإشعارات">♢ @if($pending)<i>{{ $pending }}</i>@endif</button>
    </header>

    <div class="dashboard-content">
        @if(session('created_username'))
            <div class="account-alert dashboard-alert"><b>احتفظ باسم المستخدم في مكان آمن:</b> <span dir="ltr">{{ session('created_username') }}</span><button type="button" data-copy="{{ session('created_username') }}">نسخ</button></div>
        @endif
        @if(session('success'))<div class="success-banner">✓ {{ session('success') }}</div>@endif

        <section id="home" class="dashboard-view active">
            <div class="dashboard-welcome">
                <div><span class="welcome-kicker">مرحبًا بعودتك</span><h1>{{ auth()->user()->alias }}، كيف تشعر اليوم؟</h1><p>هذه مساحتك الخاصة لمتابعة رحلتك والحصول على الدعم الذي يناسبك.</p><div class="mood-check"><span>تسجيل شعورك:</span><button>😌</button><button>🙂</button><button>😐</button><button>😟</button><button>😣</button></div></div>
                <img src="{{ asset('images/aman/companion-welcome.png') }}" alt="رفيق أمان">
            </div>

            <div class="dashboard-stats">
                <article><span class="stat-icon teal">◷</span><div><strong>{{ $pending }}</strong><small>طلبات قيد المراجعة</small></div></article>
                <article><span class="stat-icon gold">✓</span><div><strong>{{ $completed }}</strong><small>جلسات مكتملة</small></div></article>
                <article><span class="stat-icon blue">♧</span><div><strong>{{ $counselors->count() }}</strong><small>مرشدون متاحون</small></div></article>
            </div>

            <div class="dashboard-grid">
                <section class="dash-card quick-support">
                    <div class="card-heading"><div><span>ابدأ من هنا</span><h2>كيف يمكننا مساعدتك اليوم؟</h2></div></div>
                    <div class="support-actions">
                        <button data-open-booking><i>◫</i><b>احجز استشارة</b><small>اختر مرشدًا واطلب موعدك</small><span>←</span></button>
                        <button data-go="counselors"><i>♧</i><b>تصفّح المرشدين</b><small>تعرّف إلى فريق أمان</small><span>←</span></button>
                        <button data-go="programs"><i>♡</i><b>برامج الدعم</b><small>خطط مصممة لاحتياجك</small><span>←</span></button>
                    </div>
                </section>
                <section class="dash-card next-card">
                    <div class="card-heading"><div><span>متابعة الطلبات</span><h2>موعدك القادم</h2></div><button data-go="bookings">عرض الكل</button></div>
                    @if($nextBooking)
                        <div class="next-session"><div class="date-tile"><b>{{ $nextBooking->scheduled_at?->format('d') ?? '—' }}</b><span>{{ $nextBooking->scheduled_at?->translatedFormat('M') ?? 'قريبًا' }}</span></div><div><b>{{ $nextBooking->counselor?->name ?? 'سيتم تعيين المرشد' }}</b><small>{{ $nextBooking->scheduled_at?->translatedFormat('l، h:i A') ?? 'سنبلغك عند تحديد الموعد' }}</small></div></div>
                    @else
                        <div class="empty-next"><span>◷</span><b>لا يوجد موعد مؤكد حاليًا</b><p>احجز استشارة وسنتواصل معك لتحديد الوقت المناسب.</p><button data-open-booking>طلب موعد</button></div>
                    @endif
                </section>
            </div>

            <section class="identity-strip"><div><span>⌾</span><p><b>هويتك محمية</b><small>يظهر للمرشد اسمك المستعار فقط</small></p></div><div class="identity-code"><small>اسم المستخدم</small><b dir="ltr">{{ auth()->user()->username }}</b><button type="button" data-copy="{{ auth()->user()->username }}">نسخ</button></div></section>
        </section>

        <section id="bookings" class="dashboard-view">
            <div class="view-title"><div><span>رحلتك مع أمان</span><h1>حجوزاتي وطلباتي</h1><p>تابع حالة كل طلب وتفاصيل موعدك من هنا.</p></div><button class="primary-action" data-open-booking>+ طلب استشارة</button></div>
            <div class="booking-filters"><button class="active" data-filter="all">الكل <i>{{ $bookings->count() }}</i></button><button data-filter="pending">قيد المراجعة</button><button data-filter="accepted">مقبول</button><button data-filter="completed">مكتمل</button></div>
            <div class="booking-list">
            @forelse($bookings as $booking)
                <article class="booking-item" data-status="{{ $booking->status }}"><div class="booking-number">#{{ $booking->id }}</div><div class="booking-info"><b>{{ $booking->service?->name ?? $booking->supportProgram?->name ?? 'طلب استشارة عامة' }}</b><small>{{ $booking->counselor?->name ?? 'سيتم اختيار المرشد الأنسب' }} · {{ $booking->created_at->translatedFormat('d M Y') }}</small>@if($booking->note)<p>{{ $booking->note }}</p>@endif @if($booking->status==='accepted')<div class="booking-links"><a class="session-link chat-session-link" data-chat-booking="{{ $booking->id }}" href="{{ route('bookings.chat',$booking) }}">محادثة المرشد <span class="chat-unread-badge" @if(!$booking->unread_messages_count) hidden @endif>{{ $booking->unread_messages_count }}</span> ←</a>@if($booking->meeting_url || $booking->location_url)<a class="session-link" href="{{ $booking->session_method==='online'?$booking->meeting_url:$booking->location_url }}" target="_blank" rel="noopener noreferrer">{{ $booking->session_method==='online'?'الانضمام عبر Google Meet':'عرض موقع الجلسة الحضورية' }} ↗</a>@endif</div>@endif</div><span class="status status-{{ $booking->status }}">{{ ['pending'=>'قيد المراجعة','accepted'=>'مقبول','completed'=>'مكتمل','cancelled'=>'ملغى'][$booking->status] }}</span></article>
            @empty<div class="large-empty"><span>◫</span><h3>لا توجد طلبات حتى الآن</h3><p>ابدأ رحلتك بطلب استشارة آمنة وسرية.</p><button data-open-booking>احجز استشارتك الأولى</button></div>@endforelse
            </div>
        </section>

        <section id="counselors" class="dashboard-view">
            <div class="view-title"><div><span>فريق أمان</span><h1>اختر المرشد المناسب لك</h1><p>مختصون جاهزون للاستماع إليك ودعمك بسرية.</p></div></div>
            <div class="dashboard-counselors">@foreach($counselors as $counselor)<article><div class="counselor-avatar">{{ mb_substr($counselor->name,0,1) }}</div><span class="availability">● متاح</span><h3>{{ $counselor->name }}</h3><p>{{ $counselor->title }}</p><small>{{ $counselor->specialties }}</small><div class="counselor-rating">★★★★★ <i>{{ $counselor->rating }}</i></div><button data-open-booking data-counselor="{{ $counselor->id }}">طلب جلسة</button></article>@endforeach</div>
        </section>

        <section id="programs" class="dashboard-view">
            <div class="view-title"><div><span>دعم مستمر</span><h1>برامج مصممة لرحلتك</h1><p>اختر البرنامج الذي يناسب احتياجك وابدأ بخطوة مطمئنة.</p></div></div>
            <div class="dashboard-programs">@foreach($programs as $program)<article><span>♡</span><h3>{{ $program->name }}</h3><p>{{ $program->description }}</p><button data-open-booking data-program="{{ $program->id }}">الانضمام للبرنامج ←</button></article>@endforeach</div>
        </section>
    </div>
</main>

<div class="dashboard-modal" id="bookingModal" aria-hidden="true"><div class="dashboard-modal-backdrop"></div><section><button class="dashboard-modal-close" type="button">×</button><span class="modal-step">خطوة آمنة نحو الدعم</span><h2>طلب استشارة جديدة</h2><p>اختر ما يناسبك وسيراجع فريق أمان طلبك بسرية.</p><div class="anonymous-booking-note"><span>⌾</span><div><b>الحجز مجهول الهوية</b><small>لن نطلب منك الاسم أو رقم الهاتف.</small></div></div><form method="POST" action="{{ route('bookings.store') }}">@csrf<label>نوع الاستشارة<select name="service_id"><option value="">استشارة عامة</option>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach</select></label><label>المرشد المفضّل <small>(اختياري)</small><select name="counselor_id"><option value="">اختيار المرشد الأنسب لي</option>@foreach($counselors as $counselor)<option value="{{ $counselor->id }}">{{ $counselor->name }} — {{ $counselor->title }}</option>@endforeach</select></label><input type="hidden" name="support_program_id"><label>ملاحظة تساعدنا على فهم احتياجك <small>(اختياري)</small><textarea name="note" maxlength="1000" placeholder="اكتب ما تشعر بالراحة لمشاركته..."></textarea></label><div class="modal-privacy">⌾ يظهر للمرشد اسمك المستعار فقط</div><button class="submit-booking">إرسال الطلب بأمان</button></form></section></div>

<button class="aman-companion-button" id="companionButton" type="button" aria-label="افتح رفيق أمان">
    <img src="{{ asset('images/aman/companion-welcome.png') }}" alt="رفيق أمان">
    <span><b>رفيق أمان</b><small><i></i> بوت إرشادي ذكي</small></span>
</button>
<section class="aman-companion-panel" id="companionPanel" aria-hidden="true">
    <header><img src="{{ asset('images/aman/companion-welcome.png') }}" alt=""><div><b>رفيق أمان <em>AI</em></b><small><i></i> مساعد إرشادي ذكي</small></div><button type="button" aria-label="إغلاق">×</button></header>
    <div class="companion-messages"><div class="companion-message bot">مرحبًا {{ auth()->user()->alias }} 🌿 أنا رفيق أمان، مساعدك الإرشادي الذكي. أساعدك على فهم ما تمر به، وأقترح الخطوة المناسبة ومتابعة الجلسات مع المختص. أخبرني: ما أكثر شيء يزعجك اليوم؟</div></div>
    <div class="companion-suggestions"><button>أشعر بالقلق</button><button>مزاجي منخفض</button><button>لدي صعوبة في النوم</button><button>متابعة جلساتي</button></div>
    <form class="companion-form"><input type="text" placeholder="اكتب ما تشعر به..." autocomplete="off"><button aria-label="إرسال">←</button></form>
    <footer>⌾ إرشاد أولي بالذكاء الاصطناعي — ليس تشخيصًا أو بديلًا عن المختص ولا يُستخدم للطوارئ</footer>
</section>
<div class="dashboard-toast" role="status"></div>
<script>
const views=document.querySelectorAll('.dashboard-view'), links=document.querySelectorAll('.user-sidebar [data-section]');
function showView(id){views.forEach(v=>v.classList.toggle('active',v.id===id));links.forEach(a=>a.classList.toggle('active',a.dataset.section===id));document.getElementById('userSidebar').classList.remove('open');scrollTo({top:0,behavior:'smooth'});}
links.forEach(a=>a.addEventListener('click',e=>{e.preventDefault();showView(a.dataset.section)}));
document.querySelectorAll('[data-go]').forEach(b=>b.onclick=()=>showView(b.dataset.go));
document.querySelector('.sidebar-toggle').onclick=()=>document.getElementById('userSidebar').classList.toggle('open');
const modal=document.getElementById('bookingModal');
const methodLabel=document.createElement('label');
methodLabel.innerHTML='طريقة الجلسة<select name="session_method" required><option value="" selected disabled>اختر الطريقة</option><option value="in_person">حضورية</option><option value="online">أونلاين</option></select>';
modal.querySelector('form').insertBefore(methodLabel,modal.querySelector('[name="support_program_id"]'));
function openBooking(button){modal.classList.add('open');modal.setAttribute('aria-hidden','false');document.body.style.overflow='hidden';const counselor=button?.dataset.counselor,program=button?.dataset.program;modal.querySelector('[name=counselor_id]').value=counselor||'';modal.querySelector('[name=support_program_id]').value=program||'';}
function closeBooking(){modal.classList.remove('open');modal.setAttribute('aria-hidden','true');document.body.style.overflow='';}
document.querySelectorAll('[data-open-booking]').forEach(b=>b.onclick=()=>openBooking(b));document.querySelector('.dashboard-modal-close').onclick=closeBooking;document.querySelector('.dashboard-modal-backdrop').onclick=closeBooking;
document.querySelectorAll('[data-filter]').forEach(b=>b.onclick=()=>{document.querySelectorAll('[data-filter]').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.querySelectorAll('.booking-item').forEach(x=>x.hidden=b.dataset.filter!=='all'&&x.dataset.status!==b.dataset.filter)});
const toast=document.querySelector('.dashboard-toast');function notify(t){toast.textContent=t;toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),2500)}
document.querySelectorAll('[data-copy]').forEach(b=>b.onclick=()=>navigator.clipboard.writeText(b.dataset.copy).then(()=>notify('تم نسخ اسم المستخدم')));
document.querySelectorAll('.mood-check button').forEach(b=>b.onclick=()=>{document.querySelectorAll('.mood-check button').forEach(x=>x.classList.remove('selected'));b.classList.add('selected');notify('شكرًا لمشاركتنا شعورك اليوم')});
const companionButton=document.getElementById('companionButton'),companionPanel=document.getElementById('companionPanel'),companionMessages=companionPanel.querySelector('.companion-messages');
function toggleCompanion(open=!companionPanel.classList.contains('open')){companionPanel.classList.toggle('open',open);companionPanel.setAttribute('aria-hidden',String(!open));companionButton.classList.toggle('hidden',open);if(open)companionPanel.querySelector('input').focus()}
companionButton.onclick=()=>toggleCompanion(true);companionPanel.querySelector('header button').onclick=()=>toggleCompanion(false);
function companionMessage(text,type='user'){const message=document.createElement('div');message.className='companion-message '+type;message.textContent=text;companionMessages.append(message);companionMessages.scrollTop=companionMessages.scrollHeight;return message}
const userBookings={{ $bookings->count() }}, pendingBookings={{ $pending }};
function guidanceFor(text){const value=text.trim().toLowerCase();
 if(/انتحار|أقتل نفسي|اقتل نفسي|إيذاء نفسي|ايذاء نفسي|خطر فوري/.test(value))return 'سلامتك أهم شيء الآن. لا تبقَ وحدك: تواصل فورًا مع خدمات الطوارئ المحلية أو توجّه لأقرب قسم طوارئ، واطلب من شخص موثوق أن يبقى معك. رفيق أمان غير مخصص لحالات الطوارئ.';
 if(/قلق|توتر|خوف|هلع/.test(value))return 'يبدو أنك تمر بقلق أو توتر. جرّب الآن تنفسًا هادئًا: شهيق 4 ثوانٍ ثم زفير 6 ثوانٍ لخمس مرات. إذا كان القلق متكررًا أو يؤثر في يومك، أوصيك بمتابعة جلسات منتظمة مع مرشد نفسي.';
 if(/نوم|أرق|ارق/.test(value))return 'اضطراب النوم قد يرتبط بالضغط أو القلق. حاول تثبيت موعد النوم، وتجنب المنبهات والشاشات قبله، وسجّل نمط نومك لعدة أيام. إذا استمر، احجز جلسة لمناقشة الأسباب وخطة المتابعة.';
 if(/اكتئاب|حزن|منخفض|فقدان الشغف/.test(value))return 'أفهم أن انخفاض المزاج مرهق. حاول اليوم القيام بخطوة صغيرة وآمنة والتواصل مع شخص تثق به. استمرار الحزن أو تأثيره في حياتك يستحق جلسة مع مختص ومتابعة منتظمة.';
 if(/أسرة|اسرة|زواج|علاقة|خلاف/.test(value))return 'قد تساعدك جلسة إرشاد أسري على فهم الخلاف وتنظيم الحوار في مساحة آمنة. أوصيك بحجز جلسة وتدوين النقاط التي تريد مناقشتها قبل الموعد.';
 if(/متابعة|جلساتي|موعد/.test(value))return userBookings?`لديك ${userBookings} طلب أو جلسة في حسابك${pendingBookings?`، منها ${pendingBookings} قيد المراجعة`:''}. افتح قسم «حجوزاتي» لمتابعة الحالة، ومن الأفضل الالتزام بالمواعيد المتفق عليها مع المرشد.`:'لا توجد جلسات مسجلة حتى الآن. أوصيك بحجز استشارة أولية ليقيّم المرشد احتياجك ويقترح خطة متابعة مناسبة.';
 if(/حجز|استشارة|مرشد/.test(value))return 'يمكنك اختيار المرشد أو نوع الاستشارة من لوحة أمان، ثم إرسال الطلب دون اسم حقيقي أو رقم هاتف. بعد الجلسة الأولى سيقترح المختص وتيرة المتابعة المناسبة.';
 return 'شكرًا لمشاركتك. ساعدني على إرشادك بشكل أفضل: هل يتعلق ما تمر به بالقلق، المزاج، النوم، الأسرة، أم أنك تريد متابعة جلساتك؟';}
const companionHistory=[];
let companionSending=false;
async function companionReply(text){
 if(companionSending)return;
 companionSending=true;
 companionPanel.querySelector('.companion-form button').disabled=true;
 companionMessage(text);const emergency=/انتحار|أقتل نفسي|اقتل نفسي|إيذاء نفسي|ايذاء نفسي|خطر فوري/.test(text.toLowerCase());
 if(emergency){companionMessage(guidanceFor(text),'bot');companionSending=false;companionPanel.querySelector('.companion-form button').disabled=false;return}
 const typing=companionMessage('رفيق أمان يبحث في قاعدة المعرفة...','bot');typing.classList.add('typing');
 try{const response=await fetch('/companion/message',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({message:text,history:companionHistory.slice(-8)})});const data=await response.json();typing.remove();if(!response.ok)throw new Error(data.message||'تعذر الاتصال');companionMessage(data.reply,'bot');companionHistory.push({role:'user',content:text},{role:'assistant',content:data.reply});}
 catch(error){typing.remove();companionMessage(`${guidanceFor(text)}\n\nتعذر الاتصال بالذكاء الاصطناعي الآن، لذلك عُرض إرشاد أمان المحلي.`,'bot')}
 finally{companionSending=false;companionPanel.querySelector('.companion-form button').disabled=false}
}
companionPanel.querySelectorAll('.companion-suggestions button').forEach(b=>b.onclick=()=>companionReply(b.textContent));
companionPanel.querySelector('.companion-form').onsubmit=e=>{e.preventDefault();const input=e.currentTarget.querySelector('input');if(!input.value.trim())return;companionReply(input.value.trim());input.value=''};
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeBooking();toggleCompanion(false)}});
if(location.hash&&document.querySelector(location.hash+'.dashboard-view'))showView(location.hash.slice(1));
</script>
</body></html>
