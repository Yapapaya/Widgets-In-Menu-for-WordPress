jQuery('document').ready(function() {

	jQuery('#submit-ya_wim').on('click', function() {
		wpNavMenu.registerChange();
		yaWPWimAddWidgettoMenu();
	});
	
	function yaWPWimModifyItem() {
		// hack to remove the input fields
		jQuery('#update-nav-menu .menu-item-yawp_wim p.description').not('p.field-move').hide();

		// hack to display settings message
		$message = jQuery('p.msg-yawp_sim').html();
		jQuery('#update-nav-menu .menu-item-yawp_wim p.link-to-original').html($message);
	}

	function yaWPWimAddWidgettoMenu() {
		if (0 === jQuery('#menu-to-edit').length) {
			return false;
		}

		var t = jQuery('.yawp_wimdiv'), menuItems = {},
				checkboxes = t.find('li input[type="checkbox"]:checked'),
				re = /menu-item\[([^\]]*)/;

		processMethod = wpNavMenu.addMenuItemToBottom;

		// If no items are checked, bail.
		if (!checkboxes.length)
			return false;

		// Show the ajax spinner
		t.find('.spinner').show();

		// Retrieve menu item data
		jQuery(checkboxes).each(function() {
			var t = jQuery(this),
					listItemDBIDMatch = re.exec(t.attr('name')),
					listItemDBID = 'undefined' == typeof listItemDBIDMatch[1] ? 0 : parseInt(listItemDBIDMatch[1], 10);

			if (this.className && -1 != this.className.indexOf('add-to-top'))
				processMethod = wpNavMenu.addMenuItemToTop;
			menuItems[listItemDBID] = t.closest('li').getItemData('add-menu-item', listItemDBID);
		});

		// Add the items
		wpNavMenu.addItemToMenu(menuItems, processMethod, function() {
			// Deselect the items and hide the ajax spinner
			checkboxes.removeAttr('checked');
			t.find('.spinner').hide();
			yaWPWimModifyItem();
		});

	}
	
	yaWPWimModifyItem();
});