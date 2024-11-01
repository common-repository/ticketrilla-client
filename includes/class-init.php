<?php

	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLC' ) ) {
		/**
		 * Main TTLC Class
		 *
		 * @class TTLC
		 * @version 1.0
		 *
		 */
		final class TTLC extends TTLC_Functions {

			/**
			 * @var TTLC the single instance of the class
			 */
			protected static $instance = null;


			/**
			 * @var array all plugin's classes
			 */
			public $classes = array();


			/**
			 * Main TTLC Instance
			 *
			 * @since 1.0
			 * @static
			 * @see TTLC()
			 * @return TTLC - Main instance
			 */
			static public function instance() {
				if ( is_null( self::$instance ) ) {
					self::$instance = new self();
				}

				return self::$instance;
			}


			/**
			 * Create plugin classes
			 *
			 * @since 1.0
			 * @see TTLC()
			 *
			 * @param       $name
			 * @param array $params
			 *
			 * @return mixed
			 */
			public function __call( $name, array $params ) {

				if ( empty( $this->classes[ $name ] ) ) {
					$this->classes[ $name ] = apply_filters( 'ttlc_call_object_' . $name, false );
				}

				return $this->classes[ $name ];

			}

			/**
			 * Function for add classes to $this->classes
			 * for run using TTLC()
			 *
			 * @since 1.0
			 *
			 * @param string $class_name
			 * @param bool   $instance
			 */
			public function set_class( $class_name, $instance = false ) {
				if ( empty( $this->classes[ $class_name ] ) ) {
					$class                        = 'TTLC_' . $class_name;
					$this->classes[ $class_name ] = $instance ? $class::instance() : new $class;
				}
			}


			/**
			 * Cloning is forbidden.
			 *
			 * @since 1.0
			 */
			public function __clone() {
				_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', TTLC_TEXTDOMAIN ), '1.0' );
			}


			/**
			 * Unserializing instances of this class is forbidden.
			 *
			 * @since 1.0
			 */
			public function __wakeup() {
				_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', TTLC_TEXTDOMAIN ), '1.0' );
			}


			/**
			 * TTLC constructor.
			 *
			 * @since 1.0
			 */
			function __construct() {


				if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) && $this->is_request( 'admin' ) ) {

					// Include TTLC classes
					$this->ttlc_class_loader();
	
					// Set TTLC classes
					$this->set_classes();
					
					// Init TTLC
					$this->init();

				}
			}

			/**
			 * Include required core files
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			protected function ttlc_class_loader() {
				require_once 'class-ajax.php';
				require_once 'class-support.php';
				require_once 'class-cpt.php';
				require_once 'class-model.php';
				require_once 'class-post.php';
				require_once 'class-product.php';
				require_once 'class-product-available.php';
				require_once 'class-password-reset.php';
				require_once 'class-ticket.php';
				require_once 'class-ticket-response.php';
				require_once 'class-attachment.php';
				require_once 'class-table-helper.php';
				require_once 'class-enqueue.php';
				require_once 'class-page.php';
				require_once 'class-pagination.php';
				require_once 'class-breadcrumbs.php';
				require_once 'class-ticket-processor.php';
				require_once 'class-ticket-list-processor.php';
				require_once 'class-settings-attachments.php';
				require_once 'rest/class-abstract-rest.php';
				require_once 'rest/class-rest-server.php';
				require_once 'rest/class-rest-ticket.php';
				require_once 'rest/class-rest-user.php';

			}

			/**
			 * Function for add classes to $this->classes
			 * for run using TTLC() depending on request type
			 *
			 * @since 1.0
			 *
			 */
			protected function set_classes() {

				$this->set_class( 'enqueue' );
				$this->set_class( 'support' );
				$this->set_class( 'cpt' );
				$this->set_class( 'page' );
				
				if( $this->is_request( 'ajax' ) ) {
					$this->set_class( 'ajax' );
				}

			}
			
			protected function init() {
				$this->protect_uploads();
			}
			
			protected function protect_uploads() {
				global $is_apache;
				$upload_dir = wp_upload_dir( 'ttlc', true, true );
				if ( ( is_array( $upload_dir ) && $upload_dir['error'] === false ) && $this->write_test_php( $upload_dir ) ) {
					if ( $this->remote_test_php( $upload_dir ) ) {
						// Allow save files unzipped. Default — zipped.
						TTLC_Attachment::set_write_zip( false );
					} else {
						if ( $is_apache ) {
							$force_rewrite = isset( $_GET['global'] ) && $_GET['global'] === TTLC_HT_REWRITE_PARAM;
							if ( ! $force_rewrite && file_exists( trailingslashit( $upload_dir['path'] ) . '.htaccess' ) ) {
								add_action( 'admin_notices', array($this, 'configure_server_notice') );
								add_action( 'admin_notices', array($this, 'rewrite_config_notice') );
							} else {
								if ( ! $this->write_config() ) {
									add_action( 'admin_notices', array($this, 'upload_dir_writable_notice') );
								} elseif ( $force_rewrite ) {
									add_action( 'init', array( $this, 'redirect_to_main') );
								}
							}
						} else {
							add_action( 'admin_notices', array($this, 'configure_server_notice') );
						}
					}
				} else {
					add_action( 'admin_notices', array($this, 'upload_dir_writable_notice') );
				}
			}
			
			public function redirect_to_main() {
				wp_redirect( remove_query_arg( 'global' ) );
				exit;
			}
			
			protected function write_config() {
				require_once( ABSPATH . 'wp-admin/includes/misc.php' );
				$upload_dir = wp_upload_dir( 'ttlc' );
				$htaccess_file = trailingslashit( $upload_dir['path'] ) . '.htaccess';
				if ( is_writable( $upload_dir['path'] ) && ( ! file_exists( $htaccess_file ) || is_writable( $htaccess_file ) ) ) {
					return insert_with_markers( $htaccess_file, 'TTLC', 'php_flag engine off' );
				}
				return false;
			}

			public function rewrite_config_notice() {
			?>
			    <div class="notice notice-warning is-dismissible">
			        <p><?php esc_html_e( 'Or you can try to regenerate .htaccess file.', TTLC_TEXTDOMAIN ); ?></p>
			        <p><?php echo '<a class="button button-primary" href="' . esc_url( add_query_arg( 'global', TTLC_HT_REWRITE_PARAM ) ) . '">' . esc_html__( 'Regenerate', TTLC_TEXTDOMAIN ) . '</a>'; ?></p>
			    </div>
			<?php
	    	}

			public function configure_server_notice() {
				$upload_dir = wp_upload_dir( 'ttlc' );
			?>
			    <div class="notice notice-warning is-dismissible">
			        <p><?php echo esc_html( sprintf( __( 'Please, configure your server to prevent file execution in directory: %s.', TTLC_TEXTDOMAIN ), $upload_dir['path'] ) ); ?></p>
			    </div>
			<?php
	    	}
			
			public function upload_dir_writable_notice() {
			?>
			    <div class="notice notice-warning">
			        <p><?php esc_html_e( 'Please, check if uploads directory is writable.', TTLC_TEXTDOMAIN ); ?></p>
			    </div>
			<?php
	    	}
			
			protected function remote_test_php( $upload_dir ) {
				$remote_get = wp_remote_get( trailingslashit( $upload_dir['url'] ) . TTLC_TEST_PHP );
				if ( is_array( $remote_get ) ) {
					$body = $remote_get['body'];
					if ( $body !== 'PHP' ) {
						return true;
					}
				}
				return false;
			}
			
			protected function write_test_php( $upload_dir ) {
				$test_php_path = trailingslashit( $upload_dir['path'] ) . TTLC_TEST_PHP;
				if ( file_exists( $test_php_path ) ) {
					return true;
				} else {
					if ( is_writable( $upload_dir['path'] ) ) {
						if ( $test_fh = fopen( $test_php_path, 'w' ) ) {
							if ( fwrite( $test_fh, '<?php echo "PHP"; ?>' ) !== false ) {
								fclose( $test_fh );
								return true;
							}
							fclose( $test_fh );
						}
					}
				}
				return false;
			}

			public function licenses() {
				return array(
					TTLC_LICENSE => 'Ticketrilla',
					'envato' => 'Envato',
				);
			}
			
			public function activate() {
				$role = get_role( 'administrator' );
				$role->add_cap( 'manage_ttlc' );
			}
			
			public function ttls_errors() {
				return array(
					'ttls_server_noconfig' => __( 'Server is not configured', TTLC_TEXTDOMAIN ),
					'ttls_license_used' => __( 'This license is already used', TTLC_TEXTDOMAIN ),
				);
			}
			
			public function get_ttls_error( $code ) {
				$errors = $this->ttls_errors();
				if ( array_key_exists( $code, $errors ) ) {
					return $errors[$code];
				}
				return false;
			}
		}
	}

	/**
	 * Function for calling TTLC methods and variables
	 *
	 * @return TTLC
	 */
	function TTLC() {
		return TTLC::instance();
	}

	// Global for backwards compatibility.
	$GLOBALS['TTLC'] = TTLC();
