<?php
/**
 * $Horde: mimp/config/menu.php.dist,v 1.3.2.1 2007/12/20 12:09:25 jan Exp $
 *
 * This file lets you extend MIMP's menu with your own items.
 *
 * To add a new menu item, simply add a new entry to the $_menu array.
 * Valid attributes for a new menu item are:
 *
 *  'url'       The URL value for the menu item.
 *  'text'      The text to accompany the menu item.
 *
 * Here's an example entry:
 *
 *  $_menu[] = array(
 *      'url' =>        'http://www.example.com/',
 *      'text' =>       'Example, Inc.'
 *  );
 *
 * You can also add a "separator" (a spacer) between menu items.  To add a
 * separator, simply add a new string to the $_menu array set to the text
 * 'separator'.  It should look like this:
 *
 *  $_menu[] = 'separator';
 */

$_menu = array();

/* Add your custom entries below this line. */
