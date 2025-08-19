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

          <?php
          $tahunList = range(date('Y'), 2024); // Tahun dari sekarang mundur ke 2020
          $periodeList = [
            "Triwulan I",
            "Triwulan II",
            "Triwulan III",
            "Triwulan IV",
            "Semester I",
            "Semester II",
            "Tahunan"
          ];

          // ambil query string biar bisa set default value dropdown
          $currentTahun = $_GET['tahun'] ?? date('Y');
          $currentPeriode = $_GET['periode'] ?? "Tahunan";
          ?>

          <form id="filterForm" class="form-inline">
            <div class="mr-2 mb-2">
              <label class="font-weight-bold mr-2 d-block d-lg-inline">Tahun</label>
              <select name="tahun" class="custom-select custom-select-sm w-auto">
                <?php foreach ($tahunList as $t): ?>
                  <option value="<?= $t ?>" <?= ($currentTahun == $t) ? 'selected' : '' ?>>
                    <?= $t ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-2">
              <label class="font-weight-bold mr-2 d-block d-lg-inline">Periode</label>
              <select name="periode" class="custom-select custom-select-sm w-auto">
                <?php foreach ($periodeList as $p): ?>
                  <option value="<?= $p ?>" <?= ($currentPeriode == $p) ? 'selected' : '' ?>>
                    <?= $p ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>

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

                <?php if ($ik->tipe_target === 'persentase'): ?>
                  <!-- Jika persentase -->
                  <p class="small mb-0 ml-2 float-right">
                    <?= number_format($ik->persentase, 2) ?>%
                  </p>
                <?php else: ?>
                  <!-- Jika jumlah / target -->
                  <p class="small mb-0 ml-2 float-right">
                    <?= $ik->hasil ?> / <?= $ik->target ?>
                  </p>
                <?php endif; ?>

                <div class="progress mb-2">
                  <div class="progress-bar 
    <?= ($ik->persentase >= 100) ? 'bg-success'
      : (($ik->persentase >= 60) ? 'bg-info'
        : (($ik->persentase >= 40) ? 'bg-primary'
          : (($ik->persentase >= 20) ? 'bg-warning'
            : 'bg-danger'))) ?>" role="progressbar" style="width: <?= $ik->persentase ?? 0 ?>%"
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

  <script>
    // Ambil semua dropdown dalam form
    document.querySelectorAll('#filterForm select').forEach(el => {
      el.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);

        // update query sesuai dropdown yg dipilih
        params.set(this.name, this.value);

        // reload halaman dengan query yg sudah diupdate
        window.location.search = params.toString();
      });
    });
  </script>


</div>