<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function index() {
        $data['title'] = 'E-MONEV';
        $this->load->view('layout/lay_header', $data);
        $this->load->view('layout/lay_nav');
        $this->load->view('admin', $data);
        $this->load->view('layout/lay_footer');
    }
}