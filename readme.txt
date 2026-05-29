=== AI Website Assistant for Inkline Connect ===
Contributors: inklinemedia
Tags: ai, assistant, chat, conversational, inkline connect, leadconnector
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Drops an Inkline Connect–powered AI assistant into your site: a docked bar, an in-page widget (shortcode + Elementor), and matching chat-widget styling.

== Description ==

The plugin pairs two surfaces with your Inkline Connect chat widget:

* A fixed **docked bar** that follows visitors as they scroll, hiding whenever an in-page widget is in view.
* An **in-page widget** you can drop anywhere via the `[inkline_ai_assistant]` shortcode or the bundled Elementor widget.

Both surfaces share the same input bar, suggestion-chip rotation, animated placeholder, and chat-widget bridge. Configure the brand colour, typography, and starter prompts in **Settings → AI Website Assistant**.

Nothing renders on the front end until you paste your Inkline Connect chat-widget embed code into the settings.

== Installation ==

1. Upload the plugin zip from the [GitHub Releases page](https://github.com/inkline-media/inkline-connect-ai-assistant/releases).
2. Activate it.
3. Visit **Settings → AI Website Assistant**, paste your Inkline Connect chat-widget embed, set the brand colour, and save.
4. Drop the in-page widget anywhere with `[inkline_ai_assistant]`, or use the **AI Assistant** Elementor widget.

== Frequently Asked Questions ==

= Where do updates come from? =

GitHub. The plugin uses the WordPress 5.8+ `Update URI` header so updates flow directly from this repo, bypassing wp.org.

= Can I dismiss the docked bar? =

Yes — a small chip appears on hover. Clicking it dismisses the dock site-wide. Opening the chat widget clears the dismiss and returns the dock to its default behaviour.

= Does it work without Elementor? =

Yes. The Elementor widget is optional — the shortcode covers every layout.

== Changelog ==

= 0.1.4 =
* Use the Font Awesome `fa-sparkles` icon for the assistant's sparkle glyph (matches the design prototype) instead of a generic inline star SVG. Requires Font Awesome Pro to be loaded on the site (sparkles is a Pro icon). Size and vertical centring tuned to match the prototype.

= 0.1.3 =
* Fix: themes that styled all buttons (auto width, square border-radius, custom backgrounds) were squashing the assistant send button into a small rectangle instead of the brand-coloured circle. The CSS now uses two-class selectors and a defensive reset so theme rules cannot override the assistant or dock component shapes.

= 0.1.2 =
* Honor the "Check Again" button on Dashboard → Updates: the updater now invalidates its 6-hour release cache when WordPress runs a forced check (`?force-check=1`), so the click reaches GitHub for a fresh lookup instead of returning a stale entry.

= 0.1.1 =
* Fix: the docked assistant stayed hidden because the frontend script ran before the dock markup was in the DOM. The script now defers all DOM work until DOMContentLoaded, so the dock initializes correctly regardless of where the theme prints the footer script.

= 0.1.0 =
* Initial release.
