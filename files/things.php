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

              <!-- Instagram Feed -->
              <div class="col-md-3 col-sm-6 mb-sm-100" hidden>
                <div class="widget gallery-widget">
                  <h5 class="header-widget">Instagram Feed</h5>
                  <ul>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                  </ul>
                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

              <!-- Twitter Feed -->
              <div class="col-md-3 col-sm-6 mb-sm-100" hidden>
                <div class="widget twitter-widget">
                  <h5 class="header-widget">Twitter Feed</h5>
                  <ul>

                    <li>
                      <a href="#"><i class="fa fa-twitter"></i></a>
                      <p>5 Reasons You Should Take a Sabbatical from Creative Work <a href="#">http://enva.to/NTa6F</a> by <a href="#">@envato</a> <span>- AUG 10</span></p>
                    </li>

                    <li>
                      <a href="#"><i class="fa fa-twitter"></i></a>
                      <p>What is the enemy of <a href="#">#creativity</a>? <a href="#">http://enva.to/hVl5G</a>  [VIDEO] <br>by <a href="#">@envato</a> <span>- AUG 5</span></p>
                    </li>

                  </ul>
                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

              <!-- Newsletter -->
              <div class="col-md-3 col-sm-6" hidden>
                <div class="widget newsletter-widget">
                  <h5 class="header-widget">Newsletter</h5>

                  <form method="post">
                    <div class="form-group">
                      <input type="email" name="w-newssletter" placeholder="Join our newsletter">
                      <button type="submit"><i class="fa fa-send-o"></i></button>
                    </div>
                  </form>

                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

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

$userarea = '<li><a href="https://summerbo.at/login.html">User Area<span class="sr-only"></span></a></li>';
if(!empty($_SESSION['userid'])) {
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