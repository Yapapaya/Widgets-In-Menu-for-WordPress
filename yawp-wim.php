<?php

/*
  Plugin Name: Widgets in Menu for WordPress
  Plugin URI: http://wordpress.org/plugins/widgets-in-menu/
  Description: Add widgets to any WordPress menu!
  Version: 0.2.1
  Author: saurabhshukla, yapapaya
  Author URI: http://github.com/yapapaya/
  Text Domain: yawp_wim
  Domain Path: /languages
  License: GNU General Public License v2 or later
 */

/**
 * 
 * The current version
 */
define( 'YAWP_WIM_VERSION', '0.2.1' );

/**
 * Filters the prefix used in class/id attributes in html display. 
 * 
 * @since 0.1.0
 * 
 * @param string $default_prefix The default prefix: 'yawp_wim'
 */
$attr_prefix = apply_filters( 'yawp_wim_attribute_prefix', 'yawp_wim' );

/**
 *
 * A string prefix for internal names and ids 
 */
define( 'YAWP_WIM_PREFIX', $attr_prefix );

/**
 * Plugin's file path
 */
define( 'YAWP_WIM_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin's url path
 */
define( 'YAWP_WIM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include main plugin class
 */
include_once YAWP_WIM_PATH . 'classes/class-yawp-wim.php';

/**
 * Include walker class to override menu walker
 */
include_once YAWP_WIM_PATH . 'classes/class-yawp-wim-walker.php';

$yawp_wim = new YAWP_WIM();
$yawp_wim->init();

$yawp_wim_walker = new YAWP_WIM_Walker();
$yawp_wim_walker->init();
