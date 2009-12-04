<?php
/**
 * $Horde: nag/tasklists/edit.php,v 1.1.2.3 2009/01/06 15:25:09 jan Exp $
 *
 * Copyright 2002-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 */

@define('NAG_BASE', dirname(dirname(__FILE__)));
require_once NAG_BASE . '/lib/base.php';
require_once NAG_BASE . '/lib/Forms/EditTaskList.php';

// Exit if this isn't an authenticated user.
if (!Auth::getAuth()) {
    header('Location: ' . Horde::applicationUrl('list.php', true));
    exit;
}

$vars = Variables::getDefaultVariables();
$tasklist = $nag_shares->getShare($vars->get('t'));
if (is_a($tasklist, 'PEAR_Error')) {
    $notification->push($tasklist, 'horde.error');
    header('Location: ' . Horde::applicationUrl('tasklists/', true));
    exit;
} elseif ($tasklist->get('owner') != Auth::getAuth()) {
    $notification->push(_("You are not allowed to change this task list."), 'horde.error');
    header('Location: ' . Horde::applicationUrl('tasklists/', true));
    exit;
}
$form = new Nag_EditTaskListForm($vars, $tasklist);

// Execute if the form is valid.
if ($form->validate($vars)) {
    $original_name = $tasklist->get('name');
    $result = $form->execute();
    if (is_a($result, 'PEAR_Error')) {
        $notification->push($result, 'horde.error');
    } else {
        if ($tasklist->get('name') != $original_name) {
            $notification->push(sprintf(_("The task list \"%s\" has been renamed to \"%s\"."), $original_name, $tasklist->get('name')), 'horde.success');
        } else {
            $notification->push(sprintf(_("The task list \"%s\" has been saved."), $original_name), 'horde.success');
        }
    }

    header('Location: ' . Horde::applicationUrl('tasklists/', true));
    exit;
}

$vars->set('name', $tasklist->get('name'));
$vars->set('description', $tasklist->get('desc'));
$title = $form->getTitle();
require NAG_TEMPLATES . '/common-header.inc';
require NAG_TEMPLATES . '/menu.inc';
echo $form->renderActive($form->getRenderer(), $vars, 'edit.php', 'post');
require $registry->get('templates', 'horde') . '/common-footer.inc';
