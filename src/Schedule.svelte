<script lang="ts">
  import { Router, Route, Link } from "svelte-navigator";
  import { fly, fade } from "svelte/transition";
  import Graphics from "./components/Graphics.svelte";
  import { cubicOut } from "svelte/easing";
</script>

<Router>
  <div class="contentBlock main">
    <Route path="/">
      <div class="textContent">
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
              From<br /><span class="textLarge">The Estrel Hotel</span>
            </p>
          </div>
        </div>
        <ul class="points">
          <li>Ship Capacity: 300</li>
          <li>19:30 CET - 23:30 CET</li>
          <li>Party all night on a boat!</li>
          <li>Recommended for party animals</li>
          <li>Live DJs</li>
          <li>Drinks and snacks</li>
          <li>Equipped fursuit lounge</li>
        </ul>
        <p>
          Join us on our White Pearl through the city of Berlin. Starting from
          Treptower Park, we will be on a 4 hour trip to the center of Berlin
          and back. Get a drink at the bar and enjoy the sun while listening to
          our DJ’s. Just like the evening party we will have a dancefloor where
          you can dance all day long. Want to suit up? No problem! The
          fursuitlounge downstairs is of course also available with all the
          stuff you need. This event is perfect for fursuiters and photographers
          or for the people who want to start Eurofurence the relaxing way.
        </p>
      </div>
    </Route>
  </div></Router
>
