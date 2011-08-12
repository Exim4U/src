<?php
    header("Content-Type: text/html; charset=UTF-8");
    include_once("appContext.php");    
    // http://www.phplivex.com/example/submitting-html-forms-with-ajax
    extract($_POST);

    if (!isset($groupService)) throw new Exception("Missing required groupService");
    if (!isset($mlName)) throw new Exception("Missing form attribute 'mlName'");
    if (!isset($mlcontent)) throw new Exception("Missing form attribute 'mlcontent'");
    if (!isset($mlReplyTo)) throw new Exception("Missing form attribute 'replyTo'");
    if (!isset($domainId)) throw new Exception("Missing domainId");

    try {
        $ml = new MailingList4u($mlName);
        foreach (array_unique(split("\n", $mlcontent)) as $line) {
            $s = trim($line);
            if (isset($s) and strlen($s)>0) {
                $ml->addEmail($s);
            }
        }
        $ml->setReplyTo($mlReplyTo);
        $groupService->saveOrUpdateMailingListContent($domainId, $ml);
        echo "ok";
    } catch (InvalidEmailException $ex) {
        echo $ex;
    }
?>
