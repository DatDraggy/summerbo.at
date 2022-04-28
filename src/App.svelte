<script lang="ts">
  import { Router, Route, Link } from "svelte-navigator";
  import { onMount } from "svelte/internal";
  import { fly, fade } from "svelte/transition";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Graphics from "./components/Graphics.svelte";
  import Schedule from "./Schedule.svelte";
  import Privacy from "./Privacy.svelte";
  import Conduct from "./Conduct.svelte";
  import Team from "./Team.svelte";
  let departTime = new Date("2022-08-23T19:00:00+02:00");

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

<Router>
  <main>
    <div class="banner">
      <Graphics width="auto" type="hero" style="max-width:100%;" />
      <video class="banner-video" muted autoplay loop bind:this={video}>
        <source src="./img/bgoptim.webm" type="video/webm" />
      </video>
    </div>
    <div class="content">
      <div class="header">
        <h1 class="logo">
          <span class="text-highlight"
            ><Link to="/" style="color:inherit">Summerbo.at</Link></span
          ><br />
          Furry Boat Party
        </h1>
        <nav>
          <Link to="2019">2019</Link>
          <Link to="/">2022</Link>
        </nav>
      </div>
      <div class="content-wrapper">
        <Route path="/">
          <h2 class="text-headline">
            Raise The Mainsail, it's&hellip;<br /><span class="color-secondary"
              >All Paws on Deck 2022</span
            >
          </h2>
          <p>
            We&rsquo;re back! It&rsquo;s back! This year we&rsquo;re departing
            from the Estrel on August 23, 2022 at 19:30 and partying all
            evening, making a round trip through the rivers and canals of
            Berlin.
          </p>
          <p>
            Our ship this year is <strong
              >newer, more modern and better equipped</strong
            >. Once again, we have a <strong>fully stocked bar</strong> with
            plenty of cider, a secure
            <strong>fursuit lounge with cooling</strong>, live
            <strong>DJs and silent disco</strong>.
          </p>
          <p>
            Registration opens at XX:XX on May 14. <a href="#"
              >Our telegram bot can remind you!</a
            >
          </p>
        </Route>
        <Route path="privacy" component={Privacy} />
        <Route path="conduct" component={Conduct} />
        <Route path="team" component={Team} />
      </div>
      <footer class="footer">
        <div class="footer-section">
          <h3 class="text-headline-line">Boarding</h3>
          <div class="time-display tileSet">
            <div class="time-unit">
              <p>
                <span class="text-large">{days ? days : "0"}</span><br />Days
              </p>
            </div>
            <div class="time-unit">
              <p>
                <span class="text-large">{hours ? hours : "0"}</span><br />Hours
              </p>
            </div>
            <div class="time-unit">
              <p>
                <span class="text-large">{minutes ? minutes : "0"}</span><br
                />Minutes
              </p>
            </div>
            <div class="time-unit">
              <p>
                <span class="text-large">{seconds ? seconds : "0	"}</span><br
                />Seconds
              </p>
            </div>
          </div>
        </div>
        <div class="footer-section">
          <h3 class="text-headline-line">Departing</h3>
          <p>August 23, 2022<br />Estrel Hotel, Berlin<br />Biergarten Dock</p>
        </div>
        <div class="footer-section">
          <h3 class="text-headline-line">Details</h3>
          <nav class="footer-grid">
            <ul>
              <li>
                <a href="jsidfjs">Contact</a>
              </li>
              <li>
                <Link to="../privacy">Privacy</Link>
              </li>
              <li>
                <Link to="../conduct">Conduct</Link>
              </li>
            </ul>
            <ul>
              <li>
                <Link to="../team">Team</Link>
              </li>
              <li>
                <a
                  href="https://github.com/DatDraggy/summerbo.at"
                  target="_blank">Source Code</a
                >
              </li>
            </ul>
          </nav>
        </div>
      </footer>
    </div>
  </main>
</Router>
