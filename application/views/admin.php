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

      <!-- Upload File Laporan-->
      <div class="col-lg-12">
        <div class="card shadow mb-4">
          <div class="card-header bg-gradient-primary font-weight-bold text-white">
            Upload File Laporan
          </div>
          <div class="card-body">
            <div class="form-group row">
              <!-- Tahun Periode -->
              <div class="col-md-6">
                <label class="font-weight-bold m-2">Tahun Periode</label>
                <select name="tahun_periode" class="custom-select custom-select-sm">
                  <option value="2025">2025</option>
                  <option value="2024">2024</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-md-6">
                <label class="font-weight-bold m-2">Periode</label>
                <select name="periode" class="custom-select custom-select-sm">
                  <option value="Triwulan I">Triwulan I</option>
                  <option value="Triwulan II">Triwulan II</option>
                  <option value="Triwulan III">Triwulan III</option>
                  <option value="Triwulan IV">Triwulan IV</option>
                  <option value="Semester I">Semester I</option>
                  <option value="Semester II">Semester II</option>
                  <option value="Tahunan">Tahunan</option>
                </select>
              </div>
            </div>
            <h8 class="m-2 font-weight-bold">File Laporan <span class="text-danger">*</span></h8>
            <div class="form-group">
              <input type="file" name="file_pendukung[]" multiple class="form-control form-control-user"
                accept="image/png, image/gif, image/jpeg, application/pdf, application/zip, application/x-rar-compressed, .zip, .rar">
              <small class="m-2 text-muted">Upload File Laporan Sesuai Periode Pengisian</small>
            </div>
          </div>
        </div>
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
              <?php $i = 1;
              foreach ($sp->indikator as $ik): ?>
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
              <?php endforeach; ?>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Tombol Submit di akhir form -->
    <div class="row">
      <div class="col-lg-12 text-right">
        <button type="submit" class="btn btn-success btn-lg">
          <i class="fas fa-save"></i> Simpan
        </button>
      </div>
    </div>

  </form>
</div>