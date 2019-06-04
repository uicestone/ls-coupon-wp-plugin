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
			'supports' => array('title'),
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

	}

}
