<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLC_Page' ) ) {

		class TTLC_Page {

			/**
			 * @const string
			 */

			const SLUG = 'ticketrilla-client';

			/**
			 * @const string
			 */

			const SETTINGS_SLUG = 'ticketrilla-client-settings';

			static $urls;

			private $data;
			
			public function get_data() {
				return $this->data;
			}

			public function __call( $name, $args ) {
				$args = array_merge( array($name), $args );
				call_user_func_array( array($this, 'render_template'), $args );
			}

			function __construct() {
				add_action('admin_menu', array($this, 'add_pages') );				
			}
			
			public function add_pages() {
				add_menu_page( esc_html__( 'Ticketrilla', TTLC_TEXTDOMAIN ), esc_html__( 'Ticketrilla', TTLC_TEXTDOMAIN ), 'manage_ttlc', self::SLUG, array($this, 'ticketrilla') );
				
				add_submenu_page( self::SLUG, esc_html__( 'Settings', TTLC_TEXTDOMAIN ), esc_html__( 'Settings', TTLC_TEXTDOMAIN ), 'manage_ttlc', self::SETTINGS_SLUG, array($this, 'settings') );
				static::$urls = array(
					'main' => menu_page_url( self::SLUG, false ),
					'settings' => menu_page_url( self::SETTINGS_SLUG, false ),
				);
			}

			public function render_template( $template_name, $data = null ) {
				$this->data = $data;
				$file_name = TTLC_TEMPLATES;
				$file_name .= $template_name;
				$file_name .= '.php';
				if ( file_exists( $file_name ) ) {
					require $file_name;
				}
			}
			
			public function buffer_template( $template_name, $data = null ) {
				ob_start();
				$this->render_template( $template_name, $data );
				return ob_get_clean();
			}
			
			public static function get_url( $type ) {
				return static::$urls[$type];
			}
			
		}
	}