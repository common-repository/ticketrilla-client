<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLC_Ajax' ) ) {

		class TTLC_Ajax {

			function __construct() {
				add_action( 'wp_ajax_ttlc/server/check', array($this, 'server_check') );
				add_action( 'wp_ajax_ttlc/product/save', array($this, 'product_save') );
				add_action( 'wp_ajax_ttlc/product/trash', array($this, 'product_trash') );
				add_action( 'wp_ajax_ttlc/product/untrash', array($this, 'product_untrash') );
				add_action( 'wp_ajax_ttlc/password/reset', array($this, 'password_reset') );
				add_action( 'wp_ajax_ttlc/add/ticket', array($this, 'add_ticket') );
				add_action( 'wp_ajax_ttlc/edit/ticket', array($this, 'edit_ticket') );
				add_action( 'wp_ajax_ttlc/attachment/download', array($this, 'attachment_download') );
				add_action( 'wp_ajax_ttlc/settings/attachments', array($this, 'settings_attachments') );
			}
			
			public function password_reset() {
				check_ajax_referer( 'ttlc-product-password-reset-' . ( isset( $_POST['slug'] ) ? $_POST['slug'] : '' ), '_wpnonce' );
				$errors = false;
				$data = '';
				$password_reset = new TTLC_Password_Reset( $_POST );
				$product = new TTLC_Product_Available( $_POST );
				if ( isset( $_POST['secure_key'] ) ) {
					$password_reset->set_scenario( TTLC_Password_Reset::SCENARIO_KEY );
				}

				if ( $password_reset->validate() ) {
					$user = new TTLC_Rest_User( array(
						'server' => $product->server,
						'data' => array(
							'login' => $password_reset->email_login,
							'reset_key' => isset( $password_reset->secure_key ) ? $password_reset->secure_key : '',
						),
					) );
					$user->password_reset();

					$response = $user->get_response();
					$response_body = $user->get_response_body();
					
					if ( $response['response']['code'] === 200 ) {
						if ( isset( $response_body->password ) && isset( $password_reset->autosubstitution ) ) {
							$data['value'] = $response_body->password;
							$data['selector'] = '#ttlc-product-login-' . $_POST['_product_uniqid'] . '-password';
						}
					} else {
						$errors = true;
						$password_reset->add_error( isset( $password_reset->secure_key ) ? 'secure_key' : 'email_login', isset( $response_body->message ) ? $response_body->message : __( 'Unknown Rest Server Error', TTLC_TEXTDOMAIN ) );
					}
					
				} else {
					$errors = true;
				}
				
				if ( $errors ) {
					$html = TTLC()->page()->buffer_template( 'product-settings-password-reset', array(
						'product' => $product,
						'product_uniqid' => $_POST['_product_uniqid'],
						'password_reset' => $password_reset,
					) );
					$data = '<div>' . $html . '</div>';
				}
				
				wp_send_json( array(
					'status' => ! $errors,
					'data' => $data,
				) );
				
			}
			
			public function server_check() {
				check_ajax_referer( 'ttlc_server_check', '_wpnonce' );
				$server = $this->rest_server_check( array(
					'server' => isset( $_POST['server'] ) ? esc_url( $_POST['server'] ) : '',
				) );
				$response_body = $server->get_response_body();

				// If Product ID is send — load product from DB
				
				if ( isset( $_POST['id'] ) ) {
					$product_loaded = get_post( $_POST['id'] );
				}
				
				$product = isset( $product_loaded ) ? new TTLC_Product( $product_loaded ) : new TTLC_Product( $_POST );

				$html = '';
				$data = array();
				$error_message = false;
				if ( is_wp_error( $server->get_response() ) ) {
					$error_message = __( 'Wrong Server URL', TTLC_TEXTDOMAIN );
				} elseif( $server->check_response() && is_array( $response_body ) && ! empty( $response_body ) ) {
					$response_product = $response_body[0];
					
					if ( $response_product->open_registration === 'on' ) {
						$product->registration = true;
					}

					$product->slug = TTLC_Support::format_slug( $product->server, $response_product->product_id );
					$product->type = $response_product->type;
					$product->title = $response_product->title;
					$product->content = $response_product->description;
					$product->thumbnail = $response_product->image;
					$product->author = $response_product->author->name;
					$product->author_uri = $response_product->author->link;
					$product->manual = $response_product->manual;
					$product->service_terms = $response_product->terms;
					$product->privacy_statement = $response_product->privacy;
					$product->license_fields = json_encode( $response_product->license_list );
					
					$current_user = wp_get_current_user();
					if ( is_null( $product->name ) && $current_user instanceof WP_User ) {
						$product->name = trim( $current_user->user_firstname . ' ' . $current_user->user_lastname );
					}
					
				} else {
					$response_code = $server->get_code();
					if ( $response_code ) {
						if ( $ttls_error_message = TTLC()->get_ttls_error( $response_code ) ) {
							$error_message = $ttls_error_message;
						} else {
							$error_message = __( 'Unknown TTLS Error', TTLC_TEXTDOMAIN );
						}
					} else {
						$error_message = __( 'Wrong Server URL', TTLC_TEXTDOMAIN );
					}
					
				}
				
				if ( $error_message ) {
					$product->add_error( 'server', $error_message );
					$template = 'product-settings-server';
				} else {
					$template = 'product-settings';
				}
				
				$data = array(
					'product' => $product,
					'product_uniqid' => $_POST['_product_uniqid'],
				);
				$html = TTLC()->page()->buffer_template( $template, $data );

				wp_send_json( $html );				
			}
			
			protected function rest_server_check( $args ) {				
				$server = new TTLC_Rest_Server( $args );
				$server->check(); 
				return $server;
			}
			
			protected function rest_user_register( TTLC_Product $product ) {

				$user = new TTLC_Rest_User( array(
					'server' => $product->server,
					'data' => array(
						'login' => $product->login,
						'password' => $product->password,
						'email' => $product->email,
						'name' => $product->name,
					),
				) );

				$user->register();
				
				if ( $user->check_response() ) {
					
					// Registration OK — Bind License

					$rest_user_license = $this->rest_user_license( $product );
					$product = $rest_user_license['product'];
					$user = $rest_user_license['user'];
					
				}

				return array(
					'user' => $user,
					'product' => $product,
				);
			}
			
			protected function rest_user_license( TTLC_Product $product ) {
				$licenses = (Array)json_decode( $product->license_fields);
				$user = new TTLC_Rest_User( array(
					'server' => $product->server,
					'login' => $product->login,
					'password' => $product->password,
					'data' => array_merge( array(
						'license_type' => $product->license,
					), is_array( $product->license_data ) ? $product->license_data : array() ),
				) );
	
				$user->license();
				
				if ( $user->check_response() ) {
					
					$license_response_body = $user->get_response_body();
					
					foreach( $licenses[$product->license]->fields as $license_field_name => $license_field_data ) {
						if ( isset($license_response_body->$license_field_name) ) {
							$product->license_data[$license_field_name] = $license_response_body->$license_field_name;
						}
					}
				}
				
				return array(
					'product' => $product,
					'user' => $user,
				);				
			}

			public function product_save() {
				check_ajax_referer( 'ttlc_product_save_' . ( isset( $_POST['slug'] ) ? $_POST['slug'] : '' ), '_wpnonce' );
				
				$product = new TTLC_Product( $_POST );
				$form = isset( $_POST['form'] ) && in_array( $_POST['form'], array('login', TTLC_Product::SCENARIO_REGISTRATION) ) ? $_POST['form'] : 'login';
				$html = '';
				$errors = false;
				
				if ( $form === TTLC_Product::SCENARIO_REGISTRATION ) {
					$product->set_scenario( TTLC_Product::SCENARIO_REGISTRATION );
				} elseif( isset( $product->id ) ) {
					$product->set_scenario( TTLC_Product::SCENARIO_UPDATE );
				}
				
				// Process License Fields

				$product->license_fields = stripslashes( $product->license_fields );
				$licenses = (Array)json_decode( $product->license_fields);
				if ( isset( $product->license ) ) {
					foreach( $licenses[$product->license]->fields as $license_field_name => $license_field_data ) {
						if ( ( $form === 'login' && $license_field_data->login ) || ( $form === 'registration' && $license_field_data->register ) ) {
							$product->license_data[$license_field_name] = isset( $_POST[$license_field_name] ) ? $_POST[$license_field_name] : null;
						}				
					}
				}
				
				// TTLC Level Validation
				
				if ( $product->validate() ) {
					
					if ( $product->get_scenario() === TTLC_Product::SCENARIO_REGISTRATION ) {

						if ( $product->license === 'ticketrilla' && empty( $product->license_data['license_token'] ) ) {
							$rest_user_register = $this->rest_user_register( $product );
							$user = $rest_user_register['user'];
							$product = $rest_user_register['product'];
						} else {
							$user = TTLC_Rest_User::can_license( $product, false );
							if ( $user->check_response() ) {
								$rest_user_register = $this->rest_user_register( $product );
								$user = $rest_user_register['user'];
								$product = $rest_user_register['product'];
							}
						}

					} else {
						
					// Login or Settings Update

						if ( $product->get_scenario() === TTLC_Product::SCENARIO_UPDATE ) {

							// Update User Name
							
							$user_name = new TTLC_Rest_User( array(
								'server' => $product->server,
								'login' => $product->login,
								'password' => $product->password,
								'data' => array(
									'name' => $product->name,
								),
							) );
							$user_name->set_name();
							
						}

						// Login / Product Settings Update

						// No Token Sent or Not Your License or Not Found License — Try Bind License
						
						if ( empty( $product->license_data['license_token'] ) ) {
							
							$rest_user_license = $this->rest_user_license( $product );
							$product = $rest_user_license['product'];
							$user = $rest_user_license['user'];
						
						} else {
							$user = TTLC_Rest_User::can_license( $product );
							if ( $user->get_code() === 'ttls_license_notyour' || $user->get_code() === 'ttls_license_notfound' ) {
								$rest_user_license = $this->rest_user_license( $product );
								$product = $rest_user_license['product'];
								$user = $rest_user_license['user'];
							}
						}
					}

					
					// TTLS Level Validation (License/Registration)

					
					if ( $user->check_response() ) {

						// License/Registration is Valid - Save Product
						
						$save = $product->save();

						if ( ! $save['status'] ) {
							$product->add_error( '_global', $save['message'] );
							$errors = true;
						}
						
					} else {

						// License/Registration is Invalid
						
						$user_message = $user->get_message();
						
						if ( empty( $user_message ) ) {
							$product->add_error( '_global', __( 'Unknown Rest Server Error', TTLC_TEXTDOMAIN ) );
						} elseif( is_array( $user_message ) ) {
							foreach ( $user_message as $_user_message ) {
								$product->add_error( '_global', $_user_message );
							}
						} else {
							$product->add_error( '_global', $user_message );
						}
						
						
						$errors = true;

					}

				} else {
					foreach( $product->required_hidden_attributes() as $hidden_attribute ) {
						if ( $product->has_errors( $hidden_attribute ) ) {
							foreach ( $product->get_errors( $hidden_attribute ) as $message ) {
								$product->add_error( '_global', $message );
							}
							break;
						}
					}
					
					$errors = true;
					
				}

				
				if ( $errors ) {
					
					$html = TTLC()->page()->buffer_template( 'product-settings-form', array(
						'product' => $product,
						'form' => $form,
						'product_uniqid' => $_POST['_product_uniqid'],
					) );					
				} else {
					$html = TTLC()->page()->buffer_template( 'main' );
				}

				wp_send_json( array(
					'status' => ! $errors,
					'data' => $html,
				) );
				
			}
			
			public function product_trash() {
				$errors = false;
				$message = '';
				$nonce = $_POST['_wpnonce'];
				if ( ! wp_verify_nonce( $nonce, 'trash_post_' . $_POST['id'] ) ) {
				    $errors = true;
				} else {
					$product = new TTLC_Product( $_POST );
					$result = $product->trash();
					if ( is_wp_error( $result ) ) {
						$errors = true;
					    $message = $result->get_error_message();
					}
				}
				wp_send_json( array('status' => ! $errors, 'data' => $message ) );
			}

			public function product_untrash() {
				$errors = false;
				$message = '';
				$nonce = $_POST['_wpnonce'];
				if ( ! wp_verify_nonce( $nonce, 'untrash_post_' . $_POST['id'] ) ) {
				    $errors = true;
				} else {
					$product = new TTLC_Product( $_POST );
					$result = $product->untrash();
					if ( is_wp_error( $result ) ) {
						$errors = true;
					    $message = $result->get_error_message();
					}
				}
				wp_send_json( array('status' => ! $errors, 'data' => $message ) );
			}

			public function add_ticket() {
				define( 'ALLOW_UNFILTERED_UPLOADS', true );
				check_ajax_referer( 'ttlc_add_ticket', '_wpnonce' );				
				$errors = false;
				$data = '';
				$class = isset( $_POST['parent_id'] ) ? 'TTLC_Ticket_Response' : 'TTLC_Ticket';
				$ticket = new $class( $_POST );
				$ticket->license_data = stripslashes( $ticket->license_data );
				$ticket->content = trim( $ticket->content );
				$ticket->set_scenario( $class::SCENARIO_ADD );
				
				if ( $ticket->validate() ) {
					if ( empty( $_FILES['attachment']  ) ) {
						if ( empty( $ticket->content ) ) {
							$errors = true;
							$ticket->add_error('content', __( 'This field is required', TTLC_TEXTDOMAIN ) );
						}
					} else {
						foreach ( $_FILES['attachment']['error'] as $key => $error ) {
							
							if ( $error == UPLOAD_ERR_OK ) {
			
								$tmp_name = $_FILES['attachment']['tmp_name'][$key];
								$name = basename( $_FILES['attachment']['name'][$key] );
							
								$attachment = new TTLC_Attachment();
								$result = $attachment->upload( $tmp_name, $name );
								if ( $result['status'] ) {
									$ticket->attachments[] = $attachment->export_data();
								} else {
									$errors = true;
									$ticket->add_error('attachments', $name . ': ' . $result['message'] );
								}
							}
						}
					}
					
					if ( ! $errors ) {

						$rest_data = array_merge( array(
								'license_type' => $ticket->license,
								'title' => $ticket->title,
								'content' => $ticket->content,
						), (array)json_decode( $ticket->license_data ) );
						
						if ( isset( $ticket->parent_id ) ) {
							$rest_data['parent'] = $ticket->parent_id;
						}
	
						if ( ! empty( $ticket->attachments ) ) {
							$rest_data['attachments'] = $ticket->attachments;
						}
		
						$rest_ticket = new TTLC_Rest_Ticket( array(
							'server' => $ticket->server,
							'login' => $ticket->login,
							'password' => $ticket->password,
							'data' => $rest_data,
						) );
		
						$rest_ticket->add();
						$response = $rest_ticket->get_response();
						$response_body = $rest_ticket->get_response_body();
						if ( $response['response']['code'] === 200 ) {
							$data = $response_body;
						} else {
							$errors = true;
							$ticket->add_error( '_global', isset( $response_body->message ) ? $response_body->message : __( 'Unknown Rest Server Error', TTLC_TEXTDOMAIN ) );
						}
					}

					
				} else {
					$errors = true;
				}
				
				if ( $errors ) {
					$data = TTLC()->page()->buffer_template( 'add-ticket-form', array('ticket' => $ticket) );
				}

				wp_send_json( array(
					'status' => ! $errors,
					'data' => $data,
				) );
			}
			
			public function edit_ticket() {
				check_ajax_referer( 'ttlc_edit_ticket', '_wpnonce' );
				$result = array();
				$ticket = new TTLC_Rest_Ticket( array(
					'server' => $_POST['server'],
					'login' => $_POST['login'],
					'password' => $_POST['password'],
					'data' => array_merge( array(
						'license_type' => $_POST['license'],
						'parent' => $_POST['external_id'],
						'status' => $_POST['status'],
					), (array)json_decode( stripslashes( $_POST['license_data'] ) ) ),
				) );
				$ticket->edit();
				$response = $ticket->get_response();
				if ( is_wp_error( $response ) ) {
					$result['status'] = false;
					$result['message'] = $response->get_error_message();
				} else {
					$result['status'] = true;
					$result['data'] = $ticket->get_response_body();
				}
				wp_send_json( $result );
			}
			
			public function attachment_download() {				
				check_ajax_referer( 'ttlc_attachment_download', '_wpnonce' );				
				$data = '';
				$attachment = new TTLC_Attachment( $_POST );
				$result = $attachment->download();
				if ( $result ) {
					$data = TTLC()->page()->buffer_template( 'attachment', $attachment );
				}			
				wp_send_json( array('status' => $result, 'data' => $data) );
			}

			public function settings_attachments() {
				check_ajax_referer( 'ttlc_settings_attachments', '_wpnonce' );				
				$state = false;
				$attachments = new TTLC_Settings_Attachments( $_POST );
				if ( $attachments->validate() ) {
					$result = $attachments->save();
					$state = array('status' => 'success', 'message' => __('Settings successfully saved') );
				}
				
				wp_send_json( array(
					'data' => TTLC()->page()->buffer_template( 'settings-attachments-form', array('attachments' => $attachments, 'state' => $state ) ),
				) );
			}

		}
	}
