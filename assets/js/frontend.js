/*
 * AI Website Assistant for Inkline Connect — frontend behaviour.
 *
 * Three things, in one vanilla IIFE:
 *
 *  1. In-page assistant (.iaa-assist):
 *     - Builds the rotating "Try" chips by shuffling the settings pool.
 *     - Runs an animated-placeholder typewriter on the input, stopping
 *       on focus so the visitor never has to fight a moving placeholder.
 *     - On chip click, fills the input and nudges the send button to
 *       cue the next tap.
 *     - On submit, opens the connected chat widget and sends the text
 *       as the first message.
 *
 *  2. Docked assistant (.iaa-dock):
 *     - Shows on load when no in-page assistant exists on the page;
 *       otherwise hides whenever any in-page assistant is in view
 *       (offset by the site sticky header so it triggers under the nav).
 *     - While the chat widget is open, folds to a circle around its
 *       send button, which becomes the chat close control.
 *     - Visitor can dismiss with the X chip; the flag persists in
 *       localStorage and is cleared the next time the chat opens.
 *
 *  3. Chat-widget bridge (the LeadConnector chat-widget element):
 *     - Inject brand-color CSS into the widget's shadow roots.
 *     - Drive the widget's textarea + Enter to send messages from the
 *       assistant form (the widget exposes no programmatic send API).
 *
 * Configuration lives on window.ICAIA, populated by wp_localize_script:
 *   { brand, fontStack, suggestions: string[], dock: bool }
 */
(function () {
	'use strict';

	if (typeof window === 'undefined' || typeof document === 'undefined') return;

	// Defer everything until the DOM is parsed. WordPress prints this
	// script in the footer at the same hook priority as the dock + chat
	// markup, and core's footer-scripts callback runs first by
	// registration order — so without this guard the IIFE executes
	// before document.querySelector can see the dock element.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}

	function start() {

	var CFG = window.ICAIA || {};
	var SUGGESTIONS = Array.isArray(CFG.suggestions) ? CFG.suggestions.slice() : [];
	// Brand may be empty — that signals "don't override the chat widget".
	var BRAND = typeof CFG.brand === 'string' ? CFG.brand : '';
	var FONT_STACK = CFG.fontStack || "'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif";
	var REDUCED_MOTION = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ------------------------------------------------------------ */
	/*  Animated placeholder — shared by in-page assistants and dock.*/
	/* ------------------------------------------------------------ */

	function startPlaceholderTyper(input, pool) {
		if (REDUCED_MOTION) return;
		var src = (pool && pool.length) ? pool.slice() : SUGGESTIONS.slice();
		if (!src.length) return;
		// Fisher-Yates so each field cycles its own fresh order.
		for (var i = src.length - 1; i > 0; i -= 1) {
			var j = Math.floor(Math.random() * (i + 1));
			var t = src[i]; src[i] = src[j]; src[j] = t;
		}
		var original = input.getAttribute('placeholder') || '';
		var stopped = false, qi = 0, step = 0, blink = 0;

		function stop() {
			stopped = true;
			clearTimeout(step);
			clearInterval(blink);
			input.placeholder = original;
		}
		input.addEventListener('focus', stop, { once: true });

		function run() {
			if (stopped) return;
			var q = src[qi % src.length];
			qi += 1;
			var ci = 0;
			(function typeChar() {
				if (stopped) return;
				ci += 1;
				input.placeholder = q.slice(0, ci) + '|';
				if (ci < q.length) {
					step = window.setTimeout(typeChar, 42 + Math.random() * 38);
					return;
				}
				var on = true;
				blink = window.setInterval(function () {
					on = !on;
					input.placeholder = q + (on ? '|' : ' ');
				}, 520);
				step = window.setTimeout(function () {
					clearInterval(blink);
					if (stopped) return;
					input.placeholder = '';
					step = window.setTimeout(run, 320);
				}, 2000);
			})();
		}
		step = window.setTimeout(run, 700);
	}

	/* ------------------------------------------------------------ */
	/*  Chat-widget bridge.                                          */
	/* ------------------------------------------------------------ */

	function chatWidgetEl() {
		return document.querySelector('chat-widget');
	}
	function chatAPI() {
		try { return window.leadConnector && window.leadConnector.chatWidget; }
		catch (_) { return null; }
	}

	function collectShadowRoots(root, acc) {
		root.querySelectorAll('*').forEach(function (n) {
			if (n.shadowRoot) { acc.push(n.shadowRoot); collectShadowRoots(n.shadowRoot, acc); }
		});
		return acc;
	}

	function findInWidget(selector) {
		var el = chatWidgetEl();
		if (!el || !el.shadowRoot) return null;
		var roots = [el.shadowRoot].concat(collectShadowRoots(el.shadowRoot, []));
		for (var i = 0; i < roots.length; i += 1) {
			var match = roots[i].querySelector(selector);
			if (match) return match;
		}
		return null;
	}

	// Push brand colour + matching styling into the widget's shadow DOM.
	function brandTokens() {
		return {
			'--chat-widget-active-color': BRAND,
			'--chat-widget-bubble-color': BRAND,
			'--chat-widget-primary-color': BRAND,
			'--chat-widget-primary-solid-color': BRAND,
			'--chat-widget-button-color': BRAND,
			'--chat-widget-header-message-text-color': BRAND,
			'--chat-widget-sender-message-color': BRAND,
			'--chat-widget-avatar-border-color': BRAND,
			'--chat-widget-font-family': FONT_STACK,
		};
	}
	function buildShadowCss() {
		var darker = BRAND; // hover swap handled by the widget itself.
		return [
			'.lc_text-widget--box{border-radius:20px !important;box-shadow:0 4px 14px rgba(20,22,18,0.18),0 20px 52px rgba(20,22,18,0.36) !important}',
			'.lc_text-widget_heading--root{border-radius:20px 20px 0 0 !important}',
			'chat-pane{border-radius:0 0 20px 20px !important}',
			'.bubble.outgoing{border-radius:14px 3px 14px 14px !important}',
			'.bubble.incoming{border-radius:3px 14px 14px 14px !important}',
			'.lc_text-widget--bubble img{width:30px !important;height:30px !important}',
			'.lc_text-widget--bubble{box-shadow:0 4px 14px rgba(20,22,18,0.18),0 20px 52px rgba(20,22,18,0.36) !important}'
		].join('');
	}
	function injectShadowStyle(root, css) {
		if (!root.querySelector('#iaa-widget-style')) {
			var style = document.createElement('style');
			style.id = 'iaa-widget-style';
			style.textContent = css;
			root.appendChild(style);
		}
	}
	function styleChatWidget() {
		var el = chatWidgetEl();
		if (!el) return;
		// Only push brand-color tokens when an explicit brand is set in
		// the plugin settings; otherwise leave the chat widget on the
		// colors the admin configured over in Inkline Connect.
		if (BRAND) {
			var tokens = brandTokens();
			for (var k in tokens) {
				if (Object.prototype.hasOwnProperty.call(tokens, k)) el.style.setProperty(k, tokens[k]);
			}
		}
		if (!el.shadowRoot) return;
		var css = buildShadowCss();
		[el.shadowRoot].concat(collectShadowRoots(el.shadowRoot, [])).forEach(function (r) {
			injectShadowStyle(r, css);
		});
	}
	// The widget mounts its panel only when opened, so re-inject styles
	// into any new shadow roots that appear.
	setInterval(styleChatWidget, 350);

	function sendToWidget(text) {
		var api = chatAPI();
		try { if (api && typeof api.openWidget === 'function') api.openWidget(); } catch (_) {}
		var tries = 0;
		var resetClicked = false;
		var poll = setInterval(function () {
			var ta = findInWidget('textarea.native-textarea') || findInWidget('textarea');
			if (ta) {
				clearInterval(poll);
				ta.focus();
				var desc = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, 'value');
				if (desc && desc.set) desc.set.call(ta, text);
				else ta.value = text;
				ta.dispatchEvent(new Event('input', { bubbles: true }));
				setTimeout(function () {
					['keydown', 'keypress', 'keyup'].forEach(function (ev) {
						ta.dispatchEvent(new KeyboardEvent(ev, {
							key: 'Enter', code: 'Enter', keyCode: 13, which: 13,
							bubbles: true, cancelable: true
						}));
					});
				}, 180);
				return;
			}
			if (!resetClicked) {
				var reset = findInWidget('.reset-chat-button');
				if (reset) { reset.click(); resetClicked = true; }
			}
			if (++tries > 80) clearInterval(poll);
		}, 150);
	}

	// Wire every assistant form (in-page + dock) to the bridge.
	document.querySelectorAll('[data-iaa-assist-bar]').forEach(function (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var input = form.querySelector('.iaa-assist__input, .iaa-dock__input');
			var text = input ? (input.value || '').trim() : '';
			if (!text) return;
			sendToWidget(text);
			if (input) input.value = '';
		});
	});

	/* ------------------------------------------------------------ */
	/*  In-page assistant — chips, nudge, typewriter.               */
	/* ------------------------------------------------------------ */

	document.querySelectorAll('[data-iaa-assist]').forEach(function (root) {
		var input = root.querySelector('.iaa-assist__input');
		var sendBtn = root.querySelector('.iaa-assist__send');
		var list = root.querySelector('.iaa-assist__chips');

		var clearHint = function () { if (sendBtn) sendBtn.classList.remove('is-hinting'); };
		if (sendBtn) sendBtn.addEventListener('click', clearHint);
		var form = root.querySelector('.iaa-assist__bar');
		if (form) form.addEventListener('submit', clearHint);

		// Build chips from the page pool.
		if (list && SUGGESTIONS.length) {
			var pool = SUGGESTIONS.slice();
			for (var i = pool.length - 1; i > 0; i -= 1) {
				var j = Math.floor(Math.random() * (i + 1));
				var t = pool[i]; pool[i] = pool[j]; pool[j] = t;
			}
			pool.slice(0, 5).forEach(function (text) {
				var li = document.createElement('li');
				var chip = document.createElement('button');
				chip.type = 'button';
				chip.className = 'iaa-assist__chip';
				chip.textContent = text;
				chip.addEventListener('click', function () {
					if (!input) return;
					input.value = text;
					input.focus();
					if (sendBtn) sendBtn.classList.add('is-hinting');
				});
				li.appendChild(chip);
				list.appendChild(li);
			});
		}

		if (input) startPlaceholderTyper(input, SUGGESTIONS);
	});

	/* ------------------------------------------------------------ */
	/*  Docked assistant — visibility, chat-collapse, dismiss.       */
	/* ------------------------------------------------------------ */

	var dock = document.querySelector('[data-iaa-dock]');
	if (dock) {
		var DISMISS_KEY = 'iaa-dock-dismissed';
		var readDismissed = function () {
			try { return localStorage.getItem(DISMISS_KEY) === '1'; }
			catch (_) { return false; }
		};
		var writeDismissed = function (v) {
			try { v ? localStorage.setItem(DISMISS_KEY, '1') : localStorage.removeItem(DISMISS_KEY); }
			catch (_) {}
		};

		var dockInput = dock.querySelector('.iaa-dock__input');
		var dockSend = dock.querySelector('.iaa-dock__send');
		var shell = dock.querySelector('.iaa-dock__shell');
		var dismissBtn = dock.querySelector('[data-iaa-dock-dismiss]');

		var dismissed = readDismissed();
		var setVisible = function (show) {
			var effective = show && !dismissed;
			dock.classList.toggle('iaa-dock--visible', effective);
			if (effective) dock.removeAttribute('inert');
			else dock.setAttribute('inert', '');
		};

		// Visibility — show unless an inline assistant is in view.
		var inlineBars = Array.prototype.slice
			.call(document.querySelectorAll('.iaa-assist__bar'))
			.filter(function (b) { return !b.closest('[data-iaa-dock]'); });

		var computeShow = function () { return true; };
		var inView = new Set();
		if (!inlineBars.length) {
			computeShow = function () { return true; };
			setVisible(true);
		} else if ('IntersectionObserver' in window) {
			var header = document.querySelector('header.site-header, header[role="banner"], .site-header, #masthead');
			var navH = header ? Math.round(header.getBoundingClientRect().height) : 0;
			computeShow = function () { return inView.size === 0; };
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) inView.add(entry.target);
					else inView.delete(entry.target);
				});
				setVisible(computeShow());
			}, { threshold: 0, rootMargin: '-' + navH + 'px 0px 0px 0px' });
			inlineBars.forEach(function (b) { io.observe(b); });
		} else {
			// No IO — be friendly and just show the dock.
			setVisible(true);
		}

		// Chat-collapse — fold the dock to the close-button circle.
		var chatOpen = false;
		setInterval(function () {
			var open = false;
			try {
				var api = chatAPI();
				open = !!(api && typeof api.isActive === 'function' && api.isActive());
			} catch (_) {}
			if (open === chatOpen) return;
			chatOpen = open;
			dock.classList.toggle('iaa-dock--collapsed', open);
			if (dockSend) {
				dockSend.type = open ? 'button' : 'submit';
				dockSend.setAttribute('aria-label', open ? 'Close chat' : 'Send');
			}
			// Opening the chat resurrects a dismissed dock so it can
			// act as the chat close control.
			if (open && dismissed) {
				dismissed = false;
				writeDismissed(false);
				setVisible(computeShow());
			}
		}, 250);
		if (dockSend) {
			dockSend.addEventListener('click', function () {
				if (!dock.classList.contains('iaa-dock--collapsed')) return;
				try {
					var api = chatAPI();
					if (api && typeof api.closeWidget === 'function') api.closeWidget();
				} catch (_) {}
			});
		}

		// Dismiss hint — fades in on hover, lingers briefly after mouseout.
		var hintHideTimer = 0;
		var showHint = function () {
			window.clearTimeout(hintHideTimer);
			dock.classList.add('iaa-dock--hint');
		};
		var scheduleHintHide = function () {
			window.clearTimeout(hintHideTimer);
			hintHideTimer = window.setTimeout(function () {
				dock.classList.remove('iaa-dock--hint');
			}, 500);
		};
		[shell, dismissBtn].forEach(function (el) {
			if (!el) return;
			el.addEventListener('mouseenter', showHint);
			el.addEventListener('mouseleave', scheduleHintHide);
		});
		if (dismissBtn) {
			dismissBtn.addEventListener('click', function () {
				dismissed = true;
				writeDismissed(true);
				window.clearTimeout(hintHideTimer);
				dock.classList.remove('iaa-dock--hint');
				setVisible(computeShow());
			});
		}

		// Animated placeholder for the dock — borrow the page pool.
		if (dockInput) startPlaceholderTyper(dockInput, SUGGESTIONS);
	}

	} // end start()
})();
