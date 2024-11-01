<?php

	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLC_Pagination' ) ) {

		class TTLC_Pagination {
			
			public $count;
			public $pages_count;
			public $ppp = TTLC_PPP;
			public $paged = false;
			public $active;
			public $prev;
			public $next;
			
			function __construct( $count ) {
				
				$this->count = is_numeric( $count ) ? $count : null;

				if ( $this->count > $this->ppp ) {
					$this->paged = true;
					$this->pages_count = (int) ceil( $this->count / $this->ppp );		
					$this->active = isset( $_GET['page_num'] ) ? (int) $_GET['page_num'] : 1;
					
					if ( isset( $_GET['page_num'] ) && $_GET['page_num'] > 1 ) {
						$this->prev = add_query_arg( 'page_num', $_GET['page_num'] - 1 );
					}
					
					if ( empty( $_GET['page_num'] ) ) {
						$this->next = add_query_arg( 'page_num', 2 );
					} elseif ( $_GET['page_num'] < $this->pages_count ) {
						$this->next = add_query_arg( 'page_num', $_GET['page_num'] + 1 );
					}
				}
				
			}
			
			public function render( $template = false ) {
				if ( $this->paged ) {
					$default_template = 'pagination';
					$template = $template ? $default_template . '-' . $template : $default_template;
					TTLC()->page()->render_template( $template, $this );
				}
			}
			
		}
	}