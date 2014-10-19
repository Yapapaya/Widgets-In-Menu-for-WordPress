<?php
/*
  Plugin Name: Widgets in Menu
  Plugin URI:
  Description: Add widgets to any WordPress menu!
  Version: 0.0.1
  Author: saurabhshukla
  Author URI: http://yapapaya.com
  License: GNU General Public License v2 or later
 */

if (!defined('WP_WIM_URL')) {
        define('WP_WIM_URL', plugin_dir_url(__FILE__));
}

if (!class_exists('yawpWIM')) {

        class yawpWIM {

                /**
                 *
                 * @var string A string used for various attributes and ids 
                 */
                public $index = 'yawp_wim';

                /**
                 *
                 * @var type 
                 */
                public $name = 'yawp-wim';

                /**
                 * Sets up variables
                 */
                public function __construct() {
                        $this->index = apply_filters('yawp_wim_index', $this->index);
                        $this->name = apply_filters('yawp_wim_name', $this->name);
                }

                /**
                 * Hooks to the necessary actions and filters
                 */
                public function init() {
                        // hook the sidebar registration
                        add_action('widgets_init', array($this, 'register_sidebar'));

                        // filter the nav menu item output for rendering widgets
                        add_filter('walker_nav_menu_start_el', array($this, 'walker_start_el'), 1, 4);

                        // hook into the edit menus admin screen
                        add_action('admin_init', array($this, 'menu_setup'));

                        // add our custom js on edit menu screen, load very late
                        add_action('admin_enqueue_scripts', array($this,'enqueue'), 99,1);
                }

                /**
                 * Regsiter a custom widget area for our widgets
                 */
                public function register_sidebar() {
                        register_sidebar(array(
                            'name' => esc_html__('Widgets in Menu', $this->name),
                            'id' => $this->index,
                            "before_widget" => '<div id="%1$s" class="' . $this->index . ' %2$s">',
                            "after_widget" => '</div>',
                            'description' => esc_html__('Widgets in this area will be shown on the menu bar.', 'yawp-wim'),
                            'before_title' => '<span class="' . $this->index . '-title">',
                            'after_title' => '</span>'
                        ));
                }

                /**
                 * Render the widget in the nav menu
                 * 
                 * @global      array   $wp_registered_widgets
                 * @global      array   $wp_registered_sidebars
                 * @param       string  $item_output    The html output of the widget.
                 * @param       object  $item           The nav menu placeholder item, from the edit-menus ui.
                 * @param       int     $depth          Depth of the item.
                 * @param       array   $args           An array of additional arguments.
                 * @return      boolean|string          The final html output
                 */
                public function walker_start_el($item_output, $item, $depth, $args) {
                        
                        // bail early, if it is not our widget placeholder
                        if ($item->object != $this->index) {
                                return $item_output;
                        }

                        global $wp_registered_widgets, $wp_registered_sidebars;

                        // get our sidebar/widget area
                        $sidebar = $wp_registered_sidebars[$this->index];

                        // we've saved the name/slug of the widget in the xfn
                        $id = $item->xfn;

                        // if this widget is not set, bail
                        if (!isset($wp_registered_widgets[$id])) {
                                return false;
                        }

                        // set up the widget parameters
                        $params = array_merge(
                                array(array_merge($sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']))), (array) $wp_registered_widgets[$id]['params']
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

                        /**
                         * Fires before a widget's display callback is called. 
                         * 
                         * Note: Similar to 'dynamic_sidebar' action.
                         *
                         * @since 0.0.1
                         * 
                         * @see dynamic_sidebar()
                         *
                         * @param array $widget_id {
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
                        do_action('yawp_wim_pre_callback', $wp_registered_widgets[$id]);

                        /**
                         * Filter the classes applied to the widget's wrapper div.
                         *
                         * @since 0.0.1
                         *
                         * @see register_sidebar()
                         *
                         * @param array|string A string or an array of strings to be used as class names
                         * 
                         */
                        $wrapper_class = apply_filters('yawp_wim_wrapper_class', $this->name . '_wrap');

                        // setup the wrapper class
                        if (!is_array($wrapper_class)) {
                                $wrapper_class = array($wrapper_class);
                        }

                        $class_str = implode(' ', $wrapper_class);

                        // get the registered callback function for this widget
                        $callback = $wp_registered_widgets[$id]['callback'];

                        // if we have a valid callback function
                        if (is_callable($callback)) {
                                // since the callback echoes the output
                                // we use this to return the output in a var
                                ob_start();
                                ?>
                                <div class="<?php echo $class_str; ?>">
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
                 * Setup the modifications to the edit menu screen
                 */
                public function menu_setup() {
                        add_meta_box('add-widget-section', __('Widgets'), array($this, 'meta_box'), 'nav-menus', 'side', 'default');

                        // Register advanced menu items (columns)
                        add_filter('manage_nav-menus_columns', 'gs_wp_nav_menu_manage_columns');
                }

                /**
                 * Add a custom metabox on edit menu screen for widgets
                 * 
                 * @global      int     $_nav_menu_placeholder
                 * @global      type    $nav_menu_selected_id
                 * @global      array   $wp_registered_widgets
                 * @global      array   $wp_registered_sidebars
                 */
                public function meta_box() {
                        global $_nav_menu_placeholder, $nav_menu_selected_id, $wp_registered_widgets, $wp_registered_sidebars;
                        $no_widgets = false;

                        $output = '';
                        $index = sanitize_title('yawp_wim');
                        foreach ((array) $wp_registered_sidebars as $key => $value) {
                                if (sanitize_title($value['name']) == $index) {
                                        $index = $key;
                                        break;
                                }
                        }

                        $sidebars_widgets = wp_get_sidebars_widgets();
                        if (empty($wp_registered_sidebars[$index]) || empty($sidebars_widgets[$index]) || !is_array($sidebars_widgets[$index])) {
                                $no_widgets = true;
                                $output .= '<p>';
                                $output .= sprintf(__('<a href="%s%">Please add a widget</a> to the <em>Widgets in Menu</em> area', 'yawp-wim'), admin_url("widgets.php"));
                                $output .= '</p>';
                        } else {
                                $output .= '<ul>';
                                foreach ((array) $sidebars_widgets[$index] as $id) {
                                        if (!isset($wp_registered_widgets[$id]))
                                                continue;
                                        $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
                                        $output .= '<li>';
                                        $widget = $wp_registered_widgets[$id];
                                        $widget_num = $widget['params'][0]["number"];
                                        $widget_slug = rtrim(preg_replace("|[0-9]+|i", "", $id), '-');
                                        $widget_saved = get_option('widget_' . $widget_slug, array());
                                        $widget_name = $widget_saved[$widget_num]['title'];
                                        $widget_name = ($widget_name)?$widget_name:$widget['name'];
                                        $output .= '<label for="' . $id . '">';
                                        $output .= '<input name="menu-item[' . $_nav_menu_placeholder . '][menu-item-object-id]" type="checkbox" value="' . $widget_num . '" id="' . $id . '" class="menu-item-checkbox ' . $id . '">';
                                        $output .= $widget_name;
                                        $output .= '</label>';
                                        $output .= '<input type="hidden" class="menu-item-db-id" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-db-id]" value="0" />';
                                        $output .= '<input type="hidden" class="menu-item-object" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-object]" value="yawp_wim" />';
                                        $output .= '<input type="hidden" class="menu-item-parent-id" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-parent-id]" value="0" />';
                                        $output .= '<input type="hidden" class="menu-item-type" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-type]" value="yawp_wim" />';
                                        $output .= '<input type="hidden" class="menu-item-title" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-title]" value="' . $widget_name . '" />';
                                        $output .= '<input type="hidden" class="menu-item-url" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-url]" value="" />';
                                        $output .= '<input type="hidden" class="menu-item-target" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-target]" value="" />';
                                        $output .= '<input type="hidden" class="menu-item-attr_title" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-attr_title]" value="" />';
                                        $output .= '<input type="hidden" class="menu-item-classes" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-classes]" value="" />';
                                        $output .= '<input type="hidden" class="menu-item-xfn" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-xfn]" value="' . $id . '" />';
                                        $output .= '</li>';
                                }
                                $output .= '</ul>';
                        }
                        ?>
                        <div class="yawimdiv" id="yawimdiv">
                                <?php echo $output; ?>
                                <p class="button-controls">
                                        <span class="add-to-menu">
                                                <input type="submit"<?php wp_nav_menu_disabled_check($nav_menu_selected_id); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-ya-wim-menu-item" id="submit-ya-wim" />
                                                <span class="spinner"></span>
                                        </span>
                                </p>

                        </div><!-- /.customlinkdiv -->
                        <?php
                }

                public function enqueue($hook) {
                        if ('nav-menus.php' != $hook) {
                                return;
                        }
                        wp_enqueue_script($this->name, plugin_dir_url(__FILE__) . '/yawp-wim.js', array('nav-menu'), '0.0.1');
                }

        }

}

$yawp_wim = new yawpWIM();
$yawp_wim_init = $yawp_wim->init();