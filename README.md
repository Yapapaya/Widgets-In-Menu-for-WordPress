![alt text](/assets/banner-772x250.png)
# Widgets in Menu for WordPress #

* **Contributors:** [saurabhshukla] (http://profiles.wordpress.org/saurabhshukla),

* **License:** [GPL v2 or later] ( http://www.gnu.org/licenses/gpl-2.0.html)

Allows you to add Widgets in WordPress Navigation Menus

## Description ##

Allows you to add Widgets in WordPress Navigation Menus via a custom widget area and an extra box in menu edit screen.

> **Warning**
> 
> You will need to have some CSS skills for the widgets to display properly in nav menus.

#### Usage ####

1. Add the desired widget to the *Widgets in Menu* widget area in *Appearance > Widgets*.
1. The widget will now appear under *Widgets* in *Appearance > Menus*
1. Just add the widget to the menu, as usual. 
1. **Don't** change any settings for the menu item. Manage your widget from the *Appearance > Widgets* screen.
1. Add some custom css as per your need.
1. Done. 

## Installation ##

1. Add the plugin's folder in the WordPress' plugin directory.
1. Activate the plugin.
1. You can now add shortcodes in the custom links of the menus

## Frequently Asked Questions ##

*The widgets show up, but the display is all messed up.*

That's because the css for the nav menu was never meant to take care of widgets.

For example, if the widget contains a link, you might have to redo it. If it's a calendar:

`.yawp-wim_wrap a {
width: auto !important;
padding: 0 !important;
}`

*How does one style the widgets?*

Inspect element is your friend. Otherwise, the menu item will have the classes *menu-item-type-yawp_wim*, *menu-item-object-yawp_wim*.

Additionally, the widget will be wrapped in a div with the class *yawp-wim_wrap*.

Using these selectors, one can style the widgets.

*How does one use the plugin?*

1. Add the desired widget to the *Widgets in Menu* widget area in *Appearance > Widgets*.
1. The widget will now appear under *Widgets* in *Appearance > Menus*
1. Just add the widget to the menu, as usual. 
1. **Don't** change any settings for the menu item. Manage your widget from the *Appearance > Widgets* screen.
1. Add some custom css as per your need.
1. Done. 

## Changelog ##

#### 0.0.1 ####
* Initial Plugin uploaded.
