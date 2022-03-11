<?php

add_action( 'init', 'register_dynamic_block' );

/**
	 * This function is to register the Custom block
	 *
	 */
	public function register_dynamic_block() {

		register_block_type(
			'guest-posts/server-side-render',
			array(
				'attributes'  => array(
					'numOfItems' => array(
						'type'    => 'number',
						'default' => 10,
					),
					'postOrder' => array(
						'type'     => 'string',
						'default'  => 'DESC',
					),
				),
				'render_callback' => array($this,'guest_post_render_block_callback'),
			)
		);
	}

	/**
	 * This function is to render post data in the Custom block
	 *
	 * @param      array $attributes       attributes
	 */
    public function guest_post_render_block_callback( $attributes ) {
		$number_of_items = isset( $attributes['numOfItems'] ) && ! empty( $attributes['numOfItems'] ) ? $attributes['numOfItems'] : 10;
		$post_order      = isset( $attributes['postOrder'] ) && ! empty( $attributes['postOrder'] ) ? $attributes['postOrder'] : 'DESC';
		$html            = '';

		$query_args = array(
			'post_type'      => 'guest-posts',
			'posts_per_page' => $number_of_items,
			'order'          => $post_order,
            'post_status'   => 'draft',
		);

		$query_result = new WP_Query( $query_args );

		ob_start();

		if ( $query_result->have_posts() ) {

			while ( $query_result->have_posts() ) {

				$query_result->the_post();
				//single post html
				?>
                <div class="post-info">
                    <h4><?php the_title(); ?></h4>
                </div>
				<?php
			}
			wp_reset_postdata();
		}
		$html = ob_get_clean();

		return $html;
	}

	?>