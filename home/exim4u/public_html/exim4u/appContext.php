<?php
    $firephpEnabled = false;
    include_once dirname(__FILE__) . '/logger.php';

    // config file
    include_once dirname(__FILE__) . '/config/variables.php';

    // check authentication
    include_once dirname(__FILE__) . '/config/authpostmaster.php';

    // various general functions
    include_once dirname(__FILE__) . '/config/functions.php';

    // user service
    include_once dirname(__FILE__) . '/config/service_user.php';
    // not used yet
    //$userService = new UserService4uMock();

    // group service
    include_once dirname(__FILE__) . '/config/service_group.php';
    include_once dirname(__FILE__) . '/config/service_group_sql.php';
    //$groupService = new GroupService4uMock();
    $groupService = new GroupService4uSql($db);

    // current domain
    $domainId = $_SESSION['domain_id'];

