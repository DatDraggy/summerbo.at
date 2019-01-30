<?php

$head = '<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:title" content="Hot Summer Nights // Furry Boat Party" />
  <meta property="og:description" content="Summerbo.at is a super awesome furry party, on a boat, in Berlin!" />
  <meta property="og:url" content="https://summerbo.at" />
  <meta property="og:image" content="https://summerbo.at/apple-icon-152x152.png" />
  <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <meta name="msapplication-TileColor" content="#1afbc4">
  <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
  <meta name="theme-color" content="#1afbc4">
  <script src="/js/yall-2.2.0.min.js"></script>
  <link rel="stylesheet" href="/css/css.css">
  <link rel="stylesheet" href="/css/badger-accordion.css">
  <link rel="stylesheet" href="/css/line-awesome.min.css">';

$nav = '<nav id="nav" class="nav inactive">
    <div class="wrapper">
      <div class="navContent">
        <button class="navItem js-showNav">
          <i class="la la-bars"></i><span>Menu</span>
        </button>

        <div class="navLinks">
          <div class="navItem navItemLogo">
            <a class="navLink" href="/#"><img alt="Summerboat Logo" src="/images/logo@2x.png" width="48">
              <p style="margin-left: 1rem;" class="heading5 subheadline nomargin">Hot Summer Nights</p></a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/#about">About</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/#tickets">Tickets</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/#faq">FAQ</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/#team">Team</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/attendees" >Attendees</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/tos#rules" >Conduct</a>
          </div>
        </div>
        <div class="navButtons">
          <div class="navItem">
            <a class="button navItemButton buttonPrimary" href="/register"><i
                      style="font-size: 24px; margin-right: .5rem; margin-top:-4px;"
                      class="la la-ticket"></i>Register</a>
          </div>
          <div class="navItem">
            <a class="button navItemButton buttonSecondary" href="/login"><i
                      style="font-size: 24px; margin-right: .5rem; margin-top:-4px;" class="la la-key"></i>Login</a>
          </div>
        </div>
      </div>
    </div>
  </nav>';

$footer = '<section id="footer">
    <div class="footer" style="background-color: var(--colorPurple); color: white;">
      <div class="wrapper row">
        <div class="content">
          <h2>Follow Us!</h2>
          <ul class="blankList textSmall">
            <li><a class="unselectable flexCentered" href="https://t.me/summerboat" target="_blank"><i class="la la24 la-paper-plane"></i> @summerboat</a></li>
            <li><a class="unselectable flexCentered" href="https://twitter.com/summerbo_at" target="_blank"><i class="la la24 la-twitter-square"></i> @summerbo_at</a></li>
            <li><a class="unselectable flexCentered" href="mailto:team@summerbo.at" target="_blank"><i class="la la24 la-envelope"></i> team@summerbo.at</a></li>
			<li><a class="unselectable flexCentered" href="https://t.me/summerboatinfo" target="_blank"><i class="la la24 la-info"></i> @summerboatinfo</a></li>
			<li><a class="unselectable flexCentered" href="https://open.spotify.com/user/1131941723/playlist/08YOlrMp5pIcpdrQjEwfig?si=DtMG_0EuTdiHLj8nUvhWIw" target="_blank"><i class="la la24 la-spotify"></i> Summerbo.at Spotify</a></li>
          </ul>
          <ul class="blankList textSmall" style="margin-bottom: 2rem">
            <li><a href="contact" class="unselectable flexCentered" >Contact Us</a></li>
            <li><a href="/tos#terms" class="unselectable flexCentered">Terms of Service</a></li>
            <li><a href="/tos#rules" class="unselectable flexCentered">Rules of Conduct</a></li>
            <li><a href="/tos#privacy" class="unselectable flexCentered">Privacy</a></li>
			      <li><a href="/tos#cookies" class="unselectable flexCentered">Cookies</a></li>
			      <li><a href="https://github.com/datdraggy/summerbo.at" target="_blank" class="unselectable flexCentered">Source</a></li>
          </ul>
          <p class="textSmall">Content Copyright &copy; Summerbo.at Organization Group 2018-19</p>
        </div>
      </div>
    </div>
  </section>
</div> <!-- App -->
<script>document.addEventListener("DOMContentLoaded", yall);</script>
<script src="/js/badger-accordion.min.js"></script>
<script src="/js/app.js"></script>
<!-- Start Cookie Plugin -->
<script>
  window.cookieconsent_options = {
    message: \'This website uses cookies to fully function. To login, you will have to allow them.\',
    dismiss: \'Ok, I accept\',
    learnMore: \'More infos about our cookies\',
    link: \'https://summerbo.at/tos\',
    theme: \'light-floating\'
  };
</script>
<script src="/js/cookie-consent/script.js"></script>';
