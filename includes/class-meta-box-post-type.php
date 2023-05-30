<?php
/**
 * CMB2 Metabox Post Type.
 *
 * @since  0.0.1
 *
 * @category  WordPress_Plugin
 * @package   CMB2 Admin Extension
 * @author    twoelevenjay
 * @license   GPL-2.0+
 */

if ( ! class_exists( 'CMB2_Meta_Box_Post_Type' ) ) {

	/**
	 * Class CMB2_Meta_Box_Post_Type.
	 */
	class CMB2_Meta_Box_Post_Type {

		/**
		 * Field prefix.
		 *
		 * @var string
		 */
		private $prefix = '_cmb2_';

		/**
		 * Settings Page hook.
		 *
		 * @var string
		 */
		protected $settings_page = '';

		/**
		 * Initiate CMB2 Admin Extension object.
		 *
		 * @todo For now plugin will use one main object, will consider 3 seperate objects in the future.
		 * @todo Comment.
		 *
		 * @since 0.0.1
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'init_post_type' ) );
			add_action( 'add_meta_boxes', array( $this, 'remove_meta_box_slugdiv' ) );
			add_action( 'admin_head', array( $this, 'hide_edit_slug_bar' ) );
			add_action( 'cmb2_init', array( $this, 'init_custom_field_settings' ) );
			add_action( 'cmb2_init', array( $this, 'init_meta_box_settings' ) );
			add_filter( 'cmb2_row_classes', array( $this, 'show_hide_classes' ), 10, 2 );
		}

		/**
		 * Create the Metabox post type.
		 *
		 * @since  0.0.1
		 */
		public function init_post_type() {

			$labels = array(
				'name'                => _x( 'Metabox', 'Post Type General Name'),
				'singular_name'       => _x( 'Metabox', 'Post Type Singular Name'),
				'menu_name'           => __( '字段'),
				'name_admin_bar'      => __( 'Metabox'),
				'parent_item_colon'   => __( 'Metabox'),
				'all_items'           => __( '所有Metabox'),
				'add_new_item'        => __( '添加新Metabox'),
				'add_new'             => __( '添加新Metabox'),
				'new_item'            => __( '新Metabox'),
				'edit_item'           => __( '编辑Metabox'),
				'update_item'         => __( '更新Metabox'),
				'view_item'           => __( '查看Metabox'),
				'search_items'        => __( '搜索Metabox'),
				'not_found'           => __( '未找到'),
				'not_found_in_trash'  => __( '在垃圾桶里找不到'),
			);

			$args = array(
				'label'               => __( 'meta_box'),
				'description'         => __( '创建自定义元框和字段'),
				'labels'              => $labels,
				'supports'            => array(),
				'hierarchical'        => false,
				'rewrite'             => true,
				'supports'            => array( 'title' ),
				'public'              => true,
				'menu_position'       => 100,
				'menu_icon'           => 'dashicons-feedback',
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => false,
				'capability_type'     => 'page',
			);

			$args['show_ui']      = false;
			$args['show_in_menu'] = false;

			if ( $this->is_cmb2_allowed() ) {

				$args['show_ui']      = true;
				$args['show_in_menu'] = true;

			}
			register_post_type( 'meta_box', $args );
		}

		/**
		 * Set up the plugin settings page.
		 *
		 * @since  0.0.1
		 */
		public function remove_meta_box_slugdiv() {

			remove_meta_box( 'slugdiv', 'page', 'normal' );

		}

		/**
		 * Set up the plugin settings page.
		 *
		 * @since  0.0.1
		 */
		public function hide_edit_slug_bar() {

			global $post;

			if ( isset( $post->post_type ) && $post->post_type === 'meta_box' ) {

				echo '<style type="text/css"> #edit-slug-box, #minor-publishing { display: none; }</style>';

			}

		}


		/**
		 * Determine if current user has permission to CMB2 view plugins.
		 *
		 * @since  0.0.1
		 */
		private function is_cmb2_allowed() {

			$cmb2_settings = get_option( '_cmb2_settings' );

			if ( empty( $cmb2_settings ) ) {
				// No settings saved.
				return true;
			}

			$current_user  = wp_get_current_user();
			$allowed_users = isset( $cmb2_settings['_cmb2_user_multicheckbox'] ) ? $cmb2_settings['_cmb2_user_multicheckbox'] : array();

			if ( empty( $allowed_users ) || in_array( $current_user->ID, $allowed_users, true ) ) {

				return true;

			}
			return false;
		}

		/**
		 * Pass each item in an array through strpos().
		 *
		 * @since  0.0.8
		 * @todo Improve parameter documentation.
		 *
		 * @param string $field_id      Field ID.
		 * @param array  $field_classes CSS Classes.
		 * @param string $classes       CSS Classes to add.
		 */
		public function conditionally_add_class( $field_id, $field_classes, $classes ) {

			foreach ( $field_classes as $field => $class ) {
				if ( strpos( $field_id, $field ) !== false ) {
					return $classes . ' ' . $class;
				}
			}
			return $classes;

		}

		/**
		 * Add show/hide options callback.
		 *
		 * @since  0.0.8
		 * @todo Improve parameter documentation.
		 *
		 * @param array  $classes CSS Classes.
		 * @param object $field   CMB2 Field object.
		 */
		public function show_hide_classes( $classes, $field ) {

			$screen = get_current_screen();
			if ( $screen->post_type === 'meta_box' ) {
				$field_classes = array(
					'repeatable_checkbox'      => 'cmb_hide_field text text_small text_medium text_email text_url text_money textarea textarea_small textarea_code text_date text_timeselect_timezone text_date_timestamp text_datetime_timestamp text_datetime_timestamp_timezone color_picker select multicheck multicheck_inline',
					'protocols_checkbox'       => 'cmb_hide_field text_url',
					'currency_text'            => 'cmb_hide_field text_money',
					'date_format'              => 'cmb_hide_field text_date text_date_timestamp',
					'time_format'              => 'cmb_hide_field text_time text_datetime_timestamp text_datetime_timestamp_timezone',
					'time_zone_key_select'     => 'cmb_hide_field ',
					'options_textarea'         => 'cmb_hide_field radio radio_inline select multicheck multicheck_inline',
					'tax_options_radio_inline' => 'cmb_hide_field taxonomy_radio taxonomy_radio_inline taxonomy_select taxonomy_multicheck taxonomy_multicheck_inline',
					'no_terms_text'            => 'cmb_hide_field taxonomy_radio taxonomy_radio_inline taxonomy_select taxonomy_multicheck taxonomy_multicheck_inline',
					'none_checkbox'            => 'cmb_hide_field radio radio_inline select',
					'select_all_checkbox'      => 'cmb_hide_field multicheck multicheck_inline taxonomy_multicheck taxonomy_multicheck_inline',
					'add_upload_file_text'     => 'cmb_hide_field file',
					'default_value_text'       => 'default_value',
				);

				$classes = $this->conditionally_add_class( $field->args['_id'], $field_classes, $classes );
			}
			return $classes;

		}

		/**
		 * Get users for the options on the settings page.
		 *
		 * @since  0.0.6
		 */
		public function tax_options() {

			$taxonomies  = get_taxonomies( array( 'public' => true ), 'objects' );
			$tax_options = array();
			foreach ( $taxonomies as $taxonomy ) {

				$tax_options[ $taxonomy->name ] = $taxonomy->labels->name;

			}
			return $tax_options;

		}

		/**
		 * Add custom Metabox to the Metabox post type.
		 *
		 * @since  0.0.1
		 */
		public function init_meta_box_settings() {

			$prefix            = $this->prefix;
			$post_type_objects = get_post_types( '', 'object' );
			$post_types        = array();

			foreach ( $post_type_objects as $post_type_object ) {
				if ( $post_type_object->show_ui && $post_type_object->name !== 'meta_box' ) {
					$post_types[ $post_type_object->name ] = $post_type_object->label;
				}
			}

			/**
			 * Initiate the metabox.
			 */
			$cmb = new_cmb2_box( array(
				'id'            => 'metabox_settings',
				'title'         => __( 'Metabox设置'),
				'object_types'  => array( 'meta_box' ), // Post type.
				'context'       => 'side',
				'priority'      => 'low',
				'show_names'    => true,
			) );

			$cmb->add_field( array(
				'name'    => __( '文章类型'),
				'desc'    => __( '选中您要将此元盒子添加到的帖子类型。'),
				'id'      => $prefix . 'post_type_multicheckbox',
				'type'    => 'multicheck',
				'options' => $post_types,
				'inline'  => true,
			) );

			$cmb->add_field( array(
				'name'    => __( '文章IDs'),
				'desc'    => __( '输入要将此元盒子添加到的帖子ID。 用逗号分隔多个条目。 留空以使元盒子显示在所有帖子ID上。'),
				'id'      => $prefix . 'post_id_text',
				'type'    => 'text',
				'inline'  => true,
			) );

			$cmb->add_field( array(
				'name'    => __( '优先权'),
				'desc'    => __( '这是为了控制您的Metabox的显示顺序。'),
				'id'      => $prefix . 'priority_radio',
				'type'    => 'radio',
				'default' => 'high',
				'options' => array(
					'high'    => __( '高'),
					'core'    => __( '核心'),
					'default' => __( '默认'),
					'low'     => __( '低'),
				),
				'inline'  => true,
			) );

			$cmb->add_field( array(
				'name'    => __( '上下文'),
				'desc'    => __( '这附加控制用于定位Metabox。高级显示在正常之后。将元盒子放在右侧边栏中。'),
				'id'      => $prefix . 'context_radio',
				'type'    => 'radio',
				'default' => 'advanced',
				'options' => array(
					'advanced' => __( '高级'),
					'normal'   => __( '标准'),
					'side'     => __( '右侧'),
				),
				'inline'  => true,
			) );

			$cmb->add_field( array(
				'name'    => __( '显示名'),
				'desc'    => __( '在左侧显示字段名'),
				'id'      => $prefix . 'show_names',
				'type'    => 'checkbox',
				'default' => 'on',
			) );

			$cmb->add_field( array(
				'name' => __( '禁用CMB2样式'),
				'desc' => __( '选中以禁用CMB样式表'),
				'id'   => $prefix . 'disable_styles',
				'type' => 'checkbox',
			) );

			$cmb->add_field( array(
				'name' => __( '默认情况下关闭'),
				'desc' => __( '选中以使Metabox在默认情况下保持关闭状态'),
				'id'   => $prefix . 'closed',
				'type' => 'checkbox',
			) );

			$cmb->add_field( array(
				'name' => __( '可重复组'),
				'desc' => __( '将这些字段添加到可重复组中。'),
				'id'   => $prefix . 'repeatable_group',
				'type' => 'checkbox',
			) );

			$cmb->add_field( array(
				'name'   => __( '描述 '),
				'desc'   => __( '可重复组的简短说明。'),
				'id'     => $prefix . 'group_description',
				'type'   => 'textarea_small',
			) );

			$cmb->add_field( array(
				'name'   => __( '空名'),
				'desc'   => __( '用作每行条目名称的文本(即条目1)。 默认为“Entry”一词。'),
				'id'     => $prefix . 'entry_name',
				'type'   => 'text',
				'inline' => true,
			) );

			$cmb->add_field( array(
				'name'        => '原生使用',
				'description' => '这是用于获取发布meta的WordPress函数。 这应该被视为一个起点。',
				'id'          => $prefix . 'get_post_meta_repeatable',
				'type'        => 'textarea_code',
				'save_field'  => false,
				'attributes'  => array(
					'class'    => 'get_post_meta_repeatable',
					'rows'     => 2,
					'readonly' => 'readonly',
					'disabled' => 'disabled',
				),
			) );

			$cmb->add_field( array(
				'name'        => 'CMB2AE用法',
				'description' => '这是用于获取发布元的CMB2管理扩展函数。这应该被视为一个起点。',
				'id'          => $prefix . 'cmbf_repeatable',
				'type'        => 'textarea_code',
				'save_field'  => false,
				'attributes'  => array(
					'class'    => 'cmbf_repeatable',
					'rows'     => 2,
					'readonly' => 'readonly',
					'disabled' => 'disabled',
				),
			) );
		}

		/**
		 * Add custom fields to the Metabox post type
		 *
		 * @since  0.0.1
		 */
		public function init_custom_field_settings() {

			$prefix    = $this->prefix;
			$cmb_group = new_cmb2_box( array(
				'id'           => $prefix . 'custom_fields',
				'title'        => __( '自定义字段设置'),
				'object_types' => array( 'meta_box' ),
			) );

			$group_field_id = $cmb_group->add_field( array(
				'id'          => $prefix . 'custom_field',
				'type'        => 'group',
				'description' => __( '添加要在此元盒子中显示的自定义字段。'),
				'options'     => array(
					'group_title'   => __( '字段 {#}'),
					'add_button'    => __( '添加另一个字段'),
					'remove_button' => __( '删除字段'),
					'sortable'      => true, // Beta.
				),
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'       => __( '字段名' ),
				'desc'       => __( '添加字段名' ),
				'id'         => $prefix . 'name_text',
				'type'       => 'text',
				'attributes' => array(
					'class' => 'field_name',
				),
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'        => '原生用法',
				'description' => '这是用于获取POST META的WordPress函数。 将此代码复制并粘贴到模板文件中，以便在前端使用此元数据。',
				'id'          => $prefix . 'get_post_meta',
				'type'        => 'textarea_code',
				'save_field'  => false,
				'attributes'  => array(
					'class'    => 'get_post_meta',
					'rows'     => 1,
					'readonly' => 'readonly',
					'disabled' => 'disabled',
				),
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'        => 'CMB2AE用法',
				'description' => '这是用于获取发布元的CMB2管理扩展函数。将此代码复制并粘贴到模板文件中，以便在前端使用此元数据。',
				'id'          => $prefix . 'cmbf',
				'type'        => 'textarea_code',
				'save_field'  => false,
				'attributes'  => array(
					'class'    => 'cmbf',
					'rows'     => 1,
					'readonly' => 'readonly',
					'disabled' => 'disabled',
				),
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name' => __( '描述'),
				'desc' => __( '添加字段描述'),
				'id'   => $prefix . 'decription_textarea',
				'type' => 'textarea_small',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'             => __( '字段类型'),
				'desc'             => __( '选择要显示的字段类型。') . '</br>' . __( '有关字段的完整列表，请访问<a href="https://github.com/WebDevStudios/CMB2/wiki/Field-Types">文档</a>.') . '</br>* ' . __( '不能作为可重复字段使用。') . '</br>† ' . __( '使用FILE_LIST表示可重复。'),
				'id'               => $prefix . 'field_type_select',
				'attributes'       => array(
					'class' => 'cmb2_select field_type_select',
				),
				'type'             => 'select',
				'show_option_none' => false,
				'options'          => array(
					'title'                            => 'title: ' . __( '任意标题字段') . ' *',
					'text'                             => 'text: ' . __( '文本'),
					'text_small'                       => 'text_small: ' . __( '小文本'),
					'text_medium'                      => 'text_medium: ' . __( '文本媒体'),
					'text_email'                       => 'text_email: ' . __( '邮箱'),
					'text_url'                         => 'text_url: ' . __( 'URL'),
					'text_money'                       => 'text_money: ' . __( '货币'),
					'textarea'                         => 'textarea: ' . __( '文本区'),
					'textarea_small'                   => 'textarea_small: ' . __( '小文本区'),
					'textarea_code'                    => 'textarea_code: ' . __( '文本区域代码'),
					'text_date'                        => 'text_date: ' . __( '日期选取器'),
					'text_time'                        => 'text_time: ' . __( '时间选取器'),
					'select_timezone'                  => 'select_timezone: ' . __( '时区下拉列表'),
					'text_date_timestamp'              => 'text_date_timestamp: ' . __( '日期选取器(UNIX timestamp)'),
					'text_datetime_timestamp'          => 'text_datetime_timestamp: ' . __( '文本日期/时间选取器组合(UNIX timestamp)'),
					'text_datetime_timestamp_timezone' => 'text_datetime_timestamp_timezone: ' . __( '文本日期/时间选取器/时区组合(serialized DateTime object)'),
					'color_picker'                     => 'colorpicker: ' . __( '颜色选择器'),
					'radio'                            => 'radio: ' . __( '单选按钮') . ' *',
					'radio_inline'                     => 'radio_inline: ' . __( '单选按钮内联') . ' *',
					'taxonomy_radio'                   => 'taxonomy_radio: ' . __( '分类单选按钮') . ' *',
					'taxonomy_radio_inline'            => 'taxonomy_radio_inline: ' . __( '内联分类单选按钮') . ' *',
					'select'                           => 'select: ' . __( ' 选择 '),
					'taxonomy_select'                  => 'taxonomy_select: ' . __( '分类选择') . ' *',
					'checkbox'                         => 'checkbox: ' . __( '校验框') . ' *',
					'multicheck'                       => 'multicheck: ' . __( '多个复选框'),
					'multicheck_inline'                => 'multicheck_inline: ' . __( '多个复选框内联'),
					'taxonomy_multicheck'              => 'taxonomy_multicheck: ' . __( '多个分类复选框') . ' *',
					'taxonomy_multicheck_inline'       => 'taxonomy_multicheck_inline: ' . __( '多个分类复选框内联') . ' *',
					'wysiwyg'                          => 'wysiwyg: ' . __( '(TinyMCE编辑器)') . ' *',
					'file'                             => 'file: ' . __( 'Image/File 上传') . ' *†',
					'file_list'                        => 'file_list: ' . __( 'Image/File 列表上传'),
					'oembed'                           => 'oembed: ' . __( '转换嵌入的URL(Instagram、Twitter、YouTube等。嵌入到索引中)'),
				),
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name' => __( '可重复的'),
				'desc' => __( '选中此框可使该字段可重复。标有“*”的字段类型不可重复。'),
				'id'   => $prefix . 'repeatable_checkbox',
				'type' => 'checkbox',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'    => __( '协议'),
				'desc'    => __( '选中每个允许的协议对应的框。 如果您不确定，则什么都不做，所有协议都将被允许。'),
				'id'      => $prefix . 'protocols_checkbox',
				'type'    => 'multicheck_inline',
				'options' => array(
					'http'   => 'http',
					'https'  => 'https',
					'ftp'    => 'ftp',
					'ftps'   => 'ftps',
					'mailto' => 'mailto',
					'news'   => 'news',
					'irc'    => 'irc',
					'gopher' => 'gopher',
					'nntp'   => 'nntp',
					'feed'   => 'feed',
					'telnet' => 'telnet',
				),
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'    => __( '货币符号'),
				'desc'    => __( '货币符号默认 "$".'),
				'id'      => $prefix . 'currency_text',
				'type'    => 'text_small',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'    => __( '日期字段'),
				'desc'    => __( '默认:') . ' "m/d/Y". ' . __( 'See <a target="_blank" href="http://php.net/manual/en/function.date.php">php.net/manual/en/function.date.php</a>.'),
				'id'      => $prefix . 'date_format',
				'type'    => 'text_small',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'    => __( '时间字段'),
				'desc'    => __( '默认:') . ' "h:i A". ' . __( 'See <a target="_blank" href="http://php.net/manual/en/function.date.php">php.net/manual/en/function.date.php</a>.'),
				'id'      => $prefix . 'time_format',
				'type'    => 'text_small',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name' => __( '选项'),
				'desc' => __( '如果您的字段类型需要手动选项，请每行添加一个选项。 键入 value, 然后用逗号分隔名称。<br>Example:<br>sml,Small<br>med,Medium<br>lrg,Large'),
				'id'   => $prefix . 'options_textarea',
				'type' => 'textarea_small',
			) );

			$tax_options = $this->tax_options();
			reset( $tax_options );
			$default_tax_options = key( $tax_options );
			$cmb_group->add_group_field( $group_field_id, array(
				'name'    => __( '分类选项'),
				'id'      => $prefix . 'tax_options_radio_inline',
				'type'    => 'radio_inline',
				'options' => $this->tax_options(),
				'default' => $default_tax_options,
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'    => __( '无术语文本'),
				'desc'    => __( '输入文本以更改在未找到术语时显示的文本。') . '</br>' . __( 'Default:') . ' "' . __( 'No terms') . '".',
				'id'      => $prefix . 'no_terms_text',
				'type'    => 'text_small',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name' => __( '包含"none"选项'),
				'desc' => __( '选中此框可在此字段中包括 "none" 选项。'),
				'id'   => $prefix . 'none_checkbox',
				'type' => 'checkbox',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name' => __( '禁用全选'),
				'desc' => __( '选中此框可禁用此字段的全选按钮。'),
				'id'   => $prefix . 'select_all_checkbox',
				'type' => 'checkbox',
			) );

			$cmb_group->add_group_field( $group_field_id, array(
				'name'    => __( '按钮文本'),
				'desc'    => __( '输入文本以更改上载按钮文本.') . '</br>' . __( '默认:') . ' "' . __( '添加或者上传文件') . '".',
				'id'      => $prefix . 'add_upload_file_text',
				'type'    => 'text_small',
			) );
		}
	}

	$cmb2_meta_box_post_type = new CMB2_Meta_Box_Post_Type();
}
