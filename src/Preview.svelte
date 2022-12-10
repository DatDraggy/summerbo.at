<script lang="ts">
  import { Router, Route, Link, navigate } from "svelte-navigator";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Legal from "./Legal.svelte";
  import {onMount} from "svelte/internal";

  let video;

  onMount(() => {
    video.play();
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
            ><strong>summerbo.at</strong></Link>
        </h1>
        <h2 class="textLarge">Furry Boat Party</h2>
      </div>

      <p>We're working on some things. Check again later. 👀🦊</p>

      <div class="footer">
        <ComplianceNav primary />
      </div>
    </div>
    <Route path="/*legalRoute" component={Legal} primary={false} />
  </div>
</Router>

<style>
</style>
