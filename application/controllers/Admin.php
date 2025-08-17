<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load model Sasaran_model
        $this->load->model('Sasaran_model');
    }

    public function index()
    {
        $data = [
            'title' => 'E-MONEV',
            'sasaran_program' => $this->get_sasaran_program()
        ];

        $this->load->view('layout/lay_header', $data);
        $this->load->view('layout/lay_nav', $data);
        $this->load->view('admin', $data);
        $this->load->view('layout/lay_footer');
    }

    public function form_input($unit_kerja = null)
    {
        // Ambil dari query string jika parameter kosong
        if (empty($unit_kerja)) {
            $unit_kerja = $this->input->get('unit_kerja');
        }

        $unit_kerja = !empty($unit_kerja) ? urldecode($unit_kerja) : null;

        $data = [
            'title' => 'Admin ' . ($unit_kerja ?? ''),
            'unit_kerja' => $unit_kerja,
            'sasaran_program' => $this->get_sasaran_program($unit_kerja)
        ];

        $this->load->view('layout/lay_header', $data);
        $this->load->view('layout/lay_nav', $data);
        $this->load->view('admin', $data);
        $this->load->view('layout/lay_footer');
    }
    public function simpan_data()
    {
        $this->load->library('upload');

        $tahun = $this->input->post('tahun_periode', true);
        $periode = $this->input->post('periode', true);
        $user_id = $this->session->userdata('user_id');

        $this->db->trans_start();

        $touchedIK = [];

        // === Simpan nilai indikator_data ===
        foreach ($this->input->post() as $key => $value) {
            if (strpos($key, 'indikator_') === 0) {
                $indikator_data_template_id = str_replace('indikator_', '', $key);

                if (trim((string) $value) === '') {
                    continue;
                }

                $nilai = (float) $value;

                // Ambil indikator_kinerja_id dari template indikator_data
                $row_template = $this->db
                    ->get_where('indikator_data', ['id' => $indikator_data_template_id])
                    ->row();

                if (!$row_template) {
                    log_message('error', "Template indikator_data id={$indikator_data_template_id} tidak ditemukan");
                    continue;
                }

                $indikator_kinerja_id = $row_template->indikator_kinerja_id;

                // Simpan indikator_kinerja_id yang disentuh
                $touchedIK[$indikator_kinerja_id] = true;

                // Cek apakah baris periodik + nama ini sudah ada
                $cek = $this->db
                    ->where('indikator_kinerja_id', $indikator_kinerja_id)
                    ->where('periode', $periode)
                    ->where('tahun', $tahun)
                    ->where('nama', $row_template->nama)
                    ->get('indikator_data')
                    ->row();

                if ($cek) {
                    $this->db->where('id', $cek->id)->update('indikator_data', [
                        'nilai' => $nilai,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $this->db->insert('indikator_data', [
                        'nama' => $row_template->nama,
                        'indikator_kinerja_id' => $indikator_kinerja_id,
                        'periode' => $periode,
                        'tahun' => $tahun,
                        'nilai' => $nilai,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // === Hitung hasil berdasarkan formula di tabel indikator_kinerja ===
        foreach (array_keys($touchedIK) as $ikId) {
            $ikRow = $this->db->get_where('indikator_kinerja', ['id' => $ikId])->row();
            if (!$ikRow)
                continue;

            $formula = trim((string) $ikRow->formula);
            $hasil = null;

            // Ambil semua indikator_data sesuai periode + tahun
            $dataRows = $this->db
                ->where('indikator_kinerja_id', $ikId)
                ->where('periode', $periode)
                ->where('tahun', $tahun)
                ->order_by('id', 'ASC')
                ->get('indikator_data')
                ->result();

            $values = [];
            $i = 1;
            foreach ($dataRows as $row) {
                $values[$i] = (float) $row->nilai;
                $i++;
            }

            if ($formula !== '') {
                $parsed = $formula;
                foreach ($values as $index => $val) {
                    $parsed = str_replace('{' . $index . '}', $val, $parsed);
                }

                if (preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $parsed)) {
                    try {
                        eval ('$hasil = ' . $parsed . ';');
                    } catch (\Throwable $e) {
                        log_message('error', "Eval gagal untuk indikator_kinerja id={$ikId}, formula={$parsed}");
                        $hasil = null;
                    }
                }
            } else {
                // fallback → rata-rata
                $total = array_sum($values);
                $count = count($values);
                $hasil = $count > 0 ? $total / $count : null;
            }

            // === Simpan hasil ke tabel indikator_hasil ===
            $cekHasil = $this->db
                ->where('indikator_kinerja_id', $ikId)
                ->where('periode', $periode)
                ->where('tahun', $tahun)
                ->get('indikator_hasil')
                ->row();

            if ($cekHasil) {
                $this->db->where('id', $cekHasil->id)->update('indikator_hasil', [
                    'hasil' => $hasil,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->insert('indikator_hasil', [
                    'indikator_kinerja_id' => $ikId,
                    'periode' => $periode,
                    'tahun' => $tahun,
                    'hasil' => $hasil,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        } else {
            $this->session->set_flashdata('success', 'Data berhasil disimpan.');
        }

        redirect('dashboard');
    }
    // Tambahkan method ini untuk memanggil model
    private function get_sasaran_program($unit_kerja = null)
    {
        return $this->Sasaran_model->get_sasaran_program($unit_kerja);
    }
}
