<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends CI_Controller {

    function index()
	{
		$data['title'] = 'Profil';
		$this->load->view('layout/lay_header', $data);
		$this->load->view('layout/lay_nav');
		$this->load->view('profil');
        $this->load->view('layout/lay_footer');
	}
}
