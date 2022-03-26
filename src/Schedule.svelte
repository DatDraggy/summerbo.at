<script lang="ts">
  import { Router, Route, Link } from "svelte-navigator";
  import { fly, fade } from "svelte/transition";
  import Graphics from "./components/Graphics.svelte";
  import { cubicOut } from "svelte/easing";

  function peelOpen(node, { duration }) {
    let h = getComputedStyle(node).height;
    let hn = parseInt(h.split("p")[0]);
    console.log(h);
    return {
      duration,
      css: (t) => {
        let i = cubicOut(t);
        return `
              height: ${i * hn}px
              `;
      },
    };
  }
</script>

<Router>
  <div class="contentBlock centered coolbg">
    <Graphics width="100%" type="hero" />
  </div>
  <div class="contentBlock main" style="height:100%">
    <div>
      <h2 class="textSmallHeader">
        <strong
          >Summerbo.at is proud to offer two distinct experiences for
          Eurofurence 2022</strong
        >
      </h2>
    </div>
    <ul class="tileSet">
      <Link to="fursuit-cruise" class="tile">
        <p>
          Fursuit Cruise<br />13:00 CET
        </p>
      </Link>
      <Link to="party-cruise" class="tile">
        <p>
          Party Cruise<br />19:00 CET
        </p>
      </Link>
    </ul>
    <div class="textContent">
      <Route path="fursuit-cruise">
        <div in:peelOpen={{ duration: 350 }} style="overflow-y: hidden;">
          <h3 class="textLarge"><strong>Fursuit Cruise</strong></h3>
          <div class="timeDisplay tileSet">
            <div class="timeDisplayUnit">
              <p>August<br /><span class="textLarge">23</span></p>
            </div>
            <div class="timeDisplayUnit">
              <p>Departing<br /><span class="textLarge">13:00</span></p>
            </div>
            <div class="timeDisplayUnit">
              <p>Duration<br /><span class="textLarge">4 Hours</span></p>
            </div>
            <div class="timeDisplayUnit">
              <p>
                From<br /><span class="textLarge">Treptower Park</span>
              </p>
            </div>
          </div>
          <ul class="points">
            <li>Ship Capacity: 300</li>
            <li>13:00 CET - 17:00 CET</li>
            <li>Stops at Berlin's famous Museum Insel</li>
            <li>Recommended for suiters and photographers</li>
            <li>Live DJs</li>
            <li>Drinks and snacks</li>
            <li>Equipped fursuit lounge</li>
          </ul>
          <p>
            Join us on our White Pearl through the city of Berlin. Starting from
            Treptower Park, we will be on a 4 hour trip to the center of Berlin
            and back. Get a drink at the bar and enjoy the sun while listening
            to our DJ’s. Just like the evening party we will have a dancefloor
            where you can dance all day long. Want to suit up? No problem! The
            fursuitlounge downstairs is of course also available with all the
            stuff you need. This event is perfect for fursuiters and
            photographers or for the people who want to start Eurofurence the
            relaxing way.
          </p>
        </div>
      </Route>
      <Route path="party-cruise">
        <div in:peelOpen={{ duration: 350 }} style="overflow-y: hidden;">
          <h3 class="textLarge"><strong>Party Cruise</strong></h3>
          <div class="timeDisplay tileSet">
            <div class="timeDisplayUnit">
              <p>August<br /><span class="textLarge">23</span></p>
            </div>
            <div class="timeDisplayUnit">
              <p>Departing<br /><span class="textLarge">19:30</span></p>
            </div>
            <div class="timeDisplayUnit">
              <p>Duration<br /><span class="textLarge">4 Hours</span></p>
            </div>
            <div class="timeDisplayUnit">
              <p>
                From<br /><span class="textLarge">Treptower Park</span>
              </p>
            </div>
          </div>
          <ul class="points">
            <li>Ship Capacity: 300</li>
            <li>19:30 CET - 24:30 CET</li>
            <li>Party all night on a boat!</li>
            <li>Recommended for party animals</li>
            <li>Live DJs</li>
            <li>Drinks and snacks</li>
            <li>Equipped fursuit lounge</li>
          </ul>
          <p>
            Join us on our White Pearl through the city of Berlin. Starting from
            Treptower Park, we will be on a 4 hour trip to the center of Berlin
            and back. Get a drink at the bar and enjoy the sun while listening
            to our DJ’s. Just like the evening party we will have a dancefloor
            where you can dance all day long. Want to suit up? No problem! The
            fursuitlounge downstairs is of course also available with all the
            stuff you need. This event is perfect for fursuiters and
            photographers or for the people who want to start Eurofurence the
            relaxing way.
          </p>
        </div>
      </Route>
    </div>
    <Route path="/">
      <p>
        <strong>
          Whether you're looking for a relaxing cruise and opportunities for
          photos, or a party on Berlin's many rivers and canals, this year we
          got you covered.</strong
        >
      </p>
    </Route>
  </div></Router
>
