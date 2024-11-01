<?php
	$data = $this->get_data();
	$product = $data['product'];
	$save_disabled = isset( $data['save_disabled'] ) ? true : false;

?>
					<div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-default"><?php esc_html_e( 'Close', TTLC_TEXTDOMAIN ); ?></button>
						<button type="submit" class="btn btn-dark ttlc-product-save-btn <?php echo $save_disabled ? 'disabled' : ''; ?>"><?php esc_html_e( 'Save Changes', TTLC_TEXTDOMAIN ); ?></button>
					</div>