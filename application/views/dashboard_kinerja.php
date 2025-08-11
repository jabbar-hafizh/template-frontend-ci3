<div class="container-fluid">
  <div class="row">
    <div class="col-lg-12 mb-4">
      <div class="row align-items-center mb-4">
        <!-- Judul -->
        <div class="col-lg-6 col-md-12 mb-2 mb-lg-0">
          <h3 class="text-gray-800 mb-0">
            Dashboard Monitoring & Evaluasi Kinerja Badan Pengawasan
          </h3>
        </div>

        <!-- Dropdown Tahun & Periode di kanan -->
        <div class="col-lg-6 col-md-12 d-flex justify-content-lg-end flex-wrap">

          <!-- Tahun -->
          <div class="mr-2 mb-2">
            <label class="font-weight-bold mr-2 d-block d-lg-inline">Tahun</label>
            <select name="tahun_periode" class="custom-select custom-select-sm w-auto">
              <option value="2025">2025</option>
              <option value="2024">2024</option>
            </select>
          </div>

          <!-- Periode -->
          <div class="mb-2">
            <label class="font-weight-bold mr-2 d-block d-lg-inline">Periode</label>
            <select name="periode" class="custom-select custom-select-sm w-auto">
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
      </div>
      <div class="card-header bg-gradient-primary" style="border-radius: 0.35rem;">
        <h5 class="text-white text-center">
          INDIKATOR KINERJA
          <?= ($unit_kerja && $unit_kerja !== 'Kepala Badan') ? strtoupper($unit_kerja) . ' - ' : '' ?>
          BADAN PENGAWASAN
        </h5>
      </div>

    </div>

    <!-- Loop Sasaran Program -->
    <?php foreach ($sasaran_program as $sp): ?>
      <div class="col-lg-12">
        <div class="card shadow mb-4">
          <a href="#collapseCard<?= $sp->id ?>" class="d-block card-header py-3 collapse collapsed" data-toggle="collapse"
            role="button" aria-expanded="false" aria-controls="collapseCard<?= $sp->id ?>">
            <h6 class="m-0 font-weight-bold text-primary">
              Sasaran Program : <?= $sp->nama ?>
            </h6>
          </a>

          <div class="collapse" id="collapseCard<?= $sp->id ?>">
            <div class="card-body text-gray-900">

              <!-- Loop Indikator Kinerja -->
              <?php foreach ($sp->indikator as $ik): ?>
                <h7 class="large font-weight-bold d-block mb-1">
                  <?= $ik->nama ?>
                  <span class="float-right text-success">
                    Target:
                    <?php
                    // Menampilkan target sesuai tipe target
                    if ($ik->tipe_target == 'persentase') {
                      echo $ik->target . '%';
                    } else {
                      echo $ik->target;
                    }
                    ?>
                  </span>
                </h7>

                <p class="small mb-0 ml-2 float-right"> <?= $ik->persentase ?? 0 ?>%</p>
                <div class="progress mb-2">
                  <div
                    class="progress-bar 
                    <?= ($ik->persentase >= 80) ? 'bg-success' : (($ik->persentase >= 50) ? 'bg-warning' : 'bg-danger') ?>"
                    role="progressbar" style="width: <?= $ik->persentase ?? 0 ?>%"
                    aria-valuenow="<?= $ik->persentase ?? 0 ?>" aria-valuemin="0" aria-valuemax="100">
                  </div>
                </div>

                <!-- Detail data indikator -->
                <?php foreach ($ik->data_indikator as $data): ?>
                  <p class="small mb-0">
                    <?= $data->nama ?> : <?= !empty($data->nilai) ? $data->nilai : '0' ?>
                  </p>
                <?php endforeach; ?>
                <hr>
              <?php endforeach; ?>


            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>