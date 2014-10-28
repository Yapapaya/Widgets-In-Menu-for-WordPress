<?php
/*
  Plugin Name: Widgets in Menu for WordPress
  Plugin URI: http://wordpress.org/plugins/widgets-in-menu/
  Description: Add widgets to any WordPress menu!
  Version: 0.1.1
  Author: saurabhshukla
  Author URI: http://github.com/yapapaya/
  Text Domain: yawp_wim
  Domain Path: /languages
  License: GNU General Public License v2 or later
 */

if (!class_exists('yawpWIM')) {

	class yawpWIM {
		
		/**
		 *
		 * @var	string The current version
		 */
		public $version = "0.1.1";

		/**
		 *
		 * @var string A string prefix for internal names and ids 
		 */
		public $prefix = 'yawp_wim';

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
			
			$default_prefix = $this->prefix;
			/**
			 * Filters the prefix used in class/id attributes in html display. 
			 * 
			 * @since 0.1.0
			 * 
			 * @param string $default_prefix The default prefix: 'yawp_wim'
			 */
			$this->attr_prefix = apply_filters('yawp_wim_attribute_prefix', $default_prefix);
		}

		/**
		 * Hooks to the necessary actions and filters
		 */
		public function init() {
			// initialise translations
			add_action('plugins_loaded', array($this,'localise'));
			
			// hook the sidebar registration
			add_action('widgets_init', array($this, 'sidebar'));

			// filter the nav menu item output for rendering widgets
			add_filter('walker_nav_menu_start_el', array($this, 'start_el'), 1, 4);

			// hook into the edit menus admin screen
			add_action('admin_init', array($this, 'menu_setup'));

			// add our custom js on edit menu screen
			add_action('admin_enqueue_scripts', array($this, 'enqueue'));

			// filter the menu item display on edit screen
			add_filter('wp_setup_nav_menu_item', array($this, 'label'), 10, 1);
		}
		
		/**
		 * Localise the plugin
		 */
		public function localise(){
            load_plugin_textdomain(
					$this->domain, false, plugin_dir_path(__FILE__) . '/languages'
			);
		}

		/**
		 * Regsiter a custom widget area for our widgets
		 */
		public function sidebar() {
			register_sidebar(array(
				'name'			=> __('Widgets in Menu', $this->domain),
				'id'			=> $this->prefix,
				"before_widget"	=> '<div id="%1$s" class="' . $this->attr_prefix . '_widget %2$s">',
				"after_widget"	=> '</div>',
				'description'	=> __('Widgets in this area will be shown on the edit menu screen.', $this->domain),
				'before_title'	=> '<span class="' . $this->attr_prefix . '_title">',
				'after_title'	=> '</span>'
			));
		}

		/**
		 * Render the widget in the nav menu
		 * 
		 * @global      array		$wp_registered_widgets	All registered widgets
		 * @global      array		$wp_registered_sidebars All registered sidebars
		 * @param       string		$item_output			The html output of the widget.
		 * @param       object		$item					The nav menu placeholder item, from the edit-menus ui.
		 * @param       int			$depth					Depth of the item.
		 * @param       array		$args					An array of additional arguments.
		 * @return      boolean|string						The final html output
		 */
		public function start_el($item_output, $item, $depth, $args) {

			// bail early, if it is not our widget placeholder
			if ($item->object != $this->prefix) {
				return $item_output;
			}
			
			// get the list of registered widgets and sidebars
			global $wp_registered_widgets, $wp_registered_sidebars;

			// we've saved the name of the widget in the xfn of the menu item
			$id = $item->xfn;

			// if this widget is not set, bail
			if (!isset($wp_registered_widgets[$id])) {
				return $item_output;
			}
			
			// get our sidebar/widget area 
			$sidebar = array_merge(
					
					// our sidebar is at the index 'yawp_wim'
					$wp_registered_sidebars[$this->prefix], 
					
					// we merge our current widget into it 
					array(
						'widget_id' => $id,
						'widget_name' => $wp_registered_widgets[$id]['name']
					)
				);

			// set up the widget parameters
			$params = array_merge(
					array($sidebar),
					(array) $wp_registered_widgets[$id]['params']
			);

			// Substitute HTML id and class attributes into before_widget
			$classname_ = '';
			foreach ((array) $wp_registered_widgets[$id]['classname'] as $cn) {
				if (is_string($cn))
					$classname_ .= '_' . $cn;
				elseif (is_object($cn))
					$classname_ .= '_' . get_class($cn);
			}
			$classname_ = ltrim($classname_, '_');

			// set up more parameters
			$params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);

			/**
			 * Filter the parameters passed to the widget's display callback.
			 *
			 * Note: Similar to 'dynamic_sidebar_params' filter
			 *
			 * @since 0.0.1
			 *
			 * @see register_sidebar()
			 *
			 * @param array $params {
			 *     @type array $args  {
			 *         An array of widget display arguments.
			 *
			 *         @type string $name          Name of the sidebar the widget is assigned to.
			 *         @type string $id            ID of the sidebar the widget is assigned to.
			 *         @type string $description   The sidebar description.
			 *         @type string $class         CSS class applied to the sidebar container.
			 *         @type string $before_widget HTML markup to prepend to each widget in the sidebar.
			 *         @type string $after_widget  HTML markup to append to each widget in the sidebar.
			 *         @type string $before_title  HTML markup to prepend to the widget title when displayed.
			 *         @type string $after_title   HTML markup to append to the widget title when displayed.
			 *         @type string $widget_id     ID of the widget.
			 *         @type string $widget_name   Name of the widget.
			 *     }
			 *     @type array $widget_args {
			 *         An array of multi-widget arguments.
			 *
			 *         @type int $number Number increment used for multiples of the same widget.
			 *     }
			 * }
			 */
			$params = apply_filters('yawp_wim_widget_params', $params);
			
			$yawp_wim_widget = $wp_registered_widgets[$id];

			/**
			 * Fires before a widget's display callback is called. 
			 * 
			 * Note: Similar to 'dynamic_sidebar' action.
			 *
			 * @since 0.0.1
			 * 
			 * @see dynamic_sidebar()
			 *
			 * @param array $yawp_wim_widget {
			 *     An associative array of widget arguments.
			 *
			 *     @type string $name                Name of the widget.
			 *     @type string $id                  Widget ID.
			 *     @type array|callback $callback    When the hook is fired on the front-end, $callback is an array
			 *                                       containing the widget object. Fired on the back-end, $callback
			 *                                       is 'wp_widget_control', see $_callback.
			 *     @type array          $params      An associative array of multi-widget arguments.
			 *     @type string         $classname   CSS class applied to the widget container.
			 *     @type string         $description The widget description.
			 *     @type array          $_callback   When the hook is fired on the back-end, $_callback is populated
			 *                                       with an array containing the widget object, see $callback.
			 * }
			 */
			do_action('yawp_wim_pre_callback', $yawp_wim_widget);
			
			// set up the wrapper class
			$wrapper_class = $this->attr_prefix . '_wrap';

			// get the registered callback function for this widget
			$callback = $wp_registered_widgets[$id]['callback'];

			// if we have a valid callback function
			if (is_callable($callback)) {
				// since the callback echoes the output
				// we use this to return the output in a var
				ob_start();
				?>
				<div class="<?php echo $wrapper_class; ?>">
					<div class="widget-area">
				<?php
				// call the widget callback function
				call_user_func_array($callback, $params);
				?>
					</div>
				</div>
				<?php
				// assign to the variable
				$item_output = ob_get_contents();
				ob_end_clean();
			}
			// return the widget output
			return $item_output;
		}

		/**
		 * Setup our metabox on the edit menu screen
		 */
		public function menu_setup() {
			add_meta_box(
					'add-widget-section', __('Widgets', $this->domain), array($this, 'meta_box'), 'nav-menus', 'side', 'default'
			);
		}

		/**
		 * Add a custom metabox on edit menu screen for widgets
		 * 
		 * @globa		int			$_nav_menu_placeholder	A placeholder index for the menu item
		 * @global		int|string	$nav_menu_selected_id	(id, name or slug) of the currently-selected menu
		 * @global      array		$wp_registered_widgets	All registered widgets
		 * @global      array		$wp_registered_sidebars All registered sidebars
		 */
		public function meta_box() {
			
			// initialise some global variables
			global $_nav_menu_placeholder, $nav_menu_selected_id,
			$wp_registered_widgets, $wp_registered_sidebars;
			
			
			// initialise the output variable
			$output = '';
			
			// get all the sidebar widgets
			$sidebars_widgets = wp_get_sidebars_widgets();
			
			// we don't have widgets
			if (empty($wp_registered_sidebars[$this->prefix]) 
					|| empty($sidebars_widgets[$this->prefix]) 
					|| !is_array($sidebars_widgets[$this->prefix])) {
				
				// the default output
				$no_widgets_output = '<p>';
				$no_widgets_output .= sprintf(__('<a href="%s">Please add a '
						. 'widget</a> to the <em>Widgets in Menu</em> area', 
						$this->domain), admin_url("widgets.php"));
				$no_widgets_output .= '</p>';
				
				/**
				 * Filters the html displayed if no widgets are present in the sidebar. 
				 * 
				 * @since 0.1.0
				 * 
				 * @param string $no_widgets_output The default output
				 */
				$no_widgets_output = apply_filters('yawp_wim_no_widgets_message', $no_widgets_output);
				
				// add to the final output
				$output .= $no_widgets_output;
				
			} else {
				// we have widgets, so we'll output them in an unordered list,
				// like wordpress does
				$output .= '<ul>';
				
				// loop through our widgets
				foreach ((array) $sidebars_widgets[$this->prefix] as $id) {
					
					// bail if not set
					if (!isset($wp_registered_widgets[$id]))
						continue;
					
					// figure the placeholder index
					$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
					
					// this widget
					$widget = $wp_registered_widgets[$id];
					
					// the widget number (for eg, calendar-3, 3 it is)
					$widget_num = $widget['params'][0]["number"];
					
					// get the widget slug from the id
					$widget_slug = rtrim(preg_replace("|[0-9]+|i", "", $id), '-');
					
					// get the widget's settings from the options table
					$widget_saved = get_option('widget_' . $widget_slug, array());
					
					// get the title from the saved settings
					$widget_title = $widget_saved[$widget_num]['title'];
					
					// get the name
					$widget_name = $widget['name'];
					$widget_name .= (empty($widget_title)) ? '' : ': ' . $widget_title;
					
					// start the list item
					$output .= '<li>';
					$output .= '<label for="' . $id . '">';
					
					// checkbox
					$output .= '<input name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-object-id]" type="checkbox" value="' 
							. $widget_num . '" id="' . $id . '" class="menu-item-checkbox ' . $id . '">';
					$output .= $widget_name;
					$output .= '</label>';
					
					// db-id is 0,will be created when the menu item is created in the db
					$output .= '<input type="hidden" class="menu-item-db-id" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-db-id]" value="0" />';
					
					// object is our prefix
					$output .= '<input type="hidden" class="menu-item-object" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-object]" value="' 
							. $this->prefix . '" />';
					
					// no parent-id
					$output .= '<input type="hidden" class="menu-item-parent-id" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-parent-id]" value="0" />';
					
					// type is our prefix
					$output .= '<input type="hidden" class="menu-item-type" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-type]" value="' . $this->prefix . '" />';
					
					// title
					$output .= '<input type="hidden" class="menu-item-title" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-title]" value="' . $widget_name . '" />';
					
					// the empty stuff
					$output .= '<input type="hidden" class="menu-item-url" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-url]" value="" />';
					$output .= '<input type="hidden" class="menu-item-target" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-target]" value="" />';
					$output .= '<input type="hidden" class="menu-item-attr_title" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-attr_title]" value="" />';
					$output .= '<input type="hidden" class="menu-item-classes" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-classes]" value="" />';
					
					// storing our id in xfn. could have been any of the above
					$output .= '<input type="hidden" class="menu-item-xfn" name="menu-item[' 
							. $_nav_menu_placeholder . '][menu-item-xfn]" value="' . $id . '" />';
					$output .= '</li>';
				}
				
				$output .= '<p style="display:none;" class="msg-yawp_sim">';
				// no text-domain, so that the Strings translated by WordPress are used
				$output .= __('Settings', $this->domain)
						. ': '
						.sprintf('<a href="%s">',admin_url("widgets.php"))
						. __('Appearance', $this->domain)
						.' > '
						. __('Widgets', $this->domain).'</a>';
				$output .= '<p>';
				$output .= '</ul>';
			}
			
			// submit button
			?>
			<div class="yawp_wimdiv" id="yawp_wimdiv">
			<?php echo $output; ?>
				<p class="button-controls">
					<span class="add-to-menu">
						<input type="submit"<?php wp_nav_menu_disabled_check($nav_menu_selected_id); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-ya_wim-menu-item" id="submit-ya_wim" />
						<span class="spinner"></span>
					</span>
				</p>

			</div><!-- /.customlinkdiv -->
			<?php
		}
		
		/**
		 * Enqueue our js for hooking into wpNavMenu class
		 * 
		 * @param string $hook A string to identify the current screen
		 * @return null
		 */
		public function enqueue($hook) {
			
			// bail if not the edit menu screen
			if ('nav-menus.php' != $hook) {
				return;
			}
			$min = '';
			if(!WP_DEBUG){
				$min = '.min';
			}
			wp_enqueue_script(
					$this->prefix,
					plugin_dir_url(__FILE__) . "/yawp-wim{$min}.js",
					array('nav-menu'),
					$this->version
					);
		}
		
		/**
		 * Changes the label from 'Custom' to 'Widget' on the individual menu item
		 * 
		 * @param object $item The menu item
		 * @return object
		 */
		function label($item) {
			if ($item->object === $this->prefix) {

				// setup our label
				$item->type_label = __('Widget', $this->domain);
			}
			return $item;
		}

	}

}

$yawp_wim = new yawpWIM();
$yawp_wim_init = $yawp_wim->init();