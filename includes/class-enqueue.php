<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLC_Enqueue' ) ) {

		class TTLC_Enqueue {

			function __construct() {

				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

			}

			/**
			 * @register scripts and styles
			 */
			public function admin_enqueue_scripts( $hook ) {
				if ( $hook == 'toplevel_page_' . TTLC_Page::SLUG || $hook == 'ticketrilla_page_' . TTLC_Page::SETTINGS_SLUG ) {
					$this->register_css();
					$this->register_js();
				}
			}

			/**
			 * @register Admin Page Scripts
			 */
			private function register_js() {
				
				// Enqueue TTLC Scripts

				wp_enqueue_script( 'bootstrap', TTLC_URL . 'assets/js/bootstrap.min.js', array(
					'jquery',
				), filemtime(TTLC_PATH . '/assets/js/bootstrap.min.js'), true );

				wp_enqueue_script( 'ckeditor', TTLC_URL . 'assets/js/ckeditor/ckeditor.js', array(
					'jquery',
				), filemtime(TTLC_PATH . '/assets/js/ckeditor/ckeditor.js'), true );

				wp_enqueue_script( 'ttlc', TTLC_URL . 'assets/js/ttlc-script.js', array(
					'jquery',
					'bootstrap',
					'ckeditor'
				), filemtime(TTLC_PATH . '/assets/js/ttlc-script.js'), true );
				
				wp_localize_script( 'ttlc', 'ttlcText', array(
					'waiting_save' => esc_html__( 'Waiting for save', TTLC_TEXTDOMAIN),
				) );
				
			}

			/**
			 * @register Admin Page Styles
			 */
			private function register_css() {

				wp_enqueue_style( 'ttlc_main', TTLC_URL . 'assets/css/main.css', array(), filemtime(TTLC_PATH . '/assets/css/main.css') );
				wp_enqueue_style( 'ttlc_added', TTLC_URL . 'assets/css/added.css', array(), filemtime(TTLC_PATH . '/assets/css/added.css') );
			}
		}
	}