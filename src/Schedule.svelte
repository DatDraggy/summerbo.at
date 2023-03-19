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
            <p>September<br /><span class="textLarge">2nd</span></p>
          </div>
          <div class="timeDisplayUnit">
            <p>Departing<br /><span class="textLarge">19:30</span></p>
          </div>
          <div class="timeDisplayUnit">
            <p>Duration<br /><span class="textLarge">4 Hours</span></p>
          </div>
          <div class="timeDisplayUnit">
            <p>
              From<br /><span class="textLarge">Landungsbrücken</span>
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
          <li>Larger fursuit lounge</li>
        </ul>
        <p>
          Join us on the MS Princess through the city of Hamburg. Starting from
          Landungsbrücken, we will be on a 4 hour trip on the Elbe and back. Get
          a drink at one of our bars and enjoy the sun while listening to
          our DJ’s. Just like the evening party we will have a dancefloor where
          you can dance all cruise long. Want to suit up? No problem! The now improved and larger
          fursuit lounge on the lowest of our three decks is of course also available with all the
          things you need. This event is perfect for fursuiters and photographers
          or for people who want to start Eurofurence in a fun way.
        </p>
      </div>
    </Route>
  </div></Router
>
