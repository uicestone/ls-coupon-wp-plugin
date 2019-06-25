<?php

class LS_Coupon_Admin {

	public static function init() {
		self::register_post_types();
		self::manage_admin_columns();
	}


	protected static function register_post_types() {

		// register_taxonomy_for_object_type('category', 'attachment');
		// register_taxonomy_for_object_type('post_tag', 'attachment');

		add_post_type_support('attachment', '');

		register_post_type('shop', array(
			'label' => '门店',
			'labels' => array(
				'all_items' => '所有门店',
				'add_new' => '添加门店',
				'add_new_item' => '新门店',
				'not_found' => '未找到门店'
			),
			'public' => true,
			'supports' => array('title'),
			'menu_icon' => 'dashicons-store',
			'has_archive' => true
		));

		register_post_type('coupon', array(
			'label' => '优惠',
			'labels' => array(
				'all_items' => '所有优惠',
				'add_new' => '添加优惠',
				'add_new_item' => '新优惠',
				'not_found' => '未找到优惠'
			),
			'public' => true,
			'supports' => array('title', 'editor', 'thumbnail'),
			'menu_icon' => 'dashicons-megaphone',
			'has_archive' => true
		));

		register_post_type('code', array(
			'label' => '券码',
			'labels' => array(
				'all_items' => '所有券码',
				'add_new' => '添加券码',
				'add_new_item' => '新券码',
				'not_found' => '未找到券码'
			),
			'public' => true,
			'capability_type' => 'post',
			'capabilities' => array(
				'create_posts' => false
			),
			'map_meta_cap' => true,
			'menu_icon' => 'dashicons-admin-page'
		));
	}

	protected static function manage_admin_columns() {

		add_filter('manage_shop_posts_columns', function ($columns) {
			$columns['phone'] = '电话';
			$columns['address'] = '地址';
			return $columns;
		});

		add_action('manage_shop_posts_custom_column', function ($column_name) {
			global $post;
			switch ($column_name) {
				case 'phone' :
					echo get_post_meta($post->ID, 'phone', true);
					break;
				case 'address' :
					echo get_post_meta($post->ID, 'address', true);
					break;
				default;
			}
		});

		add_filter('manage_coupon_posts_columns', function ($columns) {
			$columns['desc'] = '描述';
			$columns['shops'] = '关联门店';
			return $columns;
		});

		add_action('manage_coupon_posts_custom_column', function ($column_name) {
			global $post;
			switch ($column_name) {
				case 'desc' :
					echo get_post_meta($post->ID, 'desc', true);
					break;
				case 'shops' :
					$all_shops = get_field('all_shops', $post->ID);

					if ($all_shops) {
						echo '全部门店';
					} else {
						$shops = get_field('shops', $post->ID);
						echo implode(', ', array_column($shops, 'post_title'));
					}
					break;
				default;
			}
		});

		add_filter('manage_code_posts_columns', function ($columns) {
			$columns['code'] = '券码';
			$columns['openid'] = '微信Open ID';
			$columns['used'] = '已使用';
			$columns['coupon_title'] = '优惠';
			unset($columns['title']);
			unset($columns['date']);
			return $columns;
		});

		add_action('manage_code_posts_custom_column', function ($column_name) {
			global $post;
			switch ($column_name) {
				case 'code':
					echo $post->post_name;
					break;
				case 'openid':
					echo get_field('openid', $post->ID);
					break;
				case 'used':
					$used = get_field('used', $post->ID);
					if (!$used) {
						echo '未使用';
					} else {
						echo get_post(get_post_meta($post->ID, 'used_shop', true))->post_title . ' ';
						echo date('Y-m-d H:i:s', get_field('used_time', $post->ID) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
					}
					break;
				case 'coupon_title' :
					$coupon = get_post(get_post_meta($post->ID, 'coupon', true));
					echo '<a href="' . site_url('wp-admin/post.php?post=' . $coupon->ID . '&action=edit') . '" target="_blank">' . $coupon->post_title . '</a>';
					break;
				default;
			}
		});

		/**
		 * Convert values of ACF core date time pickers from Y-m-d H:i:s to timestamp
		 * @param  string $value   unmodified value
		 * @param  int    $post_id post ID
		 * @param  object $field   field object
		 * @return string          modified value
		 */
		function acf_save_as_timestamp( $value, $post_id, $field  ) {
			if( $value ) {
				$value = strtotime( $value ) - get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
			}

			return $value;
		}

		add_filter( 'acf/update_value/type=date_time_picker', 'acf_save_as_timestamp', 10, 3 );

		/**
		 * Convert values of ACF core date time pickers from timestamp to Y-m-d H:i:s
		 * @param  string $value   unmodified value
		 * @param  int    $post_id post ID
		 * @param  object $field   field object
		 * @return string          modified value
		 */
		function acf_load_as_timestamp( $value, $post_id, $field  ) {
			if( $value ) {
				$value = date( 'Y-m-d H:i:s', (int)$value + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			}

			return $value;
		}

		add_filter( 'acf/load_value/type=date_time_picker', 'acf_load_as_timestamp', 10, 3 );

	}

}
