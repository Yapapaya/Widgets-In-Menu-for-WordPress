jQuery('document').ready(function() {

        jQuery('#submit-ya-wim').on('click', function(e) {
                wpNavMenu.registerChange();
                console.log('clicked');
                wpYaWimAddWidgettoMenu();
        });


        function wpYaWimAddWidgettoMenu() {
                if (0 === jQuery('#menu-to-edit').length) {
                        return false;
                }


                var t = jQuery('.yawimdiv'), menuItems = {},
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
                });

        }

        function wpYaWimAddWidgetItemtoMenu(menuItem, processMethod, callback) {
                var menu = jQuery('#menu').val(),
                        nonce = jQuery('#menu-settings-column-nonce').val(),
                        params;

                processMethod = processMethod || function() {
                };
                callback = callback || function() {
                };

                params = {
                        'action': 'add-ya-wim-item',
                        'menu': menu,
                        'menu-settings-column-nonce': nonce,
                        'menu-item': menuItem,
                        'menu-item-type': 'widget'
                };

                jQuery.post(ajaxurl, params, function(menuMarkup) {
                        var ins = jQuery('#menu-instructions');

                        menuMarkup = jQuery.trim(menuMarkup); // Trim leading whitespaces
                        processMethod(menuMarkup, params);

                        // Make it stand out a bit more visually, by adding a fadeIn
                        jQuery('li.pending').hide().fadeIn('slow');
                        jQuery('.drag-instructions').show();
                        if (!ins.hasClass('menu-instructions-inactive') && ins.siblings().length)
                                ins.addClass('menu-instructions-inactive');

                        callback();
                });
        }

});