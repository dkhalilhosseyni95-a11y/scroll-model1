/**
 * MOON — homepage interactions: hero parallax, clothing rack, style studio,
 * product carousel, campaign parallax, scroll story and testimonials.
 *
 * No dependencies. Loaded on the front page only.
 */
(function () {
  'use strict';

  var doc = document;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hasHover = window.matchMedia('(hover: hover)').matches;

  /* ----------------------------------------------------------------------
   * Shared scroll engine
   * -------------------------------------------------------------------- */
  var tasks = [];
  var ticking = false;

  function runTasks() {
    ticking = false;

    for (var i = 0; i < tasks.length; i += 1) {
      tasks[i]();
    }
  }

  function onScroll(task) {
    tasks.push(task);
    task();

    if (tasks.length === 1) {
      window.addEventListener('scroll', function () {
        if (!ticking) {
          ticking = true;
          window.requestAnimationFrame(runTasks);
        }
      }, { passive: true });

      window.addEventListener('resize', function () {
        if (!ticking) {
          ticking = true;
          window.requestAnimationFrame(runTasks);
        }
      });
    }
  }

  function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  }

  function piecewise(value, stops, outputs) {
    if (value <= stops[0]) {
      return outputs[0];
    }

    for (var i = 1; i < stops.length; i += 1) {
      if (value <= stops[i]) {
        var span = stops[i] - stops[i - 1] || 1;
        var ratio = (value - stops[i - 1]) / span;
        return outputs[i - 1] + (outputs[i] - outputs[i - 1]) * ratio;
      }
    }

    return outputs[outputs.length - 1];
  }

  /* ----------------------------------------------------------------------
   * Hero
   * -------------------------------------------------------------------- */
  var hero = doc.querySelector('[data-moon-hero]');

  if (hero) {
    window.requestAnimationFrame(function () {
      hero.classList.add('is-ready');
    });

    var heroImage = hero.querySelector('.moon-hero__media');
    var heroCopy = hero.querySelector('.moon-hero__copy');

    onScroll(function () {
      if (!heroImage || reduceMotion) {
        return;
      }

      var rect = hero.getBoundingClientRect();
      var progress = clamp(-rect.top / (rect.height || 1), 0, 1);

      heroImage.style.transform = 'translate3d(0,' + (progress * 25) + '%,0) scale(' + (1.05 + progress * 0.15) + ')';

      if (heroCopy) {
        heroCopy.style.transform = 'translate3d(0,' + (-progress * 120) + 'px,0)';
      }
    });
  }

  /* ----------------------------------------------------------------------
   * Clothing rack
   * -------------------------------------------------------------------- */
  var rack = doc.querySelector('[data-moon-rack]');

  if (rack) {
    var rackData = [];

    try {
      rackData = JSON.parse(rack.getAttribute('data-items') || '[]');
    } catch (error) {
      rackData = [];
    }

    var stage = rack.querySelector('[data-moon-rack-stage]');
    var panel = rack.querySelector('[data-moon-rack-panel]');
    var hangers = Array.prototype.slice.call(rack.querySelectorAll('[data-moon-hanger]'));
    var slots = Array.prototype.slice.call(rack.querySelectorAll('.moon-rack__slot'));
    var photo = rack.querySelector('[data-moon-rack-photo]');
    var rackEn = rack.querySelector('[data-moon-rack-en]');
    var rackName = rack.querySelector('[data-moon-rack-name]');
    var rackPrice = rack.querySelector('[data-moon-rack-price]');
    var rackNote = rack.querySelector('[data-moon-rack-note]');
    var rackSwatches = rack.querySelector('[data-moon-rack-swatches]');
    var rackThumbs = rack.querySelector('[data-moon-rack-thumbs]');
    var rackAdd = rack.querySelector('[data-moon-rack-add]');
    var rackAddLabel = rack.querySelector('[data-moon-rack-add-label]');
    var rackReturn = rack.querySelector('[data-moon-rack-return]');
    var current = -1;

    hangers.forEach(function (hanger, index) {
      hanger.style.setProperty('--d', (index * 0.08) + 's');
    });

    if ('IntersectionObserver' in window) {
      var rackObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            rack.classList.add('is-in');
            rackObserver.disconnect();
          }
        });
      }, { rootMargin: '-60px 0px' });

      rackObserver.observe(rack);
    } else {
      rack.classList.add('is-in');
    }

    var fillPanel = function (index) {
      var item = rackData[index];

      if (!item) {
        return;
      }

      current = index;
      photo.src = item.image;
      photo.alt = item.name;
      rackEn.textContent = item.en;
      rackName.textContent = item.name;
      rackPrice.innerHTML = item.price;
      rackNote.textContent = item.note || '';
      rackNote.hidden = !item.note;

      rackSwatches.innerHTML = '';
      (item.colors || []).forEach(function (color, colorIndex) {
        var swatch = doc.createElement('button');
        swatch.type = 'button';
        swatch.className = 'moon-rack__swatch' + (colorIndex === 0 ? ' is-active' : '');
        swatch.style.background = color;
        swatch.setAttribute('aria-label', 'رنگ ' + (colorIndex + 1));
        rackSwatches.appendChild(swatch);
      });

      rackThumbs.innerHTML = '';
      rackData.forEach(function (thumb, thumbIndex) {
        if (thumbIndex === index) {
          return;
        }

        var button = doc.createElement('button');
        button.type = 'button';
        button.className = 'moon-rack__thumb';
        button.setAttribute('data-moon-rack-thumb', String(thumbIndex));
        button.setAttribute('aria-label', thumb.name);

        var image = doc.createElement('img');
        image.src = thumb.image;
        image.alt = '';
        image.width = 640;
        image.height = 1024;

        button.appendChild(image);
        rackThumbs.appendChild(button);
      });

      if (rackAdd) {
        rackAdd.href = item.addUrl || item.link || (window.MOON && window.MOON.cartUrl) || '#';
      }

      if (rackAddLabel) {
        rackAddLabel.textContent = item.addUrl ? 'افزودن به سبد' : (item.link ? 'مشاهده محصول' : 'مشاهده فروشگاه');
      }
    };

    var flight = function (fromRect, toRect, source, done) {
      if (reduceMotion || !source || !photo) {
        done();
        return;
      }

      var clone = doc.createElement('img');
      clone.src = source.src;
      clone.alt = '';
      clone.className = 'moon-rack__flyer';
      clone.style.left = fromRect.left + 'px';
      clone.style.top = fromRect.top + 'px';
      clone.style.width = fromRect.width + 'px';
      clone.style.height = fromRect.height + 'px';
      doc.body.appendChild(clone);

      photo.style.visibility = 'hidden';

      var scale = toRect.width / (fromRect.width || 1);
      var dx = (toRect.left + toRect.width / 2) - (fromRect.left + fromRect.width / 2);
      var dy = (toRect.top + toRect.height / 2) - (fromRect.top + fromRect.height / 2);

      var animation = clone.animate(
        [
          { transform: 'translate3d(0,0,0) scale(1)' },
          { transform: 'translate3d(' + dx + 'px,' + dy + 'px,0) scale(' + scale + ')' }
        ],
        { duration: 700, easing: 'cubic-bezier(.34,1.2,.64,1)', fill: 'forwards' }
      );

      animation.onfinish = function () {
        clone.remove();

        if (photo) {
          photo.style.visibility = '';
        }

        done();
      };
    };

    var openItem = function (index) {
      var source = hangers[index] ? hangers[index].querySelector('img') : null;
      var sourceRect = source ? source.getBoundingClientRect() : null;

      fillPanel(index);

      stage.classList.add('is-open');
      panel.classList.add('is-open');
      rack.classList.add('is-dimmed');

      slots.forEach(function (slot, slotIndex) {
        slot.classList.toggle('is-taken', slotIndex === index);
      });

      var targetRect = photo.getBoundingClientRect();

      flight(sourceRect, targetRect, source, function () {
        photo.style.visibility = '';
      });
    };

    var closeRack = function () {
      if (current < 0) {
        return;
      }

      var index = current;
      var source = hangers[index] ? hangers[index].querySelector('img') : null;
      var targetRect = source ? source.getBoundingClientRect() : null;
      var fromRect = photo.getBoundingClientRect();

      slots.forEach(function (slot) {
        slot.classList.remove('is-taken');
      });

      rack.classList.remove('is-dimmed');

      if (targetRect && sourceRectVisible(targetRect)) {
        photo.style.visibility = 'hidden';
        panel.classList.remove('is-open');
        stage.classList.remove('is-open');

        flight(fromRect, targetRect, photo, function () {
          photo.style.visibility = '';
          current = -1;
        });
      } else {
        panel.classList.remove('is-open');
        stage.classList.remove('is-open');
        current = -1;
      }
    };

    function sourceRectVisible(rect) {
      return rect.width > 0 && rect.height > 0;
    }

    hangers.forEach(function (hanger) {
      hanger.addEventListener('click', function () {
        openItem(parseInt(hanger.getAttribute('data-index'), 10));
      });
    });

    if (rackReturn) {
      rackReturn.addEventListener('click', closeRack);
    }

    rack.addEventListener('click', function (event) {
      var swatch = event.target.closest('.moon-rack__swatch');

      if (swatch) {
        rackSwatches.querySelectorAll('.moon-rack__swatch').forEach(function (node) {
          node.classList.remove('is-active');
        });
        swatch.classList.add('is-active');
        return;
      }

      var thumb = event.target.closest('[data-moon-rack-thumb]');

      if (thumb) {
        var target = parseInt(thumb.getAttribute('data-moon-rack-thumb'), 10);
        fillPanel(target);
        slots.forEach(function (slot, slotIndex) {
          slot.classList.toggle('is-taken', slotIndex === target);
        });

        if (!reduceMotion && photo) {
          photo.animate(
            [
              { opacity: 0.2, transform: 'translateY(12px)' },
              { opacity: 1, transform: 'translateY(0)' }
            ],
            { duration: 500, easing: 'cubic-bezier(.22,1,.36,1)' }
          );
        }
      }
    });

    doc.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && current > -1) {
        closeRack();
      }
    });
  }

  /* ----------------------------------------------------------------------
   * Style studio
   * -------------------------------------------------------------------- */
  var studio = doc.querySelector('[data-moon-studio]');

  if (studio) {
    var outfits = [];

    try {
      outfits = JSON.parse(studio.getAttribute('data-outfits') || '[]');
    } catch (error) {
      outfits = [];
    }

    if (outfits.length) {
      var images = Array.prototype.slice.call(studio.querySelectorAll('[data-moon-studio-img]'));
      var thumbs = Array.prototype.slice.call(studio.querySelectorAll('[data-moon-studio-thumb]'));
      var caption = studio.querySelector('[data-moon-studio-caption]');
      var look = studio.querySelector('[data-moon-studio-look]');
      var studioName = studio.querySelector('[data-moon-studio-name]');
      var palette = studio.querySelector('[data-moon-studio-palette]');
      var counter = studio.querySelector('[data-moon-studio-counter]');
      var studioCta = studio.querySelector('[data-moon-studio-cta]');
      var studioIndex = 0;
      var total = outfits.length;
      var captionTimer = null;
      var studioHover = false;

      var setStudio = function (next) {
        studioIndex = (next + total) % total;

        var outfit = outfits[studioIndex];

        images.forEach(function (image, index) {
          image.classList.toggle('is-active', index === studioIndex);
        });

        thumbs.forEach(function (thumb) {
          var active = parseInt(thumb.getAttribute('data-moon-studio-thumb'), 10) === studioIndex;
          thumb.classList.toggle('is-active', active);
          thumb.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (palette) {
          palette.innerHTML = '';
          (outfit.colors || []).forEach(function (color) {
            var dot = doc.createElement('span');
            dot.className = 'moon-studio__dot';
            dot.style.background = color;
            palette.appendChild(dot);
          });
        }

        if (counter) {
          counter.textContent = (studioIndex + 1) + ' / ' + total;
        }

        if (studioCta && outfit.link) {
          studioCta.href = outfit.link;
        }

        var applyCaption = function () {
          if (look) {
            look.textContent = 'Look ' + String(studioIndex + 1).padStart(2, '0') + ' — ' + outfit.en;
          }

          if (studioName) {
            studioName.textContent = outfit.name;
          }

          if (caption) {
            caption.classList.remove('is-changing');
          }
        };

        if (caption && !reduceMotion) {
          window.clearTimeout(captionTimer);
          caption.classList.add('is-changing');
          captionTimer = window.setTimeout(applyCaption, 200);
        } else {
          applyCaption();
        }
      };

      var go = function (direction) {
        setStudio(studioIndex + direction);
      };

      studio.querySelectorAll('[data-moon-studio-prev]').forEach(function (button) {
        button.addEventListener('click', function () {
          go(-1);
        });
      });

      studio.querySelectorAll('[data-moon-studio-next]').forEach(function (button) {
        button.addEventListener('click', function () {
          go(1);
        });
      });

      thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
          setStudio(parseInt(thumb.getAttribute('data-moon-studio-thumb'), 10));
        });
      });

      studio.addEventListener('mouseenter', function () {
        studioHover = true;
      });

      studio.addEventListener('mouseleave', function () {
        studioHover = false;
      });

      if (hasHover) {
        doc.addEventListener('keydown', function (event) {
          if (!studioHover) {
            return;
          }

          if (event.key === 'ArrowRight') {
            go(-1);
          } else if (event.key === 'ArrowLeft') {
            go(1);
          }
        });
      }
    }
  }

  /* ----------------------------------------------------------------------
   * Product carousel
   * -------------------------------------------------------------------- */
  var track = doc.querySelector('[data-moon-carousel]');

  if (track) {
    var rtl = window.getComputedStyle(track).direction === 'rtl';
    var scrollStep = function (direction) {
      var amount = track.clientWidth * 0.8;
      track.scrollBy({ left: (rtl ? -1 : 1) * direction * amount, behavior: 'smooth' });
    };

    var prev = doc.querySelector('[data-moon-carousel-prev]');
    var next = doc.querySelector('[data-moon-carousel-next]');

    if (prev) {
      prev.addEventListener('click', function () {
        scrollStep(-1);
      });
    }

    if (next) {
      next.addEventListener('click', function () {
        scrollStep(1);
      });
    }

    var drag = null;

    track.addEventListener('pointerdown', function (event) {
      if (event.pointerType !== 'mouse') {
        return;
      }

      drag = { x: event.clientX, start: track.scrollLeft };
      track.classList.add('is-dragging');
    });

    track.addEventListener('pointermove', function (event) {
      if (!drag) {
        return;
      }

      track.scrollLeft = drag.start - (event.clientX - drag.x);
    });

    var endDrag = function () {
      drag = null;
      track.classList.remove('is-dragging');
    };

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointerleave', endDrag);
  }

  /* ----------------------------------------------------------------------
   * Campaign parallax
   * -------------------------------------------------------------------- */
  var campaign = doc.querySelector('[data-moon-campaign]');

  if (campaign) {
    var campaignImage = campaign.querySelector('.moon-campaign__media');

    onScroll(function () {
      if (!campaignImage || reduceMotion) {
        return;
      }

      var rect = campaign.getBoundingClientRect();
      var viewport = window.innerHeight || 1;
      var progress = clamp((viewport - rect.top) / (viewport + rect.height), 0, 1);

      campaignImage.style.transform = 'translate3d(0,' + (-12 + progress * 24) + '%,0)';
    });
  }

  /* ----------------------------------------------------------------------
   * Scroll story
   * -------------------------------------------------------------------- */
  var story = doc.querySelector('[data-moon-scrollstory]');

  if (story) {
    var scenes = Array.prototype.slice.call(story.querySelectorAll('[data-moon-scene]'));
    var bar = story.querySelector('[data-moon-scene-bar]');
    var storyTitle = story.querySelector('[data-moon-scene-title]');
    var sceneTotal = scenes.length;

    onScroll(function () {
      var rect = story.getBoundingClientRect();
      var distance = story.offsetHeight - (window.innerHeight || 1);
      var progress = clamp(-rect.top / (distance || 1), 0, 1);

      if (bar) {
        bar.style.width = (progress * 100) + '%';
      }

      if (reduceMotion) {
        var active = Math.min(sceneTotal - 1, Math.floor(progress * sceneTotal));
        scenes.forEach(function (scene, index) {
          scene.style.opacity = index === active ? '1' : '0';
        });
        return;
      }

      scenes.forEach(function (scene, index) {
        var start = index / sceneTotal;
        var end = (index + 1) / sceneTotal;
        var stops;
        var outputs;

        if (index === 0) {
          stops = [0, end - 0.04, end + 0.08];
          outputs = [1, 1, 0];
        } else if (index === sceneTotal - 1) {
          stops = [start - 0.08, start + 0.04, 1];
          outputs = [0, 1, 1];
        } else {
          stops = [start - 0.08, start + 0.04, end - 0.04, end + 0.08];
          outputs = [0, 1, 1, 0];
        }

        scene.style.opacity = String(piecewise(progress, stops, outputs));

        var image = scene.querySelector('.moon-scrollstory__img');
        var copy = scene.querySelector('[data-moon-scene-copy]');
        var local = clamp((progress - start) / ((end - start) || 1), 0, 1);

        if (image) {
          image.style.transform = 'scale(' + (1.15 - local * 0.15) + ')';
        }

        if (copy) {
          copy.style.transform = 'translate3d(0,' + piecewise(local, [0, 0.25, 0.85, 1], [60, 0, 0, -40]) + 'px,0)';
        }
      });

      if (storyTitle) {
        storyTitle.style.transform = 'translate3d(' + (10 - progress * 40) + '%,0,0)';
      }
    });
  }

  /* ----------------------------------------------------------------------
   * Testimonials
   * -------------------------------------------------------------------- */
  var testimonials = doc.querySelector('[data-moon-testimonials]');

  if (testimonials) {
    var quotes = Array.prototype.slice.call(testimonials.querySelectorAll('[data-moon-quote]'));
    var dots = Array.prototype.slice.call(testimonials.querySelectorAll('[data-moon-quote-dot]'));
    var quoteIndex = 0;
    var quoteTimer;

    var setQuote = function (index) {
      quoteIndex = (index + quotes.length) % quotes.length;

      quotes.forEach(function (quote, i) {
        quote.classList.toggle('is-active', i === quoteIndex);
      });

      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-active', i === quoteIndex);
      });
    };

    var startQuotes = function () {
      window.clearInterval(quoteTimer);
      quoteTimer = window.setInterval(function () {
        setQuote(quoteIndex + 1);
      }, 6000);
    };

    dots.forEach(function (dot, index) {
      dot.addEventListener('click', function () {
        setQuote(index);
        startQuotes();
      });
    });

    if (!reduceMotion) {
      startQuotes();
    }

    testimonials.addEventListener('mouseenter', function () {
      window.clearInterval(quoteTimer);
    });

    testimonials.addEventListener('mouseleave', function () {
      if (!reduceMotion) {
        startQuotes();
      }
    });
  }
})();
