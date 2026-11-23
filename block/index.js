/**
 * Editor side of the Social Share block.
 * Plain JavaScript (no build step needed): edit this file and refresh the editor.
 */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var c = wp.components;

	var DATA = window.sssData || {};
	var GLOBAL = DATA.global || null;
	var SETTINGS_URL = DATA.settingsUrl || '';
	var ALL = DATA.networks || window.sssNetworks || [
		{ key: 'linkedin', label: 'LinkedIn' },
		{ key: 'instagram', label: 'Instagram' },
		{ key: 'facebook', label: 'Facebook' },
		{ key: 'x', label: 'X' },
		{ key: 'whatsapp', label: 'WhatsApp' },
		{ key: 'telegram', label: 'Telegram' },
		{ key: 'email', label: 'Email' },
		{ key: 'copy', label: 'Copy link' },
	];

	function networkFor( key ) {
		for ( var i = 0; i < ALL.length; i++ ) {
			if ( ALL[ i ].key === key ) {
				return ALL[ i ];
			}
		}
		return null;
	}

	function labelFor( key ) {
		var n = networkFor( key );
		return n ? n.label : key;
	}

	function NetworksPanel( props ) {
		var selected = ( props.value || [] ).filter( function ( key ) {
			return networkFor( key );
		} );
		var set = props.onChange;
		var unselected = ALL.filter( function ( n ) {
			return selected.indexOf( n.key ) === -1;
		} );

		function move( index, delta ) {
			var next = selected.slice();
			var target = index + delta;
			if ( target < 0 || target >= next.length ) {
				return;
			}
			var tmp = next[ index ];
			next[ index ] = next[ target ];
			next[ target ] = tmp;
			set( next );
		}

		var rows = selected.map( function ( key, i ) {
			return el(
				'div',
				{ key: key, style: { display: 'flex', alignItems: 'center', gap: '4px', marginBottom: '4px' } },
				el( c.CheckboxControl, {
					__nextHasNoMarginBottom: true,
					label: labelFor( key ),
					checked: true,
					onChange: function () {
						set( selected.filter( function ( k ) {
							return k !== key;
						} ) );
					},
				} ),
				el( 'span', { style: { flex: 1 } } ),
				el( c.Button, {
					icon: 'arrow-up-alt2',
					label: __( 'Move up', 'simple-social-share' ),
					size: 'small',
					disabled: i === 0,
					onClick: function () {
						move( i, -1 );
					},
				} ),
				el( c.Button, {
					icon: 'arrow-down-alt2',
					label: __( 'Move down', 'simple-social-share' ),
					size: 'small',
					disabled: i === selected.length - 1,
					onClick: function () {
						move( i, 1 );
					},
				} )
			);
		} );

		var offRows = unselected.map( function ( n ) {
			return el(
				'div',
				{ key: n.key, style: { marginBottom: '4px' } },
				el( c.CheckboxControl, {
					__nextHasNoMarginBottom: true,
					label: n.label,
					checked: false,
					onChange: function () {
						set( selected.concat( [ n.key ] ) );
					},
				} )
			);
		} );

		return el(
			'div',
			null,
			rows,
			offRows.length ? el( 'hr', null ) : null,
			offRows
		);
	}

	/**
	 * Editor preview. Mirrors the markup in render.php so block/style.css
	 * styles both the same way. Links are not clickable in the editor.
	 */
	function Preview( props ) {
		var a = props.attributes;
		var custom = a.colorMode === 'custom';
		var classes = [
			'sss',
			'sss--' + ( a.layout || 'row' ),
			'sss--justify-' + ( a.justify || 'left' ),
			'sss--' + ( a.size || 'medium' ),
			'sss--' + ( a.shape || 'rounded' ),
			'sss--' + ( custom ? 'custom' : 'brand' ),
			a.showLabels ? 'sss--labels' : 'sss--icons',
		].join( ' ' );
		var style = custom ? { '--sss-bg': a.customBackground, '--sss-fg': a.customText } : {};

		var items = ( a.networks || [] )
			.map( function ( key ) {
				return networkFor( key );
			} )
			.filter( Boolean )
			.map( function ( n ) {
				return el(
					'li',
					{ key: n.key },
					el(
						'span',
						{ className: 'sss__btn sss__btn--' + n.key, style: { '--sss-brand': n.color || '#374151' } },
						n.icon ? el( wp.element.RawHTML, null, n.icon ) : null,
						el( 'span', { className: 'sss__label' + ( n.icon ? '' : ' sss__label--always' ) }, n.label )
					)
				);
			} );

		if ( ! items.length ) {
			return el( 'p', { style: { opacity: 0.7 } }, __( 'Social Share: tick at least one network in the sidebar.', 'simple-social-share' ) );
		}

		return el(
			'div',
			{ className: classes, style: style },
			a.heading ? el( 'p', { className: 'sss__heading' }, a.heading ) : null,
			el( 'ul', { className: 'sss__list' }, items )
		);
	}

	registerBlockType( 'simple-social-share/social-share', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = be.useBlockProps();

			var useGlobal = a.useGlobal !== false && !! GLOBAL;

			// Panels for a block with its own style (only shown when useGlobal is off).
			var ownPanels = el(
				wp.element.Fragment,
				null,
				el(
					c.PanelBody,
					{ title: __( 'Networks', 'simple-social-share' ), initialOpen: true },
					el( 'p', { style: { color: '#757575' } }, __( 'Tick the networks to show. Use the arrows to change the order.', 'simple-social-share' ) ),
					el( NetworksPanel, {
						value: a.networks,
						onChange: function ( v ) {
							set( { networks: v } );
						},
					} )
				),
				el(
					c.PanelBody,
					{ title: __( 'Text', 'simple-social-share' ), initialOpen: false },
					el( c.TextControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Heading above buttons', 'simple-social-share' ),
						help: __( 'Leave empty for no heading.', 'simple-social-share' ),
						value: a.heading,
						onChange: function ( v ) {
							set( { heading: v } );
						},
					} ),
					el( 'div', { style: { height: '16px' } } ),
					el( c.ToggleControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Show network names next to icons', 'simple-social-share' ),
						checked: a.showLabels,
						onChange: function ( v ) {
							set( { showLabels: v } );
						},
					} )
				),
				el(
					c.PanelBody,
					{ title: __( 'Layout', 'simple-social-share' ), initialOpen: false },
					el( c.SelectControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Direction', 'simple-social-share' ),
						value: a.layout,
						options: [
							{ label: __( 'In a row', 'simple-social-share' ), value: 'row' },
							{ label: __( 'Stacked', 'simple-social-share' ), value: 'column' },
						],
						onChange: function ( v ) {
							set( { layout: v } );
						},
					} ),
					el( 'div', { style: { height: '16px' } } ),
					el( c.SelectControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Alignment', 'simple-social-share' ),
						value: a.justify,
						options: [
							{ label: __( 'Left', 'simple-social-share' ), value: 'left' },
							{ label: __( 'Centre', 'simple-social-share' ), value: 'center' },
							{ label: __( 'Right', 'simple-social-share' ), value: 'right' },
						],
						onChange: function ( v ) {
							set( { justify: v } );
						},
					} ),
					el( 'div', { style: { height: '16px' } } ),
					el( c.SelectControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Size', 'simple-social-share' ),
						value: a.size,
						options: [
							{ label: __( 'Small', 'simple-social-share' ), value: 'small' },
							{ label: __( 'Medium', 'simple-social-share' ), value: 'medium' },
							{ label: __( 'Large', 'simple-social-share' ), value: 'large' },
						],
						onChange: function ( v ) {
							set( { size: v } );
						},
					} ),
					el( 'div', { style: { height: '16px' } } ),
					el( c.SelectControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Shape', 'simple-social-share' ),
						value: a.shape,
						options: [
							{ label: __( 'Rounded', 'simple-social-share' ), value: 'rounded' },
							{ label: __( 'Circle', 'simple-social-share' ), value: 'circle' },
							{ label: __( 'Square', 'simple-social-share' ), value: 'square' },
						],
						onChange: function ( v ) {
							set( { shape: v } );
						},
					} )
				),
				el(
					c.PanelBody,
					{ title: __( 'Colours', 'simple-social-share' ), initialOpen: false },
					el( c.SelectControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Colour style', 'simple-social-share' ),
						value: a.colorMode,
						options: [
							{ label: __( 'Each network’s own colour', 'simple-social-share' ), value: 'brand' },
							{ label: __( 'My own colours', 'simple-social-share' ), value: 'custom' },
						],
						onChange: function ( v ) {
							set( { colorMode: v } );
						},
					} ),
					a.colorMode === 'custom'
						? el(
								'div',
								{ style: { marginTop: '16px' } },
								el( c.BaseControl, { __nextHasNoMarginBottom: true, label: __( 'Button colour', 'simple-social-share' ), id: 'sss-bg' },
									el( c.ColorPalette, {
										value: a.customBackground,
										clearable: false,
										onChange: function ( v ) {
											set( { customBackground: v || '#1e40af' } );
										},
									} )
								),
								el( c.BaseControl, { __nextHasNoMarginBottom: true, label: __( 'Icon and text colour', 'simple-social-share' ), id: 'sss-fg' },
									el( c.ColorPalette, {
										value: a.customText,
										clearable: false,
										onChange: function ( v ) {
											set( { customText: v || '#ffffff' } );
										},
									} )
								)
						  )
						: null
				)
			);

			var stylePanel = el(
				c.PanelBody,
				{ title: __( 'Style', 'simple-social-share' ), initialOpen: true },
				el( c.ToggleControl, {
					__nextHasNoMarginBottom: true,
					label: __( 'Use the style from the settings page', 'simple-social-share' ),
					help: useGlobal
						? __( 'This block looks like the rest of your site. Change the look in Settings → Social Share.', 'simple-social-share' )
						: __( 'This block has its own style, set below.', 'simple-social-share' ),
					checked: useGlobal,
					disabled: ! GLOBAL,
					onChange: function ( on ) {
						if ( on ) {
							set( { useGlobal: true } );
						} else {
							// Start from the current site style so nothing jumps.
							set( Object.assign( {}, GLOBAL, { useGlobal: false } ) );
						}
					},
				} ),
				useGlobal && SETTINGS_URL
					? el(
							c.Button,
							{ variant: 'secondary', href: SETTINGS_URL, target: '_blank', style: { marginTop: '12px' } },
							__( 'Open Settings → Social Share', 'simple-social-share' )
					  )
					: null
			);

			var sidebar = el( be.InspectorControls, null, stylePanel, useGlobal ? null : ownPanels );
			var shown = useGlobal ? Object.assign( {}, a, GLOBAL ) : a;

			return el(
				'div',
				blockProps,
				sidebar,
				el( Preview, { attributes: shown } )
			);
		},
		save: function () {
			return null; // Rendered by render.php.
		},
	} );
} )( window.wp );
