<?php

	TTLC_Breadcrumbs::add_link( esc_html__( 'Ticketrilla: Client', TTLC_TEXTDOMAIN ), TTLC_Page::get_url( 'main' ) );
	$title = __( 'Settings', TTLC_TEXTDOMAIN );
	TTLC_Breadcrumbs::add_head( $title );
	$this->render_template( 'header' );
?>
				<div class="ttlc__header-title">
					<h1><?php echo esc_html( $title ); ?></h1>
				</div>
			</div>
			<div class="ttlc__content">
				<div class="ttlc__settings">
					<div class="row">
						<div class="col-md-12">
							<!-- Additional class for form 'ttlc__disabled'-->
						<?php 
							$attachments = new TTLC_Settings_Attachments;
							$attachments->size = get_option('ttlc_attachments_size', 5);
							$attachments->time = get_option('ttlc_attachments_time', 30);
							$attachments->autoload = get_option('ttlc_attachments_autoload', false);
							$this->render_template( 'settings-attachments-form', array('attachments' => $attachments) );
						?>
						</div>
					</div>
				</div>
			</div>

<?php $this->render_template( 'footer' ); ?>
