# AI Website Assistant for Inkline Connect

A WordPress plugin that drops an [Inkline Connect](https://inkline.ca)–powered AI assistant into your site:

- **Docked assistant** — a fixed bottom-of-viewport bar that follows visitors as they scroll, auto-hiding whenever an in-page assistant is in view. Visitors can dismiss it (persists in `localStorage`); opening the chat widget brings it back.
- **In-page widget** — drop the assistant anywhere via the `[inkline_ai_assistant]` shortcode or the **AI Assistant** Elementor widget. Two visual variants (`card`, `bare`) and two alignments (`left`, `center`).
- **Connected chat widget** — the plugin injects your Inkline Connect chat-widget embed in the footer and brand-matches it (colour, typography, rounded corners, shadow).

The dock and in-page widget share one input bar style, one set of "Try" suggestion chips, and one animated-placeholder typewriter — all driven by the colour and font you set in **Settings → AI Website Assistant**.

## Install

The plugin distributes via GitHub releases. Inside WordPress:

1. Upload the latest release zip from the [Releases page](https://github.com/inkline-media/inkline-connect-ai-assistant/releases) (Plugins → Add New → Upload Plugin), then activate.
2. Go to **Settings → AI Website Assistant** and paste your Inkline Connect chat-widget embed code. Until you do, the plugin renders nothing on the front end.

Once installed, WordPress will pull future updates straight from GitHub via the `Update URI` plugin header — no separate updater needed.

## Settings

- **Chat-widget embed code** — paste the full `<chat-widget>` element and the LeadConnector loader `<script>`.
- **Brand color** — defaults to Inkline's `#0057B8`. Applied to the send button, focus ring, accent details across the in-page widget, the dock, and the chat widget.
- **Font family** — defaults to `Inter` with a sensible system fallback. Optional Google Fonts load.
- **Docked assistant** — toggle the site-wide bar on/off.
- **Starter prompts** — newline-separated list used by the rotating "Try" chips and the animated placeholder. Phrase them in the visitor's voice (questions or first-person statements).

## Shortcode

```text
[inkline_ai_assistant]
```

With options:

```text
[inkline_ai_assistant
    eyebrow="Ask Inkline"
    heading="Tell us what you’re trying to solve."
    sub="Plain language is fine."
    variant="card"
    align="center"]
```

- `variant`: `card` (default, white surface) or `bare` (no surface — for use inside a hero band).
- `align`: `left` (default) or `center`.

## Elementor

When Elementor is active, drag the **AI Assistant** widget from the **General** category. The controls mirror the shortcode attributes.

## Behaviour overview

| State | Dock | In-page widget |
| --- | --- | --- |
| No in-page widget on page | Visible on load | — |
| In-page widget on page, in view | Hidden | Active |
| In-page widget on page, scrolled past | Visible | — |
| Chat widget open | Collapsed to a circle around the close button | — |
| Visitor dismissed the dock | Hidden across the site until they open the chat | — |

## Updates

The plugin uses the WordPress 5.8+ Update URI integration. Every release tag on this repo becomes an update offered through the Plugins screen.

To cut a release:

1. Bump `ICAIA_VERSION` in `inkline-connect-ai-assistant.php` and the `Version:` header.
2. Tag and push: `git tag v0.1.1 && git push --tags`.
3. Create a GitHub release for that tag with a short changelog.

The plugin caches release lookups for 6 hours (and failed lookups for 1 hour, so a transient GitHub API outage doesn't slow page loads).

## Requirements

- WordPress 5.8+ (for the Update URI integration)
- PHP 7.4+
- An Inkline Connect chat-widget embed

The plugin ships the Font Awesome Pro `sparkles` SVG inline (Commercial License), so the host site does not need Font Awesome loaded.

## License

GPL-2.0-or-later.
