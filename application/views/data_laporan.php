<div class="container-fluid">
  <!-- Content Wrapper -->
  <div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

      <!-- Page Heading -->
      <h1 class="h3 mb-4 text-gray-800">Laporan Kinerja Badan Pengawasan</h1>

      <!-- DataTales Example -->
      <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary">
          <h6 class="m-0 font-weight-bold" style="color: white">Daftar Laporan</h6>
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
                    <td><?= date('d-m-Y', strtotime($row->createdAt)) ?></td>
                    <td><?= $row->nama ?></td>
                    <td>
                      <!-- Tombol Edit -->
                      <a href="<?= base_url('laporan/edit/' . $row->id) ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                      </a>

                      <!-- Tombol Download -->
                      <a href="<?= base_url('uploads/' . $row->file) ?>" class="btn btn-sm btn-success" target="_blank">
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