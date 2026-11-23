<?php
/**
 * Plugin Name:       Simple Social Share
 * Description:       A "Social Share" block with buttons readers can click to share your posts on LinkedIn, Instagram, Facebook, X, WhatsApp, email, or by copying the link. Can also add the buttons to every post automatically.
 * Version:           1.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            You
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-social-share
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SSS_VERSION', '1.1.0' );
define( 'SSS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Every network the block knows about.
 *
 * - url:   share link pattern. {url}, {title} are replaced (already URL-encoded).
 *          null means the button is handled in JavaScript (view.js).
 * - icon:  name of the icon in WordPress core's Social Icons set.
 * - color: brand background colour used in "Brand colours" mode.
 *
 * To add a network, add an entry here and its key to the block's "networks" list.
 */
function sss_networks() {
	$networks = array(
		'linkedin'  => array(
			'label' => __( 'LinkedIn', 'simple-social-share' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
			'icon'  => 'linkedin',
			'color' => '#0a66c2',
		),
		'instagram' => array(
			'label' => __( 'Instagram', 'simple-social-share' ),
			'url'   => null, // Instagram has no web share link: phones get the share menu, computers copy the link.
			'icon'  => 'instagram',
			'color' => '#d62976',
		),
		'facebook'  => array(
			'label' => __( 'Facebook', 'simple-social-share' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u={url}',
			'icon'  => 'facebook',
			'color' => '#1877f2',
		),
		'x'         => array(
			'label' => __( 'X', 'simple-social-share' ),
			'url'   => 'https://x.com/intent/tweet?url={url}&text={title}',
			'icon'  => 'x',
			'color' => '#000000',
		),
		'whatsapp'  => array(
			'label' => __( 'WhatsApp', 'simple-social-share' ),
			'url'   => 'https://wa.me/?text={title}%20{url}',
			'icon'  => 'whatsapp',
			'color' => '#25d366',
		),
		'telegram'  => array(
			'label' => __( 'Telegram', 'simple-social-share' ),
			'url'   => 'https://t.me/share/url?url={url}&text={title}',
			'icon'  => 'telegram',
			'color' => '#26a5e4',
		),
		'email'     => array(
			'label' => __( 'Email', 'simple-social-share' ),
			'url'   => 'mailto:?subject={title}&body={url}',
			'icon'  => 'mail',
			'color' => '#4b5563',
		),
		'copy'      => array(
			'label' => __( 'Copy link', 'simple-social-share' ),
			'url'   => null,
			'icon'  => 'chain',
			'color' => '#374151',
		),
	);

	/** Lets themes or other plugins add, remove or change networks. */
	return apply_filters( 'sss_networks', $networks );
}

/**
 * SVG icon for a network, taken from WordPress core's Social Icons block so
 * the icons stay up to date with WordPress itself.
 */
function sss_icon( $icon_name ) {
	if ( ! function_exists( 'block_core_social_link_get_icon' ) ) {
		return '';
	}
	$svg = block_core_social_link_get_icon( $icon_name );

	return wp_kses(
		$svg,
		array(
			'svg'  => array(
				'width'       => true,
				'height'      => true,
				'viewbox'     => true,
				'version'     => true,
				'xmlns'       => true,
				'aria-hidden' => true,
				'focusable'   => true,
				'role'        => true,
			),
			'path' => array( 'd' => true, 'fill-rule' => true, 'clip-rule' => true ),
			'g'    => array(),
		)
	);
}

/** Register the block from block.json. */
function sss_register_block() {
	register_block_type( SSS_DIR . 'block' );
}
add_action( 'init', 'sss_register_block' );

/* -------------------------------------------------------------------------
 * Site-wide style (Settings → Social Share)
 * ---------------------------------------------------------------------- */

/** Saved options, with defaults filled in. */
function sss_get_options() {
	return wp_parse_args(
		get_option( 'sss_options', array() ),
		array(
			// Style.
			'heading'           => __( 'Share this post', 'simple-social-share' ),
			'networks'          => array( 'linkedin', 'instagram', 'facebook', 'x', 'whatsapp', 'email', 'copy' ),
			'layout'            => 'row',
			'justify'           => 'left',
			'size'              => 'medium',
			'shape'             => 'rounded',
			'show_labels'       => 0,
			'color_mode'        => 'brand',
			'custom_background' => '#1e40af',
			'custom_text'       => '#ffffff',
			// Automatic buttons.
			'auto_insert'       => 0,
			'position'          => 'after',
			'post_types'        => array( 'post' ),
		)
	);
}

/** The settings-page style, in the same shape as the block's attributes. */
function sss_global_style() {
	$o = sss_get_options();
	return array(
		'heading'          => (string) $o['heading'],
		'networks'         => array_values( (array) $o['networks'] ),
		'layout'           => $o['layout'],
		'justify'          => $o['justify'],
		'size'             => $o['size'],
		'shape'            => $o['shape'],
		'showLabels'       => (bool) $o['show_labels'],
		'colorMode'        => $o['color_mode'],
		'customBackground' => $o['custom_background'],
		'customText'       => $o['custom_text'],
	);
}

/**
 * Pass the network list (names, colours, icons) and the site-wide style to
 * the editor, so it can draw its preview without asking the server.
 */
function sss_editor_data() {
	$list = array();
	foreach ( sss_networks() as $key => $network ) {
		$list[] = array(
			'key'   => $key,
			'label' => $network['label'],
			'color' => $network['color'],
			'icon'  => sss_icon( $network['icon'] ),
		);
	}
	$handle = function_exists( 'generate_block_asset_handle' )
		? generate_block_asset_handle( 'simple-social-share/social-share', 'editorScript' )
		: 'simple-social-share-social-share-editor-script';

	$data = array(
		'networks'    => $list,
		'global'      => sss_global_style(),
		'settingsUrl' => admin_url( 'options-general.php?page=simple-social-share' ),
	);
	wp_add_inline_script( $handle, 'window.sssData = ' . wp_json_encode( $data ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'sss_editor_data' );

function sss_register_settings() {
	register_setting(
		'sss_settings',
		'sss_options',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'sss_sanitize_options',
		)
	);
}
add_action( 'admin_init', 'sss_register_settings' );

function sss_pick( $value, $allowed, $fallback ) {
	return in_array( $value, $allowed, true ) ? $value : $fallback;
}

function sss_sanitize_options( $input ) {
	$input = is_array( $input ) ? $input : array();

	$networks = isset( $input['networks'] ) ? array_map( 'sanitize_key', (array) $input['networks'] ) : array();
	$networks = array_values( array_unique( array_intersect( $networks, array_keys( sss_networks() ) ) ) );

	$post_types = isset( $input['post_types'] ) ? (array) $input['post_types'] : array();
	$post_types = array_values( array_intersect( $post_types, get_post_types( array( 'public' => true ) ) ) );

	return array(
		'heading'           => isset( $input['heading'] ) ? sanitize_text_field( $input['heading'] ) : '',
		'networks'          => $networks,
		'layout'            => sss_pick( $input['layout'] ?? '', array( 'row', 'column' ), 'row' ),
		'justify'           => sss_pick( $input['justify'] ?? '', array( 'left', 'center', 'right' ), 'left' ),
		'size'              => sss_pick( $input['size'] ?? '', array( 'small', 'medium', 'large' ), 'medium' ),
		'shape'             => sss_pick( $input['shape'] ?? '', array( 'rounded', 'circle', 'square' ), 'rounded' ),
		'show_labels'       => empty( $input['show_labels'] ) ? 0 : 1,
		'color_mode'        => sss_pick( $input['color_mode'] ?? '', array( 'brand', 'custom' ), 'brand' ),
		'custom_background' => sanitize_hex_color( $input['custom_background'] ?? '' ) ?: '#1e40af',
		'custom_text'       => sanitize_hex_color( $input['custom_text'] ?? '' ) ?: '#ffffff',
		'auto_insert'       => empty( $input['auto_insert'] ) ? 0 : 1,
		'position'          => sss_pick( $input['position'] ?? '', array( 'before', 'after', 'both' ), 'after' ),
		'post_types'        => $post_types,
	);
}

function sss_add_settings_page() {
	add_options_page(
		__( 'Social Share', 'simple-social-share' ),
		__( 'Social Share', 'simple-social-share' ),
		'manage_options',
		'simple-social-share',
		'sss_render_settings_page'
	);
}
add_action( 'admin_menu', 'sss_add_settings_page' );

/** Load the buttons' CSS and the live-preview script on the settings page only. */
function sss_settings_assets( $hook ) {
	if ( 'settings_page_simple-social-share' !== $hook ) {
		return;
	}
	$style_handle = function_exists( 'generate_block_asset_handle' )
		? generate_block_asset_handle( 'simple-social-share/social-share', 'style' )
		: 'simple-social-share-social-share-style';
	wp_enqueue_style( $style_handle );
	wp_enqueue_style( 'sss-admin', plugins_url( 'admin/admin.css', __FILE__ ), array(), SSS_VERSION );
	wp_enqueue_script( 'sss-admin', plugins_url( 'admin/admin.js', __FILE__ ), array(), SSS_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'sss_settings_assets' );

/** Small helper for the <select> fields. */
function sss_select( $name, $value, $choices ) {
	printf( '<select name="sss_options[%1$s]" id="sss-%1$s" data-sss-field="%1$s">', esc_attr( $name ) );
	foreach ( $choices as $key => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
	}
	echo '</select>';
}

function sss_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o          = sss_get_options();
	$all        = sss_networks();
	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	unset( $post_types['attachment'] );

	// Rows: ticked networks first (in the saved order), then the rest.
	$order = array_merge( $o['networks'], array_diff( array_keys( $all ), $o['networks'] ) );
	$order = array_values( array_intersect( $order, array_keys( $all ) ) );

	// Preview: draw every network in row order; the script hides unticked ones.
	$preview_style             = sss_global_style();
	$preview_style['networks'] = $order;
	$preview_style['useGlobal'] = false;
	?>
	<div class="wrap sss-admin">
		<h1><?php esc_html_e( 'Social Share', 'simple-social-share' ); ?></h1>
		<p><?php esc_html_e( 'This style is used by the automatic buttons and by every Social Share block that has "Use the style from the settings page" turned on (the default).', 'simple-social-share' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'sss_settings' ); ?>

			<div class="sss-admin__preview-box">
				<h2><?php esc_html_e( 'Preview', 'simple-social-share' ); ?></h2>
				<div id="sss-preview">
					<?php echo render_block( array( 'blockName' => 'simple-social-share/social-share', 'attrs' => $preview_style, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in render.php. ?>
				</div>
			</div>

			<h2><?php esc_html_e( 'Networks', 'simple-social-share' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Tick the networks to show. Use the arrows to change the order.', 'simple-social-share' ); ?></p>
			<ul id="sss-network-list" class="sss-admin__networks">
				<?php foreach ( $order as $key ) : ?>
					<li data-key="<?php echo esc_attr( $key ); ?>">
						<label>
							<input type="checkbox" name="sss_options[networks][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $o['networks'], true ) ); ?> />
							<?php echo esc_html( $all[ $key ]['label'] ); ?>
						</label>
						<span class="sss-admin__arrows">
							<button type="button" class="button button-small" data-move="-1" aria-label="<?php esc_attr_e( 'Move up', 'simple-social-share' ); ?>">↑</button>
							<button type="button" class="button button-small" data-move="1" aria-label="<?php esc_attr_e( 'Move down', 'simple-social-share' ); ?>">↓</button>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>

			<h2><?php esc_html_e( 'Look', 'simple-social-share' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="sss-heading"><?php esc_html_e( 'Heading above buttons', 'simple-social-share' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="sss-heading" name="sss_options[heading]" data-sss-field="heading" value="<?php echo esc_attr( $o['heading'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Leave empty for no heading.', 'simple-social-share' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Network names', 'simple-social-share' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="sss_options[show_labels]" value="1" data-sss-field="show_labels" <?php checked( $o['show_labels'], 1 ); ?> />
							<?php esc_html_e( 'Show network names next to icons', 'simple-social-share' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sss-layout"><?php esc_html_e( 'Direction', 'simple-social-share' ); ?></label></th>
					<td><?php sss_select( 'layout', $o['layout'], array( 'row' => __( 'In a row', 'simple-social-share' ), 'column' => __( 'Stacked', 'simple-social-share' ) ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="sss-justify"><?php esc_html_e( 'Alignment', 'simple-social-share' ); ?></label></th>
					<td><?php sss_select( 'justify', $o['justify'], array( 'left' => __( 'Left', 'simple-social-share' ), 'center' => __( 'Centre', 'simple-social-share' ), 'right' => __( 'Right', 'simple-social-share' ) ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="sss-size"><?php esc_html_e( 'Size', 'simple-social-share' ); ?></label></th>
					<td><?php sss_select( 'size', $o['size'], array( 'small' => __( 'Small', 'simple-social-share' ), 'medium' => __( 'Medium', 'simple-social-share' ), 'large' => __( 'Large', 'simple-social-share' ) ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="sss-shape"><?php esc_html_e( 'Shape', 'simple-social-share' ); ?></label></th>
					<td><?php sss_select( 'shape', $o['shape'], array( 'rounded' => __( 'Rounded', 'simple-social-share' ), 'circle' => __( 'Circle', 'simple-social-share' ), 'square' => __( 'Square', 'simple-social-share' ) ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="sss-color_mode"><?php esc_html_e( 'Colours', 'simple-social-share' ); ?></label></th>
					<td>
						<?php sss_select( 'color_mode', $o['color_mode'], array( 'brand' => __( 'Each network’s own colour', 'simple-social-share' ), 'custom' => __( 'My own colours', 'simple-social-share' ) ) ); ?>
						<div id="sss-custom-colors" class="sss-admin__colors" <?php echo 'custom' === $o['color_mode'] ? '' : 'hidden'; ?>>
							<label>
								<input type="color" name="sss_options[custom_background]" data-sss-field="custom_background" value="<?php echo esc_attr( $o['custom_background'] ); ?>" />
								<?php esc_html_e( 'Button colour', 'simple-social-share' ); ?>
							</label>
							<label>
								<input type="color" name="sss_options[custom_text]" data-sss-field="custom_text" value="<?php echo esc_attr( $o['custom_text'] ); ?>" />
								<?php esc_html_e( 'Icon and text colour', 'simple-social-share' ); ?>
							</label>
						</div>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Automatic buttons', 'simple-social-share' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Turn on', 'simple-social-share' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="sss_options[auto_insert]" value="1" <?php checked( $o['auto_insert'], 1 ); ?> />
							<?php esc_html_e( 'Add share buttons to every post automatically', 'simple-social-share' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Skipped on posts where you have already added the Social Share block yourself.', 'simple-social-share' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sss-position"><?php esc_html_e( 'Position', 'simple-social-share' ); ?></label></th>
					<td><?php sss_select( 'position', $o['position'], array( 'after' => __( 'After the post', 'simple-social-share' ), 'before' => __( 'Before the post', 'simple-social-share' ), 'both' => __( 'Before and after', 'simple-social-share' ) ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show on', 'simple-social-share' ); ?></th>
					<td>
						<?php foreach ( $post_types as $type ) : ?>
							<label style="display:block;margin-bottom:4px">
								<input type="checkbox" name="sss_options[post_types][]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, (array) $o['post_types'], true ) ); ?> />
								<?php echo esc_html( $type->labels->name ); ?>
							</label>
						<?php endforeach; ?>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/** Settings link on the Plugins screen. */
function sss_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=simple-social-share' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'simple-social-share' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'sss_action_links' );

/* -------------------------------------------------------------------------
 * Automatic buttons
 * ---------------------------------------------------------------------- */

function sss_auto_insert( $content ) {
	$options = sss_get_options();

	if ( empty( $options['auto_insert'] )
		|| empty( $options['post_types'] )
		|| ! is_singular( $options['post_types'] )
		|| ! in_the_loop()
		|| ! is_main_query()
		|| has_block( 'simple-social-share/social-share' )
	) {
		return $content;
	}

	$buttons = render_block(
		array(
			'blockName'    => 'simple-social-share/social-share',
			'attrs'        => array(), // Uses the settings-page style.
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		)
	);

	switch ( $options['position'] ) {
		case 'before':
			return $buttons . $content;
		case 'both':
			return $buttons . $content . $buttons;
		default:
			return $content . $buttons;
	}
}
add_filter( 'the_content', 'sss_auto_insert', 20 );
