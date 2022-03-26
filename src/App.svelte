<script lang="ts">
  import { Router, Route, Link } from "svelte-navigator";
  import { onMount } from "svelte/internal";
  import { fly, fade } from "svelte/transition";
  import Graphics from "./components/Graphics.svelte";
  import Schedule from "./Schedule.svelte";

  let departTime = new Date("Aug 23, 2022 13:00:00");

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
  <video class="bg" muted autoplay loop bind:this={video}>
    <source src="./img/bgoptim.webm" type="video/webm" />
  </video>
  <Router>
    <header class="header">
      <div class="headerSection secondary">
        <Link to="/" class="logo"><Graphics type="logo" />summerbo.at</Link>
      </div>
      <nav class="headerSection primary center headerNavigation">
        <Link to="/" class="headerNavItem">Home</Link>
        <Link to="schedule" class="headerNavItem">Schedule</Link>
        <Link to="/" class="headerNavItem">Onboard Offerings</Link>
        <Link to="/" class="headerNavItem">Pricing</Link>
        <Link to="/" class="headerNavItem">Attendees</Link>
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
        <div class="contentWrapper">
          <div class="contentBlock centered coolbg">
            <Graphics width="100%" type="hero" />
          </div>
          <div class="contentBlock main">
            <div class="textContent">
              <h2 class="textHuge"><strong>Summerbo.at 2022</strong></h2>
              <h3 class="textLarge">Furry Boat Party</h3>
              <p>Ahoy mateys! Please join us for 2022.</p>
              <ul class="tileSet">
                <Link to="schedule" class="tile">
                  <p>See the schedule</p>
                </Link>
              </ul>
            </div>
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
        August 22, 2022<br /><strong>13:00</strong> Treptower Park,
        <strong>19:30</strong>
        Estrel Hotel<br />Berlin, Germany
      </p>
    </div>
    <div class="footerContent">
      <h3 class="headerLine">Departing</h3>
      <p>Footer Content</p>
    </div>
  </footer>
</main>

<style>
</style>
