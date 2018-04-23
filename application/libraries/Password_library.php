<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'libraries/PasswordHash.php';

class Password_library {
    
    public function encrypt_password($password) {
    	return PasswordHash::create_hash($password);
    }
    
    public function validate_password($password, $hash) {
    	return PasswordHash::validate_password($password, $hash);
    }
}

/* End of file Password_library.php */