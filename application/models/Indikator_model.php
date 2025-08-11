<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Indikator_model extends CI_Model
{
    /**
     * Hitung formula untuk indikator tertentu
     * dan update hasil ke tabel indikator_data
     *
     * @param int $indikator_kinerja_id
     * @return bool
     */
    public function formula_indikator($indikator_kinerja_id)
    {
        // 1. Ambil deskripsi_formula
        $indikator = $this->db
            ->select('deskripsi_formula')
            ->where('id', $indikator_kinerja_id)
            ->get('indikator_kinerja')
            ->row();

        if (!$indikator || empty($indikator->deskripsi_formula)) {
            return false; // Tidak ada formula yang ditentukan
        }

        $kode_formula = strtolower(trim($indikator->deskripsi_formula));

        // 2. Ambil semua data indikator_data terkait
        $data_indikator = $this->db
            ->select('id, nilai')
            ->where('indikator_kinerja_id', $indikator_kinerja_id)
            ->get('indikator_data')
            ->result();

        if (empty($data_indikator)) {
            return false; // Tidak ada data untuk dihitung
        }

        // 3. Hitung total nilai dan jumlah data
        $total_nilai = 0;
        $count = count($data_indikator);

        foreach ($data_indikator as $data) {
            $total_nilai += (float) $data->nilai;
        }

        // 4. Tentukan perhitungan sesuai kode formula
        $hasil_formula = 0;
        switch ($kode_formula) {
            case 'apip':
                // Contoh formula: rata-rata nilai * 100
                $hasil_formula = ($count > 0) ? ($total_nilai / $count) * 100 : 0;
                break;

            case 'persentase':
                // Contoh formula: total nilai / nilai maksimal * 100
                $max_nilai = 500; // misalnya batas maksimal nilai 500
                $hasil_formula = ($max_nilai > 0) ? ($total_nilai / $max_nilai) * 100 : 0;
                break;

            case 'jumlah':
                // Hanya menjumlahkan semua nilai
                $hasil_formula = $total_nilai;
                break;

            default:
                $hasil_formula = 0; // formula tidak dikenal
                break;
        }

        // 5. Update hasil ke setiap baris indikator_data
        foreach ($data_indikator as $data) {
            $this->db->where('id', $data->id)
                ->update('indikator_data', ['hasil' => $hasil_formula]);
        }

        return true;
    }
     public function insert_data_indikator($data)
    {
        $this->db->insert('indikator_data', $data);
    }
}