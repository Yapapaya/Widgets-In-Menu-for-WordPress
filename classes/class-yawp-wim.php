<?php

if ( ! class_exists( 'YAWP_WIM' ) ) {
	class YAWP_WIM {

		
		/**
		 *
		 * @var string A string prefix for html element attributes 
		 */
		public $attr_prefix;

		/**
		 *
		 * @var string The text-domain
		 */
		public $domain = 'yawp-wim';

		/**
		 * Sets up variables
		 */
		public function __construct() {

			$default_prefix = YAWP_WIM_PREFIX;
			/**
			 * Filters the prefix used in class/id attributes in html display. 
			 * 
			 * @since 0.1.0
			 * 
			 * @param string $default_prefix The default prefix: 'yawp_wim'
			 */
			$this->attr_prefix = apply_filters( 'yawp_wim_attribute_prefix', $default_prefix );
		}

		/**
		 * Hooks to the necessary actions and filters
		 */
		public function init() {
			// initialise translations
			add_action( 'plugins_loaded', array( $this, 'localise' ) );

			// hook the sidebar registration
			add_action( 'widgets_init', array( $this, 'sidebar' ) );

			// hook into the edit menus admin screen
			add_action( 'admin_init', array( $this, 'menu_setup' ) );

			// add our custom js on edit menu screen
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

			// filter the menu item display on edit screen
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'label' ), 10, 1 );
		}

		/**
		 * Localise the plugin
		 */
		public function localise() {
			load_plugin_textdomain(
				$this->domain, false, plugin_dir_path( __FILE__ ) . '/languages'
			);
		}

		/**
		 * Regsiter a custom widget area for our widgets
		 */
		public function sidebar() {
			register_sidebar( array(
				'name' => __( 'Widgets in Menu', $this->domain ),
				'id' => YAWP_WIM_PREFIX,
				"before_widget" => '<div id="%1$s" class="' . $this->attr_prefix . '_widget %2$s">',
				"after_widget" => '</div>',
				'description' => __( 'Widgets in this area will be shown on the edit menu screen.', $this->domain ),
				'before_title' => '<span class="' . $this->attr_prefix . '_title">',
				'after_title' => '</span>'
			) );
		}

		
		/**
		 * Enqueue our js for hooking into wpNavMenu class
		 * 
		 * @param string $hook A string to identify the current screen
		 * @return null
		 */
		public function enqueue( $hook ) {

			// bail if not the edit menu screen
			if ( 'nav-menus.php' != $hook ) {
				return;
			}
			$min = '';
			if ( ! WP_DEBUG ) {
				$min = '.min';
			}
			wp_enqueue_script(
				YAWP_WIM_PREFIX, YAWP_WIM_URL . "/js/yawp-wim{$min}.js", array( 'nav-menu' ), $this->version
			);
		}

		/**
		 * Changes the label from 'Custom' to 'Widget' on the individual menu item
		 * 
		 * @param object $item The menu item
		 * @return object
		 */
		function label( $item ) {
			if ( $item->object === YAWP_WIM_PREFIX ) {

				// setup our label
				$item->type_label = __( 'Widget', $this->domain );
			}
			return $item;
		}

	}

}