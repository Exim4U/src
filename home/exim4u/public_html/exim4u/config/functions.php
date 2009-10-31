<?php
    // Strictly three aren't alone functions, but they are functions of sorts 
    // and we call it every
    // page to prevent tainted data expoits
    foreach ($_GET as $getkey => $getval) 
    {
        $_GET[$getkey] = preg_replace('/[\'";$%]/','',$getval);
    }

    foreach ($_POST as $postkey => $postval) 
    {
        $_POST[$postkey] = preg_replace('/[\'";$%]/','',$postval);
    }

    $globals = array('_GET', '_POST');
    foreach ($globals as $i => $val) 
    {
        foreach ($$val as $j => $var) 
        {
            if ( isset($$var) ) 
            { 
                unset($$var); 
            }
        }
    }


    /**
     * validate user password
     *
     * validate if password and confirmation password match
     * and contain no invalid characters. They can not be empty.
     *
     * @param   string   $clear   cleartext password
     * @param   string   $vclear  cleartext password (for validation)
     * @return  boolean  true if they match and contain no illegal characters
     */
    function validate_password($clear,$vclear) 
    {
        return ($clear == $vclear) &&
               ($clear != "") &&
               ($clear == preg_replace("/[\'\"\`\;]/","",$clear));
    }


    /**
     * validate alias password
     *
     * like validate_password, but the pasword can be empty
     *
     * @see     validate_password
     * @param   string   $clear   cleartext password
     * @param   string   $vclear  cleartext password (for validation)
     * @return  boolean  true if they match and contain no illegal characters
     */
    function alias_validate_password($clear,$vclear) 
    {
        return ($clear == $vclear) &&
               ($clear == preg_replace("/[\'\"\`\;]/","",$clear));
    }


    /**
     * Check if a user already exists.
     *
     * Queries database $db, and redirects to the $page is the user already
     * exists.
     *
     * @param  mixed   $db         database to query
     * @param  string  $localpart  
     * @param  string  $domain_id
     * @param  string  $page       page to return to
     */
    function check_user_exists($db,$localpart,$domain_id,$page) 
    {
        $query = "SELECT COUNT(*) AS c 
                  FROM   users 
                  WHERE  localpart='$localpart' 
                  AND    domain_id='$domain_id'";
        $result = $db->query($query);
        $row = $result->fetchRow();
        if ($row['c'] != 0) 
        {
            header ("Location: $page?userexists=$localpart");
            die;
        }
    }


    /**
     * Render the alphabet. Directly onto the page.
     *
     * @param  unknown  $flag  unknown
     */
    function alpha_menu($flag) 
    {
        global $letter;	// needs to be available to the parent
        if ($letter == 'all') 
        {
            $letter = '';
        }
        if ($flag) 
        {
            print "\n<p class='alpha'><a href='" . $_SERVER['PHP_SELF'] . 
                  "?LETTER=ALL' class='alpha'>ALL</a>&nbsp;&nbsp; ";
            // loops through the alphabet. 
            // For international alphabets, replace the string in the proper order
            foreach (preg_split('//', _("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -1, 
                                PREG_SPLIT_NO_EMPTY) as $i) 
            {
      	        print "<a href='" . $_SERVER['PHP_SELF'] . 
                      "?LETTER=$i' class='alpha'>$i</a>&nbsp; ";
            }
            print "</p>\n";
        }
    }

    /**
     * crypt the plaintext password.
     *
     * @golbal  string  $cryptscheme
     * @param   string  $clear  the cleartext password
     * @param   string  $salt   optional salt
     * @return  string          the properly crypted password
     */
    function crypt_password($clear, $salt = '')
    {
        global $cryptscheme;
        
        if ($cryptscheme == 'sha')
        {
            $hash = sha1($clear);
            $cryptedpass = '{SHA}' . base64_encode(pack('H*', $hash));
        }
        else
        {
            if ($salt != '')
            {
                if ($cryptscheme == 'des') 
                {
                    $salt = substr($salt, 0, 2);
                }
                else
                if ($cryptscheme == 'md5') 
                {
                    $salt = substr($salt, 0, 12);
                }
                else
                {
                    $salt = '';
                }
            }
            $cryptedpass = crypt($clear, $salt);
        }   
        
        return $cryptedpass;
    }
?>
