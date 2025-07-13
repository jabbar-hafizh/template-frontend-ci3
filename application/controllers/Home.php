<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    function index()
	{
		$data['title'] = 'Dashboard';
		$this->load->view('layout/lay_header', $data);
		$this->load->view('layout/lay_nav');
		$this->load->view('home');
        $this->load->view('layout/lay_footer');
	}
}
