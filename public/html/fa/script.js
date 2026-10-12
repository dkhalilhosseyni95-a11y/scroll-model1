/* ============================================================
   HOUSEN — نسخه فارسی راست‌چین
   Vanilla JS مشترک بین همه صفحات
   Sections: hero scroll-video, reveal, property cards,
   discovery filters, property single, agents, testimonials,
   forms (newsletter/contact/viewing), navbar, toasts.
   ============================================================ */
'use strict';

document.documentElement.setAttribute('data-hydrated', '');

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

const PROPERTIES = [
  { id: '1', title: 'ویلای ساحلی', slug: 'beachside-villa', price: 4200000, transactionType: 'BUY', propertyType: 'ویلا', location: 'مالیبو، کالیفرنیا', city: 'لس آنجلس', bedrooms: 5, bathrooms: 4, area: 6200, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: true, images: [IMG[4], IMG[2], IMG[13], IMG[5]] },
  { id: '2', title: 'لافـت شهری', slug: 'urban-loft', price: 1850000, transactionType: 'BUY', propertyType: 'لافـت', location: 'تریبکا، نیویورک', city: 'نیویورک', bedrooms: 2, bathrooms: 2, area: 2100, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: true, images: [IMG[6], IMG[1], IMG[9], IMG[3]] },
  { id: '3', title: 'پنت‌هاوس با چشم‌انداز', slug: 'penthouse-view', price: 6750000, transactionType: 'BUY', propertyType: 'پنت‌هاوس', location: 'میامی بیچ، فلوریدا', city: 'میامی', bedrooms: 3, bathrooms: 3, area: 3400, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: true, images: [IMG[11], IMG[3], IMG[7], IMG[12]] },
  { id: '4', title: 'ویلای مدرن کنار صخره', slug: 'cliffside-modern-villa', price: 8900000, transactionType: 'BUY', propertyType: 'ویلا', location: 'بیگ سور، کالیفرنیا', city: 'سانفرانسیسکو', bedrooms: 6, bathrooms: 5, area: 8400, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[2], IMG[4], IMG[15], IMG[8]] },
  { id: '5', title: 'پاویون باغ', slug: 'garden-pavilion', price: 3200000, transactionType: 'BUY', propertyType: 'ویلا', location: 'بورلی هیلز، کالیفرنیا', city: 'لس آنجلس', bedrooms: 4, bathrooms: 4, area: 4800, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[15], IMG[12], IMG[5], IMG[2]] },
  { id: '6', title: 'آپارتمان اسکای‌لاین', slug: 'skyline-apartment', price: 2400000, transactionType: 'BUY', propertyType: 'آپارتمان', location: 'منهتن، نیویورک', city: 'نیویورک', bedrooms: 3, bathrooms: 2, area: 1850, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[1], IMG[6], IMG[11], IMG[9]] },
  { id: '7', title: 'اقامتگاه کنار دریاچه', slug: 'lakeside-retreat', price: 5500000, transactionType: 'BUY', propertyType: 'ویلا', location: 'دریاچه تاهو، نوادا', city: 'رینو', bedrooms: 5, bathrooms: 4, area: 5200, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[13], IMG[7], IMG[4], IMG[15]] },
  { id: '8', title: 'اجاره پنت‌هاوس مرکز شهر', slug: 'downtown-penthouse-lease', price: 12000, transactionType: 'RENT', propertyType: 'پنت‌هاوس', location: 'منطقه مالی، شیکاگو', city: 'شیکاگو', bedrooms: 3, bathrooms: 3, area: 2800, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[3], IMG[11], IMG[1], IMG[6]] },
  { id: '9', title: 'خانه شیشه‌ای ساحلی', slug: 'coastal-glass-house', price: 3800000, transactionType: 'BUY', propertyType: 'ویلا', location: 'کارمل، کالیفرنیا', city: 'سانفرانسیسکو', bedrooms: 3, bathrooms: 3, area: 3100, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[4], IMG[13], IMG[2], IMG[10]] },
  { id: '10', title: 'اجاره لافـت میراثی', slug: 'heritage-loft-lease', price: 4500, transactionType: 'RENT', propertyType: 'لافـت', location: 'دامبو، بروکلین', city: 'نیویورک', bedrooms: 1, bathrooms: 1, area: 1100, areaUnit: 'فوت مربع', status: 'AVAILABLE', featured: false, images: [IMG[6], IMG[9], IMG[3], IMG[1]] },
];

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

function formatPrice(price, transactionType) {
  if (transactionType === 'RENT') return '$' + price.toLocaleString('en-US') + '/mo';
  if (price >= 1000000) return '$' + (price / 1000000).toFixed(1) + 'M';
  return '$' + price.toLocaleString('en-US');
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
  const heroTrack = document.getElementById('hero-track');
  const hero = document.getElementById('hero');
  const heroVideo = document.getElementById('hero-video');
  const scrollInd = document.getElementById('scroll-ind');

  if (!heroTrack || !hero) return;

  let videoReady = false;
  let videoDuration = 0;
  let targetTime = 0;
  let currentTime = 0;
  let rafId = null;
  let isSeeking = false;
  let isRevealing = false;
  let annotationsVisible = false;

  function onVideoReady() {
    if (!heroVideo) return;
    videoDuration = heroVideo.duration || 0;
    if (videoDuration > 0 && isFinite(videoDuration)) {
      videoReady = true;
      heroVideo.classList.add('is-ready');
      updateFromScroll();
    }
  }

  if (heroVideo) {
    heroVideo.addEventListener('loadedmetadata', onVideoReady, { once: true });
    heroVideo.addEventListener('canplay', onVideoReady, { once: true });
    heroVideo.addEventListener('error', () => {
      heroVideo.classList.remove('is-ready');
    });

    const prime = () => {
      heroVideo.play().then(() => {
        heroVideo.pause();
        onVideoReady();
      }).catch(() => {});
    };
    if (heroVideo.readyState >= 1) prime();
    else heroVideo.addEventListener('loadedmetadata', prime, { once: true });

    try { heroVideo.load(); } catch (e) {}
  }

  function getScrollProgress() {
    const rect = heroTrack.getBoundingClientRect();
    const trackHeight = heroTrack.offsetHeight;
    const viewportH = window.innerHeight;
    const scrollable = Math.max(trackHeight - viewportH, 1);
    return Math.min(Math.max(-rect.top / scrollable, 0), 1);
  }

  function updateFromScroll() {
    const progress = getScrollProgress();

    if (videoReady && videoDuration > 0) {
      const videoProgress = Math.min(progress / 0.95, 1);
      targetTime = videoProgress * (videoDuration - 0.05);
    }

    if (scrollInd) {
      scrollInd.style.opacity = String(Math.max(1 - progress * 6, 0));
    }

    const shouldShowAnnotations = progress < 0.05;
    if (shouldShowAnnotations !== annotationsVisible) {
      annotationsVisible = shouldShowAnnotations;
      hero.classList.toggle('annotations-visible', shouldShowAnnotations);
    }

    const shouldReveal = progress >= 0.90;
    if (shouldReveal !== isRevealing) {
      isRevealing = shouldReveal;
      hero.classList.toggle('is-revealing', shouldReveal);
      document.body.classList.toggle('hero-revealed', shouldReveal);
    }

    updateHeaderTheme();
  }

  function videoLoop() {
    rafId = requestAnimationFrame(videoLoop);
    if (!videoReady || !heroVideo || videoDuration <= 0) return;

    const diff = targetTime - currentTime;
    if (Math.abs(diff) > 0.001) {
      currentTime += diff * 0.18;
      if (!isSeeking && Math.abs(heroVideo.currentTime - currentTime) > 0.03) {
        isSeeking = true;
        try { heroVideo.currentTime = currentTime; } catch (e) {}
        requestAnimationFrame(() => { isSeeking = false; });
      }
    }
  }

  let ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      updateFromScroll();
      ticking = false;
    });
  }

  window.addEventListener('scroll', onScroll, { passive: true });

  /* نمایش نشانگرها از همان ابتدا */
  hero.classList.add('annotations-visible');
  annotationsVisible = true;

  videoLoop();
  updateFromScroll();

  window.addEventListener('beforeunload', () => {
    if (rafId) cancelAnimationFrame(rafId);
  });
}

/* ============================================================
   SCROLL REVEAL
   ============================================================ */
function initReveal(root) {
  const scope = root || document;
  scope.querySelectorAll('.reveal:not(.is-visible):not([data-observed])').forEach((el) => {
    el.setAttribute('data-observed', '');
    const delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          setTimeout(() => el.classList.add('is-visible'), delay);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
    observer.observe(el);
  });
}

/* ============================================================
   PROPERTY CARD
   ============================================================ */
function cardHTML(p) {
  return (
    '<a href="property-single.html?slug=' + p.slug + '" class="card-link">' +
      '<article class="property-card">' +
        '<img class="card-img" src="' + p.images[0] + '" alt="' + p.title + '" loading="lazy">' +
        '<div class="card-shade"></div>' +
        (p.status !== 'AVAILABLE' ? '<div class="card-status"><span>' + p.status + '</span></div>' : '') +
        '<div class="card-info">' +
          '<div class="card-row"><h3>' + p.title + '</h3><span class="card-price">' + formatPrice(p.price, p.transactionType) + '</span></div>' +
          '<div class="card-specs">' +
            '<span>' + p.bedrooms + ' خواب</span><i class="sep"></i>' +
            '<span>' + p.bathrooms + ' حمام</span><i class="sep"></i>' +
            '<span>' + p.area.toLocaleString('fa-IR') + ' ' + p.areaUnit + '</span>' +
          '</div>' +
          '<p class="card-loc">' + ICONS.pin + p.location + '</p>' +
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
    if (filters.query.trim()) {
      const q = filters.query.toLowerCase();
      result = result.filter((p) =>
        p.title.toLowerCase().includes(q) ||
        p.location.toLowerCase().includes(q) ||
        p.city.toLowerCase().includes(q) ||
        p.propertyType.toLowerCase().includes(q)
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
    countEl.textContent = filtered.length.toLocaleString('fa-IR');
    countWord.textContent = 'ملک';
    clearBtn.hidden = !hasActiveFilters();
    filtersBtn.classList.toggle('active', showAdvanced || hasActiveFilters());
    initReveal(grid);
  }

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
    document.querySelectorAll('.tx-btn').forEach((b) => b.classList.toggle('active', b.getAttribute('data-tx') === 'ALL'));
    render();
  }

  /* همگام‌سازی کنترل‌ها با فیلترهای اولیه از URL */
  searchInput.value = filters.query;
  if (filters.propertyType !== 'ALL') {
    advanced.hidden = false; showAdvanced = true;
    document.getElementById('f-property-type').value = filters.propertyType;
  }
  if (filters.transactionType !== 'ALL') {
    document.querySelectorAll('.tx-btn').forEach((b) => b.classList.toggle('active', b.getAttribute('data-tx') === filters.transactionType));
  }

  searchInput.addEventListener('input', (e) => { filters.query = e.target.value; render(); });
  document.querySelectorAll('.tx-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tx-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      filters.transactionType = btn.getAttribute('data-tx');
      render();
    });
  });
  sortSelect.addEventListener('change', (e) => { filters.sortBy = e.target.value; render(); });
  filtersBtn.addEventListener('click', () => {
    showAdvanced = !showAdvanced;
    advanced.hidden = !showAdvanced;
    render();
  });

  const bind = (id, key) => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', (e) => { filters[key] = e.target.value; render(); });
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
      '<img src="' + src + '" alt="" loading="lazy">' +
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
    '<span class="spec-chip">' + ICONS.bed + '<strong>' + p.bedrooms + '</strong> خواب</span>' +
    '<span class="spec-chip">' + ICONS.bath + '<strong>' + p.bathrooms + '</strong> حمام</span>' +
    '<span class="spec-chip">' + ICONS.area + '<strong>' + p.area.toLocaleString('fa-IR') + '</strong> ' + p.areaUnit + '</span>' +
    '<span class="spec-chip">' + ICONS.home + p.propertyType + '</span>';

  /* توضیحات (نمایشی) */
  document.getElementById('d-desc').innerHTML =
    '<p>' + p.title + ' در ' + p.location + ' قرار دارد؛ ملکی ' + p.propertyType + ' با ' + p.bedrooms + ' اتاق خواب و ' + p.bathrooms + ' حمام در فضایی حدود ' + p.area.toLocaleString('fa-IR') + ' ' + p.areaUnit + '. این ملک با بهره‌گیری از مصالح طبیعی، نور سرتاسری و هندسه‌ای روان، تجربه‌ای آرام و مدرن از زندگی ارائه می‌دهد.</p>' +
    '<p>— این توضیحات محتوای نمایشی است و پس از اتصال به وردپرس با متن واقعی ملک جایگزین می‌شود.</p>';

  /* ویژگی‌ها */
  document.getElementById('d-features').innerHTML =
    FEATURES.map((f) => '<li><svg class="ic-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>' + f + '</li>').join('');

  /* مشاور */
  document.getElementById('d-agent-img').src = agent.image;
  document.getElementById('d-agent-name').textContent = agent.name;
  document.getElementById('d-agent-role').textContent = agent.role;
  document.getElementById('d-agent-phone').textContent = agent.phone;
  document.getElementById('d-agent-phone').href = 'tel:' + agent.phone.replace(/\s/g, '');
  document.getElementById('d-agent-mail').textContent = agent.email;
  document.getElementById('d-agent-mail').href = 'mailto:' + agent.email;
  document.getElementById('d-book-link').href = 'book-viewing.html?property=' + p.slug;

  /* اطلاعات سریع */
  document.getElementById('d-facts').innerHTML =
    '<li><span class="k">نوع ملک</span><span class="v">' + p.propertyType + '</span></li>' +
    '<li><span class="k">نوع معامله</span><span class="v">' + (isRent ? 'اجاره' : 'خرید') + '</span></li>' +
    '<li><span class="k">متراژ</span><span class="v">' + p.area.toLocaleString('fa-IR') + ' ' + p.areaUnit + '</span></li>' +
    '<li><span class="k">شهر</span><span class="v">' + p.city + '</span></li>' +
    '<li><span class="k">وضعیت</span><span class="v">آماده بازدید</span></li>';

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
        '<div class="agent-photo"><img src="' + a.image + '" alt="' + a.name + '" loading="lazy"></div>' +
        '<div class="agent-body">' +
          '<p class="agent-role">' + a.role + '</p>' +
          '<h3 class="agent-name">' + a.name + '</h3>' +
          '<p class="agent-city">' + ICONS.pin + ' ' + a.city + '</p>' +
          '<div class="agent-meta">' +
            '<a href="tel:' + a.phone.replace(/\s/g, '') + '">' + ICONS.phone + a.phone + '</a>' +
            '<a href="mailto:' + a.email + '">' + ICONS.mail + a.email + '</a>' +
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
  const setOpen = (open) => {
    overlay.classList.toggle('open', open);
    overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.style.overflow = open ? 'hidden' : '';
  };
  if (openBtn) openBtn.addEventListener('click', () => setOpen(true));
  if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
  overlay.querySelectorAll('.mobile-link, .mobile-cta a').forEach((link) =>
    link.addEventListener('click', () => setOpen(false))
  );
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
  const dots = document.querySelectorAll('#t-dots .t-dot');
  if (!image) return;

  let current = 0;

  function apply(i) {
    const active = TESTIMONIALS[i];
    image.src = active.image;
    image.alt = 'فضای معماری — ' + active.name;
    portrait.src = active.portrait;
    portrait.alt = active.name;
    quote.textContent = active.quote;
    name.textContent = active.name;
    role.textContent = active.role;
    dots.forEach((d, idx) => d.classList.toggle('active', idx === i));
  }

  function goTo(i) {
    current = (i + TESTIMONIALS.length) % TESTIMONIALS.length;
    const media = image.closest('.t-media');
    media.classList.add('is-switching');
    quote.style.opacity = '0';
    setTimeout(() => {
      apply(current);
      media.classList.remove('is-switching');
      quote.style.opacity = '';
    }, 300);
  }

  setInterval(() => goTo(current + 1), 8000);
  document.getElementById('t-prev').addEventListener('click', () => goTo(current - 1));
  document.getElementById('t-next').addEventListener('click', () => goTo(current + 1));
  dots.forEach((dot) => dot.addEventListener('click', () => goTo(parseInt(dot.getAttribute('data-index'), 10))));
}

/* ============================================================
   TOASTS
   ============================================================ */
function createToastContainer() {
  const container = document.createElement('div');
  container.className = 'toast-container';
  document.body.appendChild(container);
  return container;
}

function toast(message, type) {
  type = type || 'success';
  const container = document.querySelector('.toast-container') || createToastContainer();
  const el = document.createElement('div');
  el.className = 'toast ' + type;
  el.innerHTML = ICONS[type === 'success' ? 'check' : 'alert'] +
    '<p>' + message + '</p>' +
    '<button class="toast-dismiss" aria-label="بستن">' + ICONS.x + '</button>';
  el.querySelector('.toast-dismiss').addEventListener('click', () => el.remove());
  container.appendChild(el);
  setTimeout(() => el.remove(), 4500);
}

/* ============================================================
   FORMS — خبرنامه / تماس / رزرو بازدید
   (در حالت استاتیک، خطای شبکه به‌عنوان موفقیت نمایشی 처리 می‌شود؛
    پس از اتصال به وردپرس، اندپوینت‌ها را جایگزین کنید.)
   ============================================================ */
function initNewsletter() {
  const form = document.getElementById('newsletter-form');
  if (!form) return;
  const emailInput = document.getElementById('newsletter-email');
  const submitBtn = document.getElementById('newsletter-submit');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const trimmed = emailInput.value.trim();
    if (!trimmed || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
      toast('لطفاً یک ایمیل معتبر وارد کنید.', 'error');
      return;
    }
    submitBtn.disabled = true;
    emailInput.disabled = true;
    const originalIcon = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner"></span>';

    try {
      const res = await fetch('/api/newsletter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: trimmed }),
      });
      const data = await res.json();
      if (res.ok) {
        toast(data.message || 'با موفقیت عضو شدید.', 'success');
        emailInput.value = '';
      } else {
        toast(data.error || 'عضویت ناموفق بود.', 'error');
      }
    } catch (err) {
      toast('با موفقیت عضو شدید. به هوسن خوش آمدید.', 'success');
      emailInput.value = '';
    } finally {
      submitBtn.innerHTML = originalIcon;
      submitBtn.disabled = false;
      emailInput.disabled = false;
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

    const payload = { name, email, phone: form.elements['phone'].value.trim(), subject: form.elements['subject'].value.trim(), message };
    try {
      const res = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (res.ok) toast(data.message || 'پیام شما ارسال شد.', 'success');
      else toast(data.error || 'ارسال پیام ناموفق بود.', 'error');
    } catch (err) {
      toast('پیام شما ثبت شد (نسخه نمایشی). به‌زودی پاسخ می‌دهیم.', 'success');
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
      PROPERTIES.map((p) => '<option value="' + p.slug + '"' + (p.slug === pre ? ' selected' : '') + '>' + p.title + '</option>').join('');
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
      name, email,
      phone: form.elements['phone'].value.trim(),
      propertySlug: form.elements['property'].value,
      date, time: form.elements['time'].value,
      message: form.elements['message'].value.trim(),
    };
    try {
      const res = await fetch('/api/viewings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (res.ok) toast(data.message || 'درخواست بازدید شما ثبت شد.', 'success');
      else toast(data.error || 'ثبت درخواست ناموفق بود.', 'error');
    } catch (err) {
      toast('درخواست بازدید شما ثبت شد (نسخه نمایشی).', 'success');
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
  if (yearEl) yearEl.textContent = (new Date().getFullYear()).toLocaleString('fa-IR');

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
