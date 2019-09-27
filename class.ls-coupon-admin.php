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
			$columns['customer'] = '客人微信昵称';
			$columns['used'] = '使用状态';
			$columns['coupon_title'] = '优惠';
			unset($columns['title']);
			unset($columns['date']);
			$columns['claim_date'] = '领取日期';
			return $columns;
		});

		add_action('manage_code_posts_custom_column', function ($column_name) {
			global $post;
			switch ($column_name) {
				case 'code':
					echo $post->post_name;
					break;
				case 'customer':
					echo get_field('customer_nickname', $post->ID);
						// . ' (' . get_field('openid', $post->ID) . ')'
					break;
				case 'used':
					$used = get_field('used', $post->ID);
					if (!$used) {
						echo '未使用';
					} else {
						echo get_field('used_time', $post->ID) . ' 使用';
						echo '<br>' . get_post(get_post_meta($post->ID, 'used_shop', true))->post_title . ' ';
						echo ' ' . get_field('scanned_manager', $post->ID)->display_name . ' 核销';
					}
					break;
				case 'coupon_title' :
					$coupon = get_post(get_post_meta($post->ID, 'coupon', true));
					echo '<a href="' . site_url('wp-admin/post.php?post=' . $coupon->ID . '&action=edit') . '" target="_blank">' . $coupon->post_title . '</a>';
					break;
				case 'claim_date':
					echo get_the_date('', $post->ID);
					break;
				default;
			}
		});

		add_filter('manage_edit-code_sortable_columns', function ($sortable_columns) {
			$sortable_columns['used'] = 'used';
			$sortable_columns['claim_date'] = 'date';
			return $sortable_columns;
		});


		add_filter( 'manage_users_columns', function ( $column ) {
			$column['shop'] = '门店';
			unset($column['email']);
			unset($column['posts']);
			return $column;
		} );


		add_filter( 'manage_users_custom_column', function ( $val, $column_name, $user_id ) {
			switch($column_name) {
				case 'shop' :
					$shop_id = get_user_meta($user_id, 'shop', true);
					return get_the_title($shop_id);
				default:
			}
		}, 10, 3 );

		add_action( 'admin_menu', function () {
			global $menu, $submenu;
			if (isset($submenu['edit.php?post_type=code'])) {
				$submenu['edit.php?post_type=code'][5][2] = 'edit.php?post_type=code&orderby=used&order=desc';
			}
		}, 100 );

		add_action( 'pre_get_posts', function ( $query ) {
			if ( $query->is_main_query() && $query->get('post_type') === 'code' ) {
				$orderby = $query->get('orderby');

				if ($orderby === 'used') {
					$query->set( 'meta_key', 'used_time' );
					$query->set( 'orderby', 'meta_value' );
				}
			}
		}, 1);

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

		add_action( 'add_meta_boxes', function($post_type, $post) {
			if (!in_array($post_type, array('shop', 'coupon'))) return;
			add_meta_box(
				'qr-code',
				__( '专用小程序二维码' ),
				function() use ($post_type, $post){
					?>
					<img src="<?=generate_weapp_qrcode($post_type, $post->ID)?>" style="width:100%">
					<?php
				},
				null,
				'side',
				'default'
			);
		}, 10, 2 );

		add_role('manager', '店员');

		add_filter ('sanitize_user', function ($username, $raw_username, $strict) {
			$username = wp_strip_all_tags( $raw_username );
			$username = remove_accents( $username );
			// Kill octets
			$username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
			$username = preg_replace( '/&.+?;/', '', $username ); // Kill entities

			// 网上很多教程都是直接将$strict赋值false，
			// 这样会绕过字符串检查，留下隐患
			if ($strict) {
				$username = preg_replace ('|[^a-z\p{Han}0-9 _.\-@]|iu', '', $username);
			}

			$username = trim( $username );
			// Consolidate contiguous whitespace
			$username = preg_replace( '|\s+|', ' ', $username );

			return $username;
		}, 10, 3);

		add_action('restrict_manage_posts', function() {

			global $current_screen;

			if ($current_screen->post_type == 'code') {
				?>
				<select name="used">
					<option value=""<?php if (empty($_GET['used'])){ ?> selected<?php } ?>>已使用</option>
					<option value="false"<?php if ($_GET['used']==='false'){ ?> selected<?php } ?>>未使用</option>
				</select>
				<?php
			}
		});

		add_action('restrict_manage_posts', function() {

			global $current_screen;

			if ($current_screen->post_type == 'code') {
				?>
				<select name="used_shop">
					<option value=""<?php if(empty($_GET['used_shop'])){ ?> selected<?php } ?>>所有门店</option>
					<?php foreach (get_posts('post_type=shop&posts_per_page=-1') as $shop_post): ?>
					<option value="<?=$shop_post->ID?>"<?php if($_GET['used_shop']==$shop_post->ID){ ?> selected<?php } ?>><?=get_the_title($shop_post->ID)?></option>
					<?php endforeach; ?>
				</select>
				<?php
			}
		});

		add_filter('parse_query', function ($query) {
			if (is_admin() && $query->query['post_type'] === 'code') {
				$qv = &$query->query_vars;
				$qv['meta_query'] = array();
				if (empty($_GET['used'])) {
					$qv['meta_query'][] = array(
						'field' => 'used',
						'value' => '1'
					);
				} else if ($_GET['used'] === 'false') {
					$qv['meta_query'][] = array(
						'field' => 'used',
						'compare' => 'NOT EXISTS'
					);
				}
				if (!empty($_GET['used_shop'])) {
					$qv['meta_query'][] = array(
						'field' => 'used_shop',
						'value' => $_GET['used_shop']
					);
				}
			}
		});

	}

}
