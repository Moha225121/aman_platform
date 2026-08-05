const q = (selector) => document.querySelector(selector);
const qa = (selector) => document.querySelectorAll(selector);
const authModal = q('#authModal');
const policyModal = q('#policyModal');
const loginPane = q('#loginPane');
const registerPane = q('#registerPane');
let policyAccepted = false;
const csrf = q('meta[name="csrf-token"]')?.content;

// ربط نماذج الواجهة بنظام Laravel الفعلي
const loginForm = loginPane.querySelector('form');
loginForm.method = 'POST'; loginForm.action = '/login';
loginForm.querySelectorAll('input')[0].name = 'username';
loginForm.querySelectorAll('input')[1].name = 'password';
const registerForm = registerPane.querySelector('form');
registerForm.method = 'POST'; registerForm.action = '/register';
registerForm.querySelectorAll('input')[0].name = 'alias';
registerForm.querySelectorAll('input')[1].name = 'password';
registerForm.querySelectorAll('input')[2].name = 'password_confirmation';
[loginForm, registerForm].forEach(form => { const token=document.createElement('input'); token.type='hidden'; token.name='_token'; token.value=csrf; form.prepend(token); });

function lockPage(locked = true) { document.body.style.overflow = locked ? 'hidden' : ''; }
function openAuth(type = 'login') {
    authModal.classList.add('open');
    authModal.setAttribute('aria-hidden', 'false');
    loginPane.hidden = type !== 'login';
    registerPane.hidden = type === 'login';
    lockPage();
}
function closeAuth() { authModal.classList.remove('open'); authModal.setAttribute('aria-hidden', 'true'); lockPage(false); }
function openPolicy() {
    policyModal.classList.add('open');
    policyModal.setAttribute('aria-hidden', 'false');
    lockPage();
    const scroll = q('.policy-scroll');
    scroll.scrollTop = 0;
    q('.policy-card').classList.remove('policy-read');
    q('.policy-checks').classList.add('locked');
    qa('.policy-checks input').forEach(input => { input.checked = policyAccepted; });
    updatePolicyButton();
}
function closePolicy() { policyModal.classList.remove('open'); policyModal.setAttribute('aria-hidden', 'true'); lockPage(false); }

qa('.login-trigger').forEach(button => button.addEventListener('click', () => openAuth('login')));
qa('.register-trigger').forEach(button => button.addEventListener('click', openPolicy));
qa('.policy-trigger').forEach(button => button.addEventListener('click', openPolicy));
qa('.switch-auth').forEach(button => button.addEventListener('click', () => {
    if (loginPane.hidden) openAuth('login');
    else { closeAuth(); policyAccepted ? openAuth('register') : openPolicy(); }
}));
q('.modal-close').onclick = closeAuth;
authModal.querySelector('.modal-backdrop').onclick = closeAuth;
q('.policy-close').onclick = closePolicy;
policyModal.querySelector('.modal-backdrop').onclick = closePolicy;

const policyScroll = q('.policy-scroll');
policyScroll.addEventListener('scroll', () => {
    const reachedEnd = policyScroll.scrollTop + policyScroll.clientHeight >= policyScroll.scrollHeight - 8;
    if (reachedEnd) {
        q('.policy-card').classList.add('policy-read');
        q('.policy-checks').classList.remove('locked');
        q('.read-hint').textContent = '✓ اكتملت القراءة — وافق على جميع البنود للمتابعة.';
    }
    updatePolicyButton();
});
qa('.policy-checks input').forEach(input => input.addEventListener('change', updatePolicyButton));
function updatePolicyButton() {
    const read = q('.policy-card').classList.contains('policy-read');
    const allChecked = [...qa('.policy-checks input')].every(input => input.checked);
    q('.policy-accept').disabled = !(read && allChecked);
}
q('.policy-accept').onclick = () => {
    policyAccepted = true;
    closePolicy();
    openAuth('register');
};

const panel = q('.chat-panel');
const pose = q('.companion-pose');
const liveState = q('.companion-live');
const stateLabel = q('.state-bubble b');
const statusLabel = q('.companion-status');
let replyTimer;
function setCompanionState(state) {
    const labels = {
        listening: ['رفيق أمان يستمع إليك', 'ينتظر رسالتك'],
        thinking: ['رفيق أمان يفكر في رسالتك', 'يفكر الآن...'],
        writing: ['رفيق أمان يجهّز الرد', 'يكتب ردًا...']
    };
    pose.classList.add('changing');
    setTimeout(() => { pose.src = pose.dataset[state]; pose.classList.remove('changing'); }, 140);
    stateLabel.textContent = labels[state][0];
    statusLabel.textContent = labels[state][1];
    liveState.classList.toggle('busy', state !== 'listening');
}
function openChat() {
    if (q('meta[name="authenticated"]')?.content !== '1') {
        notify('رفيق أمان متاح بعد تسجيل الدخول فقط.');
        openAuth('login');
        return false;
    }
    panel.classList.add('open'); panel.setAttribute('aria-hidden', 'false'); setCompanionState('listening'); q('.panel-input input').focus();
    return true;
}
qa('.chat-trigger').forEach(button => button.addEventListener('click', openChat));
q('.chat-close').onclick = () => panel.classList.remove('open');
function addMessage(text, type) {
    const message = document.createElement('div');
    message.className = `message ${type}`;
    message.textContent = text;
    q('.panel-messages').append(message);
    q('.panel-messages').scrollTop = q('.panel-messages').scrollHeight;
}
qa('.moods button').forEach(button => button.onclick = () => {
    if (!openChat()) return; addMessage(button.dataset.mood, 'user'); setCompanionState('thinking');
    setTimeout(() => setCompanionState('writing'), 700);
    setTimeout(() => { addMessage('شكرًا لأنك شاركتني. هل ترغب أن تخبرني أكثر عما جعلك تشعر بهذا اليوم؟', 'bot'); setCompanionState('listening'); }, 1500);
});
const chatInput = q('.panel-input input');
chatInput.addEventListener('input', () => {
    clearTimeout(replyTimer);
    if (chatInput.value.trim()) setCompanionState('listening');
});
chatInput.addEventListener('focus', () => setCompanionState('listening'));
q('.panel-input').onsubmit = event => {
    event.preventDefault();
    const input = event.currentTarget.querySelector('input');
    if (!input.value.trim()) return;
    addMessage(input.value.trim(), 'user'); input.value = ''; setCompanionState('thinking');
    setTimeout(() => setCompanionState('writing'), 900);
    replyTimer = setTimeout(() => { addMessage('أنا معك. خذ وقتك في التعبير، وسنحاول معًا فهم الخطوة الأنسب لك.', 'bot'); setCompanionState('listening'); }, 1900);
};
q('.menu-btn').onclick = event => {
    const nav = q('.mobile-nav'); nav.classList.toggle('open');
    event.currentTarget.setAttribute('aria-expanded', nav.classList.contains('open'));
};
qa('.mobile-nav a').forEach(link => link.onclick = () => q('.mobile-nav').classList.remove('open'));
const toast = q('.toast');
function notify(text) { toast.textContent = text; toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 2800); }
registerForm.addEventListener('submit', event => {
    const fields=registerForm.querySelectorAll('input[type="password"]');
    if(fields[0].value!==fields[1].value){event.preventDefault();notify('تأكيد كلمة المرور غير مطابق.');}
});
qa('.booking-trigger').forEach(button => button.onclick = () => {
    if(q('meta[name="authenticated"]')?.content !== '1') { notify('يلزم إنشاء حساب مجهول لإتمام الطلب.'); setTimeout(openPolicy,700); return; }
    const form=document.createElement('form'); form.method='POST'; form.action='/bookings';
    const values={_token:csrf,counselor_id:button.dataset.counselor,service_id:button.dataset.service,support_program_id:button.dataset.program};
    Object.entries(values).filter(([,value])=>value).forEach(([name,value])=>{const input=document.createElement('input');input.type='hidden';input.name=name;input.value=value;form.append(input);});
    document.body.append(form); form.submit();
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') { closeAuth(); closePolicy(); panel.classList.remove('open'); }
});
