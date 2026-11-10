/**
 * MOON Core — front-end behaviour.
 * Vanilla JS only: no jQuery dependency, no framework, no external library.
 */
( function () {
	'use strict';

	var data = window.moonCore || {};

	function $( selector, context ) {
		return ( context || document ).querySelector( selector );
	}

	function $$( selector, context ) {
		return Array.prototype.slice.call( ( context || document ).querySelectorAll( selector ) );
	}

	function ajax( action, payload ) {
		var body = new FormData();
		body.append( 'action', action );
		body.append( 'nonce', data.nonce || '' );

		Object.keys( payload || {} ).forEach( function ( key ) {
			var value = payload[ key ];
			if ( Array.isArray( value ) ) {
				value.forEach( function ( item ) {
					body.append( key + '[]', item );
				} );
			} else {
				body.append( key, value );
			}
		} );

		return fetch( data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body,
		} ).then( function ( response ) {
			return response.json();
		} );
	}

	/* ---------------------------------------------------------------------
	 * Header: shrink on scroll
	 * ------------------------------------------------------------------ */
	function initHeader() {
		var wrap = $( '[data-moon-header]' );
		if ( ! wrap ) {
			return;
		}

		var header = $( '.moon-header', wrap );
		var last = window.pageYOffset;
		var ticking = false;

		function onScroll() {
			var y = window.pageYOffset;
			wrap.classList.toggle( 'is-scrolled', y > 24 );

			if ( header ) {
				header.style.setProperty( '--moon-scroll-progress', Math.min( 1, y / 240 ) );
			}

			last = y;
			ticking = false;
		}

		window.addEventListener(
			'scroll',
			function () {
				if ( ! ticking ) {
					ticking = true;
					window.requestAnimationFrame( onScroll );
				}
			},
			{ passive: true }
		);

		onScroll();
		document.body.classList.toggle( 'moon-hero-under-header', !! $( '[data-moon-hero]' ) );
	}

	/* ---------------------------------------------------------------------
	 * Search panel + mobile drawer
	 * ------------------------------------------------------------------ */
	function initSearch() {
		var toggle = $( '[data-moon-search-toggle]' );
		var panel = $( '#moon-search-panel' );
		if ( ! toggle || ! panel ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var open = panel.hasAttribute( 'hidden' );
			if ( open ) {
				panel.removeAttribute( 'hidden' );
				var input = $( 'input', panel );
				if ( input ) {
					window.setTimeout( function () {
						input.focus();
					}, 60 );
				}
			} else {
				panel.setAttribute( 'hidden', '' );
			}
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! panel.hasAttribute( 'hidden' ) ) {
				panel.setAttribute( 'hidden', '' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	}

	function initMobileNav() {
		var burger = $( '.moon-header__burger' );
		var drawer = $( '#moon-mobile-nav' );
		if ( ! burger || ! drawer ) {
			return;
		}

		function close() {
			drawer.setAttribute( 'hidden', '' );
			burger.setAttribute( 'aria-expanded', 'false' );
			document.body.classList.remove( 'moon-nav-open' );
		}

		burger.addEventListener( 'click', function () {
			var open = drawer.hasAttribute( 'hidden' );
			if ( open ) {
				drawer.removeAttribute( 'hidden' );
				document.body.classList.add( 'moon-nav-open' );
			} else {
				close();
			}
			burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		$$( '[data-moon-mobile-close]', drawer ).forEach( function ( button ) {
			button.addEventListener( 'click', close );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! drawer.hasAttribute( 'hidden' ) ) {
				close();
				burger.focus();
			}
		} );
	}

	/* ---------------------------------------------------------------------
	 * Day / night switch
	 * ------------------------------------------------------------------ */
	function initTheme() {
		var root = document.documentElement;
		var toggles = $$( '[data-moon-theme-toggle]' );
		if ( ! toggles.length ) {
			return;
		}

		function label( mode ) {
			return mode === 'dark'
				? ( data.strings && data.strings.themeLight ) || 'حالت روز'
				: ( data.strings && data.strings.themeDark ) || 'حالت شب';
		}

		function sync( mode ) {
			toggles.forEach( function ( toggle ) {
				toggle.setAttribute( 'aria-pressed', mode === 'dark' ? 'true' : 'false' );
				var text = $( '[data-moon-theme-label]', toggle );
				if ( text ) {
					text.textContent = label( mode );
				}
			} );
		}

		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var current = root.getAttribute( 'data-theme' ) === 'dark' ? 'dark' : 'light';
				var next = current === 'dark' ? 'light' : 'dark';

				root.setAttribute( 'data-theme', next );
				root.style.colorScheme = next;

				try {
					window.localStorage.setItem( 'moonTheme', next );
				} catch ( e ) {}

				sync( next );

				ajax( 'moon_set_theme', { mode: next } ).catch( function () {} );
			} );
		} );

		sync( root.getAttribute( 'data-theme' ) === 'dark' ? 'dark' : 'light' );
	}

	/* ---------------------------------------------------------------------
	 * Wishlist (only when Moon Core handles it)
	 * ------------------------------------------------------------------ */
	function updateWishlistCount( count ) {
		$$( '[data-moon-wishlist-count]' ).forEach( function ( node ) {
			node.textContent = String( count );
		} );
	}

	function initWishlist() {
		$$( '[data-moon-wishlist]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				var id = button.getAttribute( 'data-moon-wishlist' );
				var labelNode = $( '.moon-wishlist-btn__label', button );
				button.disabled = true;

				ajax( 'moon_toggle_wishlist', { product_id: id } )
					.then( function ( response ) {
						if ( ! response || ! response.success ) {
							throw new Error( 'wishlist' );
						}

						var active = !! response.data.active;

						$$( '[data-moon-wishlist="' + id + '"]' ).forEach( function ( twin ) {
							twin.classList.toggle( 'is-active', active );
							twin.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
							twin.setAttribute( 'aria-label', response.data.label );
							var twinLabel = $( '.moon-wishlist-btn__label', twin );
							if ( twinLabel ) {
								twinLabel.textContent = response.data.label;
							}
						} );

						if ( labelNode ) {
							labelNode.textContent = response.data.label;
						}

						updateWishlistCount( response.data.count );
					} )
					.catch( function () {
						if ( labelNode ) {
							labelNode.textContent = ( data.strings && data.strings.error ) || '';
						}
					} )
					.finally( function () {
						button.disabled = false;
					} );
			} );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Shop filters + load more
	 * ------------------------------------------------------------------ */
	function shopGrid() {
		return $( '.moon-core ul.products' ) || $( 'ul.products' );
	}

	function loadMoreButton() {
		var box = $( '.moon-load-more' );

		if ( ! box ) {
			box = document.createElement( 'div' );
			box.className = 'moon-load-more';
			var grid = shopGrid();
			if ( grid && grid.parentNode ) {
				grid.parentNode.insertBefore( box, grid.nextSibling );
			}
		}

		return box;
	}

	function setLoadMore( page, maxPages ) {
		if ( ! data.loadMore ) {
			return;
		}

		var box = loadMoreButton();
		if ( ! box ) {
			return;
		}

		if ( page >= maxPages ) {
			box.innerHTML = '';
			box.setAttribute( 'data-page', String( page ) );
			box.setAttribute( 'data-max', String( maxPages ) );
			return;
		}

		box.innerHTML = '';
		box.setAttribute( 'data-page', String( page ) );
		box.setAttribute( 'data-max', String( maxPages ) );

		var button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'moon-btn moon-btn--outline';
		button.textContent = ( data.strings && data.strings.loadMore ) || 'Load more';
		box.appendChild( button );

		button.addEventListener( 'click', function () {
			var next = parseInt( box.getAttribute( 'data-page' ), 10 ) + 1;
			button.disabled = true;
			button.textContent = ( data.strings && data.strings.loading ) || '…';

			ajax( 'moon_filter_products', currentFilters( next ) ).then( function ( response ) {
				if ( ! response || ! response.success ) {
					throw new Error( 'load-more' );
				}

				var grid = shopGrid();
				if ( grid ) {
					var temp = document.createElement( 'div' );
					temp.innerHTML = response.data.html;
					var items = $$( 'li.product', temp );
					items.forEach( function ( item ) {
						grid.appendChild( item );
					} );
				}

				initWishlist();
				setLoadMore( response.data.paged, response.data.max_pages );
			} ).catch( function () {
				button.disabled = false;
				button.textContent = ( data.strings && data.strings.error ) || '';
			} );
		} );
	}

	function currentFilters( page ) {
		var form = $( '[data-moon-filters]' );
		var payload = { paged: page || 1 };

		if ( form ) {
			var formData = new FormData( form );
			formData.forEach( function ( value, key ) {
				if ( 0 === key.indexOf( 'moon_' ) && 'moon_nonce' !== key ) {
					var clean = key.replace( '[]', '' );
					if ( payload[ clean ] ) {
						payload[ clean ] = [].concat( payload[ clean ], value );
					} else {
						payload[ clean ] = value;
					}
				}
			} );

			var cat = form.getAttribute( 'data-cat' );
			if ( cat ) {
				payload.product_cat = cat;
			}
		}

		return payload;
	}

	function initFilters() {
		var form = $( '[data-moon-filters]' );
		if ( ! form ) {
			return;
		}

		var grid = shopGrid();
		if ( ! grid ) {
			return;
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			var gridEl = shopGrid();
			if ( gridEl ) {
				gridEl.classList.add( 'moon-shop-loading' );
			}

			var submit = $( 'button[type="submit"]', form );
			if ( submit ) {
				submit.disabled = true;
			}

			ajax( 'moon_filter_products', currentFilters( 1 ) )
				.then( function ( response ) {
					if ( ! response || ! response.success ) {
						throw new Error( 'filters' );
					}

					var list = shopGrid();
					var wrapper = document.createElement( 'div' );

					if ( response.data.empty ) {
						wrapper.innerHTML = '<div class="moon-shop-empty">' + ( response.data.empty_label || '' ) + '</div>';
					} else {
						wrapper.innerHTML = response.data.html;
					}

					if ( list ) {
						list.outerHTML = wrapper.innerHTML;
					}

					initWishlist();
					setLoadMore( response.data.paged, response.data.max_pages );

					// Keep the URL shareable without reloading.
					if ( window.history && window.history.replaceState ) {
						var params = new URLSearchParams( new FormData( form ) );
						params.delete( 'moon_nonce' );
						var query = params.toString();
						window.history.replaceState( {}, '', window.location.pathname + ( query ? '?' + query : '' ) );
					}
				} )
				.catch( function () {
					var list = shopGrid();
					if ( list ) {
						list.classList.remove( 'moon-shop-loading' );
					}
				} )
				.finally( function () {
					if ( submit ) {
						submit.disabled = false;
					}
				} );
		} );

		var clear = $( '[data-moon-filters-clear]', form );
		if ( clear ) {
			clear.addEventListener( 'click', function () {
				$$( 'input[type="checkbox"]', form ).forEach( function ( input ) {
					input.checked = false;
				} );
				$$( 'input[type="number"]', form ).forEach( function ( input ) {
					input.value = '';
				} );
				if ( form.requestSubmit ) {
					form.requestSubmit();
				} else {
					form.submit();
				}
			} );
		}
	}

	/* ---------------------------------------------------------------------
	 * Countdown
	 * ------------------------------------------------------------------ */
	function initCountdowns() {
		$$( '[data-moon-countdown]' ).forEach( function ( box ) {
			var end = parseInt( box.getAttribute( 'data-moon-countdown' ), 10 ) * 1000;
			if ( ! end ) {
				return;
			}

			function pad( value ) {
				return value < 10 ? '0' + value : String( value );
			}

			function tick() {
				var diff = end - Date.now();
				if ( diff <= 0 ) {
					box.setAttribute( 'data-finished', 'true' );
					window.clearInterval( timer );
					return;
				}

				var days = Math.floor( diff / 86400000 );
				var hours = Math.floor( ( diff % 86400000 ) / 3600000 );
				var minutes = Math.floor( ( diff % 3600000 ) / 60000 );
				var seconds = Math.floor( ( diff % 60000 ) / 1000 );

				var map = { days: days, hours: hours, minutes: minutes, seconds: seconds };
				Object.keys( map ).forEach( function ( key ) {
					var node = $( '[data-' + key + ']', box );
					if ( node ) {
						node.textContent = pad( map[ key ] );
					}
				} );
			}

			var timer = window.setInterval( tick, 1000 );
			tick();
		} );
	}

	/* ---------------------------------------------------------------------
	 * Forms
	 * ------------------------------------------------------------------ */
	function bindForm( form, action ) {
		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			var button = $( 'button[type="submit"]', form );
			var message = $( '.moon-newsletter__msg, .moon-form-msg', form );
			var payload = {};

			new FormData( form ).forEach( function ( value, key ) {
				payload[ key ] = value;
			} );

			if ( button ) {
				button.disabled = true;
				button.setAttribute( 'data-label', button.textContent );
				button.textContent = ( data.strings && data.strings.loading ) || '…';
			}

			ajax( action, payload )
				.then( function ( response ) {
					if ( response && response.data && response.data.message ) {
						if ( message ) {
							message.textContent = response.data.message;
						}
						if ( response.success ) {
							form.reset();
						}
					}
				} )
				.catch( function () {
					if ( message ) {
						message.textContent = ( data.strings && data.strings.error ) || '';
					}
				} )
				.finally( function () {
					if ( button ) {
						button.disabled = false;
						button.textContent = button.getAttribute( 'data-label' ) || button.textContent;
					}
				} );
		} );
	}

	function initForms() {
		$$( '[data-moon-newsletter]' ).forEach( function ( form ) {
			bindForm( form, 'moon_subscribe' );
		} );

		$$( '[data-moon-contact]' ).forEach( function ( form ) {
			bindForm( form, 'moon_contact' );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Motion, hero video on mobile, back to top
	 * ------------------------------------------------------------------ */
	function initMotion() {
		var targets = $$( '.moon-motion--fade, .moon-motion--fade-up, .moon-motion--fade-in, .moon-motion--scale' );

		if ( ! ( 'IntersectionObserver' in window ) || ! data.animations ) {
			targets.forEach( function ( node ) {
				node.classList.add( 'is-inview' );
			} );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-inview' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
		);

		targets.forEach( function ( node ) {
			observer.observe( node );
		} );
	}

	function initHeroVideo() {
		$$( '[data-moon-hero]' ).forEach( function ( hero ) {
			var video = $( '.moon-hero__video', hero );
			if ( ! video ) {
				return;
			}

			var isMobile = window.matchMedia( '(max-width: 720px)' ).matches;
			var off = hero.getAttribute( 'data-mobile-video' ) === 'off';

			if ( isMobile && off ) {
				video.pause();
				video.removeAttribute( 'autoplay' );
				document.body.classList.add( 'moon-hero-mobile-poster' );
			}
		} );
	}

	function initBackToTop() {
		var button = $( '.moon-to-top' );
		if ( ! button ) {
			return;
		}

		window.addEventListener(
			'scroll',
			function () {
				button.classList.toggle( 'is-visible', window.pageYOffset > 600 );
			},
			{ passive: true }
		);

		button.addEventListener( 'click', function () {
			window.scrollTo( { top: 0, behavior: 'smooth' } );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Boot
	 * ------------------------------------------------------------------ */
	function initAll() {
		initHeader();
		initSearch();
		initMobileNav();
		initTheme();
		initWishlist();
		initFilters();
		initCountdowns();
		initForms();
		initMotion();
		initHeroVideo();
		initBackToTop();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}

	// Re-run inside the Elementor editor preview after each widget renders.
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}

		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function () {
			initWishlist();
			initCountdowns();
			initForms();
			initMotion();
			initHeroVideo();
		} );
	} );
} )();
