<?php
$footer = '<footer id="contact" class="footer-widgets">
          <div class="container">
            <div class="row section">

              <!-- About Us -->
              <div class="col-md-3 col-sm-6 mb-sm-100">
                <div class="widget about-widget">
                  <h5 class="header-widget">About Us</h5>
                  <p>Since 2012 we organise parties in Europe. .</p>

                  <ul class="social-links">
                    <li><a href="https://github.com/DatDraggy/summerbo.at"><i class="fa fa-github"></i></a></li>
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                    <li><a href="#"><i class="fa fa-youtube-play"></i></a></li>
                  </ul>
                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

              
              <div class="container">
            
            <!-- Contact Form -->
            <div class="row">
              <form action="../assets/contact-form/contact-form.php" method="POST" id="contact-form-1" class="form-ajax">
                  <div class="col-md-offset-2 col-md-4 wow fadeInUp" data-wow-duration="1s">

                    <!-- Name -->
                    <div class="form-group">
                      <input type="text" name="name" id="name-contact-1" class="form-control validate-locally" placeholder="Enter your name">
                      <label for="name-contact-1">Name</label>
                      <span class="pull-right alert-error"></span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                      <input type="email" name="email" id="email-contact-1" class="form-control validate-locally" placeholder="Enter your email">
                      <label for="email-contact-1">Email</label>
                      <span class="pull-right alert-error"></span>
                    </div>

                  </div>

                  <div class="col-md-4 wow fadeInUp" data-wow-duration="1s">

                    <!-- Message -->
                    <div class="form-group">
                      <textarea name="message" id="message-contact-1" class="form-control" rows="5" placeholder="Your Message"></textarea>
                      <label for="message-contact-1">Message</label>
                    </div>
                    <div>
                      <input type="submit" class="btn pull-right" value="Send Message">
                    </div>

                    <!-- Ajax Message -->
                    <div class="ajax-message col-md-12 no-gap"></div>

                  </div><!-- / .col-md-4 -->

                </form>
            </div><!-- / .row -->
          </div><!-- / .container -->

            </div><!-- / .row -->
          </div><!-- / .container -->


          <!-- Copyright -->
          <div class="copyright">
            <div class="container">
              <div class="row">

                <div class="col-sm-6">
                  <small>&copy; 2018 Made by <a class="no-style-link" href="#" target="_blank">Hunter</a>, Hausken & <a class="no-style-link" href="https://kieran.de" target="_blank">Kieran</a></small>
                </div>

                <div class="col-sm-6">
                  <small><a href="#page-top" class="pull-right to-the-top">To the top<i class="fa fa-angle-up"></i></a></small>
                </div>

              </div><!-- / .row -->
            </div><!-- / .container -->
          </div><!-- / .copyright -->

        </footer><!-- / .footer-widgets -->
        <!-- ========== Scripts ========== -->

        <script src="assets/js/vendor/jquery-2.1.4.min.js"></script>
        <script src="assets/js/vendor/google-fonts.js"></script>
        <script src="assets/js/vendor/jquery.easing.js"></script>
        <script src="assets/js/vendor/jquery.waypoints.min.js"></script>
        <script src="assets/js/vendor/bootstrap.min.js"></script>
        <script src="assets/js/vendor/bootstrap-hover-dropdown.min.js"></script>
        <script src="assets/js/vendor/smoothscroll.js"></script>
        <script src="assets/js/vendor/jquery.localScroll.min.js"></script>
        <script src="assets/js/vendor/jquery.scrollTo.min.js"></script>
        <script src="assets/js/vendor/jquery.stellar.min.js"></script>
        <script src="assets/js/vendor/jquery.parallax.js"></script>
        <script src="assets/js/vendor/slick.min.js"></script>
        <script src="assets/js/vendor/jquery.easypiechart.min.js"></script>
        <script src="assets/js/vendor/countup.min.js"></script>
        <script src="assets/js/vendor/isotope.min.js"></script>
        <script src="assets/js/vendor/jquery.magnific-popup.min.js"></script>
        <script src="assets/js/vendor/wow.min.js"></script>
        <script src="assets/js/vendor/jquery.mb.YTPlayer.min.js"></script>
        <script src="assets/js/vendor/jquery.ajaxchimp.js"></script>';

$userarea = '<li><a href="https://summerbo.at/login.html">Login/Registration<span class="sr-only"></span></a></li>';
if(!empty($_SESSION['userId'])) {
  $userarea = '<li><a href="https://summerbo.at/userarea.html">User Area<span class="sr-only"></span></a></li>';
}

$nav = '<nav class="navbar navbar-default navbar-fixed-top mega navbar-fw">
          <div class="navbar-header page-scroll">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <!-- Logo -->
            <a class="navbar-brand" href="index.html"><img class="navbar-logo" width="46" src="assets/images/logo@2x.png" alt="Hot Summer Nights - Logo"></a>
          </div>

          <!-- Navbar Links -->
          <div id="navbar" class="navbar-collapse collapse page-scroll navbar-right">
            <ul class="nav navbar-nav">
              <li><a href="https://summerbo.at/#home">Home<span class="sr-only"></span></a></li>
              <li><a href="https://summerbo.at/#about">About<span class="sr-only"></span></a></li>
              <li><a href="https://summerbo.at/#prices">Prices<span class="sr-only"></span></a></li>
              <li><a href="https://summerbo.at/#gallery">Gallery<span class="sr-only"></span></a></li>
              <li><a href="https://summerbo.at/#faq">FAQ<span class="sr-only"></span></a></li>
              <li><a href="https://summerbo.at/#team">Team<span class="sr-only"></span></a></li>
              <li><a href="https://summerbo.at/#contact">Contact<span class="sr-only"></span></a></li>
              '. $userarea .'
            </ul><!-- / .nav .navbar-nav -->
          </div><!--/.navbar-collapse -->
        </nav><!-- / .navbar -->';