<!DOCTYPE html>
<html lang="en">

<head>
  @isset($pagePreview)
    <meta name="robots" content="noindex,nofollow">
    <script>window.__PAGE_PREVIEW__ = {{ \Illuminate\Support\Js::from($pagePreview) }};</script>
  @endisset
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>West East Freight</title>
  <!-- favicons Icons -->
  <link rel="apple-touch-icon" sizes="180x180"
    href="/assets/img/west-east-freight-meta-tag.png">
  <link rel="icon" type="image/png" sizes="32x32"
    href="/assets/img/west-east-freight-meta-tag.png">
  <link rel="icon" type="image/png" sizes="16x16"
    href="/assets/img/west-east-freight-meta-tag.png">
  <meta name="description" content="West East Freight is a leading logistics and freight forwarding company, providing reliable and efficient shipping solutions worldwide. Our services include air freight, sea freight, road transport, and customs clearance. We are committed to delivering excellence in every shipment, ensuring your goods reach their destination safely and on time.">

  <!-- fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
    rel="stylesheet">
  
  <!-- CSS files -->

  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/animate.min.css">
  <link rel="stylesheet" href="/assets/css/custom-animate.css">
  <link rel="stylesheet" href="/assets/css/swiper.min.css">
  <link rel="stylesheet" href="/assets/css/font-awesome-all.css">
  <link rel="stylesheet" href="/assets/css/jarallax.css">
  <link rel="stylesheet" href="/assets/css/jquery.magnific-popup.css">
  <link rel="stylesheet" href="/assets/css/flaticon.css">
  <link rel="stylesheet" href="/assets/css/owl.carousel.min.css">
  <link rel="stylesheet" href="/assets/css/owl.theme.default.min.css">
  <link rel="stylesheet" href="/assets/css/nice-select.css">
  <link rel="stylesheet" href="/assets/css/jquery-ui.css">
  <link rel="stylesheet" href="/assets/css/aos.css">
  <link rel="stylesheet" href="/assets/css/odometer.min.css">
   <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/responsive.css">
</head>

<body class="custom-cursor" data-aos-easing="ease" data-aos-duration="1200" data-aos-delay="0" cz-shortcut-listen="true">
  <div class="custom-cursor__cursor"></div>
  <div class="custom-cursor__cursor-two"></div>
  
  <!--Start Preloader-->
  <div class="loader js-preloader">
    <div></div>
    <div></div>
    <div></div>
  </div>
  <!--End Preloader-->
  
  <div id="app"></div>

  <!-- Vue main entry point -->
  @vite('resources/frontend/main.js')
  
  <!-- jQuery and plugins -->
  <script src="/assets/js/jquery-latest.js"></script>
  <script src="/assets/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/jarallax.min.js"></script>
  <script src="/assets/js/jquery.ajaxchimp.min.js"></script>
  <script src="/assets/js/jquery.appear.min.js"></script>
  <script src="/assets/js/swiper.min.js"></script>
  <script src="/assets/js/jquery.magnific-popup.min.js"></script>
  <script src="/assets/js/jquery.validate.min.js"></script>
  <script src="/assets/js/wNumb.min.js"></script>
  <script src="/assets/js/wow.js"></script>
  <script src="/assets/js/isotope.js"></script>
  <script src="/assets/js/owl.carousel.min.js"></script>
  <script src="/assets/js/jquery-ui.js"></script>
  <script src="/assets/js/jquery.nice-select.min.js"></script>
  <script src="/assets/js/marquee.min.js"></script>
  <script src="/assets/js/countdown.min.js"></script>
  <script src="/assets/js/gsap.js"></script>
  <script src="/assets/js/jquery.circleType.js"></script>
  <script src="/assets/js/jquery.fittext.js"></script>
  <script src="/assets/js/jquery.lettering.min.js"></script>
  <script src="/assets/js/jquery-sidebar-content.js"></script>
  <script src="/assets/js/aos.js"></script>
  <script src="/assets/js/odometer.min.js"></script>
  <script src="/assets/js/typed-2.0.11.js"></script>
  <script src="/assets/js/jquery.circle-progress.min.js"></script>
  <script src="/assets/js/ScrollTrigger.js"></script>
  <script src="/assets/js/SplitText.js"></script>
  <script src="/assets/js/script.js"></script>
  
  <!-- jsPDF for PDF generation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
  

</body>
</html>
