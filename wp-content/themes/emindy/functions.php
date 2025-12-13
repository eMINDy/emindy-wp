<?php
/**
 * eMINDy child theme functions.
 *
 * Keep the theme lightweight; platform logic lives in the emindy-core plugin.
 *
 * @package emindy
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme constants.
 */
if ( ! defined( 'EMINDY_THEME_SLUG' ) ) {
	define( 'EMINDY_THEME_SLUG', 'emindy' );
}

if ( ! defined( 'EMINDY_THEME_VERSION' ) ) {
	$emindy_theme = wp_get_theme( get_stylesheet() );
	$version      = (string) $emindy_theme->get( 'Version' );
	define( 'EMINDY_THEME_VERSION', $version ? $version : '1.0.0' );
}

/**
 * Get a stable version string for an asset (filemtime when available).
 */
if ( ! function_exists( 'emindy_asset_version' ) ) {
	function emindy_asset_version( string $absolute_path, string $fallback = EMINDY_THEME_VERSION ): string {
		return file_exists( $absolute_path ) ? (string) filemtime( $absolute_path ) : $fallback;
	}
}

/**
 * Print an inline script tag safely.
 */
if ( ! function_exists( 'emindy_print_inline_script_tag' ) ) {
	function emindy_print_inline_script_tag( string $js, string $id = '' ): void {
		$attrs = [];
		if ( '' !== $id ) {
			$attrs['id'] = $id;
		}

		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag( $js, $attrs );
			return;
		}

		$id_attr = ( '' !== $id ) ? ' id="' . esc_attr( $id ) . '"' : '';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<script' . $id_attr . '>' . $js . '</script>' . "\n";
	}
}

/**
 * Theme setup.
 */
add_action( 'after_setup_theme', 'emindy_theme_setup' );
if ( ! function_exists( 'emindy_theme_setup' ) ) {
	function emindy_theme_setup(): void {
		load_child_theme_textdomain( 'emindy', get_stylesheet_directory() . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );

		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
			]
		);

		// Optional (used by fallback shortcode only). Safe to keep even in block themes.
		register_nav_menus(
			[
				'primary' => __( 'Primary Navigation', 'emindy' ),
				'footer'  => __( 'Footer Navigation', 'emindy' ),
				'social'  => __( 'Social Links', 'emindy' ),
			]
		);
	}
}

/**
 * Enqueue theme stylesheet (front + editor).
 */
add_action( 'enqueue_block_assets', 'emindy_enqueue_theme_styles', 20 );
if ( ! function_exists( 'emindy_enqueue_theme_styles' ) ) {
	function emindy_enqueue_theme_styles(): void {
		$child_style_path = get_stylesheet_directory() . '/style.css';
		$child_style_ver  = emindy_asset_version( $child_style_path );
		$deps             = [];

		/**
		 * Opt-in parent stylesheet enqueue (disabled by default for performance).
		 *
		 * If you ever need the parent theme's style.css, set this filter to true.
		 */
		$enqueue_parent = (bool) apply_filters( 'emindy_enqueue_parent_stylesheet', false );
		if ( $enqueue_parent && get_template_directory() !== get_stylesheet_directory() ) {
			$parent_path = get_template_directory() . '/style.css';
			if ( file_exists( $parent_path ) ) {
				wp_enqueue_style(
					'emindy-parent',
					get_template_directory_uri() . '/style.css',
					[],
					emindy_asset_version( $parent_path, (string) wp_get_theme( get_template() )->get( 'Version' ) )
				);
				$deps = [ 'emindy-parent' ];
			}
		}

		// NOTE: Block themes do not automatically enqueue style.css. Be explicit.
		wp_enqueue_style(
			'emindy-style',
			get_stylesheet_uri(),
			$deps,
			$child_style_ver
		);

		wp_style_add_data( 'emindy-style', 'rtl', 'replace' );
	}
}

/**
 * Keep emindy-core plugin fonts disabled (fonts are defined in theme.json).
 */
add_filter( 'emindy_core_enqueue_fonts', '__return_false' );

/**
 * Accessibility: Skip link.
 */
add_action( 'wp_body_open', 'emindy_output_skip_link', 1 );
if ( ! function_exists( 'emindy_output_skip_link' ) ) {
	function emindy_output_skip_link(): void {
		printf(
			'<a class="skip-link" href="#main-content">%s</a>',
			esc_html__( 'Skip to content', 'emindy' )
		);
	}
}

/**
 * Block style variations.
 */
add_action( 'init', 'emindy_register_block_styles' );
if ( ! function_exists( 'emindy_register_block_styles' ) ) {
	function emindy_register_block_styles(): void {
		if ( ! function_exists( 'register_block_style' ) ) {
			return;
		}

		// Canonical card style (matches theme.json CSS selector: .is-style-emindy-card).
		register_block_style(
			'core/group',
			[
				'name'  => 'emindy-card',
				'label' => __( 'eMINDy Card', 'emindy' ),
			]
		);

		// Legacy alias kept for backwards compatibility (older patterns/templates).
		register_block_style(
			'core/group',
			[
				'name'  => 'em-card',
				'label' => __( 'eMINDy Card (Legacy)', 'emindy' ),
			]
		);

		register_block_style(
			'core/button',
			[
				'name'  => 'em-soft',
				'label' => __( 'Soft', 'emindy' ),
			]
		);
	}
}

/**
 * Early bootstrap script: apply saved theme choice before styles render.
 */
add_action( 'wp_head', 'emindy_head_theme_bootstrap', 0 );
if ( ! function_exists( 'emindy_head_theme_bootstrap' ) ) {
	function emindy_head_theme_bootstrap(): void {
		if ( (bool) apply_filters( 'emindy_disable_theme_toggle', false ) ) {
			return;
		}

		$js = "(function(){try{var t=localStorage.getItem('emindy_theme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-em-theme',t);}}catch(e){}})();";
		emindy_print_inline_script_tag( $js, 'emindy-theme-bootstrap' );
	}
}

/**
 * Theme JS (footer): dark/light toggle.
 */
add_action( 'wp_footer', 'emindy_print_theme_js', 20 );
if ( ! function_exists( 'emindy_print_theme_js' ) ) {
	function emindy_print_theme_js(): void {
		if ( (bool) apply_filters( 'emindy_disable_theme_toggle', false ) ) {
			return;
		}

		if ( wp_script_is( 'emindy-theme-toggle', 'enqueued' ) || wp_script_is( 'emindy-theme-toggle', 'done' ) ) {
			// A plugin/site layer has provided the toggle script; do not duplicate.
			return;
		}

		$js = emindy_get_theme_toggle_js();
		if ( '' === $js ) {
			return;
		}

		emindy_print_inline_script_tag( $js, 'emindy-theme-toggle-inline' );
	}
}

/**
 * Theme toggle JS.
 *
 * Supports BOTH attribute naming schemes:
 * - data-labelToDark / data-labelToLight (preferred)
 * - data-labelLight / data-labelDark (legacy, currently used in header.html)
 */
if ( ! function_exists( 'emindy_get_theme_toggle_js' ) ) {
	function emindy_get_theme_toggle_js(): string {
		return <<<JS
(function(){
	'use strict';

	var KEY = 'emindy_theme';
	var root = document.documentElement;

	function prefersDark(){
		try{ return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches; }
		catch(e){ return false; }
	}

	function getStored(){
		try{ return localStorage.getItem(KEY); }
		catch(e){ return null; }
	}

	function setStored(v){
		try{ if(v===null){ localStorage.removeItem(KEY); } else { localStorage.setItem(KEY, v); } }
		catch(e){}
	}

	function getExplicit(){
		var explicit = root.getAttribute('data-em-theme');
		if(explicit==='dark'||explicit==='light'){ return explicit; }
		var stored = getStored();
		if(stored==='dark'||stored==='light'){ return stored; }
		return null;
	}

	function getEffectiveMode(){
		var explicit = getExplicit();
		if(explicit){ return explicit; }
		return prefersDark() ? 'dark' : 'light';
	}

	function applyMode(mode){
		if(mode==='dark'){ root.setAttribute('data-em-theme','dark'); }
		else if(mode==='light'){ root.setAttribute('data-em-theme','light'); }
		else { root.removeAttribute('data-em-theme'); }
	}

	function readLabel(btn, attrPrimary, attrLegacy){
		if(!btn){ return ''; }
		return btn.getAttribute(attrPrimary) || btn.getAttribute(attrLegacy) || '';
	}

	function updateButton(btn){
		if(!btn){ return; }

		var mode = getEffectiveMode();
		var isDark = (mode==='dark');
		btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');

		// Preferred: data-labelToDark/data-labelToLight
		// Legacy:    data-labelLight/data-labelDark
		var labelToDark  = readLabel(btn, 'data-labelToDark',  'data-labelLight') || 'Switch to dark mode';
		var labelToLight = readLabel(btn, 'data-labelToLight', 'data-labelDark')  || 'Switch to light mode';
		btn.setAttribute('aria-label', isDark ? labelToLight : labelToDark);

		var icon = btn.querySelector('span[aria-hidden="true"]');
		if(icon){ icon.textContent = isDark ? '☀️' : '🌙'; }
	}

	function toggle(){
		var current = getEffectiveMode();
		var next = (current==='dark') ? 'light' : 'dark';
		applyMode(next);
		setStored(next);
		updateButton(document.getElementById('em-dark-mode-toggle'));
	}

	document.addEventListener('click', function(e){
		var el = e.target;
		if(!el){ return; }
		var btn = (el.id==='em-dark-mode-toggle') ? el : (el.closest ? el.closest('#em-dark-mode-toggle') : null);
		if(!btn){ return; }
		e.preventDefault();
		toggle();
	});

	document.addEventListener('DOMContentLoaded', function(){
		updateButton(document.getElementById('em-dark-mode-toggle'));
	});

	// Keep UI in sync with system changes when the user has no explicit choice.
	try{
		if(window.matchMedia){
			var mq = window.matchMedia('(prefers-color-scheme: dark)');
			var handler = function(){ if(getExplicit()){ return; } updateButton(document.getElementById('em-dark-mode-toggle')); };
			if(mq.addEventListener){ mq.addEventListener('change', handler); }
			else if(mq.addListener){ mq.addListener(handler); }
		}
	}catch(e){}
})();
JS;
	}
}

/**
 * Fallback shortcodes for templates (avoid raw shortcode output if plugin is disabled).
 */
add_action( 'init', 'emindy_register_fallback_shortcodes', 11 );
if ( ! function_exists( 'emindy_register_fallback_shortcodes' ) ) {
	function emindy_register_fallback_shortcodes(): void {
		if ( ! function_exists( 'add_shortcode' ) ) {
			return;
		}

		if ( function_exists( 'shortcode_exists' ) && ! shortcode_exists( 'em_lang_switcher' ) ) {
			add_shortcode( 'em_lang_switcher', 'emindy_shortcode_lang_switcher' );
		}

		if ( function_exists( 'shortcode_exists' ) && ! shortcode_exists( 'em_social_icons' ) ) {
			add_shortcode( 'em_social_icons', 'emindy_shortcode_social_icons' );
		}
	}
}

/**
 * [em_lang_switcher] fallback (Polylang).
 */
if ( ! function_exists( 'emindy_shortcode_lang_switcher' ) ) {
	function emindy_shortcode_lang_switcher( array $atts = [] ): string {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}

		$atts = shortcode_atts(
			[
				'dropdown'   => '0',
				'show_names' => '1',
				'show_flags' => '0',
			],
			$atts,
			'em_lang_switcher'
		);

		$args = [
			'echo'          => 0,
			'dropdown'      => (int) ( '1' === (string) $atts['dropdown'] ),
			'show_names'    => (int) ( '1' === (string) $atts['show_names'] ),
			'show_flags'    => (int) ( '1' === (string) $atts['show_flags'] ),
			'hide_if_empty' => 0,
		];

		$html = pll_the_languages( $args );
		if ( empty( $html ) || ! is_string( $html ) ) {
			return '';
		}

		return '<div class="em-lang-switcher" aria-label="' . esc_attr__( 'Language switcher', 'emindy' ) . '">' . $html . '</div>';
	}
}

/**
 * [em_social_icons] fallback.
 */
if ( ! function_exists( 'emindy_shortcode_social_icons' ) ) {
	function emindy_shortcode_social_icons(): string {
		if ( ! has_nav_menu( 'social' ) ) {
			return '';
		}

		return (string) wp_nav_menu(
			[
				'theme_location'  => 'social',
				'container'       => 'nav',
				'container_class' => 'em-social-icons',
				'container_id'    => 'em-social-icons',
				'menu_class'      => 'em-social-icons__list',
				'depth'           => 1,
				'echo'            => false,
				'fallback_cb'     => '__return_empty_string',
			]
		);
	}
}
