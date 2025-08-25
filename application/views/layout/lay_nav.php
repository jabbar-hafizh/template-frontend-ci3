<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('dashboard'); ?>">
        <div class="sidebar-brand-icon">
          <img src="<?= base_url('assets/img/logo_simonev.svg'); ?>" class="img-fluid" style="max-height: 300px;">
        </div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <?php
      // Daftar unit kerja
      $unit_kerja_list = [
        // 'Sekretaris',
        'Inspektur Wilayah I',
        'Inspektur Wilayah II',
        'Inspektur Wilayah III',
        'Inspektur Wilayah IV'
      ];

      // Unit kerja untuk dashboard guest (tanpa Kepala Badan)
      $unit_kerja_dashboard = array_filter($unit_kerja_list, function ($uk) {
        return $uk !== 'Kepala Badan';
      });

      // Ambil role user dari session
      $role = isset($role) ? $role : $this->session->userdata('role');
      // Redirect otomatis jika guest pertama kali mengakses halaman lain selain dashboard
      $current_url = uri_string();
      if ($role !== 'admin' && $current_url !== 'dashboard') {
        redirect('dashboard');
      }
      ?>

      <!-- Menu untuk ADMIN -->
      <?php if ($role === 'admin'): ?>

        <!-- Dashboard -->
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('dashboard'); ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Menu Admin (termasuk Kepala Badan) -->
        <?php foreach ($unit_kerja_list as $uk): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/form_input?unit_kerja=' . urlencode($uk)); ?>">
              <i class="fas fa-fw fa-edit"></i>
              <span><?= $uk ?></span>
            </a>
          </li>
        <?php endforeach; ?>
        <!-- Divider -->      
        <hr class="sidebar-divider my-0">

        <!-- Data Laporan -->
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('laporan'); ?>">
            <i class="fas fa-fw fa-file-alt"></i>
            <span>Data Laporan</span>
          </a>
        </li>

        <!-- Menu untuk GUEST -->
      <?php else: ?>

        <!-- Dashboard -->
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('dashboard'); ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <!-- Divider -->
         <hr class="sidebar-divider my-0">

        <!-- Menu unit kerja (tanpa Kepala Badan) -->
        <?php foreach ($unit_kerja_dashboard as $uk): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('dashboard?unit_kerja=' . urlencode($uk)); ?>">
              <i class="fas fa-fw fa-chart-area"></i>
              <span><?= $uk ?></span>
            </a>
          </li>
        <?php endforeach; ?>

      <?php endif; ?>

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->


    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">
            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle " href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-700">
                  <?= $role === 'admin' ? 'Admin' : 'Guest'; ?>
                </span>
                <img class="img-profile rounded-circle" src="<?= base_url('assets/img/profile.png'); ?>">
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <?php if ($role === 'admin'): ?>
                  <a class="dropdown-item" href="<?= base_url('/login/logout'); ?>" data-toggle="modal"
                    data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                  </a>
                <?php else: ?>
                  <a class="dropdown-item" href="<?= base_url('login'); ?>">
                    <i class="fas fa-sign-in-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Login
                  </a>
                <?php endif; ?>
              </div>
            </li>

          </ul>

        </nav>