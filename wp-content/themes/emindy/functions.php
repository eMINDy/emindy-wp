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
 * Print an inline script tag safely (WP 5.7+ helper when available).
 */
if ( ! function_exists( 'emindy_print_inline_script_tag' ) ) {
	function emindy_print_inline_script_tag( string $js, string $id = '' ): void {
		$attrs = array();
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
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
			)
		);

		register_nav_menus(
			array(
				'primary' => __( 'Primary Navigation', 'emindy' ),
				'footer'  => __( 'Footer Navigation', 'emindy' ),
				'social'  => __( 'Social Links', 'emindy' ),
			)
		);
	}
}

/**
 * Enqueue styles.
 */
add_action( 'wp_enqueue_scripts', 'emindy_enqueue_assets', 20 );
if ( ! function_exists( 'emindy_enqueue_assets' ) ) {
	function emindy_enqueue_assets(): void {
		$child_style_path = get_stylesheet_directory() . '/style.css';
		$child_style_ver  = file_exists( $child_style_path ) ? (string) filemtime( $child_style_path ) : EMINDY_THEME_VERSION;

		$parent_style_path = get_template_directory() . '/style.css';
		$parent_style_ver  = file_exists( $parent_style_path ) ? (string) filemtime( $parent_style_path ) : (string) wp_get_theme( get_template() )->get( 'Version' );

		// Parent stylesheet (safe for both classic + block parents).
		$deps = array();
		if ( get_template_directory() !== get_stylesheet_directory() && file_exists( $parent_style_path ) ) {
			wp_enqueue_style(
				'emindy-parent',
				get_template_directory_uri() . '/style.css',
				array(),
				$parent_style_ver
			);
			$deps = array( 'emindy-parent' );
		}

		// Child stylesheet.
		wp_enqueue_style(
			'emindy-style',
			get_stylesheet_uri(),
			$deps,
			$child_style_ver
		);

		wp_style_add_data( 'emindy-style', 'rtl', 'replace' );

		// Compatibility / alias variables (keeps templates stable).
		$compat_css = emindy_get_compat_css();
		if ( '' !== $compat_css ) {
			wp_add_inline_style( 'emindy-style', $compat_css );
		}
	}
}

/**
 * Early bootstrap script: apply saved theme choice before styles render.
 */
add_action( 'wp_head', 'emindy_head_theme_bootstrap', 0 );
if ( ! function_exists( 'emindy_head_theme_bootstrap' ) ) {
	function emindy_head_theme_bootstrap(): void {
		$js = "(function(){try{var t=localStorage.getItem('emindy_theme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-em-theme',t);}}catch(e){}})();";
		emindy_print_inline_script_tag( $js, 'emindy-theme-bootstrap' );
	}
}

/**
 * Theme JS (footer): dark/light toggle using data-em-theme and localStorage('emindy_theme').
 */
add_action( 'wp_footer', 'emindy_print_theme_js', 20 );
if ( ! function_exists( 'emindy_print_theme_js' ) ) {
	function emindy_print_theme_js(): void {
		$js = emindy_get_theme_js();
		if ( '' === $js ) {
			return;
		}
		emindy_print_inline_script_tag( $js, 'emindy-theme-js' );
	}
}

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
 * Block style variations used by templates/CSS.
 */
add_action( 'init', 'emindy_register_block_styles' );
if ( ! function_exists( 'emindy_register_block_styles' ) ) {
	function emindy_register_block_styles(): void {
		if ( ! function_exists( 'register_block_style' ) ) {
			return;
		}

		register_block_style(
			'core/group',
			array(
				'name'  => 'em-card',
				'label' => __( 'eMINDy Card', 'emindy' ),
			)
		);

		register_block_style(
			'core/button',
			array(
				'name'  => 'em-soft',
				'label' => __( 'Soft', 'emindy' ),
			)
		);
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
 *
 * Attributes:
 * - dropdown="1|0"
 * - show_names="1|0"
 * - show_flags="1|0"
 */
if ( ! function_exists( 'emindy_shortcode_lang_switcher' ) ) {
	function emindy_shortcode_lang_switcher( array $atts = array() ): string {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'dropdown'   => '0',
				'show_names' => '1',
				'show_flags' => '0',
			),
			$atts,
			'em_lang_switcher'
		);

		$args = array(
			'echo'          => 0,
			'dropdown'      => (int) ( '1' === (string) $atts['dropdown'] ),
			'show_names'    => (int) ( '1' === (string) $atts['show_names'] ),
			'show_flags'    => (int) ( '1' === (string) $atts['show_flags'] ),
			'hide_if_empty' => 0,
		);

		$html = pll_the_languages( $args );

		if ( empty( $html ) || ! is_string( $html ) ) {
			return '';
		}

		return '<div class="em-lang-switcher" aria-label="' . esc_attr__( 'Language switcher', 'emindy' ) . '">' . $html . '</div>';
	}
}

/**
 * [em_social_icons] fallback.
 *
 * Renders the "social" menu location if assigned.
 */
if ( ! function_exists( 'emindy_shortcode_social_icons' ) ) {
	function emindy_shortcode_social_icons(): string {
		if ( ! has_nav_menu( 'social' ) ) {
			return '';
		}

		return (string) wp_nav_menu(
			array(
				'theme_location'  => 'social',
				'container'       => 'nav',
				'container_class' => 'em-social-icons',
				'container_id'    => 'em-social-icons',
				'menu_class'      => 'em-social-icons__list',
				'depth'           => 1,
				'echo'            => false,
				'fallback_cb'     => '__return_empty_string',
			)
		);
	}
}

/**
 * Prevent PHP code from leaking if someone accidentally put PHP into .html block templates.
 */
add_filter( 'render_block', 'emindy_filter_render_block', 10, 2 );
if ( ! function_exists( 'emindy_filter_render_block' ) ) {
	function emindy_filter_render_block( string $block_content, array $block ): string {
		if ( '' === $block_content || empty( $block['blockName'] ) ) {
			return $block_content;
		}

		// Only touch template parts (header/footer/etc).
		if ( 'core/template-part' !== (string) $block['blockName'] ) {
			return $block_content;
		}

		if ( false === strpos( $block_content, '<?php' ) ) {
			return $block_content;
		}

		$slug = '';
		if ( ! empty( $block['attrs']['slug'] ) ) {
			$slug = (string) $block['attrs']['slug'];
		}

		$block_content = emindy_safe_template_php_replace( $block_content, $slug );

		// Strip any remaining PHP tags (never render raw PHP).
		$block_content = preg_replace( '/<\?php[\s\S]*?\?>/m', '', (string) $block_content );

		return (string) $block_content;
	}
}

/**
 * Safe template PHP replacement (minimal allowlist).
 */
if ( ! function_exists( 'emindy_safe_template_php_replace' ) ) {
	function emindy_safe_template_php_replace( string $html, string $template_part_slug = '' ): string {
		$site_name = get_bloginfo( 'name' );
		$year      = gmdate( 'Y' );

		$replace_cb = static function ( array $m ) use ( $site_name, $year, $template_part_slug ): string {
			$php = (string) $m[0];

			// Strip common headers/guards.
			if ( false !== strpos( $php, '@package' ) || false !== strpos( $php, 'ABSPATH' ) ) {
				return '';
			}

			// Year.
			if ( false !== strpos( $php, 'gmdate' ) && preg_match( "/gmdate\(\s*['\"]Y['\"]\s*\)/", $php ) ) {
				return esc_html( $year );
			}

			// Site name.
			if ( false !== strpos( $php, 'get_bloginfo' ) && preg_match( "/get_bloginfo\(\s*['\"]name['\"]\s*\)/", $php ) ) {
				return esc_html( $site_name );
			}

			// Simple translations: esc_html_e( 'Text', 'emindy' ) or esc_html_e( "Text", "emindy" ).
			if ( preg_match( "/esc_html_e\(\s*(['\"])([^'\"]+)\\1\s*,\s*(['\"])emindy\\3\s*\)/", $php, $mm ) ) {
				return esc_html__( (string) $mm[2], 'emindy' );
			}

			// URLs: home_url( '/path/' ).
			if ( preg_match( "/home_url\(\s*(['\"])([^'\"]+)\\1\s*\)/", $php, $mm ) ) {
				$path = (string) $mm[2];
				return esc_url( home_url( $path ) );
			}

			// Footer disclaimer (known snippet).
			if ( 'footer' === $template_part_slug && false !== strpos( $php, 'Calm, friendly and accessible resources' ) ) {
				$text = __( 'Calm, friendly and accessible resources. Educational self-help only (not medical advice). For urgent help, see <a href="%s">Emergency</a>.', 'emindy' );
				$out  = sprintf( (string) $text, esc_url( home_url( '/emergency/' ) ) );
				return wp_kses_post( $out );
			}

			return '';
		};

		// Replace each PHP segment with a safe computed value (or empty).
		$html = preg_replace_callback( '/<\?php[\s\S]*?\?>/m', $replace_cb, $html );

		return (string) $html;
	}
}

/**
 * Compatibility CSS variables for tokens referenced across templates and CSS.
 *
 * Keeps templates stable even if upstream tokens (from a parent theme) are absent.
 */
if ( ! function_exists( 'emindy_get_compat_css' ) ) {
	function emindy_get_compat_css(): string {
		$lines = array(
			':root{',
			'  --font-sans: var(--wp--preset--font-family--emindy-sans, Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif);',
			'  --font-serif: var(--wp--preset--font-family--emindy-serif, Literata, ui-serif, Georgia, serif);',
			'  --wp--custom--brand--radius--lg: 12px;',
			'  --wp--custom--brand--radius--xl: 16px;',
			'  --wp--custom--brand--radius--2xl: 24px;',
			'  --wp--custom--brand--shadow--card: 0 10px 30px rgba(0,0,0,0.08);',
			'  --wp--custom--brand--colors--deepBlue: #0b2d49;',
			'  --wp--custom--brand--colors--gold: #f4d483;',
			'  --wp--custom--brand--colors--teal: #00a3a3;',
			'  --wp--preset--shadow--natural: var(--wp--custom--brand--shadow--card);',
			'  --wp--preset--color--white: #ffffff;',
			'  --wp--preset--color--black: #000000;',
			'  --wp--preset--color--base: #ffffff;',
			'  --wp--preset--color--base-2: var(--em-card, #f8fafc);',
			'  --wp--preset--color--background: var(--em-bg, #ffffff);',
			'  --wp--preset--color--border: var(--em-border, rgba(0,0,0,0.08));',
			'  --wp--preset--color--secondary: rgba(244,212,131,0.18);',
			'  --wp--preset--color--teal: var(--em-teal, #00a3a3);',
			'  --wp--preset--color--contrast: var(--em-text, #0a2a43);',
			'  --wp--preset--color--contrast-3: rgba(0,0,0,0.12);',
			'  --wp--preset--color--contrast-5: #f1f5f9;',
			'}',
		);

		/**
		 * Filter compatibility CSS lines.
		 *
		 * @param string[] $lines CSS lines to be joined with "\n".
		 */
		$lines = apply_filters( 'emindy_compat_css_lines', $lines );

		if ( empty( $lines ) || ! is_array( $lines ) ) {
			return '';
		}

		$lines = array_map(
			static function ( $line ): string {
				return is_string( $line ) ? $line : '';
			},
			$lines
		);

		$lines = array_values( array_filter( $lines, 'strlen' ) );

		return implode( "\n", $lines ) . "\n";
	}
}

/**
 * Theme JS:
 * - Dark/light toggle using data-em-theme and localStorage('emindy_theme').
 * - Updates aria-label and icon for accessibility.
 */
if ( ! function_exists( 'emindy_get_theme_js' ) ) {
	function emindy_get_theme_js(): string {
		return <<<JS
(function(){
	'use strict';
	var KEY='emindy_theme';
	var root=document.documentElement;

	function prefersDark(){
		try{
			return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
		}catch(e){return false;}
	}

	function getStored(){
		try{return localStorage.getItem(KEY);}catch(e){return null;}
	}

	function setStored(v){
		try{
			if(v===null){localStorage.removeItem(KEY);}else{localStorage.setItem(KEY,v);}
		}catch(e){}
	}

	function getExplicit(){
		var explicit=root.getAttribute('data-em-theme');
		if(explicit==='dark'||explicit==='light'){return explicit;}
		var stored=getStored();
		if(stored==='dark'||stored==='light'){return stored;}
		return null;
	}

	function getEffectiveMode(){
		var explicit=getExplicit();
		if(explicit){return explicit;}
		return prefersDark() ? 'dark' : 'light';
	}

	function applyMode(mode){
		if(mode==='dark'){root.setAttribute('data-em-theme','dark');}
		else if(mode==='light'){root.setAttribute('data-em-theme','light');}
		else{root.removeAttribute('data-em-theme');}
	}

	function updateButton(btn){
		if(!btn){return;}
		var mode=getEffectiveMode();
		var isDark=(mode==='dark');
		btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');

		var labelToDark  = btn.getAttribute('data-labelToDark')  || 'Switch to dark mode';
		var labelToLight = btn.getAttribute('data-labelToLight') || 'Switch to light mode';
		btn.setAttribute('aria-label', isDark ? labelToLight : labelToDark);

		var icon = btn.querySelector('span[aria-hidden="true"]');
		if(icon){icon.textContent = isDark ? '☀️' : '🌙';}
	}

	function toggle(){
		var current=getEffectiveMode();
		var next=(current==='dark') ? 'light' : 'dark';
		applyMode(next);
		setStored(next);
		updateButton(document.getElementById('em-dark-mode-toggle'));
	}

	document.addEventListener('click', function(e){
		var el = e.target;
		if(!el){return;}
		var btn = (el.id==='em-dark-mode-toggle') ? el : (el.closest ? el.closest('#em-dark-mode-toggle') : null);
		if(!btn){return;}
		e.preventDefault();
		toggle();
	});

	document.addEventListener('DOMContentLoaded', function(){
		updateButton(document.getElementById('em-dark-mode-toggle'));
	});

	// If the user has not explicitly chosen a mode, keep the toggle UI in sync with system changes.
	try{
		if(window.matchMedia){
			var mq = window.matchMedia('(prefers-color-scheme: dark)');
			var handler = function(){
				if(getExplicit()){ return; }
				updateButton(document.getElementById('em-dark-mode-toggle'));
			};
			if(mq.addEventListener){ mq.addEventListener('change', handler); }
			else if(mq.addListener){ mq.addListener(handler); }
		}
	}catch(e){}
})();
JS;
	}
}
