<?php
/*
 * Simple login script
 * (c) 2014 - 2015 Pieter De Praetere <pieter.de.praetere@helptux.be>
 *  This program is free software: you can redistribute it and/or modify
    it under the terms of version 3 of the GNU General Public License
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include_once ('lib/page_generator.php');
include_once ('lib/class_login.php');

class login_module {

    private $p;
    private $l;
    public $msg; /* Message describing what the function did */
    public $loc; /* Contains, for successful actions, the URL the user came from/must go to */
    public $html; /* Contains, for failed actions, a HTML form */

    function __construct () {
        $this->p = new page_generator ();
        $this->l = new login ();
    }

    /**
     * Perform the login business
     * @return bool
     * If the return value is true, $this->loc is set, so you may redirect the user to where they came from.
     * If it is false; $this->html is set to a form you can output
     * $this->msg contains a short description of what went right/wrong
     */
    function l () {
        $this->l->l_session_start ();
        /* Referral URL */
        if (isset ($_GET['return-to'])) {
            $ref = $_GET['return-to'];
        } else {
            $ref = 'index.php';
        }
        if (isset ($_SERVER['HTTPS'])) {
            $loc = 'https://'.$_SERVER['SERVER_NAME'].'/'.$ref;
        } else {
            $loc = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $ref;
        }
        /* If users are logged in, send them back where they came from */
        if ($this->l->check_login () === true) {
            //header ("location: $loc", 302);
            $this->loc = $loc;
            $this->msg = "ALREADY_LOGGED_IN";
            return true;
        }

        /* If the form was submitted $_POST['submit'] will be 1 */
        if (isset ($_POST['submit'])) {
            if (!isset ($_POST['username']) || !isset ($_POST['password']) || $_POST['username'] == null || $_POST['password'] == null) {
                $this->html =  $this->p->g_login ($ref, 'Gebruikersnaam of wachtwoord niet ingevuld!');
                $this->msg = "NO_USERNAME";
                return false;
            }
            /* Check provided information */
            $username = $_POST['username'];
            $password = $_POST['password'];
            if ($this->l->l_login ($username, $password, 'username') === true) {
                /* Correctly logged in */
                $this->loc = $loc;
                $this->msg = "LOGGED_IN";
                return true;
            }
            /* Something was wrong */
            $this->html = $this->p->g_login ($ref, 'Gebruikersnaam of wachtwoord is fout!');
            $this->msg = "FAILED";
            return false;
        }

        $this->html =  $this->p->g_login ($ref);
        $this->msg = "PLEASE_LOGIN";
        return false;
    }

    /**
     * Logout
     * @return bool
     * Sets $this->loc
     */
    function o () {
        if (isset ($_GET['return-to'])) {
            $ref = urlencode ($_GET['return-to']);
        } else {
            $ref = 'login.php';
        }
        if (isset ($_SERVER['HTTPS'])) {
            $loc = 'https://'.$_SERVER['SERVER_NAME'].'/'.$ref;
        } else {
            $loc = 'http://'.$_SERVER['SERVER_NAME'].'/'.$ref;
        }
        $this->l->l_session_stop ();
        $this->loc = $loc;
        $this->msg = "LOGGED_OUT";
        return true;
    }

}
?>