<?php

namespace app\controllers;

class LoginController {
    public function __construct() {
        
    }

    public function index() {
        require_view("login");
    }

    public function register() {
        require_view("auth/register");
    }
}

?>