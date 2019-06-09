<?php

class LS_Coupon_REST_Shop_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'v1/ls-coupon';
		$this->rest_base = 'shop';
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_shops' ),
			)
		) );

	}

	/**
	 * Get a list of shops
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_shops( $request ) {

		$parameters = array('post_type' => 'shop', 'limit' => -1);

		$posts = get_posts($parameters);

		$shops = array_map(function (WP_Post $post) {

			$valid_coupons = array_map(function($post) {
				return array(
					'id' => $post->ID,
					'desc' => get_field('desc', $post->ID),
					'all_shop' => !!get_field('all_shop', $post->ID),
				);
			}, get_posts(array(
				'post_type' => 'coupon',
				'posts_per_page' => -1,
				'meta_query' => array(
					'relation' => 'OR',
					'shops' => array(
						'key' => 'shops',
						'value' => '%"' . $post->ID . '"%',
						'compare' => 'LIKE'
					),
					'all_shops' => array(
						'key' => 'all_shops',
						'value' => '1'
					),
				)
			)));

			$shop = array(
				'id' => $post->ID,
				'name' => get_the_title($post->ID),
				'address' => get_field('address', $post->ID),
				'phone' => get_field('phone', $post->ID),
				'validCoupons' => $valid_coupons
			);

			return (object) $shop;
		}, $posts);

		return rest_ensure_response($shops);
	}

}
