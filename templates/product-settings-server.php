<?php
	$data = $this->get_data();
	$data['show_server'] = true;
	$this->render_template( 'product-settings-header', $data );
	$this->render_template( 'product-settings-server-modal', $data );

?>

	</div>
</div>