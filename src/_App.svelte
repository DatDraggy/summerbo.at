<script lang="ts">
  import { Router, Route, Link } from "svelte-navigator";
  import { onMount } from "svelte/internal";
  import { fly, fade } from "svelte/transition";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Graphics from "./components/Graphics.svelte";
  import Schedule from "./Schedule.svelte";

  let departTime = new Date("2023-08-1T19:00:00+02:00");

  let days;
  let hours;
  let minutes;
  let seconds;
  let video;

  const getTimeOffset = (reference) => {
    let now = new Date().getTime();
    let distance = reference - now;
    days = Math.floor(distance / (1000 * 60 * 60 * 24));
    hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    seconds = Math.floor((distance % (1000 * 60)) / 1000);
  };

  let x = setInterval(() => getTimeOffset(departTime), 1000);

  onMount(() => {
    video.play();
    getTimeOffset(departTime);
  });
  let preview = true;
  let overlay = false;
</script>

<main class="layout">
  <Router>
    <header class="header">
      <div class="headerSection secondary">
        <Link to="/" class="logo"><Graphics type="logo" />summerbo.at</Link>
      </div>
      <nav class="headerSection primary center headerNavigation">
        <Link to="schedule" class="headerNavItem">2023</Link>
      </nav>
      <nav
        class="headerSection headerNavigation"
        style="flex:none; margin-left:auto;"
      >
        <Link to="/" class="headerNavItem headerNavButton primary">Login</Link>
        <Link to="/" class="headerNavItem headerNavButton secondary"
          >Register</Link
        >
      </nav>
    </header>
    <section class="mainContent">
      <Route path="/">
        <div
          class="coolbg"
          style="overflow:hidden;position:relative; width:100%; padding: var(--ui-unit-medium); color:white; align-items:center; display: flex; flex-direction:column;"
        >
          <video
            style="mix-blend-mode:multiply; position:absolute; min-width: 100%; max-width: none !important; top:0; z-index: 0"
            muted
            autoplay
            loop
            bind:this={video}
          >
            <source src="./img/bgoptim.webm" type="video/webm" />
          </video>
          <div style="z-index:500">
            <Graphics width="100%" type="hero" />
            <button>Register Now</button>
          </div>
        </div>
      </Route>
      <Route path="schedule/*"
        ><div class="contentWrapper">
          <Schedule />
        </div>
      </Route>
    </section>
  </Router>
  <footer class="footer">
    <div class="footerContent">
      <h3 class="headerLine">Departing</h3>
      <div class="timeDisplay tileSet">
        <div class="timeDisplayUnit">
          <p><span class="textLarge">{days ? days : "0"}</span><br />Days</p>
        </div>
        <div class="timeDisplayUnit">
          <p>
            <span class="textLarge">{hours ? hours : "0"}</span><br />Hours
          </p>
        </div>
        <div class="timeDisplayUnit">
          <p>
            <span class="textLarge">{minutes ? minutes : "0"}</span><br
            />Minutes
          </p>
        </div>
        <div class="timeDisplayUnit">
          <p>
            <span class="textLarge">{seconds ? seconds : "0	"}</span><br
            />Seconds
          </p>
        </div>
      </div>
    </div>
    <div class="footerContent">
      <h3 class="headerLine">Departure Information</h3>
      <p>
        September 2nd, 2023<br />
        <strong>19:30</strong>
        Landungsbrücken<br />Hamburg, Germany
      </p>
    </div>
    <div class="footerContent">
      <h3 class="headerLine">Legal</h3>
      <ComplianceNav />
    </div>
  </footer>
</main>

<style>
</style>
