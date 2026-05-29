=== AI Website Assistant for Inkline Connect ===
Contributors: inklinemedia
Tags: ai, assistant, chat, conversational, inkline connect, leadconnector
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.13
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

= 0.1.13 =
* Expose the chat-widget surface colours as admin settings. Two new optional colour pickers — **Chat header background** and **Received message bubble** — sit alongside the brand colour. Leave them blank to keep the cream prototype defaults (#FBFBF8 header, #F3F0E9 bubbles). When the header colour is set, the matching header bottom border is auto-derived as a subtly darker shade.
* Fix: the chat-widget close (down) arrow was almost invisible against the cream header. The shadow-DOM stylesheet now recolours the arrow to a foreground that reads on whichever header background is in effect (dark text on a light header, white on a dark header).
* Internal: restructured the shadow-DOM token push so the surface tokens can apply independently of the brand colour — previously they only flowed in when a brand colour was set.

= 0.1.12 =
* Add a "Restore Inkline default (Inter)" button in the Font section. One click switches back to Google mode, sets the family to Inter, and re-enables the Google Fonts loader — useful when an admin has experimented with another font and forgot what the default was.

= 0.1.11 =
* Replace the single font-family input with a proper Google Fonts picker: 1,900+ families bundled with the plugin, type-to-filter autocomplete, and a "Load from Google Fonts" toggle so admins can leave loading to their theme if it already ships the font. Switch the radio to "Custom CSS font-family" to paste a self-hosted font stack instead — the plugin won't load anything from Google in that mode.

= 0.1.10 =
* Add a "Settings" action link on the plugin's row in Plugins → Installed Plugins for one-click access to the settings page.

= 0.1.9 =
* Show a small clickable version pill (e.g. `v0.1.9`) next to the page title on the Settings → AI Website Assistant screen. The pill links to Plugins → Installed Plugins so you can jump straight to the plugin's row.

= 0.1.8 =
* Tighten namespacing so the plugin can't collide with anything else on the host site:
  - PHP constants and classes are now guarded with `defined()` / `class_exists()` so a duplicate install can't trigger a fatal redefine.
  - CSS custom properties (`--iaa-brand`, `--iaa-font`, etc.) are scoped to `.iaa-assist, .iaa-dock` instead of `:root`, so they can no longer leak into or be overridden by host-site styles.
  - The dock dismiss flag in localStorage now uses the `icaia-` prefix that matches the rest of the plugin's namespacing. The previous `iaa-dock-dismissed` key is migrated forward on first read so visitors who dismissed before the upgrade stay dismissed.
  - Removed the generic `.iaa-icon` / `.iaa-icon--sparkles` classes from the bundled sparkles SVG partial; only consumer-supplied scoped classes (`.iaa-dock__icon-svg`, `.iaa-assist__icon-svg`, `.iaa-assist__eyebrow-glyph`) are used now.

= 0.1.7 =
* Replace the legacy blue chat-widget header (and other Inkline Connect default surfaces) with the neutral palette from the design prototype whenever a brand colour is set in plugin settings. Brand colour still drives the brand-coloured slots (header text, send bubble, active state, avatar border); structural surfaces (header background, message-pane background, received-message bubbles) get the matching neutrals. Leave the brand colour blank to skip the override and keep the chat widget on its Inkline Connect configuration.

= 0.1.6 =
* Bundle the Font Awesome Pro 7 `sparkles` glyph as an inline SVG so the assistant renders correctly on sites that don't have Font Awesome Pro loaded. The plugin no longer requires Font Awesome on the host site.

= 0.1.5 =
* Add a master "Show the assistant on the front end" toggle. When off, the dock, every in-page widget instance (shortcode and Elementor), and the chat-widget embed are all suppressed — the shortcode renders nothing, takes no space.
* Brand colour is now optional. Leave it blank to keep the Inkline Connect chat widget on whatever colours you configured there; the in-page widget and dock fall back to a neutral default.
* Move the dock dismiss chip to the top-left corner of the bar (straddling the rounded edge), matching the latest design prototype.

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
