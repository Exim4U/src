<html>
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('List mailing lists'); ?></title>
        <link rel="stylesheet" href="style.css" type="text/css">
        <script type="text/javascript" src="ajaxLayer/phplivex.js"></script>
        <script type="text/javascript" src="translateJs.php?name=group.js"></script>
    </head>
    <body>
        <?php
            require_once("ajaxLayer/group.php");
            $ajax->Run();
            include dirname(__FILE__) . '/config/header.php'; 
        ?>
        <div id="Menu">
            <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
            <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
        </div>

        <div id="Content">
            <div id="mlAndGroupLists">
                <? //////////////////////////////////////////////// Mailing List ?>
                <div id="mllist">
                    <h2>Simple Mailing Lists</h2>
                    <table class="mllist">
                        <thead>
                            <tr>
                                <td> </td> 
                                <td colspan="2" class="addmlbox"><a onclick="openAddMl()" title="<?=_("Add new mailing list")?>"><img src="images/user-group-new.png"></a></td>
                            </tr>
                            <tr style="display:none;">
                                <td> </td>  
                                <td>Local part</td>
                                <td>Enabled</td>
                                <td>Content</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; foreach ($groupService->findMailingLists($domainId) as $key => $ml) { ?>
                                <tr onmouseover="showItem('m<?=$i?>')" onmouseout="hideItem('m<?=$i?>')" id="ml<?=$i?>">
                                    <td class="trashbox">
                                        <img class="trash" id="m<?=$i?>" src="images/trashcan.gif" style="display:none;" onclick="confirmDeleteMl('<?= $ml->getName() ?>', '<?= _('Confirm delete Mailing List')?>', 'ml<?=$i?>', '<?=_("Following mailing list has been deleted")?>', '<?=_("Deleting mailing list")?>')">
                                    </td>  
                                    <td onclick="openEditMlForm('<?=$ml->getName()?>')"><a class="ml"><?= $ml->getName() ?></a></td>
                                    <td class="mlcheckbox" onclick="confirmSwitchMlStatus('<?=$ml->getName()?>', 'ml<?=$i?>Status')">
                                        <img id="ml<?=$i?>Status" class="mlcheck" src="images/<?= $ml->isEnabled() ? 'enabled.png' : 'disabled.png' ?>">
                                    </td>
                                    <td><?= '('.$ml->getEmailCount().') '.getMailingListPreview($ml, 32) ?></td>
                                </tr>
                            <?php $i++; } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <? //////////////////////////////////////////////// Mailing List hidden items ?>
            <div id="mledit" style="display:none;">
                <form name="mleditform" id="mleditform" method="post" action="saveMailingListChanges.php" onsubmit="return saveMlChanges(this);">
                    <input type="hidden" name="mlName" id="mlName" value="">
                    <table class="nospace">
                        <tr>
                            <td><h2 id="mlNameTitle">Mailing list name</h2></td>
                        </tr>
                        <tr>
                            <td>
                                <table class="nospace" cellspacing="0" cellpadding="0"><tr>
                                    <td class="mlfieldname" title="<?=_('Indicates where the reply to a message in the mailing list should be posted to')?>"><?=_('Reply To')?></td>
                                    <td id="mlreplyfield">
                                        <input type="radio" name="mlReplyTo" value="s" id="mlReplyTo_s" checked="true"><?=_('Sender')?></input>
                                        <br>
                                        <input type="radio" name="mlReplyTo" value="m" id="mlReplyTo_m"><?=_('Mailing list')?>
                                    </td>
                                </tr></table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="mlfieldname"><?=_('List Members Email Addresses')?></div>
                                <textarea name="mlcontent" id="mlcontent"></textarea>
                            </td>
                        </tr>
                        <tr><td><div class="buttons">
                            <button type="submit" name="cancel" onclick="return discardMlChanges()"><?=_('Discard changes')?></button>
                            <button type="submit" name="save"><?=_('Save')?></button>
                        </div></td></tr>
                    </table>
                    <span style="display:none;" id="mlActionType"></span>
                </form>
            </div>
        </div>

        <? //////////////////////////////////////////////// Message bar ?>
        <div id="messageBar">
            <span id="running" style="visibility:hidden;">running</span><br>
            <span id="state"></span>
        </div>
        <div id="mask"></div>
    </body>
</html>
