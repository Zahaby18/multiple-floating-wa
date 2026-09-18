=== Multiple Floating WhatsApp ===
Contributors: genwork
Tags: whatsapp, floating button, click to chat, whatsapp chat, sticky button
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A floating WhatsApp launcher with unlimited numbers, per button labels, prefilled messages, color controls and a built in WhatsApp icon.

== Description ==

Multiple Floating WhatsApp adds a floating launcher to the corner of your site. Hovering or tapping it opens a panel that lists every WhatsApp number you configured, so visitors pick the right department, branch or team in one tap. The panel stays open until a visitor clicks somewhere else.

Features:

* Unlimited buttons, each with its own label, number or full WhatsApp URL and prefilled message
* Fallback text so unfinished rows still show up as Button 1, Button 2 and so on
* Fallback link so rows without a number point to the # anchor
* WhatsApp icon included and drawn inline, plus an optional custom icon URL
* Color controls for the launcher, the panel header, the panel and the text
* Editable panel title and intro line
* Small, medium and large launcher sizes
* Bottom right or bottom left placement
* Panel opens on hover, on focus or on tap, and closes on an outside click
* Auto open after a chosen delay, once per visitor session
* Per device visibility for desktop, tablet and mobile
* Live preview inside the settings screen, no page reload needed
* Optional shortcode for selected pages, with attribute overrides
* Vanilla JavaScript on the front end, no jQuery dependency
* Translated strings, GPL licensed and multisite aware

== Installation ==

1. Upload the plugin folder to wp-content/plugins, or install the zip through Plugins, Add New, Upload Plugin.
2. Activate the plugin.
3. Open Settings, Floating WhatsApp.
4. Add one row per number, set the label and the number in international format.
5. Save changes.

== Frequently Asked Questions ==

= Which number format should I use? =

International format without spaces or symbols, for example 6281234567890. A leading plus sign is removed automatically. A full WhatsApp URL such as https://wa.me/6281234567890 is also accepted.

= How does the panel open and close? =

It opens when a visitor hovers, focuses or taps the launcher, and closes when a visitor clicks anywhere outside the widget. Escape also closes it.

= How do I place the widget on some pages only? =

Turn off the site wide option, then add [multiple_floating_wa] to the pages you want. Attributes override saved values, for example [multiple_floating_wa position="bottom-left" size="large" button_color="#128c7e"].

= Can I change the WhatsApp icon? =

The WhatsApp icon is included and used automatically. Paste an image URL in the custom icon field to replace it.

= Does the widget work with page builders? =

Yes. It is rendered on the front end, so Elementor, block themes and classic themes all work. The shortcode is available when you need manual placement.

= Is the time in the auto open field in seconds? =

Yes. Use 0 to keep the panel closed until a visitor interacts with the launcher.

== Screenshots ==

1. Settings screen with the button repeater and the live preview.
2. Floating panel with multiple WhatsApp numbers.

== Changelog ==

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.0.0 =
* First release.
