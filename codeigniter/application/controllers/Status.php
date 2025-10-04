<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Status extends MY_Controller
{
    public function index()
    {
        $this->respond(['status' => 'online']);
    }
}
