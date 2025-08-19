<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Sasaran_model');
		$this->load->model('Dokumen_model');
		$this->load->model('Laporan_model'); // model untuk data laporan
		$this->load->library('upload');
		$this->load->library('session');
	}

	public function index()
	{
		$data = [
			'title' => 'SI-MONEV',
			'sasaran_program' => $this->get_sasaran_program(),
			'laporan' => $this->Laporan_model->getLaporanLengkap()
		];

		$this->load->view('layout/lay_header', $data);
		$this->load->view('layout/lay_nav', $data);
		$this->load->view('data_laporan', $data); // view gabungan upload + list laporan
		$this->load->view('layout/lay_footer');
	}


	public function upload()
	{
		$unit_kerja = $this->input->post('unit_kerja');
		$periode = $this->input->post('periode');
		$tahun = $this->input->post('tahun_periode');
		$user_id = $this->session->userdata('user_id');

		// Pastikan folder upload ada
		$upload_path = './uploads/laporan/';
		if (!is_dir($upload_path)) {
			mkdir($upload_path, 0777, true);
		}

		if (!empty($_FILES['file_pendukung']['name'][0])) {
			$files = $_FILES;
			$count = count($_FILES['file_pendukung']['name']);

			for ($i = 0; $i < $count; $i++) {
				$_FILES['file_pendukung']['name'] = $files['file_pendukung']['name'][$i];
				$_FILES['file_pendukung']['type'] = $files['file_pendukung']['type'][$i];
				$_FILES['file_pendukung']['tmp_name'] = $files['file_pendukung']['tmp_name'][$i];
				$_FILES['file_pendukung']['error'] = $files['file_pendukung']['error'][$i];
				$_FILES['file_pendukung']['size'] = $files['file_pendukung']['size'][$i];

				$config['upload_path'] = $upload_path;
				$config['allowed_types'] = 'pdf|zip|rar|jpg|jpeg|png|gif';
				$config['max_size'] = 20000; // 20 MB
				$config['encrypt_name'] = TRUE;

				$this->upload->initialize($config);

				if ($this->upload->do_upload('file_pendukung')) {
					$uploadData = $this->upload->data();

					// simpan ke database
					$this->db->insert('dokumen_laporan', [
						'unit_kerja' => $unit_kerja,
						'periode' => $periode,
						'tahun' => $tahun,
						'url' => base_url('uploads/laporan/' . $uploadData['file_name']),
						'created_at' => date('Y-m-d H:i:s'),
						'user_id' => $user_id
					]);

				} else {
					$this->session->set_flashdata('error', $this->upload->display_errors());
					redirect('laporan');
				}
			}

			$this->session->set_flashdata('success', 'File laporan berhasil diupload.');
		} else {
			$this->session->set_flashdata('error', 'Tidak ada file yang dipilih.');
		}

		redirect('laporan');
	}

	public function hapus($id)
	{
		// Cek dulu apakah user masih login
		if (!$this->session->userdata('user_id')) {
			$this->session->set_flashdata('error', 'Sesi anda sudah habis, silakan login kembali.');
			redirect('auth/login');
		}

		// Ambil data dulu sebelum dihapus (untuk tahu lokasi file)
		$dokumen = $this->db->get_where('dokumen_laporan', ['id' => $id])->row();

		if ($dokumen) {
			// Hapus file fisik jika ada
			$file_path = str_replace(base_url(), FCPATH, $dokumen->url);
			if (file_exists($file_path)) {
				unlink($file_path);
			}

			// Hapus data dari database
			$this->db->where('id', $id);
			$this->db->delete('dokumen_laporan');

			$this->session->set_flashdata('success', 'Data dan file berhasil dihapus.');
		} else {
			$this->session->set_flashdata('error', 'Data tidak ditemukan.');
		}

		redirect('laporan');
	}

	private function get_sasaran_program($unit_kerja = null)
	{
		return $this->Sasaran_model->get_sasaran_program($unit_kerja);
	}
}
