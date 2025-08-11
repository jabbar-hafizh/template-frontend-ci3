<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DataLaporan extends CI_Controller
{
	public function index()
	{
		$this->load->model('Laporan_model'); // pastikan model sudah diload
		$data['laporan'] = $this->Laporan_model->getLaporanLengkap();

		$data['title'] = 'SI-MONEV';
		$this->load->view('layout/lay_header', $data);
		$this->load->view('layout/lay_nav');
		$this->load->view('data_laporan', $data);
		$this->load->view('layout/lay_footer');
	}

}
