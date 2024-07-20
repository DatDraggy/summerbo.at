<script lang="ts">
  import { Router, Route, Link, navigate } from "svelte-navigator";
  import { onMount } from "svelte/internal";
  import { fly, fade } from "svelte/transition";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Graphics from "./components/Graphics.svelte";
  import Privacy from "./Privacy.svelte";
  import Conduct from "./Conduct.svelte";
  import Team from "./Team.svelte";
  import Redirect from "./Redirect.svelte";
  import BackToTopButton from "./BackToTopButton.svelte";
  import TwentyNineteen from "./2019.svelte";
  import TwentyTwentyTwo from "./2022.svelte";
  import TwentyTwentyThree from "./2023.svelte";
  import Tos from "./Tos.svelte";
  import Faq from "./Faq.svelte";
  import NotFound from "./NotFound.svelte";
  import Default from "./Default.svelte";
  import Glympse from "./Glympse.svelte"
  import Benefactors from "./Benefactors.svelte";
  let header;
  let departTime = new Date("2024-09-17T19:00:00+02:00");
  let regTime = new Date("2024-03-09T19:00:00+01:00");

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
          <Link to="/archive/2023">2023</Link>
          <Link to="/">2024</Link>
        </nav>
      </div>
      <div class="content-wrapper">
        <Route path="/">
          <div class="text-content">
            <h2 class="text-headline">
              Load up on CHOOH2, it's the&hellip;<br /><span
                class="color-secondary">Cyber Yacht 2024!</span
              >
            </h2>
            <p>
              Hamburch wir sind zurück! Another Eurofurence in Hamburg, another Summerbo.at in Hamburg!
              Unlike last year, this time we'll board the great and grand MS Hamburg at Überseebrücke to allow even more furs to join us on the Elbe on September 17th, 2024 at 18:30 and depart at 19:00 to party all
              evening, making a round trip on the Elbe through Hamburg with live music on all decks, including the TWO outer decks!
            </p>
            <div class="pricing">
              <div class="pricing-unit">
                <h3>Standard Ticket &mdash; 40&euro;</h3>
                <ul>
                  <li>Access to cabin and amenities</li>
                </ul>
              </div>
              <div class="pricing-unit vip">
                <h3>VIP Upgrade &plus; 25&euro;</h3>
                <ul>
                  <li>Support the party</li>
                  <li>One exclusive gift</li>
                </ul>
              </div>
            </div>
            <p>
              Our ship in Hamburg is <em>even</em> <strong>larger than the one in Berlin, with more decks for more room,
              more modern, equipped with AC and colorful lighting</strong>.
              As last time, we'll have <strong>multiple fully stocked bars</strong> with
              plenty of cider, a larger
              <strong>fursuit lounge with cooling</strong> and live
              <strong>DJs for music inside <em>and</em> outside</strong>.
            </p>
            <p>
              Made possible by our lovely 2023 <Link to="/benefactors">Benefactors</Link> and current VIPs!
            </p>
            <p>
              Registration opens on {regTime.toLocaleDateString('en-US', {day: "numeric", month: "short"})}.
              Follow us on
              <a href="https://twitter.com/summerbo_at">Twitter</a>
              or
              <a href="https://bsky.app/profile/summerbo.at">Bsky</a>
              and join our <a href="https://t.me/summerboatinfo">Telegram</a> channel to be reminded!
            </p>
          </div>
        </Route>
        <Route path="privacy" component={Privacy} />
        <Route path="conduct" component={Conduct} />
        <Route path="tos" component={Tos} />
        <Route path="team" component={Team} />
        <Route path="faq" component={Faq} />
        <Route path="glympse" component={Glympse} />
        <Route path="benefactors" component={Benefactors} />
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
        <Route path="archive/2023" component={TwentyTwentyThree} />

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
          <h3 class="text-headline-line">Boarding</h3>
          <p>18:30 September 17th, 2024<br />Überseebrücke, Hamburg</p>
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
                <a href="https://bsky.app/profile/summerbo.at" class="social-link">
                  <Graphics
                          type={"bsky"}
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
