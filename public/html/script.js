/* ============================================================
   HOUSEN — Luxury Real Estate
   Vanilla JavaScript conversion of the original React components.
   Sections: navbar, hero carousel + parallax, property cards,
   discovery filters, testimonials, newsletter, toasts, scroll reveal.
   ============================================================ */
'use strict';

document.documentElement.setAttribute('data-hydrated', '');

/* ---------- Property data (mirrors the original seed data) ---------- */
const IMG = (id, w) => 'https://images.unsplash.com/' + id + '?auto=format&fit=crop&w=' + w + '&q=80';
const GALLERY = (ids) => ids.map((id) => IMG(id, 1600));

const PROPERTIES = [
  {
    id: '1', title: 'Beachside Villa', slug: 'beachside-villa', price: 4200000,
    transactionType: 'BUY', propertyType: 'Villa', location: 'Malibu, California', city: 'Los Angeles',
    bedrooms: 5, bathrooms: 4, area: 6200, areaUnit: 'sqft', status: 'AVAILABLE', featured: true,
    images: GALLERY(['photo-1505843513577-22bb7d21e455', 'photo-1600596542815-ffad4c1539a9', 'photo-1613977257363-707ba9348227', 'photo-1580587771525-78b9dba3b914']),
  },
  {
    id: '2', title: 'Urban Loft', slug: 'urban-loft', price: 1850000,
    transactionType: 'BUY', propertyType: 'Loft', location: 'Tribeca, New York', city: 'New York',
    bedrooms: 2, bathrooms: 2, area: 2100, areaUnit: 'sqft', status: 'AVAILABLE', featured: true,
    images: GALLERY(['photo-1706808849780-7a04fbac83ef', 'photo-1613977257363-707ba9348227', 'photo-1580587771525-78b9dba3b914', 'photo-1505843513577-22bb7d21e455']),
  },
  {
    id: '3', title: 'Penthouse View', slug: 'penthouse-view', price: 6750000,
    transactionType: 'BUY', propertyType: 'Penthouse', location: 'Miami Beach, Florida', city: 'Miami',
    bedrooms: 3, bathrooms: 3, area: 3400, areaUnit: 'sqft', status: 'AVAILABLE', featured: true,
    images: GALLERY(['photo-1706808849780-7a04fbac83ef', 'photo-1600596542815-ffad4c1539a9', 'photo-1613977257363-707ba9348227', 'photo-1613490493576-7fde63acd811']),
  },
  {
    id: '4', title: 'Cliffside Modern Villa', slug: 'cliffside-modern-villa', price: 8900000,
    transactionType: 'BUY', propertyType: 'Villa', location: 'Big Sur, California', city: 'San Francisco',
    bedrooms: 6, bathrooms: 5, area: 8400, areaUnit: 'sqft', status: 'AVAILABLE', featured: false,
    images: GALLERY(['photo-1580587771525-78b9dba3b914', 'photo-1505843513577-22bb7d21e455', 'photo-1600596542815-ffad4c1539a9', 'photo-1613977257363-707ba9348227']),
  },
  {
    id: '5', title: 'Garden Pavilion', slug: 'garden-pavilion', price: 3200000,
    transactionType: 'BUY', propertyType: 'Villa', location: 'Beverly Hills, California', city: 'Los Angeles',
    bedrooms: 4, bathrooms: 4, area: 4800, areaUnit: 'sqft', status: 'AVAILABLE', featured: false,
    images: GALLERY(['photo-1600596542815-ffad4c1539a9', 'photo-1505843513577-22bb7d21e455', 'photo-1613977257363-707ba9348227', 'photo-1580587771525-78b9dba3b914']),
  },
  {
    id: '6', title: 'Skyline Apartment', slug: 'skyline-apartment', price: 2400000,
    transactionType: 'BUY', propertyType: 'Apartment', location: 'Manhattan, New York', city: 'New York',
    bedrooms: 3, bathrooms: 2, area: 1850, areaUnit: 'sqft', status: 'AVAILABLE', featured: false,
    images: GALLERY(['photo-1706808849780-7a04fbac83ef', 'photo-1613977257363-707ba9348227', 'photo-1613490493576-7fde63acd811', 'photo-1580587771525-78b9dba3b914']),
  },
  {
    id: '7', title: 'Lakeside Retreat', slug: 'lakeside-retreat', price: 5500000,
    transactionType: 'BUY', propertyType: 'Villa', location: 'Lake Tahoe, Nevada', city: 'Reno',
    bedrooms: 5, bathrooms: 4, area: 5200, areaUnit: 'sqft', status: 'AVAILABLE', featured: false,
    images: GALLERY(['photo-1505843513577-22bb7d21e455', 'photo-1580587771525-78b9dba3b914', 'photo-1600596542815-ffad4c1539a9', 'photo-1613977257363-707ba9348227']),
  },
  {
    id: '8', title: 'Downtown Penthouse Lease', slug: 'downtown-penthouse-lease', price: 12000,
    transactionType: 'RENT', propertyType: 'Penthouse', location: 'Financial District, Chicago', city: 'Chicago',
    bedrooms: 3, bathrooms: 3, area: 2800, areaUnit: 'sqft', status: 'AVAILABLE', featured: false,
    images: GALLERY(['photo-1706808849780-7a04fbac83ef', 'photo-1613977257363-707ba9348227', 'photo-1600596542815-ffad4c1539a9', 'photo-1613490493576-7fde63acd811']),
  },
  {
    id: '9', title: 'Coastal Glass House', slug: 'coastal-glass-house', price: 3800000,
    transactionType: 'BUY', propertyType: 'Villa', location: 'Carmel, California', city: 'San Francisco',
    bedrooms: 3, bathrooms: 3, area: 3100, areaUnit: 'sqft', status: 'AVAILABLE', featured: false,
    images: GALLERY(['photo-1580587771525-78b9dba3b914', 'photo-1505843513577-22bb7d21e455', 'photo-1613977257363-707ba9348227', 'photo-1600596542815-ffad4c1539a9']),
  },
  {
    id: '10', title: 'Heritage Loft Lease', slug: 'heritage-loft-lease', price: 4500,
    transactionType: 'RENT', propertyType: 'Loft', location: 'DUMBO, Brooklyn', city: 'New York',
    bedrooms: 1, bathrooms: 1, area: 1100, areaUnit: 'sqft', status: 'AVAILABLE', featured: false,
    images: GALLERY(['photo-1706808849780-7a04fbac83ef', 'photo-1613977257363-707ba9348227', 'photo-1580587771525-78b9dba3b914', 'photo-1505843513577-22bb7d21e455']),
  },
];

/* ---------- Testimonial data (mirrors the original component data) ---------- */
const TESTIMONIALS = [
  {
    quote: "HOUSEN didn't just find us a house — they found us a piece of architecture that has transformed how we live. Their attention to every detail, from the structural integrity to the way light moves through the space, was extraordinary.",
    name: 'Adrian Voss', role: 'Property Owner, Malibu',
    image: IMG('photo-1505843513577-22bb7d21e455', 800),
    portrait: IMG('photo-1500648767791-00dcc994a43e', 400),
  },
  {
    quote: 'Working with HOUSEN felt like collaborating with a gallery curator. Every property they showed us had a story, a reason for being. We ended up with a home that feels like it was designed specifically for us.',
    name: 'Camille Laurent', role: 'Interior Designer, New York',
    image: IMG('photo-1580587771525-78b9dba3b914', 800),
    portrait: IMG('photo-1517841905240-472988babdf9', 400),
  },
  {
    quote: 'The level of service was unmatched. HOUSEN understood that buying a home of this calibre is not just a transaction — it is a life decision. They guided us with patience, expertise, and genuine care.',
    name: 'Marcus Holloway', role: 'Investor, Miami',
    image: IMG('photo-1600596542815-ffad4c1539a9', 800),
    portrait: IMG('photo-1507003211169-0a1dd7228f2d', 400),
  },
];

/* ---------- Icons (Lucide, inlined) ---------- */
const ICONS = {
  bed: '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>',
  bath: '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20v3a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4z"/><path d="M4 12V6a2 2 0 0 1 2-2h2"/><path d="M17 19v2"/><path d="M7 19v2"/></svg>',
  maximize: '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>',
  arrowRight: '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
  check: '<svg class="ic-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>',
  alert: '<svg class="ic-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
  x: '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
};

/* ---------- Price formatting (identical to the original lib/utils) ---------- */
function formatPrice(price, transactionType) {
  if (transactionType === 'RENT') {
    return '$' + price.toLocaleString('en-US') + '/mo';
  }
  if (price >= 1000000) {
    return '$' + (price / 1000000).toFixed(1) + 'M';
  }
  return '$' + price.toLocaleString('en-US');
}

/* ---------- Toasts (mirrors the original Toaster component) ---------- */
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
  el.innerHTML =
    ICONS[type === 'success' ? 'check' : 'alert'] +
    '<p>' + message + '</p>' +
    '<button class="toast-dismiss" aria-label="Dismiss">' + ICONS.x + '</button>';
  el.querySelector('.toast-dismiss').addEventListener('click', () => el.remove());
  container.appendChild(el);
  setTimeout(() => el.remove(), 4500);
}

/* ---------- Scroll reveal (mirrors the original ScrollReveal component) ---------- */
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

/* ---------- Property card (mirrors the original PropertyCard component) ---------- */
function cardHTML(p) {
  return (
    '<a href="/properties/' + p.slug + '" class="card-link">' +
      '<article class="property-card">' +
        '<div class="card-media">' +
          '<img class="card-img" src="' + p.images[0] + '" alt="' + p.title + '" loading="lazy">' +
          '<div class="card-shade"></div>' +
          '<div class="card-price"><span>' + formatPrice(p.price, p.transactionType) + '</span></div>' +
          (p.status !== 'AVAILABLE'
            ? '<div class="card-status"><span>' + p.status + '</span></div>'
            : '') +
          '<div class="card-meta"><h3>' + p.title + '</h3><p>' + p.location + '</p></div>' +
        '</div>' +
        '<div class="card-specs">' +
          '<span>' + ICONS.bed + p.bedrooms + ' Bed</span>' +
          '<span>' + ICONS.bath + p.bathrooms + ' Bath</span>' +
          '<span>' + ICONS.maximize + p.area.toLocaleString('en-US') + ' ' + p.areaUnit + '</span>' +
          '<span class="card-type">' + p.propertyType + '</span>' +
        '</div>' +
      '</article>' +
    '</a>'
  );
}

/* ---------- Featured properties ---------- */
function initFeatured() {
  const grid = document.getElementById('featured-grid');
  if (!grid) return;
  const featured = PROPERTIES.filter((p) => p.featured).slice(0, 3);
  const list = featured.length >= 3 ? featured : PROPERTIES.slice(0, 3);
  grid.innerHTML = list
    .map((p, i) => '<div class="reveal" data-reveal-delay="' + i * 120 + '">' + cardHTML(p) + '</div>')
    .join('');
}

/* ---------- Property discovery (mirrors the original filter logic) ---------- */
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

  function getFiltered() {
    let result = PROPERTIES.slice();

    if (filters.query.trim()) {
      const q = filters.query.toLowerCase();
      result = result.filter(
        (p) =>
          p.title.toLowerCase().includes(q) ||
          p.location.toLowerCase().includes(q) ||
          p.city.toLowerCase().includes(q) ||
          p.propertyType.toLowerCase().includes(q)
      );
    }
    if (filters.transactionType !== 'ALL') {
      result = result.filter((p) => p.transactionType === filters.transactionType);
    }
    if (filters.propertyType !== 'ALL') {
      result = result.filter((p) => p.propertyType === filters.propertyType);
    }
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
    return !!(
      filters.query ||
      filters.transactionType !== 'ALL' ||
      filters.propertyType !== 'ALL' ||
      filters.minPrice ||
      filters.maxPrice ||
      filters.bedrooms !== '0' ||
      filters.minArea ||
      filters.maxArea
    );
  }

  function render() {
    const filtered = getFiltered();

    grid.innerHTML = filtered
      .map((p, i) => {
        const delay = Math.min(i * 80, 400);
        return '<div class="reveal" data-reveal-delay="' + delay + '">' + cardHTML(p) + '</div>';
      })
      .join('');

    empty.hidden = filtered.length > 0;
    grid.style.display = filtered.length > 0 ? '' : 'none';
    countEl.textContent = String(filtered.length);
    countWord.textContent = filtered.length === 1 ? 'property' : 'properties';
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
    document.querySelectorAll('.tx-btn').forEach((b) =>
      b.classList.toggle('active', b.getAttribute('data-tx') === 'ALL')
    );
    render();
  }

  // Search
  searchInput.addEventListener('input', (e) => {
    filters.query = e.target.value;
    render();
  });

  // Transaction type toggle
  document.querySelectorAll('.tx-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tx-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      filters.transactionType = btn.getAttribute('data-tx');
      render();
    });
  });

  // Sort
  sortSelect.addEventListener('change', (e) => {
    filters.sortBy = e.target.value;
    render();
  });

  // Advanced filters toggle
  filtersBtn.addEventListener('click', () => {
    showAdvanced = !showAdvanced;
    advanced.hidden = !showAdvanced;
    render();
  });

  // Advanced fields
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

  // Clear filters
  clearBtn.addEventListener('click', reset);
  document.getElementById('clear-all-filters').addEventListener('click', reset);

  render();
}

/* ---------- Navbar (mirrors the original Navbar component) ---------- */
function initNavbar() {
  const header = document.getElementById('site-header');
  if (!header) return;
  const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 40);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  // Mobile menu
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

/* ---------- Hero (mirrors the original Hero component) ---------- */
function initHero() {
  const slides = document.querySelectorAll('#hero .hero-slide');
  const dots = document.querySelectorAll('#hero-dots .hero-dot');
  if (!slides.length) return;

  let current = 0;
  let timer = null;

  function goTo(i) {
    current = (i + slides.length) % slides.length;
    slides.forEach((s, idx) => s.classList.toggle('active', idx === current));
    dots.forEach((d, idx) => d.classList.toggle('active', idx === current));
  }

  const startTimer = () => {
    timer = setInterval(() => goTo(current + 1), 6000);
  };

  document.getElementById('hero-prev').addEventListener('click', () => goTo(current - 1));
  document.getElementById('hero-next').addEventListener('click', () => goTo(current + 1));
  dots.forEach((dot) =>
    dot.addEventListener('click', () => goTo(parseInt(dot.getAttribute('data-index'), 10)))
  );
  startTimer();

  // Parallax + wordmark fade (mirrors the framer-motion scroll transforms)
  const bg = document.getElementById('hero-bg');
  const wordmark = document.getElementById('hero-wordmark');
  const scrollInd = document.getElementById('scroll-ind');
  const onScroll = () => {
    const y = window.scrollY;
    if (bg) bg.style.transform = 'translateY(' + Math.min((y / 800) * 150, 150) + 'px)';
    const fade = Math.max(1 - y / 500, 0);
    if (wordmark) wordmark.style.opacity = String(fade);
    if (scrollInd) scrollInd.style.opacity = String(fade);
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
}

/* ---------- Testimonials (mirrors the original Testimonials component) ---------- */
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
    image.alt = 'Architectural setting — ' + active.name;
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

  const startTimer = () => setInterval(() => goTo(current + 1), 8000);

  document.getElementById('t-prev').addEventListener('click', () => goTo(current - 1));
  document.getElementById('t-next').addEventListener('click', () => goTo(current + 1));
  dots.forEach((dot) =>
    dot.addEventListener('click', () => goTo(parseInt(dot.getAttribute('data-index'), 10)))
  );
  startTimer();
}

/* ---------- Newsletter (mirrors the original NewsletterForm component) ---------- */
function initNewsletter() {
  const form = document.getElementById('newsletter-form');
  if (!form) return;
  const emailInput = document.getElementById('newsletter-email');
  const submitBtn = document.getElementById('newsletter-submit');
  const icon = document.getElementById('newsletter-icon');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const trimmed = emailInput.value.trim();

    if (!trimmed || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
      toast('Please enter a valid email address.', 'error');
      return;
    }

    submitBtn.disabled = true;
    emailInput.disabled = true;
    icon.outerHTML = '<span class="spinner" id="newsletter-spinner"></span>';

    try {
      const res = await fetch('/api/newsletter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: trimmed }),
      });
      const data = await res.json();

      if (res.ok) {
        toast(data.message || 'Subscribed successfully. Welcome to HOUSEN.', 'success');
        emailInput.value = '';
      } else {
        toast(data.error || 'Subscription failed. Please try again.', 'error');
      }
    } catch (err) {
      toast('Network error. Please try again.', 'error');
    } finally {
      const spinner = document.getElementById('newsletter-spinner');
      if (spinner) spinner.outerHTML = '<svg class="ic" id="newsletter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';
      submitBtn.disabled = false;
      emailInput.disabled = false;
    }
  });
}

/* ---------- Boot ---------- */
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('footer-year').textContent = String(new Date().getFullYear());
  initNavbar();
  initHero();
  initFeatured();
  initDiscovery();
  initTestimonials();
  initNewsletter();
  initReveal();
});
