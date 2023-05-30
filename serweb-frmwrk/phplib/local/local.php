<?php
/**
 *  @author    Karel Kozlik
 *  @package   PHPLib
 */

if (isset($GLOBALS['_phplib_page_open']['sess'])){
    /**
     *  main session class
     *
     *  @package   PHPLib
     */

    class phplib_Session extends Session {
        var $classname = "phplib_Session";

        var $trans_id_enabled = false;
        var $cookiename     = "";                ## defaults to classname
        var $mode           = "cookie";          ## We propagate session IDs with cookies
        var $fallback_mode  = "get";
        var $allowcache     = "no";              ## "public", "private", or "no"
        var $lifetime       = 0;                 ## 0 = do session cookies, else minutes
        var $cookie_secure   = true;
        var $cookie_httponly = false;
    }
}

if (isset($GLOBALS['_phplib_page_open']['auth'])){
    /**
     *  default auth class
     *
     *  @package   PHPLib
     */

    class phplib_Auth extends Auth {
        var $classname      = "phplib_Auth";
        var $lifetime       = 20;

        /**
         *  contructor
         */

        function phplib_Auth(){
            global $config;
            /* call parent's constructor */
            $this->Auth();

            $this->lifetime = $config->auth_lifetime;
            $this->auth['adm_domains'] = null;
        }

        /**
         * Function is called when user is not authenticated
         *
         * If user is logged in and authentication expired, this function
         * display relogin page. Otherwise if user is not logged in yet, is
         * redirected to login page
         */

        function auth_loginform() {
            global $sess;
            global $_SERWEB;

            $this->auth['adm_domains'] = null;

            //user is not logged in, forward to login screen
            if (!isset($this->auth["uid"]) or is_null($this->auth["uid"])){
                Header("Location: ".$sess->url("index.php"));
                exit;
            }

            //else display relogin form
            include($_SERWEB["serwebdir"] . "relogin.php");
        }

        /**
         * Function validate password obtained from re-login form
         *
         * If password is valid, function authenticate user again and return true,
         * otherwise return false.
         *
         * @return bool
         */

        function auth_validatelogin() {

            $password = "";
            if (isset($_POST['password'])) $password = $_POST['password'];

            $opt = array();
            if (false === $this->validate_credentials($this->auth['uname'], $this->auth['did'], $password, $opt)){
                return false;
            }

            $this->authenticate();

            $perms = $this->find_out_perms($this->auth['uid'], array());
            if (false === $perms) return false;
            $this->set_perms($perms);

            return true;
        }

    }

    /**
     *  @package   PHPLib
     *  @deprec
     */
    class phplib_Pre_Auth extends phplib_Auth {
    }
}

if (isset($GLOBALS['_phplib_page_open']['perm'])){
    /**
     *  default perm class
     *
     *  @package   PHPLib
     */

    class phplib_Perm extends Perm {
        var $classname = "phplib_Perm";

        var $permissions = array(
                                "admin"      => 1,
                                "change_priv"=> 2,
                                "hostmaster" => 4
                            );

        /**
         *  Function is called when permission of user is invalid
         *
         *  This function should display page with "permissions invalid" message
         */

        function perm_invalid($does_have, $must_have) {
            global $_SERWEB;
            include($_SERWEB["serwebdir"] . "perm_invalid.php");
        }
    }
}
