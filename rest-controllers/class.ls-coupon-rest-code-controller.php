<?php

class LS_Coupon_REST_Code_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'ls-coupon/v1';
		$this->rest_base = 'code';
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_codes' ),
			), array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'post_code' ),
			), array(
				'methods' => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_code' ),
			)
		) );

	}

	/**
	 * Get a list of codes
	 *
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_codes( $request ) {

		if (!$openid = $request->get_param('openid')) {
			return rest_ensure_response(new WP_Error(400, 'Missing openid.'));
		}

		$parameters = array('post_type' => 'code', 'limit' => -1, 'post_status' => 'any', 'meta_query' => array(
			array('key' => 'openid', 'value' => $openid)
		));

		$posts = get_posts($parameters);

		$codes = array_map(function (WP_Post $post) {
			$coupon_id = get_post_meta($post->ID, 'coupon', true);

			$code = array(
				'id' => $post->ID,
				'codeString' => $post->post_name,
				'couponId' => $coupon_id,
				// 'expires_at' => '',
				'coupon' => array(
					'id' => $coupon_id,
					'desc' => get_field('desc', $coupon_id),
					'shops' => array_map(function($shop_post) {
						return array(
							'id' => $shop_post->ID,
							'name' => get_the_title($shop_post->ID),
							'address' => get_field('address', $shop_post->ID),
							'phone' => get_field('phone', $shop_post->ID),
						);
					}, get_field('shops', $coupon_id)),
					'allShop' => !!get_field('all_shop', $coupon_id),
				)
			);
			return (object) $code;
		}, $posts);

		return rest_ensure_response($codes);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function post_code( $request ) {

		$body = $request->get_json_params();

		// validate user
		if (!array_key_exists('openid', $body)) {
			return rest_ensure_response(new WP_Error(400, 'Missing openid.'));
		}

		$openid = $body['openid'];

		if (!array_key_exists('couponIds', $body) || !is_array($body['couponIds'])) {
			return rest_ensure_response(new WP_Error(400, 'Missing coupon.'));
		}

		$codes = array();

		foreach ($body['couponIds'] as $coupon_id) {

			// TODO
			// validate coupon
			// valid duplicate coupon code

			$code_string = crc32(sha1($openid . ',' . $coupon_id));

			$code_post_exists = get_page_by_path($code_string, 'OBJECT', 'code');

			if ($code_post_exists) {
				return rest_ensure_response(new WP_Error(409, 'Code exists with this openid + coupon.'));
			}

			$code_id = wp_insert_post(array(
				'post_type' => 'code',
				'post_status' => 'private',
				'post_title' => $code_string
			));

			add_post_meta($code_id, 'coupon', $coupon_id);
			add_post_meta($code_id, 'openid', $openid);
			add_post_meta($code_id, 'expires_at', time() + 86400*30);

			$code_post = get_post($code_id);

			$code = array(
				'id' => $code_id,
				'codeString' => $code_post->post_name,
				'couponId' => $coupon_id,
				// 'expires_at' => '',
				'coupon' => array(
					'id' => $coupon_id,
					'desc' => get_field('desc', $coupon_id),
					'shops' => array_map(function($shop_post) {
						return array(
							'id' => $shop_post->ID,
							'name' => get_the_title($shop_post->ID),
							'address' => get_field('address', $shop_post->ID),
							'phone' => get_field('phone', $shop_post->ID),
						);
					}, get_field('shops', $coupon_id)),
					'allShop' => !!get_field('all_shop', $coupon_id),
				)
			);

			$codes[] = $code;
		}

		return rest_ensure_response($codes);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public static function delete_code( $request ) {

	}

}
