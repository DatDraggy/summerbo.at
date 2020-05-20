<?php
require_once('funcs.php');

$head = '<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="mask-icon" href="/safari-pinned-tab.svg?v=2020XBbnOXWxGx" color="#3ec1c7">
    <link rel="shortcut icon" href="/favicon.ico?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png?v=2020XBbnOXWxGx">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png?v=2020XBbnOXWxGx">
    <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png?v=2020XBbnOXWxGx">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=2020XBbnOXWxGx">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png?v=2020XBbnOXWxGx">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=2020XBbnOXWxGx">
    <link rel="manifest" href="/manifest.json?v=2020XBbnOXWxGx">
    <meta name="msapplication-TileColor" content="#3ec1c7">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png?v=2020XBbnOXWxGx">
    <meta name="theme-color" content="#3ec1c7">

    <meta name="apple-mobile-web-app-title" content="All Paws on Deck '.getPartyDate('Y').' Summerbo.at Party">
    <meta name="application-name" content="All Paws on Deck '.getPartyDate('Y').' Summerbo.at Party">
    <script src="/js/yall-2.2.0.min.js"></script>
    <link rel="stylesheet" href="/css/css.css">
    <link rel="stylesheet" href="/css/badger-accordion.css">
    <link rel="stylesheet" href="/css/line-awesome.min.css">
';

$nav = '<nav id="nav" class="nav inactive">
    <div class="wrapper">
      <div class="navContent">
        <button class="navItem js-showNav">
          <i class="la la-bars"></i><span>Menu</span>
        </button>

        <div class="navLinks">
          <div class="navItem navItemLogo">
            <a class="navLink" href="/#"><img alt="Summerboat Logo" src="/images/logo@2x.png" width="48">
              <p style="margin-left: 1rem;" class="heading5 subheadline nomargin">All Paws on Deck</p></a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/admin/pickup">Check-in</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/admin/list">Attendee List</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/admin/stats">Statistics</a>
          </div>
          <div class="navItem">
            <a class="navLink" href="/admin/help">Help</a>
          </div>
        </div>
      </div>
    </div>
  </nav>';

$footer = '</div> <!-- App -->
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