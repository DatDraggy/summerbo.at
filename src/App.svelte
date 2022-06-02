<script lang="ts">
  import { Router, Route, Link, navigate } from "svelte-navigator";
  import { onMount } from "svelte/internal";
  import { fly, fade } from "svelte/transition";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Graphics from "./components/Graphics.svelte";
  import Schedule from "./Schedule.svelte";
  import Privacy from "./Privacy.svelte";
  import Conduct from "./Conduct.svelte";
  import Team from "./Team.svelte";
  import Redirect from "./Redirect.svelte";
  import BackToTopButton from "./BackToTopButton.svelte";
  import TwentyNineteen from "./2019.svelte";
  import Tos from "./Tos.svelte";
  import Faq from "./Faq.svelte";
  import NotFound from "./NotFound.svelte";
  import Default from "./Default.svelte";
  let header;
  let departTime = new Date("2022-08-23T19:00:00+02:00");
  let regTime = new Date("2022-05-14T19:00:00+02:00");

  let days;
  let hours;
  let minutes;
  let seconds;
  let distance;

  const getTimeOffset = (reference) => {
    let now = new Date().getTime();
    distance = reference - now;
    days = Math.floor(distance / (1000 * 60 * 60 * 24));
    hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    seconds = Math.floor((distance % (1000 * 60)) / 1000);
  };

  let x = setInterval(() => getTimeOffset(departTime), 1000);

  onMount(() => {
    getTimeOffset(departTime);
  });
</script>

<Router>
  <main>
    <div class="banner">
      <Graphics type="hero" style="max-width:100%; margin-bottom:4rem" />
      <div class="registration-banner">
        <a
          class="registration-button"
          style="margin-bottom: 1rem;"
          href="http://reg.summerbo.at/">Register Here</a
        >
        <p
          style="font-size: 0.66rem; text-transform: uppercase; letter-spacing: 1.5px;"
        >
          Open until 30 June, 19:00 CEST
        </p>
      </div>
      <video class="banner-video" muted autoplay loop>
        <source src="/img/bgoptim.webm" type="video/webm" />
      </video>
    </div>
    <div class="content">
      <div class="header" bind:this={header}>
        <h1 class="logo">
          <span class="text-highlight"
            ><Link to="/" style="color:inherit">Summerbo.at</Link></span
          ><br />
          Furry Boat Party
        </h1>
        <nav>
          <Link to="/archive/2019">2019</Link>
          <Link to="/">2022</Link>
        </nav>
      </div>
      <div class="content-wrapper">
        <Route path="/">
          <div class="text-content">
            <h2 class="text-headline">
              Raise The Mainsail, it's&hellip;<br /><span
                class="color-secondary">All Paws on Deck 2022</span
              >
            </h2>
            <p>
              We&rsquo;re back! It&rsquo;s back! This year we begin boarding
              from the Estrel on August 23, 2022 at 19:00 and partying all
              evening, making a round trip through the rivers and canals of
              Berlin.
            </p>
            <div class="pricing">
              <div class="pricing-unit">
                <h3>Standard Ticket &mdash; &euro;35</h3>
                <ul>
                  <li>Access to cabin and amenities</li>
                </ul>
              </div>
              <div class="pricing-unit vip">
                <h3>VIP Upgrade &plus; &euro;15</h3>
                <ul>
                  <li>Support the party</li>
                  <li>One exclusive gift</li>
                </ul>
              </div>
            </div>
            <p>
              Our ship this year is <strong
                >newer, more modern and better equipped</strong
              >. Once again, we have a <strong>fully stocked bar</strong> with
              plenty of cider, a secure
              <strong>fursuit lounge with cooling</strong>, live
              <strong>DJs and silent disco</strong>.
            </p>
            <p>
              Registration opens on May 14. <a
                href="https://twitter.com/summerbo_at"
                >Follow us on Twitter to be reminded!</a
              >
              or on our <a href="https://t.me/summerboatinfo">Telegram</a> channel.
            </p>
          </div>
        </Route>
        <Route path="privacy" component={Privacy} />
        <Route path="conduct" component={Conduct} />
        <Route path="tos" component={Tos} />
        <Route path="team" component={Team} />
        <Route path="faq" component={Faq} />
        <Route path="register">
          <Redirect url="https://reg.summerbo.at" />
        </Route>
        <Route path="login">
          <Redirect url="https://reg.summerbo.at/login" />
        </Route>
        <Route path="attendees">
          <Redirect url="https://reg.summerbo.at/attendees" />
        </Route>
        <Route path="archive">
          <Redirect url="/archive/2019" external={false} />
        </Route>
        <Route path="archive/2019" component={TwentyNineteen} />
        <Route path="404" component={NotFound} />
        <Route component={Default} />
        <BackToTopButton scrollTo={header} />
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
              <li class="socials">
                <a href="mailto:contact@summerbo.at" class="social-link">
                  <Graphics
                    type={"email"}
                    style={"width: 24px; height: 24px"}
                  /></a
                >
                <a href="https://t.me/summerboat" class="social-link"
                  ><Graphics
                    type={"telegram"}
                    style={"width: 24px; height: 24px"}
                  /></a
                >
                <a href="https://twitter.com/summerbo_at" class="social-link">
                  <Graphics
                    type={"twitter"}
                    style={"width: 24px; height: 24px;"}
                  /></a
                >
              </li>
              <li>
                <Link to="../faq">FAQ</Link>
              </li>
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
            <ul>
              <li>
                <Link to="../privacy">Privacy</Link>
              </li>
              <li>
                <Link to="../conduct">Conduct</Link>
              </li>
              <li>
                <Link to="../tos">Terms of Service</Link>
              </li>
            </ul>
          </nav>
        </div>
      </footer>
    </div>
  </main>
</Router>
