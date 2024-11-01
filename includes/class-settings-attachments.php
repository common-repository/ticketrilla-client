<?php

	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLC_Settings_Attachments' ) ) {

		class TTLC_Settings_Attachments extends TTLC_Model {

			public $size;
			public $time;
			public $autoload;
			
			public function attributes() {
				return array(
					'size' => __( 'Max attachment size', TTLC_TEXTDOMAIN),
					'time' => __( 'Max load time', TTLC_TEXTDOMAIN),
					'autoload' => __( 'Autoload attachments', TTLC_TEXTDOMAIN),
				);
			}
			
			public function rules() {
				return array(
					array(
						array('size', 'time'),
						'number'
					),
				);
			}
			
			public function save() {
				$result = true;
				$not_saved = array();
				foreach( $this->attributes() as $attribute => $label ) {
					$update = update_option( 'ttlc_attachments_' . $attribute , $this->$attribute );
					if ( ! $update ) {
						$not_saved[] = $label;
						$result = false;
					}
				}

				return $result ? true : new WP_Error('ttlc_attachments_settings', sprintf( __('%s: options values have not changed', TTLC_TEXTDOMAIN ), implode( ', ', $not_saved ) ) );
			}
		}
	
	}