<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class IrwilEmpat extends CI_Controller {

    function index(): void
	{
		$data['title'] = 'E-MONEV';
		$this->load->view('layout/lay_header', $data);
		$this->load->view('layout/lay_nav');
		$this->load->view('irwil_empat');
        $this->load->view('layout/lay_footer');
	}
}
