<script lang="ts">
  import { Router, Route, Link, navigate } from "svelte-routing";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Legal from "./Legal.svelte";
  import {onMount} from "svelte";

  let video;

  onMount(() => {
    video.play();
  });
</script>

<Router>
  <div class="banner">
    <video class="banner-video" muted autoplay loop bind:this={video}>
      <source src="./img/bgoptim.webm" type="video/webm" />
    </video>
  </div>
    <div class="content">
      <div style="margin-top: auto;margin-bottom: 50%;">
        <h1 class="textHuge">
          <Link to="/" style="text-decoration:none"
            ><strong>summerbo.at</strong></Link>
        </h1>
        <h2 class="textLarge">Furry Boat Party</h2><br>
        <p>We're working on some things. Check again later. 👀🦊</p>
      </div>

      <div class="footer">
        <ComplianceNav primary />
      </div>
    </div>
    <Route path="/*legalRoute" component={Legal} primary={false} />
</Router>

<style>
</style>
