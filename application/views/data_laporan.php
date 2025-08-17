<div class="container-fluid">

  <form action="<?= base_url('laporan/simpan') ?>" method="post" enctype="multipart/form-data">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="h3 mb-4 text-gray-800">
          Upload File Laporan Kinerja
        </h1>
      </div>

      <!-- Upload File Laporan-->
      <div class="col-lg-12">
        <div class="card shadow mb-4">
          <div class="card-header bg-gradient-primary font-weight-bold text-white">
            Upload File Laporan
          </div>
          <div class="card-body">
            <div class="form-group row">

              <!-- Unit Kerja -->
              <div class="col-md-4">
                <label class="font-weight-bold m-2">Unit Kerja</label>
                <select name="unit_kerja" id="filterUnitkerja" class="custom-select custom-select-sm">
                  <?php
                  $unitkerja_options = ['Kepala Badan', 'Inspektur Wilayah I', 'Inspektur Wilayah II', 'Inspektur Wilayah III', 'Inspektur Wilayah IV'];
                  $selected_unitkerja = $_POST['unit_kerja'] ?? $unitkerja_options[0];
                  foreach ($unitkerja_options as $opt):
                    ?>
                    <option value="<?= $opt ?>" <?= ($selected_unitkerja == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <!-- Tahun Periode -->
              <div class="col-md-4">
                <label class="font-weight-bold m-2">Tahun Periode</label>
                <select name="tahun_periode" class="custom-select custom-select-sm">
                  <option value="2025" <?= (isset($_POST['tahun_periode']) && $_POST['tahun_periode'] == '2025') ? 'selected' : '' ?>>2025</option>
                  <option value="2024" <?= (isset($_POST['tahun_periode']) && $_POST['tahun_periode'] == '2024') ? 'selected' : '' ?>>2024</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-md-4">
                <label class="font-weight-bold m-2">Periode</label>
                <select name="periode" id="filterPeriode" class="custom-select custom-select-sm">
                  <?php
                  $periode_options = ['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV', 'Semester I', 'Semester II', 'Tahunan'];
                  $selected_periode = $_POST['periode'] ?? $periode_options[0];
                  foreach ($periode_options as $opt):
                    ?>
                    <option value="<?= $opt ?>" <?= ($selected_periode == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <h8 class="m-2 font-weight-bold">File Laporan <span class="text-danger">*</span></h8>
            <div class="form-group">
              <input type="file" name="file_pendukung[]" multiple class="form-control form-control-user"
                accept="image/png, image/gif, image/jpeg, application/pdf, application/zip, application/x-rar-compressed, .zip, .rar"
                required>
              <small class="m-2 text-muted">Upload File Laporan Sesuai Periode Pengisian</small>
            </div>

            <!-- Tombol Simpan -->
            <div class="form-group mt-3">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Content Wrapper -->
  <div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

      <!-- Page Heading -->
      <h1 class="h3 mb-4 text-gray-800">Laporan Kinerja Badan Pengawasan</h1>

      <!-- DataTales Example -->
      <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary">
          <h6 class="m-0 font-weight-bold" style="color: white">Daftar Laporan Kinerja</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>Unit Kerja</th>
                  <th>Periode</th>
                  <th>Tahun</th>
                  <th>Tanggal Upload</th>
                  <th>PIC</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($laporan as $row): ?>
                  <tr>
                    <td><?= $row->unit_kerja ?></td>
                    <td><?= $row->periode ?></td>
                    <td><?= $row->tahun ?></td>
                    <td><?= date('d-m-Y', strtotime($row->created_at)) ?></td>
                    <td><?= $row->nama ?></td>
                    <td>
                      <!-- Tombol Delete -->
                      <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete" data-id="<?= $row->id ?>">
                        <i class="fas fa-trash"></i> Delete
                      </a>

                      <!-- Tombol Download -->
                      <a href="<?= $row->url ?>" class="btn btn-sm btn-success" target="_blank">
                        <i class="fas fa-download"></i> Download
                      </a>
                    </td>

                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>


    </div>
    <!-- End of Main Content -->

  </div>
  <!-- End of Content Wrapper -->


</div>