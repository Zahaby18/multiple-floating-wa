# Multiple Floating WhatsApp

A WordPress plugin that adds one floating WhatsApp launcher to your site. The launcher opens a panel listing every WhatsApp number you configured, each with its own label and prefilled message.

Built for sites that run several numbers, such as multiple branches, departments or teams, where a single floating button is not enough.

## Features

- Unlimited buttons in one repeater, each with a label, a number or full URL, and a prefilled message
- Fallback label so an unfinished row renders as Button 1, Button 2 and so on
- Fallback link so a row without a number points to the # anchor
- Built in WhatsApp icon drawn inline, plus an optional custom icon URL
- Color controls for launcher, panel header, panel background and text
- Editable panel title and optional intro line
- Launcher sizes: small, medium, large
- Position: bottom right or bottom left
- Panel opens on hover, on focus or on tap, and closes on an outside click or the Escape key
- Auto open after a delay, once per visitor session
- Per device visibility: desktop, tablet, mobile
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

## Shortcode

```
[multiple_floating_wa]
```

When the site wide option is disabled, the widget renders only where the shortcode is placed. The legacy shortcode `[show_wa]` behaves the same way.

Attributes override the saved values for that instance.

```
[multiple_floating_wa position="bottom-left" size="large" title="Need help?" button_color="#128c7e" header_color="#075e54" panel_color="#ffffff" text_color="#222222" intro="Pick the closest branch." auto_open="5"]
```

Accepted attributes: position, size, title, intro, button_color, header_color, panel_color, text_color, auto_open.

## Layout reference

The default front end matches a 260px panel in CSS pixels.

- Launcher: 54px circle, 20px from the bottom and the right edge, 34px glyph
- Panel: 260px wide, positioned 34px above the launcher
- Header: 10px padding, 30px icon, 14px white title
- Body: 16px padding, 1px border in the button color, 10px radius
- Buttons: 46px tall, 10px padding, 16px label text, 1px border, 10px radius, 16px between buttons
- Hover on a button fills it with the button color and switches the label to white

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
