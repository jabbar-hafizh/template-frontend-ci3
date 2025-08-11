<div class="container">

  <!-- Outer Row -->
  <div class="row justify-content-center">

    <div class="col-xl-10 col-lg-12 col-md-9">

      <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
          <!-- Nested Row within Card Body -->
          <div class="row">
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-gradient-primary">
              <img src="../assets/img/logo_simonev.svg" class="img-fluid" style="max-height: 300px;">
            </div>
            <div class="col-lg-6">
              <div class="p-5">

                <div class="text-center">
                  <h1 class="h4 mb-4 font-weight-bold text-primary">Log In</h1>
                </div>

                <!-- Flashdata Error -->
                <?php if ($this->session->flashdata('error')): ?>
                  <div class="alert alert-danger">
                    <?= $this->session->flashdata('error') ?>
                  </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form class="user" method="POST" action="<?= base_url('login/login') ?>">
                  <div class="form-group">
                    <input type="text" class="form-control form-control-user" name="nip" placeholder="NIP" required>
                  </div>
                  <div class="form-group">
                    <input type="password" class="form-control form-control-user" name="password" placeholder="Password"
                      required>
                  </div>
                  <button type="submit" class="btn btn-primary btn-user btn-block mb-4">
                    Login
                  </button>
                </form>
                <div class="text-center">
                  <?php if ($this->session->userdata('role') === 'admin'): ?>
                    <a href="<?= base_url('dashboard'); ?>" class="btn btn-warning btn-user btn-block">
                      Back To Dashboard
                    </a>
                  <?php else: ?>
                    <a href="<?= base_url('dashboard?guest=1'); ?>" class="btn btn-warning btn-user btn-block">
                      Back To Dashboard
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>

</div>