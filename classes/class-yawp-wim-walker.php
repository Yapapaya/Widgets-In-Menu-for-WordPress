<?php
if ( ! class_exists( 'YAWP_WIM_Walker' ) ) {

	class YAWP_WIM_Walker {

		public function init() {
			// filter the nav menu item output for rendering widgets
			add_filter( 'walker_nav_menu_start_el', array( $this, 'start_el' ), 1, 4 );
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
		public function start_el( $item_output, $item, $depth, $args ) {

			// bail early, if it is not our widget placeholder
			if ( $item->object != YAWP_WIM_PREFIX ) {
				return $item_output;
			}

			// get the list of registered widgets and sidebars
			global $wp_registered_widgets, $wp_registered_sidebars;

			// we've saved the name of the widget in the xfn of the menu item
			$id = $item->xfn;

			// if this widget is not set, bail
			if ( ! isset( $wp_registered_widgets[ $id ] ) ) {
				return $item_output;
			}

			// get our sidebar/widget area 
			$sidebar = array_merge(
				// our sidebar is at the index 'yawp_wim'
				$wp_registered_sidebars[ YAWP_WIM_PREFIX ],
				// we merge our current widget into it 
				array(
				'widget_id' => $id,
				'widget_name' => $wp_registered_widgets[ $id ][ 'name' ]
				)
			);

			// set up the widget parameters
			$params = array_merge(
				array( $sidebar ), ( array ) $wp_registered_widgets[ $id ][ 'params' ]
			);

			// Substitute HTML id and class attributes into before_widget
			$classname_ = '';
			foreach ( ( array ) $wp_registered_widgets[ $id ][ 'classname' ] as $cn ) {
				if ( is_string( $cn ) )
					$classname_ .= '_' . $cn;
				elseif ( is_object( $cn ) )
					$classname_ .= '_' . get_class( $cn );
			}
			$classname_ = ltrim( $classname_, '_' );

			// set up more parameters
			$params[ 0 ][ 'before_widget' ] = sprintf( $params[ 0 ][ 'before_widget' ], $id, $classname_ );

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
			$params = apply_filters( 'yawp_wim_widget_params', $params );

			$yawp_wim_widget = $wp_registered_widgets[ $id ];

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
			do_action( 'yawp_wim_pre_callback', $yawp_wim_widget );

			// set up the wrapper class
			$wrapper_class = YAWP_WIM_PREFIX . '_wrap';

			// get the registered callback function for this widget
			$callback = $wp_registered_widgets[ $id ][ 'callback' ];

			// if we have a valid callback function
			if ( is_callable( $callback ) ) {
				// since the callback echoes the output
				// we use this to return the output in a var
				ob_start();
				?>
				<div class="<?php echo $wrapper_class; ?>">
					<div class="widget-area">
						<?php
						// call the widget callback function
						call_user_func_array( $callback, $params );
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

	}

}


