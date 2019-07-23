<?php

class LS_Coupon_REST_Banner_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'v1/ls-coupon';
		$this->rest_base = 'banner';
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_banners' ),
			)
		) );

	}

	/**
	 * Get a list of banners
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_banners( $request ) {

		$parameters = array('category_name' => 'home-banner', 'posts_per_page' => -1);

		$posts = get_posts($parameters);

		$banners = array_map(function (WP_Post $post) {
			$banner = array(
				'id' => $post->ID,
				'name' => get_the_title($post->ID),
				'imageUrl' => get_the_post_thumbnail_url($post->ID, 'full'),
				'couponId' => get_post_meta($post->ID, 'coupon_id', true),
				'shopId' => get_post_meta($post->ID, 'shop_id', true),
			);

			return (object) $banner;

		}, $posts);

		return rest_ensure_response($banners);
	}

}
