<?php
/**
 * $Horde: kronolith/workweek.php,v 1.34.8.10 2009/01/06 15:24:43 jan Exp $
 *
 * Copyright 1999-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Chuck Hagenbuch <chuck@horde.org>
 */

require_once dirname(__FILE__) . '/lib/base.php';

$view = Kronolith::getView('WorkWeek');
$title = sprintf(_("Week %d"), $view->week);
$print_view = (bool)Util::getFormData('print');

Horde::addScriptFile('tooltip.js', 'horde', true);
if (!$print_view) {
    Horde::addScriptFile('popup.js', 'horde', true);
}
require KRONOLITH_TEMPLATES . '/common-header.inc';

if ($print_view) {
    require $registry->get('templates', 'horde') . '/javascript/print.js';
} else {
    require KRONOLITH_TEMPLATES . '/menu.inc';
}

echo '<div id="page">';
if (!$print_view) {
    Kronolith::tabs();
}
$view->html(KRONOLITH_TEMPLATES);
echo '</div>';

if ($print_view) {
    require KRONOLITH_TEMPLATES . '/calendar_titles.inc';
} else {
    require KRONOLITH_TEMPLATES . '/panel.inc';
}
require $registry->get('templates', 'horde') . '/common-footer.inc';
