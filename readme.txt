=== Simple Social Share ===
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later

A "Social Share" block with buttons readers click to share your posts on LinkedIn, Instagram, Facebook, X, WhatsApp, Telegram, email, or by copying the link.

== Installation ==

1. Plugins → Add New → Upload Plugin → choose simple-social-share.zip → Install → Activate.
2. In any post, click + and search "Social Share", or turn on automatic buttons in Settings → Social Share.

== How the Instagram button works ==

Instagram has no web link for sharing, so:
* On phones and tablets it opens the phone's share menu, where readers pick Instagram.
* On computers it copies the post link and tells the reader to paste it into Instagram.

== Customising ==

Site-wide: Settings → Social Share. Pick networks and their order, heading, labels, layout, size, shape and colours, with a live preview. Every block uses this style unless you switch off "Use the style from the settings page" in the block's sidebar.

One block only: click the block, switch off "Use the style from the settings page", then use the sidebar:
* Networks: tick which to show, and reorder them with the arrows.
* Text: heading above the buttons, show/hide network names.
* Layout: row or stacked, alignment, size, shape.
* Colours: each network's own colour, or your own colours.

Developers:
* block/style.css: all styling. Uses CSS variables (--sss-icon, --sss-pad, --sss-radius, --sss-gap).
* sss_networks() in simple-social-share.php: the list of networks. Add one there, or use the 'sss_networks' filter from your theme:

  add_filter( 'sss_networks', function ( $n ) {
      $n['reddit'] = array(
          'label' => 'Reddit',
          'url'   => 'https://www.reddit.com/submit?url={url}&title={title}',
          'icon'  => 'reddit',
          'color' => '#ff4500',
      );
      return $n;
  } );

Icons come from WordPress's own Social Icons block.
