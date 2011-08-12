/* Generic functions */
function showItem(id) {
    document.getElementById(id).style.display = '';
}
function hideItem(id) {
    document.getElementById(id).style.display = 'none';
}
function displayStateMessage(message) {
    document.getElementById('state').innerHTML = message;
}
function displayRunningMessage(message) {
    document.getElementById('running').innerHTML = message;
}
function getContent(id) {
    return document.getElementById(id).innerHTML;
}
function setContent(id, content) {
    document.getElementById(id).innerHTML = content;
}
function emptyContent(id) {
    document.getElementById(id).innerHTML = '';
}
function getAttribute(id, attName, attValue) {
    document.getElementById(id).getAttribute(attName);
}
function setAttribute(id, attName, attValue) {
    document.getElementById(id).setAttribute(attName, attValue);
}

/* Mailing list related functions */
function confirmDeleteMl(mlName, confirmMsg, itemToDelete, itemDeletedMsg, runningDeleteMsg) {
    if (confirm(confirmMsg + ' ' + mlName)) {
        displayRunningMessage(runningDeleteMsg + mlName);
        deleteMailingList(mlName, {
                'preloader':'running',
                'onFinish': function(response) {
                    hideItem(itemToDelete);
                    displayStateMessage(itemDeletedMsg +': '+ mlName);
                }});
    } else {
        displayStateMessage('');
    }
}
function confirmSwitchMlStatus(mlName, mlStatusImg) {
    var img = document.getElementById(mlStatusImg);
    var enable = img.src.search(/enabled.png$/) == -1;
    if (confirm('__Are sure you want to ' + (enable ? '__enable' : '__disable') + ' ' +
                '__mailing list ' + mlName)) {
        displayRunningMessage((enable ? '__Enabling' : '__Disabling') + ' ' + mlName);
        changeMailingListStatus(mlName, enable, {
                'preloader':'running',
                'onFinish': function(response) {
                    img.src = enable ? 'images/enabled.png' : 'images/disabled.png';
                    displayStateMessage(mlName + ' ' + (enable ? '__enabled' : '__disabled'));
                }});
    } else {
        displayStateMessage('');
    }
}
function openEditMlForm(mlName) {
    displayRunningMessage('__Fetching mailing list content');
    hideItem('mlAndGroupLists');
    setContent('mlActionType', 'update');
    getMailingListEmails(mlName, {'preloader':'running', 
            'onUpdate': function(response) {
                    showItem('mledit');
                    // JSON text to object
                    ml = eval('(' + response + ')');
                    document.mleditform.mlcontent.value = ml.content;
                    document.getElementById('mlReplyTo_' + ml.replyTo).checked = 'true';
                    displayStateMessage('__Editing ' + mlName);
                }});
    setContent('mlNameTitle', mlName);
    setAttribute('mlName', 'value', mlName);
}
function discardMlChanges() {
    displayStateMessage('');
    hideItem('mledit');
    document.getElementById('mlcontent').value = '';
    showItem('mlAndGroupLists');
    return false;
}
function openAddMl() {
    var localPart = prompt('__Enter mailing list local part', '');
    if (localPart == null || localPart == '') {
        displayStateMessage('__Mailing list creation canceled');
    } else {
        hideItem('mlAndGroupLists');
        displayStateMessage('__Creating mailing list ' + localPart);
        setContent('mlNameTitle', localPart);
        setContent('mlActionType', 'create');
        setAttribute('mlName', 'value', localPart);
        showItem('mledit');
        document.getElementById('mlcontent').value = '';
    }
}
function saveMlChanges(form) {
    displayStateMessage('');
    return PLX.Submit(form, {  
            'preloader':'running',  
            'onFinish': function(response) {  
                    if ('ok' == response) {
                        hideItem('mledit');
                        displayStateMessage(getContent('mlNameTitle') + ' ' + '__saved');
                        showItem('mlAndGroupLists');
                        if (getContent('mlActionType') == 'create') {
                            window.location.href=window.location.href;
                        } else {
                            // todo: we should refresh mailing list description
                            // or reload the page
                        }
                    } else {
                        displayStateMessage('__Update failed, edit your input');
                        alert(response);
                    }
                }});
}
