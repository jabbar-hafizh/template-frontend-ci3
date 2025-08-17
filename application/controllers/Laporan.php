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

	public function simpan_data()
	{
		$periode = $this->input->post('periode', true);
		$tahun = $this->input->post('tahun', true);

		// Mulai transaksi database
		$this->db->trans_start();

		foreach ($this->input->post() as $key => $value) {
			if (strpos($key, 'indikator_') === 0) {
				$indikator_id = str_replace('indikator_', '', $key);
				$nilai = trim($value) === '' ? null : (float) $value; // Pastikan nilai kosong jadi NULL

				// Cek apakah data indikator dengan periode & tahun sudah ada
				$cek = $this->db
					->where('indikator_kinerja_id', $indikator_id)
					->where('periode', $periode)
					->where('tahun', $tahun)
					->get('indikator_data')
					->row();

				if ($cek) {
					// Update nilai
					$this->db->where('id', $cek->id);
					$this->db->update('indikator_data', [
						'nilai' => $nilai,
						'updated_at' => date('Y-m-d H:i:s')
					]);
				} else {
					// Insert data baru
					$this->db->insert('indikator_data', [
						'indikator_kinerja_id' => $indikator_id,
						'periode' => $periode,
						'tahun' => $tahun,
						'nilai' => $nilai,
						'created_at' => date('Y-m-d H:i:s')
					]);
				}
			}
		}

		// Selesaikan transaksi
		$this->db->trans_complete();

		if ($this->db->trans_status() === false) {
			$this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan data.');
		} else {
			$this->session->set_flashdata('success', 'Data berhasil disimpan.');
		}

		redirect('laporan');
	}

	public function hapus($id)
	{
		// Hapus data di database
		$this->db->where('id', $id);
		$this->db->delete('dokumen_laporan'); // ganti dengan nama tabel

		// Redirect kembali dengan pesan
		$this->session->set_flashdata('success', 'Data berhasil dihapus');
		redirect('laporan');
	}

	private function get_sasaran_program($unit_kerja = null)
	{
		return $this->Sasaran_model->get_sasaran_program($unit_kerja);
	}
}
