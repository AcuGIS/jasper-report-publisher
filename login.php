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

  <title>GeoSync</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <link href="assets/images/favicon.ico" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600;1,700&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Raleway:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  
 
 
	
	
<style>
.bg-image-vertical {
position: relative;
overflow: hidden;
background-repeat: no-repeat;
background-position: right center;
background-size: auto 100%;
}

@media (min-width: 1025px) {
    .h-custom-2 {
        height: 65%;
    }

}
</style>
  
</head>

<body>

<section class="vh-100">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6 text-black">

        <div class="px-5 ms-xl-4">
          <i class="fas fa-crow fa-2x me-3 pt-5 mt-xl-4" style="color: #709085;"></i>
<p>&nbsp;</p>

<p>&nbsp;</p>

          <span class="h1 fw-bold mb-0" style="color:#666">Jasper Report Publisher</span><br><br>



          <!--<span class="h6 fw-bold mb-0" style="color:#666; position: absolute;  bottom: 8px;  right: 52%;">Jasper Report Server, Release 3.0.1.2.beta</span>-->

        </div>

        <div class="d-flex align-items-center h-custom-2 px-5 ms-xl-4 mt-5 pt-5 pt-xl-0 mt-xl-n5">

           <form method="post" action="admin/action/login.php">
										<?php if(!empty($_GET['err'])){ ?>
											<div class="alert alert-danger" role="alert" style="width: 80%"><?=$_GET['err']?></div>
										<?php } else if(!empty($_GET['msg'])){ ?>
											<div class="alert alert-success" role="alert" style="width: 80%"><?=$_GET['msg']?></div>
										<?php } ?>


            <h4 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Access</h4>

            <div data-mdb-input-init class="form-outline mb-4">
              <!--<input type="email" id="form2Example18" class="form-control form-control-lg" />-->
                          <input type="email" class="form-control form-control-lg" name="email" id="email" required>

              <label class="form-label" for="form2Example18">Email address</label>
            </div>

            <div data-mdb-input-init class="form-outline mb-4">
              <!--<input type="password" id="form2Example28" class="form-control form-control-lg" />-->
	       <input type="password" class="form-control form-control-lg" name="pwd" id="pwd" value="" required>
	
              <label class="form-label" for="form2Example28">Password</label>
            </div>

            <div class="pt-1 mb-4">
              <button data-mdb-button-init data-mdb-ripple-init class="btn btn-lg btn-block" type="submit" value="Login" name="submit" style="width:100%!important;background-color:#666;color:#fff">Login</button>
            </div>
<p>&nbsp;</p>

<p>&nbsp;</p>

<span class="h8 fw-bold mb-0" style="color:#666; font-size:12px!important">Release 3.0.1.2</span>

            <!--<p class="small mb-5 pb-lg-2"><a class="text-muted" href="#!">Forgot password?</a></p>
            <p>Don't have an account? <a href="#!" class="link-info">Register here</a></p>-->

          </form>


        </div>

      </div>
      <div class="col-sm-6 px-0 d-none d-sm-block">
        <img src="background.jpg"
          alt="Login image" class="w-100 vh-100" style="object-fit: cover; object-position: left;">
      </div>

    </div>

  </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/web.js"></script>

</body>
</html>
