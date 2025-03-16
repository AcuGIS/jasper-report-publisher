<?php
		require('admin/incl/const.php');
		require('admin/class/database.php');
		require('admin/class/user.php');

		session_start(['read_and_close' => true]);
    if(isset($_SESSION[SESS_USR_KEY])) {
        header("Location: index.php");
    }
?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>AcuGIS | Jasper Report Publisher</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <link href="assets/images/favicon.ico" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600;1,700&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Raleway:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
	
	

  
</head>

<body>


<!-- Login 8 - Bootstrap Brain Component -->
<section class="bg-light p-3 p-md-4 p-xl-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-xxl-11">
        <div class="card border-light-subtle shadow-sm">
          <div class="row g-0">
            <div class="col-12 col-md-6">
              <img class="img-fluid rounded-start w-100 h-100 object-fit-cover" loading="lazy" src="assets/images/login_page.png" alt="Welcome back you've been missed!">
            </div>
            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
              <div class="col-12 col-lg-11 col-xl-10">
                <div class="card-body p-3 p-md-4 p-xl-5">
                  <div class="row">
                    <div class="col-12">
                      <div class="mb-5">
                        <div class="text-center mb-4">
                          <a href="#!">
                            <img src="assets/images/login_box.png" alt="QuartzMap" width="125" height="125">
                          </a>
                        </div>
                        <h4 class="text-center">Jasper Publisher</h4>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <div class="d-flex gap-3 flex-column">
                        
                      </div>
                      
                    </div>
                  </div>
                  <form method="post" action="admin/action/login.php">
										<?php if(!empty($_GET['err'])){ ?>
											<div class="alert alert-danger" role="alert" style="width: 80%"><?=$_GET['err']?></div>
										<?php } else if(!empty($_GET['msg'])){ ?>
											<div class="alert alert-success" role="alert" style="width: 80%"><?=$_GET['msg']?></div>
										<?php } ?>
          <!-- Email input -->
          <div class="form-outline mb-4">
            <input type="email" class="form-control form-control-lg" id="email"   placeholder="Enter a valid email address" name="email"/>


            <label class="form-label" for="form3Example3">Email address</label>
          </div>

          <!-- Password input -->
          <div class="form-outline mb-3">
            <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="pwd">
             
            <label class="form-label" for="form3Example4">Password</label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <!-- Checkbox -->
            
          </div>

          <div class="col-12">
             <div class="d-grid">
             	<button class="btn btn-dark btn-lg" type="submit" value="Submit" name="submit">Log in</button>
             </div>
          </div>
        </form>
                  <div class="row">
                    <div class="col-12">
                      <div class="d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-md-center mt-5">
                        <a href="https://www.acugis.com" class="link-secondary text-decoration-none">From AcuGIS</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/web.js"></script>

</body>
</html>
