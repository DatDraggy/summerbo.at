<script lang="ts">
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Legal from "./Legal.svelte";
  import {onMount} from "svelte";
  import { Route } from "tinro/cmp";

  let video: HTMLVideoElement;

  onMount(() => {
    video.play();
  });
</script>

<div>
  <div class="banner">
    <video class="banner-video" muted autoplay loop bind:this={video}>
      <source src="./img/bgoptim.webm" type="video/webm" />
    </video>
  </div>
    <div class="content">
      <div style="margin-top: auto;margin-bottom: 50%;">
        <h1 class="textHuge">
          <a href="/" style="text-decoration:none"
            ><strong>summerbo.at</strong></a
          >
        </h1>
        <h2 class="textLarge">Furry Boat Party</h2><br>
        <p>We're working on some things. Check again later. 👀🦊</p>
      </div>

      <div class="footer">
        <ComplianceNav />
      </div>
    </div>
    <Route path="/*legalRoute" let:meta>
      <Legal legalRoute={meta.pattern} />
    </Route>
</div>

<style>
</style>
