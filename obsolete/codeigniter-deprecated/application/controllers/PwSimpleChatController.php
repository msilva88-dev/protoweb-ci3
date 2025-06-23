<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PwSimpleChatController extends CI_Controller {
    // Load the chat view
    public function index() {
        // Load the chat view page
        $this->load->view('simple_chat');
    }
}
