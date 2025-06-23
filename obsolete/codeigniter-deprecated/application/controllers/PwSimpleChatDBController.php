<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PwSimpleChatDBController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('PwSimpleChatDBModel');
    }

    // Load the chat view
    public function index() {
        // Load the chat view page
        $this->load->view('simple_chatdb');
    }

    // Add a new user
    public function add_user() {
        $username = $this->input->post('username');
        if (!$username) {
            echo json_encode(['error' => 'Username is required']);
            return;
        }

        $user_id = $this->PwSimpleChatDBModel->add_user($username);
        echo json_encode(['success' => true, 'user_id' => $user_id]);
    }

    // Fetch user details by ID
    public function get_user($id) {
        $user = $this->PwSimpleChatDBModel->get_user_by_id($id);
        if (!$user) {
            echo json_encode(['error' => 'User not found']);
            return;
        }

        echo json_encode(['success' => true, 'user' => $user]);
    }
}
