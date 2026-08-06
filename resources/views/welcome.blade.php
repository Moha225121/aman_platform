<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="authenticated" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="description" content="منصة أمان الليبية للدعم النفسي والإرشاد الأسري بخصوصية كاملة.">
    <title>أمان — الفهم بداية التغيير</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="#top" aria-label="أمان - الصفحة الرئيسية">
                <img class="brand-logo" src="{{ asset('images/aman/logo.png') }}" alt="شعار منصة أمان"><span><strong>أمان</strong><small>SAFETY</small></span>
            </a>
            <nav class="desktop-nav" aria-label="التنقل الرئيسي">
                <a href="#consultations">خدماتنا</a><a href="#support-programs">برامج الدعم</a><a href="#available-counselors">المرشدون</a><a href="#about">عن أمان</a>
            </nav>
            <div class="nav-actions">
                <button class="btn btn-ghost login-trigger">تسجيل الدخول</button>
                <button class="btn btn-primary register-trigger">حساب مجهول</button>
                <button class="menu-btn" aria-label="فتح القائمة" aria-expanded="false">☰</button>
            </div>
        </div>
        <div class="mobile-nav"><a href="#consultations">خدماتنا</a><a href="#support-programs">برامج الدعم</a><a href="#available-counselors">المرشدون</a><a href="#about">عن أمان</a></div>
    </header>

    <main id="top">
        <section class="hero">
            <div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
            <div class="container hero-grid">
                <div class="hero-copy">
                    <div class="eyebrow"><span>●</span> مساحة آمنة، لك أنت</div>
                    <h1>لست وحدك.<br><em>ابدأ من هنا.</em></h1>
                    <p>مساحة ليبية آمنة وهادئة تساعدك على فهم ما تمرّ به، والتحدث بحرية، والوصول إلى الدعم المناسب — دون الحاجة للكشف عن هويتك.</p>
                    <div class="hero-actions"><button class="btn btn-primary btn-large chat-trigger">تحدّث مع رفيق أمان <span>←</span></button><a class="btn btn-light btn-large" href="#available-counselors">تصفّح المرشدين</a></div>
                    <div class="trust-row"><span>◉ خصوصية كاملة</span><span>◷ متاح في أي وقت</span><span>♙ مرشدون موثوقون</span></div>
                </div>
                <div class="companion-stage"><img class="companion-person" src="{{ asset('images/aman/companion-welcome.png') }}" alt="رفيق أمان يرحب بك"><div class="companion-card">
                    <div class="card-top"><div class="avatar">ر</div><div><strong>رفيق أمان</strong><small><i></i> متاح الآن</small></div><span class="lock">⌾</span></div>
                    <div class="chat-preview">
                        <div class="message bot">أهلًا بك، أنا هنا لأستمع إليك بهدوء وبدون أحكام. كيف تشعر اليوم؟</div>
                        <div class="mood-label">اختر ما يعبّر عنك الآن</div>
                        <div class="moods"><button data-mood="أشعر بالقلق">قلق</button><button data-mood="أشعر بالضغط">ضغط</button><button data-mood="أشعر بالحزن">حزن</button><button data-mood="أحتاج للحديث">أحتاج للحديث</button></div>
                    </div>
                    <button class="chat-input chat-trigger"><span>اكتب ما تشعر به...</span><b>←</b></button>
                    <p class="private-note">🔒 محادثتك خاصة. لا نطلب اسمك الحقيقي.</p>
                </div></div>
            </div>
            <div class="container stats"><div><strong>100%</strong><span>هوية مجهولة</span></div><div><strong>24/7</strong><span>رفيق أمان متاح</span></div><div><strong>+12</strong><span>تخصصًا إرشاديًا</span></div><div><strong>ليبيا</strong><span>بفهمٍ لسياقنا</span></div></div>
        </section>

        <section class="section services" id="services">
            <div class="container"><div class="section-heading"><span>كيف نساعدك؟</span><h2>الدعم الذي تحتاجه، بالطريقة التي تناسبك</h2><p>ابدأ بخطوة بسيطة، واترك لنا مساعدتك في الوصول إلى المسار الأنسب.</p></div>
                <div class="service-grid">
                    <article class="service-card featured"><div class="service-icon">✦</div><small>ابدأ الآن</small><h3>رفيق أمان الذكي</h3><p>تحدث بحرية وفي أي وقت. يساعدك رفيق أمان على فهم احتياجك واقتراح خطوتك التالية.</p><button class="text-link chat-trigger">ابدأ محادثة ←</button></article>
                    <article class="service-card"><div class="service-icon">♙</div><h3>جلسات إرشادية</h3><p>اختر مرشدًا متخصصًا واحجز جلستك النصية أو الصوتية أو المرئية بخصوصية.</p><a class="text-link" href="#guides">اختر مرشدك ←</a></article>
                    <article class="service-card"><div class="service-icon">◫</div><h3>برامج دعم متخصصة</h3><p>مسارات عملية للأفراد والأزواج والأسر، مصممة لمرافقتك خطوة بخطوة.</p><a class="text-link" href="#programs">اكتشف البرامج ←</a></article>
                </div>
            </div>
        </section>

        <section class="section soft" id="programs"><div class="container split"><div><span class="kicker">أمان لكل مرحلة</span><h2>مهما كان ما تمرّ به، هناك مساحة تفهمك</h2><p>برامجنا تراعي الإنسان والأسرة والسياق الاجتماعي الليبي، وتمنحك دعمًا عمليًا بعيدًا عن الوصم والأحكام.</p><a class="btn btn-primary" href="#services">استكشف كل البرامج</a></div><div class="topic-grid"><div><span>☁</span><b>القلق والضغوط</b><small>فهم المشاعر واستعادة التوازن</small></div><div><span>♡</span><b>الإرشاد الزوجي</b><small>مساحة آمنة للحوار والتفاهم</small></div><div><span>⌂</span><b>الدعم الأسري</b><small>تحديات التربية والعلاقات</small></div><div><span>✿</span><b>دعم الأمهات</b><small>قبل الولادة وبعدها</small></div></div></div></section>

        <section class="privacy" id="about"><div class="container privacy-grid"><div class="shield">⌾</div><div><span class="kicker light">خصوصيتك أولًا</span><h2>هويتك تبقى لك</h2><p>لا نطلب اسمك الحقيقي أو رقم هاتفك أو بريدك الإلكتروني. تنشئ اسمًا مستعارًا، ونمنحك اسم مستخدم فريدًا للعودة إلى حسابك ومتابعة رحلتك بأمان.</p><div class="privacy-points"><span>✓ كلمات مرور مشفّرة</span><span>✓ أقل قدر من البيانات</span><span>✓ حذف حسابك متى شئت</span></div></div><button class="btn btn-white register-trigger">أنشئ حسابك المجهول</button></div></section>
        <section class="consultations" id="consultations"><div class="container"><div class="section-heading"><span>الاستشارات المتاحة</span><h2>تخصصات تفهم ما تمرّ به</h2><p>اختر المجال الأقرب لاحتياجك، وسنساعدك في الوصول إلى المرشد المناسب بسرية واهتمام.</p></div><div class="consultation-grid">@foreach($services as $service)<article><i>{{ mb_substr($service->name,0,1) }}</i><h3>{{ $service->name }}</h3><p>{{ $service->description }}</p><button class="consultation-more booking-trigger" data-service="{{ $service->id }}">اعرف أكثر</button></article>@endforeach</div></div></section>

        <section class="support-programs" id="support-programs"><div class="container"><div class="section-heading"><span>رعاية تناسب احتياجك</span><h2>برامج دعم متخصصة</h2><p>برامج عملية ترافق الفرد والأسرة خلال المراحل والتحديات المختلفة.</p></div><div class="program-grid">@foreach($programs as $program)<article><i>{{ mb_substr($program->name,7,1) }}</i><h3>{{ $program->name }}</h3><p>{{ $program->description }}</p><button class="program-action booking-trigger" data-program="{{ $program->id }}">اطلب الانضمام</button></article>@endforeach</div></div></section>

        <section class="real-counselors" id="available-counselors"><div class="container"><div class="section-heading"><span>فريق أمان</span><h2>أخصائيون ومرشدون نفسيون</h2><p>كل شخصية في هذا البروتوتايب تحمل نفس الوظيفة داخل أمان: أخصائي ومرشد نفسي.</p></div><div class="real-counselor-grid">@foreach($counselors as $counselor)<article><div class="initials">{{ collect(explode(' ',$counselor->name))->map(fn($p)=>mb_substr($p,0,1))->take(2)->join(' ') }}</div><div class="stars">★★★★★</div><h3>{{ $counselor->name }}</h3><p>{{ $counselor->title }}</p><span>{{ $counselor->specialties }}</span><button class="booking-trigger" data-counselor="{{ $counselor->id }}">احجز جلسة</button></article>@endforeach</div></div></section>
    </main>

    <footer><div class="container footer-grid"><div class="brand footer-brand"><img class="brand-logo" src="{{ asset('images/aman/logo.png') }}" alt="شعار أمان"><span><strong>أمان</strong><small>SAFETY</small></span></div><p>الفهم بداية التغيير</p><div><a href="#services">الخدمات</a><a href="#programs">البرامج</a><button class="footer-policy policy-trigger">سياسة الخصوصية والأمان</button></div><small>© {{ date('Y') }} منصة أمان. جميع الحقوق محفوظة.</small></div></footer>

    <div class="modal" id="authModal" aria-hidden="true"><div class="modal-backdrop"></div><section class="modal-card" role="dialog" aria-modal="true"><button class="modal-close" aria-label="إغلاق">×</button><img class="modal-brand" src="{{ asset('images/aman/logo.png') }}" alt="أمان"><div id="loginPane"><span class="kicker">مرحبًا بعودتك</span><h2>تسجيل الدخول</h2><p>استخدم اسم المستخدم الفريد وكلمة المرور.</p><form><label>اسم المستخدم<input dir="ltr" placeholder="AMAN-48291" required></label><label>كلمة المرور<input type="password" placeholder="••••••••" required></label><button class="btn btn-primary" type="submit">دخول آمن</button></form><button class="switch-auth">ليس لديك حساب؟ أنشئ حسابًا مجهولًا</button></div><div id="registerPane" hidden><span class="kicker">بدون بيانات شخصية</span><h2>أنشئ حسابًا مجهولًا</h2><p class="consent-confirmed">✓ قرأت سياسة الخصوصية والأمان ووافقت عليها.</p><form><label>الاسم المستعار<input placeholder="مثال: طمأنينة" required></label><label>كلمة المرور<input type="password" placeholder="8 أحرف على الأقل" minlength="8" required></label><label>تأكيد كلمة المرور<input type="password" placeholder="أعد كتابة كلمة المرور" required></label><input type="hidden" name="policy_accepted" value="1"><button class="btn btn-primary" type="submit">إنشاء حسابي الآمن</button></form><button class="switch-auth">لديك حساب؟ سجّل الدخول</button></div></section></div>

    <div class="modal policy-modal" id="policyModal" aria-hidden="true"><div class="modal-backdrop"></div><section class="policy-card" role="dialog" aria-modal="true" aria-labelledby="policyTitle"><button class="policy-close" aria-label="إغلاق">×</button><div class="policy-head"><img src="{{ asset('images/aman/logo.png') }}" alt=""><div><span>سياسة الخصوصية والأمان</span><h2 id="policyTitle">مرحبًا بك في منصة أمان</h2><p>يرجى قراءة البنود كاملة والموافقة عليها قبل إنشاء الحساب.</p></div></div><div class="policy-scroll" tabindex="0"><h3>خصوصيتك أساس خدمتنا</h3><p>تعتمد منصة أمان مبدأ جمع أقل قدر ممكن من المعلومات. لا نطلب اسمك الحقيقي أو رقمك الوطني أو عنوانك أو صورتك أو بريدك الإلكتروني أو رقم هاتفك عند إنشاء الحساب الأساسي.</p><h3>الحساب المجهول</h3><p>تختار اسمًا مستعارًا وكلمة مرور، ويولّد النظام اسم مستخدم فريدًا. أنت مسؤول عن حفظ بيانات الدخول، وقد يتعذر استرجاع الحساب عند فقدانها لعدم ارتباطه ببيانات شخصية.</p><h3>حماية البيانات</h3><p>تُخزّن كلمات المرور مشفّرة، وتُفصل بيانات الدخول عن سجلات الإرشاد. لا يظهر للمرشد سوى الاسم المستعار ورقم الحالة، ولا نشارك المحادثات إلا عند الضرورة وبصلاحية واضحة.</p><h3>حدود الخدمة والطوارئ</h3><p>أمان خدمة للدعم النفسي والإرشاد الأسري وليست بديلًا عن التشخيص أو العلاج الطبي، وليست مخصّصة لمكالمات الطوارئ أو الحالات الحرجة. عند وجود خطر فوري، تواصل مع خدمات الطوارئ المحلية أو شخص موثوق قريب منك.</p><h3>حقوقك ومسؤوليتك</h3><p>يمكنك حذف محادثاتك أو حسابك. التزم باستخدام المنصة باحترام، وعدم إرسال محتوى مخالف. مشاركة المعلومات اللازمة للاستشارة تتم بإرادتك وبالقدر الذي تختاره.</p><p class="policy-end">وصلت إلى نهاية السياسة — يمكنك الآن تأكيد البنود أدناه.</p></div><div class="policy-checks"><label><input type="checkbox"> أستخدم المنصة بمحض إرادتي.</label><label><input type="checkbox"> أفهم أن الخدمة للدعم والإرشاد وليست بديلًا عن العلاج الطبي.</label><label><input type="checkbox"> أفهم أنها ليست للطوارئ أو الحالات الحرجة.</label><label><input type="checkbox"> أوافق على معالجة المعلومات اللازمة بسرية وفق هذه السياسة.</label><label><input type="checkbox"> ألتزم بالاستخدام المحترم وعدم إرسال محتوى مخالف.</label></div><div class="read-hint">مرّر حتى نهاية السياسة ثم وافق على جميع البنود.</div><button class="btn policy-accept" disabled>قرأت وأوافق — متابعة التسجيل</button></section></div>

    <div class="chat-panel" aria-hidden="true"><header><img class="chat-avatar" src="{{ asset('images/aman/companion-young.png') }}" alt="رفيق أمان"><div><strong>رفيق أمان</strong><small><i></i> <span class="companion-status">معك الآن</span></small></div><button class="chat-close" aria-label="إغلاق">×</button></header><div class="companion-live"><img class="companion-pose" src="{{ asset('images/aman/companion-welcome.png') }}" data-listening="{{ asset('images/aman/companion-welcome.png') }}" data-thinking="{{ asset('images/aman/companion-thinking.png') }}" data-writing="{{ asset('images/aman/companion-notes.png') }}" alt="رفيق أمان"><div class="state-bubble"><b>رفيق أمان يستمع إليك</b><div class="typing-dots"><i></i><i></i><i></i></div></div></div><div class="panel-messages"><div class="message bot">أهلًا بك في مساحتك الآمنة. خذ وقتك، واكتب لي ما الذي يشغلك اليوم؟</div></div><form class="panel-input"><input aria-label="رسالتك" placeholder="اكتب هنا بكل أريحية..."><button aria-label="إرسال">←</button></form><small class="disclaimer">هذا توجيه أولي ولا يغني عن التشخيص أو العلاج المهني.</small></div>
    <div class="toast" role="status"></div>
</body></html>
