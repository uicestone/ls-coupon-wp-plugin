<?php

class LS_Coupon_REST_Coupon_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'ls-coupon/v1';
		$this->rest_base = 'coupon';
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_coupons' ),
			), array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'post_coupon' ),
			), array(
				'methods' => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_coupon' ),
			)
		) );

	}

	/**
	 * Get a list of coupons
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_coupons( $request ) {

		$parameters = array('post_type' => 'coupon', 'limit' => -1);

		$posts = get_posts($parameters);

		$coupons = array_map(function (WP_Post $post) {
			$coupon = array(
				'id' => $post->ID,
				'desc' => get_field('desc', $post->ID),
				'shops' => get_field('shops', $post->ID),
				'allShop' => get_field('all_shop', $post->ID),
			);
			return (object) $coupon;
		}, $posts);

		return rest_ensure_response($coupons);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function post_coupon( $request ) {

		$body = $request->get_body_params();

		$coupon_id = wp_insert_post(array(
			'post_type' => 'coupon',
			'post_status' => 'publish'
		));

		foreach ($body as $key => $value) {
			add_post_meta($coupon_id, $key, $value);
		}

		return rest_ensure_response(array(
			'id' => $coupon_id,
			'type' => get_post_meta($coupon_id, 'type', true)
		));
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function delete_coupon( $request ) {

	}

}
