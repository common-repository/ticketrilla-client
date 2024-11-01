<?php
	$data = $this->get_data();
	$product = $data['product'];
	$this->render_template( 'product-settings-header', $data );
	$product_uniqid = $data['product_uniqid'];
	$common_modal_id = 'ttlc-product-common-' . $product_uniqid;
	$login_tab_id = 'ttlc-product-login-' . $product_uniqid;
	$registration_tab_id = 'ttlc-product-registration-' . $product_uniqid;

?>

		<div id="<?php echo esc_attr( $common_modal_id ); ?>" class="modal-content modal-common collapse fade in">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
				<h4 class="modal-title"><?php esc_html_e( 'Product Settings', TTLC_TEXTDOMAIN ); ?></h4>
			</div>
			<?php if ( is_null( $product->id ) ) { ?>
			<div class="modal-tabs ttlc-tabs fade in" role="tablist">
				<a href="#<?php echo esc_attr( $login_tab_id ); ?>" class="active" data-toggle="tab" role="tab" aria-controls="<?php echo esc_attr( $login_tab_id ); ?>" aria-selected="true"><?php esc_html_e( 'Login', TTLC_TEXTDOMAIN ); ?></a>
				<a class="<?php echo empty( $product->registration ) ? 'disabled' : ''; ?>" <?php echo empty( $product->registration ) ? 'title="' . esc_html__( 'Registration is closed', TTLC_TEXTDOMAIN ) . '"' : ''; ?> href="#<?php echo ! empty( $product->registration ) ? esc_attr( $registration_tab_id ) : ''; ?>" data-toggle="tab"  role="tab" aria-controls="<?php echo esc_attr( $registration_tab_id ); ?>" aria-selected="false"><?php esc_html_e( 'Registration', TTLC_TEXTDOMAIN ); ?></a>
			</div>
			<?php } ?>
			<div class="modal-body">
				<div class="tab-content">
	
					<!--Login Tab-->
	
					<div class="tab-pane fade in active" id="<?php echo esc_attr( $login_tab_id ); ?>" role="tabpanel">
					<?php
						$data['form'] = 'login';
						$this->render_template( 'product-settings-form', $data );
					?>
					</div>
					<?php if ( isset( $product->registration ) && is_null( $product->id ) ) { ?>
	
					<!--Registration Tab-->
	
					<div class="tab-pane fade" id="<?php echo esc_attr( $registration_tab_id ); ?>" role="tabpanel">
					<?php
						$data['form'] = 'registration';
						$this->render_template( 'product-settings-form', $data );
						$data['form'] = null;
					?>
					</div>
					<?php } ?>
	
				</div>
			</div>
		<?php $this->render_template( 'product-settings-common-footer', $data ); ?>
		</div>
		
		<?php $this->render_template( 'product-settings-server-modal', $data ); ?>

		<?php $this->render_template( 'product-settings-password-reset', $data ); ?>
	
	</div>
</div>
