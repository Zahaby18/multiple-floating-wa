# Multiple Floating WhatsApp

A WordPress plugin that adds one floating WhatsApp launcher to your site. The launcher opens a panel listing every WhatsApp number you configured, each with its own label and prefilled message.

Built for sites that run several numbers, such as multiple branches, departments or teams, where a single floating button is not enough.

## Features

- Unlimited buttons in one repeater, each with a label, a number or full URL, and a prefilled message
- Fallback label so an unfinished row renders as Button 1, Button 2 and so on
- Fallback link so a row without a number points to the # anchor
- Built in WhatsApp icon drawn inline, plus an optional custom icon URL
- Color controls for launcher, icon, panel header, panel background and text
- Widget styles locked against theme button, link and svg rules
- Editable panel title and optional intro line
- Launcher sizes: small, medium, large
- Position: bottom right or bottom left
- Panel opens on hover, on focus or on tap, and closes on an outside click or the Escape key
- Auto open after a delay, once per visitor session
- Per device visibility: desktop, tablet, mobile
- Exclusions: by post, page or custom post type ID, by whole post type, and for the front page or the blog page
- Live preview in the settings screen
- Shortcode with attribute overrides
- Vanilla JavaScript on the front end, no jQuery
- Multisite aware, GPL licensed, translation ready

## Requirements

- WordPress 5.6 or newer
- PHP 7.4 or newer

## Installation

1. Upload the folder to wp-content/plugins, or upload the zip through Plugins, Add New, Upload Plugin.
2. Activate the plugin.
3. Go to Settings, Floating WhatsApp.
4. Add one row per number and save.

The widget is loaded on the whole site by default.

## Settings

### Buttons

Each row holds three values.

- Label is the visible button text. Leave it empty to get a numbered placeholder.
- Number or URL accepts a phone number in international format, for example 6281234567890, or a complete link such as https://wa.me/6281234567890. Leave it empty and the button keeps the # fallback.
- Prefilled message is appended to the chat link, so the visitor lands in WhatsApp with the message already typed.

Rows can be reordered by dragging the handle on the left.

### Appearance

- Button color drives the launcher, the panel border and the button hover state. Default #0bb3b9.
- Icon color draws the launcher glyph and the icon in the panel header. Default #ffffff.
- Header background defaults to the button color when left empty.
- Panel background and panel text color control the panel body.
- Title is the text in the panel header, for example Chat with us.
- Intro text is an optional line above the buttons.
- Launcher size and position.
- Custom icon URL replaces the built in WhatsApp icon.

### Behavior

- Load the floating widget on the whole site. Turn it off to use the shortcode on selected pages only.
- Open chats in a new tab.
- Auto open, in seconds. Zero keeps the panel closed until a visitor interacts. The panel opens once per session.
- Hide on desktop, tablet or mobile.

### Exclusions

The widget is shown on every page by default. Anything below is skipped on top of that.

- Exclude by ID takes a comma separated list of post, page or custom post type IDs, for example 12, 45, 301. The ID appears in the editor URL, for example post=301.
- Exclude post types skips every single view of the checked post types.
- Exclude special pages skips the front page and the blog page.

Manual placement wins. A page that carries the shortcode keeps its widget even when its ID or its post type is excluded.

## Shortcode

```
[multiple_floating_wa]
```

When the site wide option is disabled, the widget renders only where the shortcode is placed. The legacy shortcode `[show_wa]` behaves the same way.

Attributes override the saved values for that instance.

```
[multiple_floating_wa position="bottom-left" size="large" title="Need help?" button_color="#128c7e" icon_color="#ffffff" header_color="#075e54" panel_color="#ffffff" text_color="#222222" intro="Pick the closest branch." auto_open="5"]
```

Accepted attributes: position, size, title, intro, button_color, icon_color, header_color, panel_color, text_color, auto_open.

## Layout reference

The default front end matches a 260px panel in CSS pixels.

- Launcher: 54px circle, 20px from the bottom and the right edge, 34px glyph
- Panel: 260px wide, positioned 34px above the launcher
- Header: 10px padding, 30px icon, 14px white title
- Body: 16px padding, 1px border in the button color, 10px radius
- Buttons: 46px tall, 10px padding, 16px label text, 1px border, 10px radius, 16px between buttons
- Hover on a button fills it with the button color and switches the label to white

## Theme compatibility

Themes often style every button, link and svg on the page, which turns a launcher into a bordered rectangle in the theme accent color. Every declaration that decides how the widget looks is scoped to the widget classes and marked important, so the launcher keeps its circle shape, background color, icon color and borderless edge whatever the theme does with plain buttons and links. The panel and its buttons are locked the same way.

## Developer hooks

```php
// Change the settings array right before it is used.
add_filter( 'mfw_settings', function ( $settings ) {
	$settings['button_color'] = '#075e54';

	return $settings;
} );

// Change the generated list of buttons.
add_filter( 'mfw_items', function ( $items, $settings ) {
	$items[] = array(
		'label' => 'Support',
		'href'  => 'https://wa.me/6281234567890',
	);

	return $items;
}, 10, 2 );

// Force the assets to load, useful when the markup is printed by custom code.
add_filter( 'mfw_needs_assets', '__return_true' );

// Hide the widget on a request the settings screen does not cover.
add_filter( 'mfw_is_hidden', function ( $hidden, $settings ) {
	return is_404() ? true : $hidden;
}, 10, 2 );
```

Front end helper for custom markup:

```php
echo mfw()->frontend->render( MFW_Plugin::settings() );
```

## File structure

```
multiple-floating-wa/
├── multiple-floating-wa.php
├── uninstall.php
├── readme.txt
├── README.md
├── includes/
│   ├── class-mfw-admin.php
│   ├── class-mfw-frontend.php
│   └── class-mfw-icon.php
└── assets/
    ├── css/admin.css
    ├── css/frontend.css
    ├── js/admin.js
    └── js/frontend.js
```

## Credits

- WhatsApp icon from Bootstrap Icons (MIT), inlined as SVG so no extra request is made.

## Changelog

### 1.0.0

- First release.

## License

GPL-2.0-or-later. See https://www.gnu.org/licenses/gpl-2.0.html.

WhatsApp is a trademark of Meta Platforms, Inc. This plugin is not affiliated with, endorsed by or sponsored by Meta Platforms, Inc.
