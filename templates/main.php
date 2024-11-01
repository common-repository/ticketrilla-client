<?php
$title = __( 'Supported Themes and Plugins', TTLC_TEXTDOMAIN );
TTLC_Breadcrumbs::add_head( $title );
$this->render_template( 'header' );

?>
		<div class="ttlc__header-title">
			<h1><?php echo esc_html( $title ); ?></h1><a href="#" data-toggle="modal" data-target="#ttlc-modal-product-custom" class="btn btn-info"><i></i><?php esc_html_e( 'Add Custom', TTLC_TEXTDOMAIN ); ?></a>
			<?php
				$custom_product = new TTLC_Product_Available;
				$custom_product->slug = 'custom';
				$this->render_template( 'product-settings-server', array(
					'product' => $custom_product,
					'product_uniqid' => uniqid(),
				) );
			?>
		</div>
		<?php
	        $filter_check = TTLC_Product::find_one();
	        
	        if ( ! empty( $filter_check ) ) {
			
				$this->render_template( 'filter', array(
					'theme' => array(
						'label' => __( 'Themes', TTLC_TEXTDOMAIN ),
					),
					'plugin' => array(
						'label' => __( 'Plugins', TTLC_TEXTDOMAIN ),
					),
					'custom' => array(
						'label' => __( 'Others', TTLC_TEXTDOMAIN ),
					),
					'archive' => array(
						'label' => __( 'Archive', TTLC_TEXTDOMAIN ),
					),
				) );
			}
		?>
	</div>

	<div class="ttlc__content">

        <?php
	        $connected_args = array(

	        );
	        if ( isset( $_GET['filter'] ) ) {
		        if ( $_GET['filter'] === 'archive' ) {
			        $connected_args = array(
				    	'post_status' => 'trash'
			        );
		        } else {
			        $connected_args = array(
		        		'meta_key' => TTLC_Product::PREFIX . 'type',
		        		'meta_value' => $_GET['filter'],
			        );
		        }
	        }
	        $connected = TTLC_Product::find_all( $connected_args );
	        $connected_list = $connected['items'];

	        if ( empty( $connected_list ) ) {
		        echo '<p>' . esc_html__('No added products found', TTLC_TEXTDOMAIN ) . '</p>';
		    } else {
	    ?>

		<div class="ttlc__cards">
			<?php
		        foreach ( $connected_list as $connected_product ) {
			        $connected_product_url = add_query_arg( 'product_id', $connected_product->id, remove_query_arg( 'filter', $_SERVER['REQUEST_URI'] ) );
			?>
			<article class="ttlc__card plugin"><a href="#" title="<?php esc_html_e( 'Settings', TTLC_TEXTDOMAIN ); ?>" data-toggle="modal" data-target="#ttlc-modal-product-<?php echo esc_attr( $connected_product->slug ); ?>" class="ttlc__card-settings"><i class="fa fa-cog"></i></a>
				
				<div class="ttlc__card-thumbnail">
					<a href="<?php echo esc_url( $connected_product_url ); ?>">
					<?php if ( $connected_product->thumbnail ) { ?>
						<img src="<?php echo esc_url( $connected_product->thumbnail ); ?>" alt="<?php echo isset( $connected_product->title ) ? esc_attr( $connected_product->title ) : ''; ?>">
					<?php } else { ?>
						<span class="fa fa-image"></span>
					<?php } ?>
					</a><span class="badge label-danger"></span>
				</div>
				<div class="ttlc__card-entry">
					<header class="ttlc__card-header">
						<h3 class="ttlc__card-title"><a href="<?php echo esc_url( $connected_product_url ) ?>"><?php echo isset( $connected_product->title ) ? esc_html( $connected_product->title ) : ''; ?></a></h3>
					</header>
					<?php if ( isset( $connected_product->content ) ) { ?>
					<div class="ttlc__card-excerpt">
						<p><?php echo wp_kses_post( $connected_product->content ); ?></p>
					</div>
					<?php } ?>
					<?php if ( $connected_product->author ) { ?>
					<div class="ttlc__card-authors">
						<cite>
						<?php
							esc_html_e( 'By', TTLC_TEXTDOMAIN );
							if ( ! empty( $connected_product->author_uri ) ) {								
						?> <a href="<?php echo esc_url( $connected_product->author_uri ); ?>"><?php echo esc_html( $connected_product->author ); ?></a>
						<?php } else {
							echo ' ' . esc_html( $connected_product->author );
						}
						?>
						</cite>
					</div>
					<?php } ?>
				</div>
	            <div class="ttlc__card-footer">
		        <?php
			        if ( isset( $_GET['filter'] ) && $_GET['filter'] === 'archive' ) {
				    	$untrash_nonce = wp_create_nonce( 'untrash_post_' . $connected_product->id );
			    ?>
	            	<div class="ttlc__card-footer-inner"><a href="<?php echo esc_url( add_query_arg( array(
		            		'id' => $connected_product->id,
		            		'action' => 'ttlc/product/untrash',
				            '_wpnonce' => $untrash_nonce,
			            ) ) ); ?>" class="btn btn-dark ttlc-product-untrash"><i class="fa fa-file-archive"></i> <?php esc_html_e( 'Unarchive', TTLC_TEXTDOMAIN ); ?></a></div>
			    <?php
			        } else {
				    	$trash_nonce = wp_create_nonce( 'trash_post_' . $connected_product->id );
			    ?>
	            	<div class="ttlc__card-footer-inner"><a href="<?php echo esc_url( add_query_arg( array(
		            		'id' => $connected_product->id,
		            		'action' => 'ttlc/product/trash',
				            '_wpnonce' => $trash_nonce,
			            ) ) ); ?>" class="btn btn-default ttlc-product-trash"><i class="fa fa-file-archive"></i> <?php esc_html_e( 'Archive', TTLC_TEXTDOMAIN ); ?></a></div>
			    <?php
			        }
			    ?>
	            </div>
			</article>
			<?php $this->render_template( 'product-settings', array('product' => $connected_product, 'product_uniqid' => uniqid()) ); ?>
			<?php } ?>
		</div>

		<?php } ?>
		<hr>
		<div class="ttlc__available">
			<h3><?php esc_html_e( 'Available for Support', TTLC_TEXTDOMAIN ); ?></h3>
        <?php
	        $available_type = isset( $_GET['filter'] ) ? $_GET['filter'] : false;
	        $available_list = TTLC_Product_Available::get_list( $available_type );
	        if( empty( $available_list ) ) {
		        echo '<p>' . esc_html__('No available for support products found', TTLC_TEXTDOMAIN ) . '</p>';
			} else { ?>

			<div class="ttlc__available-inner">
				<?php
					$table_available = new TTLC_Table_Helper(
						'support-available',
						array(
							'title' => array(
								'label' => esc_html__( 'Product Name', TTLC_TEXTDOMAIN ),
								'value' => function( $data ) {
									$html = '<h4>' . esc_html( $data->title ) . '</h4>';
									if ( isset( $data->description ) ) {
										$html .= '<p>' . wp_kses_post( $data->description ) . '</p>';
									}
									return $html;
								}
							),
							'actions' => array(
								'label' => esc_html__( 'Actions', TTLC_TEXTDOMAIN ),
								'value' => function( $data ) {
									ob_start();
									?><a href="#" class="btn btn-block btn-dark" data-toggle="modal" data-target="#ttlc-modal-product-<?php echo esc_attr( $data->slug ); ?>"><?php esc_html_e( 'Add',TTLC_TEXTDOMAIN ); ?></a><?php
									$this->render_template( 'product-settings-server', array(
										'product' => $data,
										'product_uniqid' => uniqid(),
									) );
									return ob_get_clean();
								},
							),
						),
						$available_list
					);
					$table_available->render( 'table table-striped' );
				?>

			</div>
		
		<?php } ?>
		</div>
	</div>

<?php

$this->render_template( 'footer' );

?>