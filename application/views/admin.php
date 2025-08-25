<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Mulai Form -->
  <form action="<?= base_url('admin/simpan_data') ?>" method="post" enctype="multipart/form-data">

    <div class="row">
      <div class="col-lg-12 mb-4">
        <h3 class="mb-4 text-gray-800">
          Form Input Data Kinerja <?= $unit_kerja ?>
        </h3>
        <div class="card-header bg-gradient-primary" style="border-radius: calc(0.35rem - 1px)">
          <h5 style="color: white; display: flex; align-items: center; justify-content: center;">
            INDIKATOR KINERJA <?= strtoupper($unit_kerja) ?> - BADAN PENGAWASAN
          </h5>
        </div>
      </div>
    </div>

    <div class="form-group row mb-4">

      <!-- Tahun Periode -->
      <div class="col-md-6">
        <label class="font-weight-bold m-2">Tahun Periode</label>
        <select name="tahun_periode" class="custom-select custom-select-sm">
          <?php
          $tahunList = range(date('Y'), 2024); // Tahun dari sekarang mundur ke 2020
          $currentTahun = $_GET['tahun_periode'] ?? date('Y');
          ?>
          <?php foreach ($tahunList as $t): ?>
            <option value="<?= $t ?>" <?= ($currentTahun == $t) ? 'selected' : '' ?>>
              <?= $t ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Periode -->
      <div class="col-md-6">
        <label class="font-weight-bold m-2">Periode</label>
        <select name="periode" class="custom-select custom-select-sm">
          <?php
          $periode_options = ['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'];
          $selected_periode = $_POST['periode'] ?? $periode_options[0];
          foreach ($periode_options as $opt):
            ?>
            <option value="<?= $opt ?>" <?= ($selected_periode == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Daftar Sasaran Program dan Indikator -->
    <div class="row">
      <?php foreach ($sasaran_program as $sp): ?>
        <div class="col-lg-12">
          <div class="card shadow mb-4">
            <div class="card-header bg-gradient-primary font-weight-bold text-white">
              Sasaran Program : <?= $sp->sp_nama ?>
            </div>
            <div class="card-body">
              <?php $i = 1; ?>
              <?php foreach ($sp->indikator as $ik): ?>
                <div class="indikator-card mb-3" data-periode="<?= htmlspecialchars($ik->periode ?? 'unknown') ?>">
                  <a href="#collapseCard<?= $sp->sp_id ?>-<?= $ik->id ?>"
                    class="m-2 d-block card-header py-3 collapse collapsed" data-toggle="collapse" role="button">
                    <h6 class="m-0 font-weight-bold text-primary">
                      Indikator Kinerja <?= $i++ ?> : <?= $ik->nama ?>
                    </h6>
                  </a>
                  <div class="collapse" id="collapseCard<?= $sp->sp_id ?>-<?= $ik->id ?>">
                    <div class="card-body">
                      <?php foreach ($ik->data_indikator as $data): ?>
                        <h8 class="m-2 font-weight-bold">
                          <?= $data->nama ?> <span class="text-danger">*</span>
                        </h8>
                        <div class="form-group">
                          <input type="number" name="indikator_<?= $data->id ?>" class="form-control form-control-user"
                            placeholder="contoh : 1" required>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Tombol Submit di akhir form -->
    <div class="row">
      <div class="col-lg-12 text-left">
        <button type="submit" class="mb-4 btn btn-success btn-lg">
          <i class="fas fa-save"></i> Simpan
        </button>
      </div>
    </div>

  </form>

  



</div>