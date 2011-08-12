<?php
    /**
     * GUI functions on top of groupService
     *
     * These functions return plain text or JSON
     * http://www.json.org/js.html
     */
    if (! isset($groupService))
        throw new Exception("Missing required groupService");
    if (! isset($domainId))
        throw new Exception("Missing required domainId");

    function getMailingListEmails($mlName) {
        global $groupService, $domainId, $firephp;
        $firephp->log('building text representation of '.$mlName.' emails on domain '.$domainId);
        $ml = $groupService->getMailingList($domainId, $mlName);
        $res = '';
        foreach ($ml->getEmails() as $email) {
            if (strlen($res) > 0) $res .= '\n';
            $res .= $email->toString();
        }
        $replyTo = $ml->getReplyTo();
        return "{'content': '${res}', 'replyTo': '${replyTo}'}";
    }

    function getMailingListPreview($ml, $threshold) {
        $res = "";
        foreach ($ml->getEmails() as $email) {
            if (strlen($res) > 0) {
                $res .= ", ";
            }
            $res .= $email->toString();
            if (strlen($res) > $threshold) {
                $res = substr($res, 0, $threshold)."...";
                break;
            }
        }
        return $res;
    }

    function deleteMailingList($mlName) {
        global $groupService, $firephp, $domainId;
        $firephp->log('deleting mailing list '.$mlName.' from domain '.$domainId);
        $groupService->deleteMailingList($domainId, $mlName);
    }

    function changeMailingListStatus($mlName, $enabled) {
        global $groupService, $domainId;
        $groupService->changeMailingListStatus($domainId, $mlName, $enabled);
    }

    require_once("PHPLiveX.php");
    if (! isset($ajax)) {
        $ajax = new PHPLiveX();  
    }
    // Ajaxify Your PHP Functions   
    $ajax->Ajaxify(array("getMailingListEmails", "deleteMailingList", "changeMailingListStatus"));
    //$ajax->AjaxifyObjectMethods(array("groupService" => array("deleteMailingList", "changeMailingListStatus")));
?>
