<?php
	$pagination = $this->data;
?>

	<nav aria-label="Page navigation" class="text-center">
		<ul class="pagination ttlc-pagination">
<?php 
	
	if ( $pagination->prev ) {

		echo '<li><a href="' . esc_url( $pagination->prev ) . '" aria-label="Previous"><span aria-hidden="true">&larr;</span></a></li>';
		
	}

	for( $i = 1; $i <= $pagination->pages_count; $i++ ) {
		
		if ( $pagination->active === $i ) {
?>
		<li class="active"><a href="#"><?php echo $i; ?></a></li>
<?php
		
		} else {
			echo '<li><a href="' . esc_url( add_query_arg( 'page_num', $i ) ) . '">' . $i . '</a></li>';
		}

	}

	if ( $pagination->next ) {

		echo '<li><a href="' . esc_url( $pagination->next ) . '" aria-label="Next"><span aria-hidden="true">&rarr;</span></a></li>';

	} ?>
		</ul>
	</nav>
