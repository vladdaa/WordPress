<!doctype html>
<html lang="en">

<head>
  <?php wp_head(); ?>
</head>

<body>
  <!--wrapper-->
  <div class="wrapper">
    <!--start header -->
    <?php get_header(); ?>
    <!--end header -->
    <!--start page wrapper -->
    <?php get_template_part('page-wrapper'); ?>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button-->
    <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <?php get_footer(); ?>
  </div>
  <!--end wrapper-->
  <!--start switcher-->
  <?php get_template_part('switcher-wrapper'); ?>
  <!--end switcher-->
  
<!-- </body>
</html> -->