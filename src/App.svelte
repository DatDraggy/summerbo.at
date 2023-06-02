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
  import TwentyTwentyTwo from "./2022.svelte";
  import Tos from "./Tos.svelte";
  import Faq from "./Faq.svelte";
  import NotFound from "./NotFound.svelte";
  import Default from "./Default.svelte";
  import Glympse from "./Glympse.svelte";
  let header;
  let departTime = new Date("2023-09-02T19:00:00+02:00");
  let regTime = new Date("2023-05-06T19:00:00+02:00");

  let timeTillReg = 1;

  let days;
  let hours;
  let minutes;
  let seconds;
  let distance;

  const getTimeOffset = (reference) => {
    let now = new Date().getTime();
    distance = Math.max(reference - now, 0);
    days = Math.floor(distance / (1000 * 60 * 60 * 24));
    hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    seconds = Math.floor((distance % (1000 * 60)) / 1000);
  };

  let x = setInterval(() => getTimeOffset(departTime), 1000);

  onMount(() => {
    getTimeOffset(departTime);

    let now = new Date().getTime();
    timeTillReg = Math.max(regTime - now, 0);
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
          {timeTillReg === 0 ? 'Open until June 30, 19:00 CEST' : `Starts at ${regTime.toLocaleDateString('en-US', {day: "numeric", month: "short", year: "numeric"})} ${regTime.toLocaleTimeString('de-DE', {hour: "numeric", minute: "numeric"})}`}
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
          <Link to="/archive/2022">2022</Link>
          <Link to="/">2023</Link>
        </nav>
      </div>
      <div class="content-wrapper">
        <Route path="/">
          <div class="text-content">
            <h2 class="text-headline">
              Lower The Mainsail, it's the&hellip;<br /><span
                class="color-secondary">Cursed Cruise 2023!</span
              >
            </h2>
            <p>
              Hamburch meine Perle! With Eurofurence moving to Hamburg, of course so did we!
              This year we depart from Überseebrücke on the Elbe in Hamburg on September 2nd, 2023 at 19:00 and party all
              evening, making a round trip on the Elbe through Hamburg with live music on the upper AND lower deck.
            </p>
            <div class="pricing">
              <div class="pricing-unit">
                <h3>Standard Ticket &mdash; 35&euro;</h3>
                <ul>
                  <li>Access to cabin and amenities</li>
                </ul>
              </div>
              <div class="pricing-unit vip">
                <h3>VIP Upgrade &plus; 20&euro;</h3>
                <ul>
                  <li>Support the party</li>
                  <li>One exclusive gift</li>
                </ul>
              </div>
            </div>
            <p>
              This years ship is <em>even</em> <strong>larger with more decks for more room,
              more modern and equipped with AC</strong>.
              This time, we'll have <strong>multiple fully stocked bars</strong> with
              plenty of cider, a larger
              <strong>fursuit lounge with cooling</strong> and live
              <strong>DJs for music inside <em>and</em> outside</strong>.
            </p>
            <p>
              Registration opens on {regTime.toLocaleDateString('en-US', {day: "numeric", month: "short"})}. <a
                href="https://twitter.com/summerbo_at"
                >Follow us on Twitter to be reminded</a
              >
              or join our <a href="https://t.me/summerboatinfo">Telegram</a> channel.
            </p>
          </div>
        </Route>
        <Route path="privacy" component={Privacy} />
        <Route path="conduct" component={Conduct} />
        <Route path="tos" component={Tos} />
        <Route path="team" component={Team} />
        <Route path="faq" component={Faq} />
        <Route path="glympse" component={Glympse} />
        <Route path="register">
          <Redirect url="https://reg.summerbo.at" />
        </Route>
        <Route path="login">
          <Redirect url="https://reg.summerbo.at/login" />
        </Route>
        <Route path="badge">
          <Redirect url="https://summerbo.at" />
        </Route>
        <Route path="attendees">
          <Redirect url="https://reg.summerbo.at/attendees" />
        </Route>
        <Route path="contact">
          <Redirect url="https://reg.summerbo.at/contact" />
        </Route>

        <Route path="archive">
          <Redirect url="/archive/2019" external={false} />
        </Route>
        <Route path="archive/2019" component={TwentyNineteen} />
        <Route path="archive/2022" component={TwentyTwentyTwo} />

        <Route path="404" component={NotFound} />
        <Route component={Default} />
        <BackToTopButton scrollTo={header} />
      </div>
      <footer class="footer">
        <div class="footer-section {distance === 0 ? 'hidden' : ''}">
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
          <p>September 2nd, 2023<br />Überseebrücke, Hamburg</p>
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
