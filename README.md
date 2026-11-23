# Simple Social Share

A WordPress plugin that adds a **Social Share** block and share buttons readers can click to share your posts on LinkedIn, Instagram, Facebook, X, WhatsApp, Telegram, email, or by copying the link.

## Features

- **Social Share block**: add it anywhere from the block editor (`+` → search "Social Share").
- **Site-wide style** under **Settings → Social Share**, with a live preview:
  - networks and their order
  - heading, network names
  - row or stacked layout, alignment, size, shape
  - each network's own colours, or your own colours
- **Per-block override**: switch off "Use the style from the settings page" in a block's sidebar to give that block its own look.
- **Automatic buttons**: add them before or after every post (or page) without touching the editor.
- **Instagram button**: Instagram has no web share link, so on phones the button opens the phone's share menu (where Instagram is listed). On computers it copies the post link and asks the reader to paste it into Instagram.
- Icons come from WordPress core's Social Icons block. No build step: plain PHP, CSS and JavaScript.

## Install

1. Download this repo as a zip (**Code → Download ZIP**), or zip the folder yourself. The zip must contain the `simple-social-share` folder.
2. In WordPress: **Plugins → Add New → Upload Plugin**, choose the zip, then click **Install** and **Activate**.

Requires WordPress 6.3+ and PHP 7.4+.

## Add a network

Add an entry to `sss_networks()` in `simple-social-share.php`, or use the filter from your theme:

```php
add_filter( 'sss_networks', function ( $n ) {
    $n['reddit'] = array(
        'label' => 'Reddit',
        'url'   => 'https://www.reddit.com/submit?url={url}&title={title}',
        'icon'  => 'reddit', // Name from WordPress core's Social Icons.
        'color' => '#ff4500',
    );
    return $n;
} );
```

## Files

| Path | What it does |
| --- | --- |
| `simple-social-share.php` | Plugin setup, network list, settings page, automatic buttons |
| `block/block.json` | Block definition and attributes |
| `block/render.php` | Front-end HTML for the buttons |
| `block/index.js` | Block editor sidebar and preview |
| `block/view.js` | Copy-link and Instagram button behaviour |
| `block/style.css` | Button styles (CSS variables `--sss-icon`, `--sss-pad`, `--sss-radius`, `--sss-gap`) |
| `admin/` | Settings page live preview |

## License

GPL-2.0-or-later
