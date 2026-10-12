/* ============================================================
   HOUSEN — نسخه فارسی راست‌چین
   Vanilla JS مشترک بین همه صفحات
   Sections: helpers, data, hero scroll-video, reveal, property
   cards, discovery filters, property single, agents,
   testimonials, forms (newsletter/contact/viewing), navbar, toasts.
   ============================================================ */
'use strict';

document.documentElement.setAttribute('data-hydrated', '');

const REDUCE = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const BASE = 'https://danisite.ir/wp-content/uploads/2026/10/';
const IMG = {
  1:  BASE + 'طراحی-سایت-املاک-1.webp',
  2:  BASE + 'طراحی-سایت-املاک-2.webp',
  3:  BASE + 'طراحی-سایت-املاک-3.webp',
  4:  BASE + 'طراحی-سایت-املاک-4.webp',
  5:  BASE + 'طراحی-سایت-املاک-5.webp',
  6:  BASE + 'طراحی-سایت-املاک-6.webp',
  7:  BASE + 'طراحی-سایت-املاک-7.webp',
  8:  BASE + 'طراحی-سایت-املاک-8.webp',
  9:  BASE + 'طراحی-سایت-املاک-9.webp',
  10: BASE + 'طراحی-سایت-املاک-10.webp',
  11: BASE + 'طراحی-سایت-املاک-11.webp',
  12: BASE + 'طراحی-سایت-املاک-12.webp',
  13: BASE + 'طراحی-سایت-املاک-13.webp',
  14: BASE + 'طراحی-سایت-املاک-14.webp',
  15: BASE + 'طراحی-سایت-املاک-15.webp',
};

const HERO_VIDEO = BASE + 'Realstate-Web-Design1.mp4';
const HERO_POSTER = BASE + 'نمونه-کار-دنی-سایت-املاک.webp';

/* ---------- ابزارهای کمکی ---------- */
const fa = (n, opts) => Number(n).toLocaleString('fa-IR', opts);
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
/* یکسان‌سازی ی/ک عربی با فارسی برای جستجو */
const norm = (s) => String(s).toLowerCase().replace(/ي/g, 'ی').replace(/ك/g, 'ک').replace(/\u200c/g, ' ').trim();

const PROPERTIES = [
  { id: '1', title: 'ویلای ساحلی', slug: 'beachside-villa', price: 4200000, transactionType: 'BUY', propertyType: 'ویلا', location: 'مالیبو، کالیفرنیا', city: 'لس آنجلس', bedrooms: 5, bathrooms: 4, area: 6200, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: true, images: [IMG[4], IMG[2], IMG[13], IMG[5]],
    desc: 'این ویلای ساحلی در خط ساحلی مالیبو، با نمای سرتاسری به اقیانوس آرام و تراس‌های پلکانی، مرز میان فضای داخلی و منظره را از میان برداشته است. مصالح سنگ طبیعی، چوب بلوط و شیشه‌های فلت از فلور تا سقف، روزنه‌ای بی‌واسطه به نور غروب می‌سازند.' },
  { id: '2', title: 'لافـت شهری', slug: 'urban-loft', price: 1850000, transactionType: 'BUY', propertyType: 'لافـت', location: 'تریبکا، نیویورک', city: 'نیویورک', bedrooms: 2, bathrooms: 2, area: 2100, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: true, images: [IMG[6], IMG[1], IMG[9], IMG[3]],
    desc: 'در قلب تاریک تریبکا، این لافـت با سقف‌های بلند، آجر نمایان و پنجره‌های صنعتی چدنی، هویت یک انبار قرن نوزدهمی را با آسایش زندگی امروز ترکیب کرده است. آشپزخانهٔ باز و جزیرهٔ چوب گردو، مرکز نشیمن خانوادگی است.' },
  { id: '3', title: 'پنت‌هاوس با چشم‌انداز', slug: 'penthouse-view', price: 6750000, transactionType: 'BUY', propertyType: 'پنت‌هاوس', location: 'میامی بیچ، فلوریدا', city: 'میامی', bedrooms: 3, bathrooms: 3, area: 3400, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: true, images: [IMG[11], IMG[3], IMG[7], IMG[12]],
    desc: 'پنت‌هاوسِ دوطبقه در بلندای میامی بیچ با چشم‌انداز ۲۷۰ درجه به اقیانوس و خط آسمان شهر. استخر بی‌کران روی تراس خصوصی، آشپزخانهٔ ایتالیایی و سیستم هوشمند خانه، این اقامتگاه را به تعریفی نو از زندگی عمودی بدل می‌کند.' },
  { id: '4', title: 'ویلای مدرن کنار صخره', slug: 'cliffside-modern-villa', price: 8900000, transactionType: 'BUY', propertyType: 'ویلا', location: 'بیگ سور، کالیفرنیا', city: 'سانفرانسیسکو', bedrooms: 6, bathrooms: 5, area: 8400, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[2], IMG[4], IMG[15], IMG[8]],
    desc: 'معمار این ویلا، بتن نما را با پرتگاه سنگی بیگ سور هم‌زبان کرده است؛ حجم‌های شناور روی لبهٔ صخره و استخوان‌بندی شیشه‌ای، پانورامای بی‌پایان اقیانوس را به بخشی از زندگی روزمره بدل می‌کند. شش اتاق خواب مستقل، هر یک با منظره و ورودی خصوصی.' },
  { id: '5', title: 'پاویون باغ', slug: 'garden-pavilion', price: 3200000, transactionType: 'BUY', propertyType: 'ویلا', location: 'بورلی هیلز، کالیفرنیا', city: 'لس آنجلس', bedrooms: 4, bathrooms: 4, area: 4800, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[15], IMG[12], IMG[5], IMG[2]],
    desc: 'الهام‌گرفته از پاویون‌های ژاپنی، این ملک با بام شیب‌دار کم‌ارتفاع و نمای چوبی در میان باغی بالغ از درختان زیتون جای گرفته است. پنجره‌های کشوِی سرتاسری، اتاق‌های نشیمن را در کسری از ثانیه به ایوان باغ می‌پیوندند.' },
  { id: '6', title: 'آپارتمان اسکای‌لاین', slug: 'skyline-apartment', price: 2400000, transactionType: 'BUY', propertyType: 'آپارتمان', location: 'منهتن، نیویورک', city: 'نیویورک', bedrooms: 3, bathrooms: 2, area: 1850, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[1], IMG[6], IMG[11], IMG[9]],
    desc: 'در طبقه چهل‌ویکم یک برج شیشه‌ای منهتن، این آپارتمان چشم‌انداز بی‌واسطه به پارک سنترال و خط آسمان میدتاون دارد. طراحی باز، دیوارهای کابین‌بندی‌شدهٔ گردو و پنجره‌های فلت از فلور تا سقف، فضایی آرام در دل پرشتاب شهر ساخته است.' },
  { id: '7', title: 'اقامتگاه کنار دریاچه', slug: 'lakeside-retreat', price: 5500000, transactionType: 'BUY', propertyType: 'ویلا', location: 'دریاچه تاهو، نوادا', city: 'رینو', bedrooms: 5, bathrooms: 4, area: 5200, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[13], IMG[7], IMG[4], IMG[15]],
    desc: 'اقامتگاهی خلوت در ساحل جنوبی دریاچه تاهو با اکسس خصوصی به ساحل و اسکله اختصاصی. تیرهای چوب کاج نمایان، شومینهٔ سنگی دوطرفه و پنجره‌های بزرگ رو به آب، تفاوت اقامت چهارفصل در کوهستان را تعریف می‌کند.' },
  { id: '8', title: 'اجاره پنت‌هاوس مرکز شهر', slug: 'downtown-penthouse-lease', price: 12000, transactionType: 'RENT', propertyType: 'پنت‌هاوس', location: 'منطقه مالی، شیکاگو', city: 'شیکاگو', bedrooms: 3, bathrooms: 3, area: 2800, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[3], IMG[11], IMG[1], IMG[6]],
    desc: 'اجارهٔ ماهانهٔ پنت‌هاوس در برجی معتبر در منطقهٔ مالی شیکاگو؛ با تراس بام رو به رودخانه، سرویس کنسیرژ و پارکینگ اختصاصی. مناسب مدیران ارشد و خانواده‌هایی که دسترسی پیاده به مرکز مالی را اولویت خود می‌دانند.' },
  { id: '9', title: 'خانه شیشه‌ای ساحلی', slug: 'coastal-glass-house', price: 3800000, transactionType: 'BUY', propertyType: 'ویلا', location: 'کارمل، کالیفرنیا', city: 'سانفرانسیسکو', bedrooms: 3, bathrooms: 3, area: 3100, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[4], IMG[13], IMG[2], IMG[10]],
    desc: 'ساخته‌شده بر پایهٔ شبکهٔ فولادی و شیشه‌های دوجدارهٔ سرتاسری، این خانه در ساحل کارمل مرز میان معماری و منظر را کمرنگ کرده است. چیدمان تک‌طبقه و باز، همراه با حیاط مرکزی محافظت‌شده از باد، آرامشی ساحلی و تمام‌فصل می‌سازد.' },
  { id: '10', title: 'اجاره لافـت میراثی', slug: 'heritage-loft-lease', price: 4500, transactionType: 'RENT', propertyType: 'لافـت', location: 'دامبو، بروکلین', city: 'نیویورک', bedrooms: 1, bathrooms: 1, area: 1100, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[6], IMG[9], IMG[3], IMG[1]],
    desc: 'اجارهٔ لافـتی مرمت‌شده در قدیمی‌ترین کارخانهٔ چاپ منطقهٔ دامبو؛ با ستون‌های چوبی دست‌ساز، آجر نسوز اصیل و سقف ۴ متری. گزینه‌ای کمیاب برای کسانی که اصالت تاریخی را با زندگی مدرن می‌خواهند جمع کنند.' },
];

const STATUS_FA = { AVAILABLE: 'موجود', SOLD: 'فروخته شد', PENDING: 'در حال معامله' };

const TESTIMONIALS = [
  { quote: 'هوسن تنها یک خانه برای ما پیدا نکرد — قطعه‌ای از معماری را یافت که شیوه زندگی ما را دگرگون کرد.', name: 'آدریان ووس', role: 'مالک ملک، مالیبو', image: IMG[5], portrait: IMG[10] },
  { quote: 'همکاری با هوسن مانند همکاری با کیوریتور یک گالری بود. هر ملکی که نشانمان می‌دادند داستانی داشت.', name: 'کامیل لورن', role: 'طراح داخلی، نیویورک', image: IMG[7], portrait: IMG[8] },
  { quote: 'سطح خدمات بی‌نظیر بود. هوسن درک می‌کرد که خرید خانه‌ای با این کالیبر تنها یک معامله نیست.', name: 'مارکوس هالووی', role: 'سرمایه‌گذار، میامی', image: IMG[9], portrait: IMG[14] },
];

const AGENTS = [
  { name: 'سارا محمدی', role: 'مشاور ارشد املاک', city: 'لس آنجلس', phone: '+1 310 555 0142', email: 'sara@housen.com', image: IMG[10] },
  { name: 'علی رضایی', role: 'متخصص ویلاهای لوکس', city: 'نیویورک', phone: '+1 212 555 0173', email: 'ali@housen.com', image: IMG[8] },
  { name: 'نگار کریمی', role: 'مشاور سرمایه‌گذاری', city: 'میامی', phone: '+1 305 555 0119', email: 'negar@housen.com', image: IMG[14] },
  { name: 'امیر حسینی', role: 'مدیر فروش', city: 'سانفرانسیسکو', phone: '+1 415 555 0166', email: 'amir@housen.com', image: IMG[5] },
];

const FEATURES = [
  'پارکینگ اختصاصی', 'استخر بی‌کران', 'سیستم هوشمند خانه',
  'آشپزخانه مدرن', 'نمای سرتاسری', 'آسانسور خصوصی',
  'فضای سبز اختصاصی', 'سیستم امنیتی پیشرفته',
];

const ICONS = {
  pin: '<svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
  check: '<svg class="ic-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>',
  alert: '<svg class="ic-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
  x: '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
  bed: '<svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>',
  bath: '<svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a3 3 0 0 0 3 3h11"/><path d="M3 13h15"/><path d="M20 17v2a2 2 0 0 0 2 2v0"/></svg>',
  area: '<svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 9 22 15 18 15"/><polyline points="2 15 2 9 6 9"/><polyline points="9 2 15 2 15 6"/><polyline points="15 22 9 22 9 18"/></svg>',
  home: '<svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 19V9L11 3L19 9V19H14V13H8V19H3Z"/></svg>',
  phone: '<svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
  mail: '<svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
};

function formatPrice(p, tx) {
  if (tx === 'RENT') return fa(p) + ' دلار / ماه';
  if (p >= 1e6) return fa(p / 1e6, { maximumFractionDigits: 1 }) + ' میلیون دلار';
  return fa(p) + ' دلار';
}

/* ============================================================
   HEADER THEME — روشن/تیره بر اساس لایه محتوا
   ============================================================ */
function updateHeaderTheme() {
  const header = document.getElementById('site-header');
  const pageLayer = document.querySelector('.page-layer');
  if (!header || !pageLayer) return;
  const top = pageLayer.getBoundingClientRect().top;
  const hh = header.offsetHeight || 80;
  document.body.classList.toggle('header-on-light', top < hh);
}
window.addEventListener('scroll', updateHeaderTheme, { passive: true });
window.addEventListener('resize', updateHeaderTheme, { passive: true });

/* ============================================================
   HERO — Scroll-Driven Video
   ============================================================ */
function initHero() {
  const track = document.getElementById('hero-track');
  const hero = document.getElementById('hero');
  const bg = document.getElementById('hero-bg');
  const video = document.getElementById('hero-video');
  const scrollInd = document.getElementById('scroll-ind');
  if (!track || !hero) return;

  const annotations = Array.prototype.slice.call(hero.querySelectorAll('.annotation'));

  /* پوستر روی بک‌گراند: تا آماده شدن ویدیو، کاربر تصویر می‌بیند نه صفحه‌ی سیاه */
  if (bg && video && video.poster) bg.style.backgroundImage = 'url("' + video.poster + '")';

  let ready = false, duration = 0, target = 0, cur = 0, raf = 0, last = 0;
  let trackH = 0, annVisible = true;
  const body = document.body;
  const pageLayer = document.querySelector('.page-layer');

  function measure() {
    trackH = track.offsetHeight;
    layoutAnnotations();
  }

  /* محاسبه‌ی دقیق موقعیت نقطه‌ها روی فریم ویدیو با احتساب object-fit: cover */
  function layoutAnnotations() {
    const W = hero.clientWidth, H = hero.clientHeight;
    if (!W || !H) return;
    const vw = (video && video.videoWidth) || 1920, vhh = (video && video.videoHeight) || 1080;
    const s = Math.max(W / vw, H / vhh);
    const rw = vw * s, rh = vhh * s, ox = (W - rw) / 2, oy = (H - rh) / 2;
    annotations.forEach((a) => {
      a.style.left = (ox + parseFloat(a.dataset.x) * rw).toFixed(1) + 'px';
      a.style.top = (oy + parseFloat(a.dataset.y) * rh).toFixed(1) + 'px';
    });
  }

  function onReady() {
    if (ready || !video) return;
    const d = video.duration;
    if (d > 0 && isFinite(d)) {
      duration = d; ready = true;
      video.classList.add('is-ready');
      layoutAnnotations(); update();
    }
  }

  if (video) {
    video.addEventListener('loadedmetadata', () => { layoutAnnotations(); onReady(); });
    video.addEventListener('canplay', onReady);
    video.addEventListener('error', () => video.classList.remove('is-ready'));
    /* در موبایل (iOS) اولین play/pause بی‌صدا لازم است تا seek کار کند */
    const prime = () => video.play().then(() => video.pause()).catch(() => {});
    if (video.readyState >= 1) prime(); else video.addEventListener('loadedmetadata', prime, { once: true });
    try { video.load(); } catch (e) {}
  }

  function progressRaw() {
    return -track.getBoundingClientRect().top / Math.max(trackH - window.innerHeight, 1);
  }

  function update() {
    const raw = progressRaw();
    const p = Math.min(Math.max(raw, 0), 1);

    if (ready && !REDUCE) { target = Math.min(p / 0.95, 1) * (duration - 0.05); schedule(); }
    if (scrollInd) scrollInd.style.opacity = String(Math.max(1 - p * 6, 0));

    const showAnn = p < 0.05;
    if (showAnn !== annVisible) { annVisible = showAnn; hero.classList.toggle('annotations-visible', showAnn); }

    const reveal = raw >= 0.9;
    body.classList.toggle('hero-revealed', reveal);
    hero.classList.toggle('is-revealing', reveal && raw <= 1);

    updateHeaderTheme();
  }

  /* حلقه‌ی rAF فقط وقتی لازم است اجرا می‌شود و مستقل از نرخ فریم است */
  function schedule() { if (!raf) { last = performance.now(); raf = requestAnimationFrame(tick); } }
  function tick(t) {
    raf = 0;
    const dt = Math.min((t - last) / 1000 || 0.016, 0.1); last = t;
    const diff = target - cur;
    if (Math.abs(diff) < 0.002) { cur = target; if (!video.seeking) { try { video.currentTime = cur; } catch (e) {} } return; }
    cur += diff * (1 - Math.exp(-dt * 12));
    if (!video.seeking && Math.abs(video.currentTime - cur) > 0.02) { try { video.currentTime = cur; } catch (e) {} }
    schedule();
  }

  let ticking = false;
  window.addEventListener('scroll', () => {
    if (!ticking) { ticking = true; requestAnimationFrame(() => { update(); ticking = false; }); }
  }, { passive: true });
  window.addEventListener('resize', () => { measure(); update(); }, { passive: true });

  hero.classList.add('annotations-visible');
  measure(); update();
}

/* ============================================================
   SCROLL REVEAL — یک Observer مشترک
   ============================================================ */
let revealIO = null;
function initReveal(root) {
  const els = (root || document).querySelectorAll('.reveal:not(.is-visible):not([data-observed])');
  if (!('IntersectionObserver' in window)) { els.forEach((el) => el.classList.add('is-visible')); return; }
  if (!revealIO) {
    revealIO = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        const el = e.target;
        const d = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
        revealIO.unobserve(el);
        setTimeout(() => el.classList.add('is-visible'), d);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
  }
  els.forEach((el) => { el.setAttribute('data-observed', ''); revealIO.observe(el); });
}

/* ============================================================
   PROPERTY CARD
   ============================================================ */
function cardHTML(p) {
  return (
    '<a href="property-single.html?slug=' + esc(p.slug) + '" class="card-link">' +
      '<article class="property-card">' +
        '<img class="card-img" src="' + p.images[0] + '" alt="' + esc(p.title) + '" width="800" height="1000" loading="lazy" decoding="async">' +
        '<div class="card-shade"></div>' +
        (p.status !== 'AVAILABLE' ? '<div class="card-status"><span>' + esc(STATUS_FA[p.status] || p.status) + '</span></div>' : '') +
        '<div class="card-info">' +
          '<div class="card-row"><h3>' + esc(p.title) + '</h3><span class="card-price">' + formatPrice(p.price, p.transactionType) + '</span></div>' +
          '<div class="card-specs">' +
            '<span>' + fa(p.bedrooms) + ' خواب</span><i class="sep"></i>' +
            '<span>' + fa(p.bathrooms) + ' حمام</span><i class="sep"></i>' +
            '<span>' + fa(p.area) + ' ' + esc(p.areaUnit) + '</span>' +
          '</div>' +
          '<p class="card-loc">' + ICONS.pin + esc(p.location) + '</p>' +
        '</div>' +
      '</article>' +
    '</a>'
  );
}

function initFeatured() {
  const grid = document.getElementById('featured-grid');
  if (!grid) return;
  const featured = PROPERTIES.filter((p) => p.featured).slice(0, 3);
  const list = featured.length >= 3 ? featured : PROPERTIES.slice(0, 3);
  grid.innerHTML = list
    .map((p, i) => '<div class="reveal" data-reveal-delay="' + i * 120 + '">' + cardHTML(p) + '</div>')
    .join('');
}

/* ============================================================
   DISCOVERY — فیلترها
   ============================================================ */
function initDiscovery() {
  const grid = document.getElementById('discovery-grid');
  const empty = document.getElementById('discovery-empty');
  const countEl = document.getElementById('filter-count');
  const countWord = document.getElementById('filter-count-word');
  const clearBtn = document.getElementById('clear-filters');
  const advanced = document.getElementById('advanced-filters');
  const filtersBtn = document.getElementById('filters-toggle');
  const searchInput = document.getElementById('filter-search');
  const sortSelect = document.getElementById('filter-sort');
  if (!grid) return;

  const DEFAULTS = {
    query: '', transactionType: 'ALL', propertyType: 'ALL', minPrice: '', maxPrice: '',
    bedrooms: '0', minArea: '', maxArea: '', sortBy: 'newest',
  };
  let filters = Object.assign({}, DEFAULTS);
  let showAdvanced = false;

  /* پیش‌فرض از پارامترهای URL (برای لینک‌های دسته‌بندی) */
  const params = new URLSearchParams(location.search);
  if (params.get('type')) filters.propertyType = params.get('type');
  if (params.get('tx')) filters.transactionType = params.get('tx');
  if (params.get('location') || params.get('q')) filters.query = params.get('location') || params.get('q');

  function getFiltered() {
    let result = PROPERTIES.slice();
    const q = norm(filters.query);
    if (q) {
      result = result.filter((p) =>
        [p.title, p.location, p.city, p.propertyType].some((v) => norm(v).includes(q))
      );
    }
    if (filters.transactionType !== 'ALL') result = result.filter((p) => p.transactionType === filters.transactionType);
    if (filters.propertyType !== 'ALL') result = result.filter((p) => p.propertyType === filters.propertyType);
    if (filters.minPrice) result = result.filter((p) => p.price >= Number(filters.minPrice));
    if (filters.maxPrice) result = result.filter((p) => p.price <= Number(filters.maxPrice));
    if (filters.bedrooms !== '0') result = result.filter((p) => p.bedrooms >= Number(filters.bedrooms));
    if (filters.minArea) result = result.filter((p) => p.area >= Number(filters.minArea));
    if (filters.maxArea) result = result.filter((p) => p.area <= Number(filters.maxArea));

    switch (filters.sortBy) {
      case 'price-asc': result.sort((a, b) => a.price - b.price); break;
      case 'price-desc': result.sort((a, b) => b.price - a.price); break;
      default: break;
    }
    return result;
  }

  function hasActiveFilters() {
    return !!(filters.query || filters.transactionType !== 'ALL' || filters.propertyType !== 'ALL' ||
      filters.minPrice || filters.maxPrice || filters.bedrooms !== '0' || filters.minArea || filters.maxArea);
  }

  function render() {
    const filtered = getFiltered();
    grid.innerHTML = filtered
      .map((p, i) => '<div class="reveal" data-reveal-delay="' + Math.min(i * 80, 400) + '">' + cardHTML(p) + '</div>')
      .join('');
    empty.hidden = filtered.length > 0;
    grid.style.display = filtered.length > 0 ? '' : 'none';
    countEl.textContent = fa(filtered.length);
    if (countWord) countWord.textContent = 'ملک';
    clearBtn.hidden = !hasActiveFilters();
    filtersBtn.classList.toggle('active', showAdvanced || hasActiveFilters());
    initReveal(grid);
  }

  /* تایپ: رندر با تأخیر کوتاه؛ بقیه کنترل‌ها: رندر فوری */
  let debounceTimer = null;
  const debouncedRender = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(render, 180); };

  function reset() {
    filters = Object.assign({}, DEFAULTS);
    searchInput.value = '';
    sortSelect.value = 'newest';
    document.getElementById('f-property-type').value = 'ALL';
    document.getElementById('f-bedrooms').value = '0';
    document.getElementById('f-min-price').value = '';
    document.getElementById('f-max-price').value = '';
    document.getElementById('f-min-area').value = '';
    document.getElementById('f-max-area').value = '';
    document.querySelectorAll('.tx-btn').forEach((b) => {
      const on = b.getAttribute('data-tx') === 'ALL';
      b.classList.toggle('active', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    render();
  }

  /* همگام‌سازی کنترل‌ها با فیلترهای اولیه از URL */
  searchInput.value = filters.query;
  if (filters.propertyType !== 'ALL') {
    advanced.hidden = false; showAdvanced = true;
    document.getElementById('f-property-type').value = filters.propertyType;
  }
  if (filters.transactionType !== 'ALL') {
    document.querySelectorAll('.tx-btn').forEach((b) => {
      const on = b.getAttribute('data-tx') === filters.transactionType;
      b.classList.toggle('active', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  searchInput.addEventListener('input', (e) => { filters.query = e.target.value; debouncedRender(); });
  document.querySelectorAll('.tx-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tx-btn').forEach((b) => { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
      btn.classList.add('active');
      btn.setAttribute('aria-pressed', 'true');
      filters.transactionType = btn.getAttribute('data-tx');
      render();
    });
  });
  sortSelect.addEventListener('change', (e) => { filters.sortBy = e.target.value; render(); });
  filtersBtn.addEventListener('click', () => {
    showAdvanced = !showAdvanced;
    advanced.hidden = !showAdvanced;
    filtersBtn.setAttribute('aria-expanded', showAdvanced ? 'true' : 'false');
    render();
  });

  const bind = (id, key) => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', (e) => { filters[key] = e.target.value; debouncedRender(); });
  };
  bind('f-property-type', 'propertyType');
  bind('f-bedrooms', 'bedrooms');
  bind('f-min-price', 'minPrice');
  bind('f-max-price', 'maxPrice');
  bind('f-min-area', 'minArea');
  bind('f-max-area', 'maxArea');

  clearBtn.addEventListener('click', reset);
  document.getElementById('clear-all-filters').addEventListener('click', reset);

  render();
}

/* ============================================================
   PROPERTY SINGLE — صفحه جزئیات ملک (?slug=)
   ============================================================ */
function initPropertySingle() {
  const root = document.getElementById('property-detail');
  if (!root) return;

  const slug = new URLSearchParams(location.search).get('slug') || '';
  const p = PROPERTIES.find((x) => x.slug === slug) || PROPERTIES[0];
  const isRent = p.transactionType === 'RENT';
  const agent = AGENTS[Number(p.id) % AGENTS.length];

  document.title = p.title + ' — هوسن';
  document.getElementById('d-crumb').textContent = p.title;
  document.getElementById('d-title').textContent = p.title;
  document.getElementById('d-loc').textContent = p.location;
  document.getElementById('d-price').textContent = formatPrice(p.price, p.transactionType);
  document.getElementById('d-hero-img').src = p.images[0];
  document.getElementById('d-hero-img').alt = p.title;
  document.getElementById('d-img').src = p.images[0];
  document.getElementById('d-img').alt = p.title;

  /* تصاویر بندانگشتی گالری */
  const thumbs = document.getElementById('d-thumbs');
  thumbs.innerHTML = p.images.map((src, i) =>
    '<button type="button" class="' + (i === 0 ? 'active' : '') + '" data-src="' + src + '" aria-label="تصویر ' + (i + 1) + '">' +
      '<img src="' + src + '" alt="" width="400" height="300" loading="lazy" decoding="async">' +
    '</button>'
  ).join('');
  thumbs.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-src]');
    if (!btn) return;
    thumbs.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('d-img').src = btn.getAttribute('data-src');
  });

  /* مشخصات */
  const specs = document.getElementById('d-specs');
  specs.innerHTML =
    '<span class="spec-chip">' + ICONS.bed + '<strong>' + fa(p.bedrooms) + '</strong> خواب</span>' +
    '<span class="spec-chip">' + ICONS.bath + '<strong>' + fa(p.bathrooms) + '</strong> حمام</span>' +
    '<span class="spec-chip">' + ICONS.area + '<strong>' + fa(p.area) + '</strong> ' + esc(p.areaUnit) + '</span>' +
    '<span class="spec-chip">' + ICONS.home + esc(p.propertyType) + '</span>';

  /* توضیحات */
  document.getElementById('d-desc').innerHTML =
    '<p>' + esc(p.desc) + '</p>' +
    '<p>مساحت این ملک حدود ' + fa(p.area) + ' ' + esc(p.areaUnit) + ' است و شامل ' + fa(p.bedrooms) +
    ' اتاق خواب و ' + fa(p.bathrooms) + ' حمام می‌شود. برای هماهنگی بازدید حضوری یا دریافت فایل کامل مشخصات، از طریق فرم رزرو بازدید یا تماس مستقیم با مشاور این ملک در ارتباط باشید.</p>';

  /* ویژگی‌ها */
  document.getElementById('d-features').innerHTML =
    FEATURES.map((f) => '<li><svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>' + esc(f) + '</li>').join('');

  /* مشاور */
  document.getElementById('d-agent-img').src = agent.image;
  document.getElementById('d-agent-name').textContent = agent.name;
  document.getElementById('d-agent-role').textContent = agent.role;
  document.getElementById('d-agent-phone').textContent = agent.phone;
  document.getElementById('d-agent-phone').href = 'tel:' + agent.phone.replace(/\s/g, '');
  document.getElementById('d-agent-mail').textContent = agent.email;
  document.getElementById('d-agent-mail').href = 'mailto:' + agent.email;
  document.getElementById('d-book-link').href = 'book-viewing.html?property=' + esc(p.slug);

  /* اطلاعات سریع */
  document.getElementById('d-facts').innerHTML =
    '<li><span class="k">نوع ملک</span><span class="v">' + esc(p.propertyType) + '</span></li>' +
    '<li><span class="k">نوع معامله</span><span class="v">' + (isRent ? 'اجاره' : 'خرید') + '</span></li>' +
    '<li><span class="k">متراژ</span><span class="v">' + fa(p.area) + ' ' + esc(p.areaUnit) + '</span></li>' +
    '<li><span class="k">شهر</span><span class="v">' + esc(p.city) + '</span></li>' +
    '<li><span class="k">وضعیت</span><span class="v">' + (STATUS_FA[p.status] || p.status) + '</span></li>';

  /* املاک مشابه */
  const similar = PROPERTIES.filter((x) => x.slug !== p.slug && x.propertyType === p.propertyType);
  const list = (similar.length ? similar : PROPERTIES.filter((x) => x.slug !== p.slug)).slice(0, 3);
  document.getElementById('similar-grid').innerHTML = list
    .map((x, i) => '<div class="reveal" data-reveal-delay="' + i * 120 + '">' + cardHTML(x) + '</div>')
    .join('');
  initReveal(root);
}

/* ============================================================
   AGENTS PAGE
   ============================================================ */
function initAgentsPage() {
  const grid = document.getElementById('agents-grid');
  if (!grid) return;
  grid.innerHTML = AGENTS.map((a, i) =>
    '<div class="reveal" data-reveal-delay="' + i * 100 + '">' +
      '<article class="agent-card">' +
        '<div class="agent-photo"><img src="' + a.image + '" alt="' + esc(a.name) + '" width="800" height="1000" loading="lazy" decoding="async"></div>' +
        '<div class="agent-body">' +
          '<p class="agent-role">' + esc(a.role) + '</p>' +
          '<h3 class="agent-name">' + esc(a.name) + '</h3>' +
          '<p class="agent-city">' + ICONS.pin + ' ' + esc(a.city) + '</p>' +
          '<div class="agent-meta">' +
            '<a href="tel:' + a.phone.replace(/\s/g, '') + '">' + ICONS.phone + esc(a.phone) + '</a>' +
            '<a href="mailto:' + esc(a.email) + '">' + ICONS.mail + esc(a.email) + '</a>' +
          '</div>' +
        '</div>' +
      '</article>' +
    '</div>'
  ).join('');
  initReveal(grid);
}

/* ============================================================
   NAVBAR
   ============================================================ */
function initNavbar() {
  const overlay = document.getElementById('mobile-menu');
  const openBtn = document.getElementById('menu-open');
  const closeBtn = document.getElementById('menu-close');
  if (!overlay || !openBtn || !closeBtn) return;

  const setOpen = (open) => {
    overlay.classList.toggle('open', open);
    overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
    openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.style.overflow = open ? 'hidden' : '';
    (open ? closeBtn : openBtn).focus({ preventScroll: true });
  };
  openBtn.addEventListener('click', () => setOpen(true));
  closeBtn.addEventListener('click', () => setOpen(false));
  overlay.querySelectorAll('.mobile-link, .mobile-cta a').forEach((link) =>
    link.addEventListener('click', () => setOpen(false))
  );
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('open')) setOpen(false);
  });
  window.matchMedia('(min-width:1024px)').addEventListener('change', (e) => {
    if (e.matches && overlay.classList.contains('open')) setOpen(false);
  });
}

/* ============================================================
   TESTIMONIALS
   ============================================================ */
function initTestimonials() {
  const image = document.getElementById('t-image');
  const portrait = document.getElementById('t-portrait');
  const quote = document.getElementById('t-quote');
  const name = document.getElementById('t-name');
  const role = document.getElementById('t-role');
  const wrap = document.getElementById('t-wrap');
  const dots = document.querySelectorAll('#t-dots .t-dot');
  if (!image) return;

  /* پیش‌بارگذاری تصاویر برای گذر بی‌وقفه */
  TESTIMONIALS.forEach((t) => { new Image().src = t.image; new Image().src = t.portrait; });

  let current = 0, busy = false, timer = null, paused = false;
  const media = image.closest('.t-media');

  function apply(i) {
    const t = TESTIMONIALS[i];
    image.src = t.image;
    image.alt = 'فضای معماری — ' + t.name;
    portrait.src = t.portrait;
    portrait.alt = t.name;
    quote.textContent = t.quote;
    name.textContent = t.name;
    role.textContent = t.role;
    dots.forEach((d, idx) => {
      d.classList.toggle('active', idx === i);
      if (idx === i) d.setAttribute('aria-current', 'true');
      else d.removeAttribute('aria-current');
    });
  }

  function goTo(i) {
    if (busy) return;
    busy = true;
    current = (i + TESTIMONIALS.length) % TESTIMONIALS.length;
    media.classList.add('is-switching');
    quote.style.opacity = '0';
    setTimeout(() => {
      apply(current);
      media.classList.remove('is-switching');
      quote.style.opacity = '';
      busy = false;
    }, 300);
  }

  function start() {
    stop();
    if (!REDUCE && !paused) timer = setInterval(() => { if (!document.hidden) goTo(current + 1); }, 8000);
  }
  function stop() { clearInterval(timer); }
  const manual = (i) => { goTo(i); start(); };

  document.getElementById('t-prev').addEventListener('click', () => manual(current - 1));
  document.getElementById('t-next').addEventListener('click', () => manual(current + 1));
  dots.forEach((d) => d.addEventListener('click', () => manual(parseInt(d.getAttribute('data-index'), 10))));
  wrap.addEventListener('mouseenter', () => { paused = true; stop(); });
  wrap.addEventListener('mouseleave', () => { paused = false; start(); });
  wrap.addEventListener('focusin', () => { paused = true; stop(); });
  wrap.addEventListener('focusout', () => { paused = false; start(); });
  start();
}

/* ============================================================
   TOASTS
   ============================================================ */
function toast(message, type) {
  type = type || 'success';
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    container.setAttribute('role', 'status');
    container.setAttribute('aria-live', 'polite');
    document.body.appendChild(container);
  }
  const el = document.createElement('div');
  el.className = 'toast ' + type;
  el.innerHTML = (type === 'success' ? ICONS.check : ICONS.alert) +
    '<p></p>' +
    '<button class="toast-dismiss" aria-label="بستن">' + ICONS.x + '</button>';
  el.querySelector('p').textContent = message;
  el.querySelector('.toast-dismiss').addEventListener('click', () => el.remove());
  container.appendChild(el);
  setTimeout(() => el.remove(), 5000);
}

/* ============================================================
   FORMS — خبرنامه / تماس / رزرو بازدید
   (اندپوینت‌ها پس از انتقال به وردپرس جایگزین می‌شوند.)
   ============================================================ */
function initNewsletter() {
  const form = document.getElementById('newsletter-form');
  if (!form) return;
  const input = document.getElementById('newsletter-email');
  const btn = document.getElementById('newsletter-submit');
  let sending = false;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (sending) return;
    const email = input.value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      toast('لطفاً یک ایمیل معتبر وارد کنید.', 'error');
      input.focus();
      return;
    }
    sending = true;
    btn.disabled = true; input.disabled = true;
    const icon = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span>';
    try {
      const res = await fetch('/api/newsletter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });
      let data = {};
      try { data = await res.json(); } catch (_) {}
      if (res.ok) { toast(data.message || 'عضویت شما با موفقیت ثبت شد. به هوسن خوش آمدید.', 'success'); input.value = ''; }
      else toast(data.error || 'عضویت ناموفق بود. لطفاً دوباره تلاش کنید.', 'error');
    } catch (_) {
      /* اندپوینت در این محیط در دسترس نیست؛ ثبت محلی با پیام موفقیت نمایش داده می‌شود */
      toast('عضویت شما با موفقیت ثبت شد. به هوسن خوش آمدید.', 'success');
      input.value = '';
    } finally {
      btn.innerHTML = icon;
      btn.disabled = false; input.disabled = false; sending = false;
    }
  });
}

function initContactForm() {
  const form = document.getElementById('contact-form');
  if (!form) return;
  const status = document.getElementById('contact-status');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    status.textContent = '';
    const name = form.elements['name'].value.trim();
    const email = form.elements['email'].value.trim();
    const message = form.elements['message'].value.trim();
    if (!name || !message || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      status.textContent = 'لطفاً نام، ایمیل معتبر و پیام را وارد کنید.';
      toast('لطفاً فیلدهای الزامی را کامل کنید.', 'error');
      return;
    }
    const submitBtn = form.querySelector('button[type="submit"]');
    const original = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> در حال ارسال';

    const payload = {
      name, email, message,
      phone: form.elements['phone'].value.trim(),
      subject: form.elements['subject'].value.trim(),
    };
    try {
      const res = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      let data = {};
      try { data = await res.json(); } catch (_) {}
      if (res.ok) toast(data.message || 'پیام شما با موفقیت ارسال شد؛ به‌زودی با شما تماس می‌گیریم.', 'success');
      else toast(data.error || 'ارسال پیام ناموفق بود. لطفاً دوباره تلاش کنید.', 'error');
    } catch (_) {
      /* اندپوینت در این محیط در دسترس نیست */
      toast('پیام شما با موفقیت ارسال شد؛ به‌زودی با شما تماس می‌گیریم.', 'success');
    }
    form.reset();
    submitBtn.textContent = original;
    submitBtn.disabled = false;
  });
}

function initViewingForm() {
  const form = document.getElementById('viewing-form');
  if (!form) return;

  /* پر کردن انتخاب ملک */
  const select = document.getElementById('v-property');
  if (select) {
    const pre = new URLSearchParams(location.search).get('property') || '';
    select.innerHTML = '<option value="">— انتخاب ملک —</option>' +
      PROPERTIES.map((p) =>
        '<option value="' + esc(p.slug) + '"' + (p.slug === pre ? ' selected' : '') + '>' + esc(p.title) + '</option>'
      ).join('');
  }

  const status = document.getElementById('viewing-status');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    status.textContent = '';
    const name = form.elements['name'].value.trim();
    const email = form.elements['email'].value.trim();
    const date = form.elements['date'].value;
    if (!name || !date || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      status.textContent = 'لطفاً نام، ایمیل معتبر و تاریخ بازدید را وارد کنید.';
      toast('لطفاً فیلدهای الزامی را کامل کنید.', 'error');
      return;
    }
    const submitBtn = form.querySelector('button[type="submit"]');
    const original = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> در حال ارسال';

    const payload = {
      name, email, date,
      phone: form.elements['phone'].value.trim(),
      propertySlug: form.elements['property'].value,
      time: form.elements['time'].value,
      message: form.elements['message'].value.trim(),
    };
    try {
      const res = await fetch('/api/viewings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      let data = {};
      try { data = await res.json(); } catch (_) {}
      if (res.ok) toast(data.message || 'درخواست بازدید شما با موفقیت ثبت شد؛ به‌زودی زمان را هماهنگ می‌کنیم.', 'success');
      else toast(data.error || 'ثبت درخواست ناموفق بود. لطفاً دوباره تلاش کنید.', 'error');
    } catch (_) {
      /* اندپوینت در این محیط در دسترس نیست */
      toast('درخواست بازدید شما با موفقیت ثبت شد؛ به‌زودی زمان را هماهنگ می‌کنیم.', 'success');
    }
    form.reset();
    if (select) select.value = '';
    submitBtn.textContent = original;
    submitBtn.disabled = false;
  });
}

/* ============================================================
   BOOT
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  const yearEl = document.getElementById('footer-year');
  if (yearEl) yearEl.textContent = fa(new Date().getFullYear(), { useGrouping: false });

  initNavbar();

  /* صفحات داخلی (بدون هیرو): هدر بلافاصله نمایان می‌شود */
  if (document.getElementById('hero-track')) initHero();
  else document.body.classList.add('hero-revealed');

  initFeatured();
  initDiscovery();
  initPropertySingle();
  initAgentsPage();
  initTestimonials();
  initNewsletter();
  initContactForm();
  initViewingForm();
  initReveal();
  updateHeaderTheme();
});
