<?php

require_once("logger.php");

interface IUserService4u {

    public function findUsers($domainId);

}

class Email4u {
    private $e;

    function __construct() {
        $a = func_get_args();
        if (func_num_args() == 1)
            $this->__construct1($a[0]);
    }
    function __construct1($email) {
        if (! is_string($email)) throw new Exception("A string is required here");
        $this->e = $email;
        if (!check_email_address($email)) throw new InvalidEmailException("Invalid email address:".$email);
    }
    function toString() {
        return $this->e;
    }
}

class InvalidEmailException extends Exception {

    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class User4u {
    private $id, $localpart, $username;

    function __construct() {
        $a = func_get_args();
        if (func_num_args() == 3) {
            $this->id = $a[0];
            $this->localpart = $a[1];
            $this->username = $a[2];
        }
    }
    function toString() {
        return $this->localpart."(".$this->id.")";
    }
    function getLocalpart() {
        return $this->localpart;
    }
    function getId() {
        return $this->id;
    }
    function getUsername() {
        return $this->username;
    }
}

class UserService4uMock implements IUserService4u {

    private $users = array();

    function __construct() {
        global $firephp;
        $this->users[1] = new User4u(1,"g.norman","Gregory Norman");
        $this->users[2] = new User4u(2,"p.parker","Peter Parker");
        $this->users[3] = new User4u(3,"c.prestan","Charles Preston");
        $this->users[4] = new User4u(4,"j.wayne","John Wayne");

        // $firephp->log($this->users);
    }
    public function findUsers($domainId) {
        return $this->users;
    }
}

// check if we have already email validator in Exim4u
// http://www.linuxjournal.com/article/9585
function check_email_address($email) {
    // First, we check that there's one @ symbol, 
    // and that the lengths are right.
    if (!@ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
        // Email invalid because wrong number of characters 
        // in one section or wrong number of @ symbols.
        return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
        if (!@ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
            $local_array[$i])) {
                return false;
            }
    }
    // Check if domain is IP. If not, 
    // it should be valid domain name
    if (!@ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) {
            return false; // Not enough parts to domain
        }
        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!@ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
                return false;
            }
        }
    }
    return true;
}
