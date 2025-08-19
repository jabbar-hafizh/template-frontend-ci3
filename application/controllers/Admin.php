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

        $touchedIK = []; // IK yg disentuh dari input form
        $wilayahUnits = ['Inspektur Wilayah I', 'Inspektur Wilayah II', 'Inspektur Wilayah III', 'Inspektur Wilayah IV'];
        $unitKepala = 'Kepala Badan';

        // === 1) Simpan nilai indikator_data dari form input ===
        foreach ($this->input->post() as $key => $value) {
            if (strpos($key, 'indikator_') === 0) {
                $indikator_data_template_id = str_replace('indikator_', '', $key);

                if (trim((string) $value) === '')
                    continue;

                $nilai = (float) $value;

                // Ambil "template" indikator_data untuk tahu indikator_kinerja_id & nama
                $row_template = $this->db
                    ->get_where('indikator_data', ['id' => $indikator_data_template_id])
                    ->row();

                if (!$row_template) {
                    log_message('error', "Template indikator_data id={$indikator_data_template_id} tidak ditemukan");
                    continue;
                }

                $indikator_kinerja_id = $row_template->indikator_kinerja_id;
                $namaIndikatorData = $row_template->nama;

                // Catat IK yg disentuh
                $touchedIK[$indikator_kinerja_id] = true;

                // Upsert indikator_data (berdasar IK + tahun + periode + nama)
                $cek = $this->db
                    ->where('indikator_kinerja_id', $indikator_kinerja_id)
                    ->where('periode', $periode)
                    ->where('tahun', $tahun)
                    ->where('nama', $namaIndikatorData)
                    ->get('indikator_data')
                    ->row();

                if ($cek) {
                    $this->db->where('id', $cek->id)->update('indikator_data', [
                        'nilai' => $nilai,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $this->db->insert('indikator_data', [
                        'nama' => $namaIndikatorData,
                        'indikator_kinerja_id' => $indikator_kinerja_id,
                        'periode' => $periode,
                        'tahun' => $tahun,
                        'nilai' => $nilai,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // === 2) Hitung indikator_hasil utk IK yg disentuh (berdasar formula / rata-rata) ===
        foreach (array_keys($touchedIK) as $ikId) {
            $ikRow = $this->db->get_where('indikator_kinerja', ['id' => $ikId])->row();
            if (!$ikRow)
                continue;

            $formula = trim((string) $ikRow->formula);
            $hasil = null;

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
                $total = array_sum($values);
                $count = count($values);
                $hasil = $count > 0 ? $total / $count : null;
            }

            // Upsert ke indikator_hasil
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

        // === 3) Agregasi nilai indikator_data untuk Kepala Badan
        //      (SUM per kode_indikator + NAMA + periode + tahun dari Wilayah I–IV)
        // Kumpulkan kode_indikator yg terlibat dari IK yg disentuh
        $touchedCodes = [];
        foreach (array_keys($touchedIK) as $ikId) {
            $row = $this->db->select('kode_indikator')
                ->get_where('indikator_kinerja', ['id' => $ikId])
                ->row();
            if ($row && !empty($row->kode_indikator)) {
                $touchedCodes[$row->kode_indikator] = true;
            }
        }

        $kepalaIKTouched = []; // utk hitung indikator_hasil Kepala Badan setelah upsert data

        foreach (array_keys($touchedCodes) as $kode) {
            // a) Ambil SUM(nilai) per NAMA dari seluruh Inspektur Wilayah (periode & tahun sama)
            $sumRows = $this->db
                ->select('id.nama, SUM(id.nilai) AS total_nilai', false)
                ->from('indikator_data id')
                ->join('indikator_kinerja ik', 'ik.id = id.indikator_kinerja_id')
                ->join('sasaran_program sp', 'sp.id = ik.sasaran_program_id')
                ->where('ik.kode_indikator', $kode)
                ->where('id.periode', $periode)
                ->where('id.tahun', $tahun)
                ->where_in('sp.unit_kerja', $wilayahUnits) // hanya Wilayah I–IV
                ->group_by('id.nama')
                ->get()
                ->result();

            if (!$sumRows)
                continue;

            // b) Ambil IK milik Kepala Badan utk kode_indikator ini
            $ikKepala = $this->db
                ->select('ik.*')
                ->from('indikator_kinerja ik')
                ->join('sasaran_program sp', 'sp.id = ik.sasaran_program_id')
                ->where('ik.kode_indikator', $kode)
                ->where('sp.unit_kerja', $unitKepala)
                ->get()
                ->row();

            if (!$ikKepala) {
                log_message('error', "IK Kepala Badan dengan kode_indikator {$kode} tidak ditemukan");
                continue;
            }

            $kepalaIKTouched[$ikKepala->id] = true;

            // c) Upsert indikator_data Kepala Badan per NAMA hasil agregasi
            foreach ($sumRows as $r) {
                $nama = $r->nama;
                $totalNilai = (float) $r->total_nilai;

                // cari baris existing utk (IK Kepala Badan, periode, tahun, nama)
                $cek = $this->db
                    ->where('indikator_kinerja_id', $ikKepala->id)
                    ->where('periode', $periode)
                    ->where('tahun', $tahun)
                    ->where('nama', $nama)
                    ->get('indikator_data')
                    ->row();

                if ($cek) {
                    $this->db->where('id', $cek->id)->update('indikator_data', [
                        'nilai' => $totalNilai,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $this->db->insert('indikator_data', [
                        'nama' => $nama,                // <- PENTING: gunakan nama yg sama
                        'indikator_kinerja_id' => $ikKepala->id,
                        'periode' => $periode,
                        'tahun' => $tahun,
                        'nilai' => $totalNilai,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // === 4) (Opsional tapi berguna) Hitung indikator_hasil utk IK Kepala Badan yg terpengaruh ===
        foreach (array_keys($kepalaIKTouched) as $ikId) {
            $ikRow = $this->db->get_where('indikator_kinerja', ['id' => $ikId])->row();
            if (!$ikRow)
                continue;

            $formula = trim((string) $ikRow->formula);
            $hasil = null;

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
                        log_message('error', "Eval gagal (Kepala Badan) untuk IK id={$ikId}, formula={$parsed}");
                        $hasil = null;
                    }
                }
            } else {
                $total = array_sum($values);
                $count = count($values);
                $hasil = $count > 0 ? $total / $count : null;
            }

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

        // === 5) Selesai ===
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
