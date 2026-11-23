<?php
/**
 * Front-end (and editor preview) output for the Social Share block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content (unused).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sss_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$sss_url     = $sss_post_id ? get_permalink( $sss_post_id ) : home_url( '/' );
$sss_title   = $sss_post_id ? wp_strip_all_tags( get_the_title( $sss_post_id ) ) : get_bloginfo( 'name' );

// Blocks set to "Use the style from the settings page" (the default) take
// their look from Settings → Social Share.
if ( ! isset( $attributes['useGlobal'] ) || $attributes['useGlobal'] ) {
	$attributes = array_merge( $attributes, sss_global_style() );
}

$sss_all      = sss_networks();
$sss_selected = isset( $attributes['networks'] ) && is_array( $attributes['networks'] ) ? $attributes['networks'] : array();
$sss_selected = array_values( array_filter( $sss_selected, function ( $key ) use ( $sss_all ) {
	return isset( $sss_all[ $key ] );
} ) );

if ( empty( $sss_selected ) ) {
	return;
}

$sss_layout  = in_array( $attributes['layout'] ?? '', array( 'row', 'column' ), true ) ? $attributes['layout'] : 'row';
$sss_justify = in_array( $attributes['justify'] ?? '', array( 'left', 'center', 'right' ), true ) ? $attributes['justify'] : 'left';
$sss_size    = in_array( $attributes['size'] ?? '', array( 'small', 'medium', 'large' ), true ) ? $attributes['size'] : 'medium';
$sss_shape   = in_array( $attributes['shape'] ?? '', array( 'rounded', 'circle', 'square' ), true ) ? $attributes['shape'] : 'rounded';
$sss_labels  = ! empty( $attributes['showLabels'] );
$sss_mode    = ( $attributes['colorMode'] ?? 'brand' ) === 'custom' ? 'custom' : 'brand';

$sss_style = '';
if ( 'custom' === $sss_mode ) {
	$sss_bg    = sanitize_hex_color( $attributes['customBackground'] ?? '' );
	$sss_fg    = sanitize_hex_color( $attributes['customText'] ?? '' );
	$sss_style = ( $sss_bg ? '--sss-bg:' . $sss_bg . ';' : '' ) . ( $sss_fg ? '--sss-fg:' . $sss_fg . ';' : '' );
}

$sss_wrapper = get_block_wrapper_attributes(
	array(
		'class' => implode(
			' ',
			array(
				'sss',
				'sss--' . $sss_layout,
				'sss--justify-' . $sss_justify,
				'sss--' . $sss_size,
				'sss--' . $sss_shape,
				'sss--' . $sss_mode,
				$sss_labels ? 'sss--labels' : 'sss--icons',
			)
		),
		'style' => $sss_style,
		'data-url'   => $sss_url,
		'data-title' => $sss_title,
	)
);

$sss_enc_url   = rawurlencode( $sss_url );
$sss_enc_title = rawurlencode( $sss_title );
?>
<div <?php echo $sss_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by get_block_wrapper_attributes(). ?>>
	<?php if ( ! empty( $attributes['heading'] ) ) : ?>
		<p class="sss__heading"><?php echo esc_html( $attributes['heading'] ); ?></p>
	<?php endif; ?>

	<ul class="sss__list">
		<?php
		foreach ( $sss_selected as $sss_key ) :
			$sss_net  = $sss_all[ $sss_key ];
			if ( 'copy' === $sss_key ) {
				$sss_aria = $sss_net['label'];
			} elseif ( 'email' === $sss_key ) {
				$sss_aria = __( 'Share by email', 'simple-social-share' );
			} else {
				/* translators: %s: network name, e.g. LinkedIn. */
				$sss_aria = sprintf( __( 'Share on %s', 'simple-social-share' ), $sss_net['label'] );
			}
			$sss_attr = sprintf(
				'class="sss__btn sss__btn--%1$s" style="--sss-brand:%2$s" aria-label="%3$s"',
				esc_attr( $sss_key ),
				esc_attr( $sss_net['color'] ),
				esc_attr( $sss_aria )
			);
			$sss_svg   = sss_icon( $sss_net['icon'] );
			// If the icon is unavailable, always show the name so the button is never blank.
			$sss_inner = $sss_svg . '<span class="sss__label' . ( '' === $sss_svg ? ' sss__label--always' : '' ) . '">' . esc_html( $sss_net['label'] ) . '</span>';
			?>
			<li>
				<?php if ( $sss_net['url'] ) : ?>
					<?php
					$sss_href = str_replace( array( '{url}', '{title}' ), array( $sss_enc_url, $sss_enc_title ), $sss_net['url'] );
					$sss_ext  = 0 !== strpos( $sss_href, 'mailto:' );
					?>
					<a href="<?php echo esc_url( $sss_href, array( 'https', 'mailto' ) ); ?>" <?php echo $sss_attr; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?><?php echo $sss_ext ? ' target="_blank" rel="noopener noreferrer nofollow"' : ''; ?>><?php echo $sss_inner; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></a>
				<?php else : ?>
					<button type="button" data-sss-action="<?php echo esc_attr( $sss_key ); ?>" <?php echo $sss_attr; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>><?php echo $sss_inner; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></button>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="sss__notice" role="status" aria-live="polite" hidden></p>
</div>
