<script lang="ts">
  import { Router, Route, Link, navigate } from "svelte-navigator";
  import { onMount } from "svelte/internal";
  import Privacy from "./Privacy.svelte";
  import Overlay from "./components/Overlay.svelte";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Legal from "./Legal.svelte";

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
    getTimeOffset(departTime);
  });
</script>

<Router>
  <div class="preview">
    <video class="bg" muted autoplay loop bind:this={video}>
      <source src="./img/bgoptim.webm" type="video/webm" />
    </video>
    <div class="previewContent">
      <div style="flex:1">
        <h1 class="textHuge">
          <Link to="/" style="text-decoration:none"
            ><strong>summerbo.at</strong></Link
          >
        </h1>
        <h2 class="textLarge">Furry Boat Party</h2>
      </div>

      <p>We're working on some things. Registration opens May 14th.</p>
      <h2 class="headerLine">Departing...</h2>
      <div class="timeDisplay tileSet ma-1">
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
      <div class="footer">
        <ComplianceNav primary />
      </div>
    </div>
    <Route path="/*legalRoute" component={Legal} let:params />
  </div>
</Router>

<style>
</style>
