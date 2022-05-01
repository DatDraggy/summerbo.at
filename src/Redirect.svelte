<script lang="ts">
  import { onMount } from "svelte";
  export let url = "";
  const redirect = (url) => {
    window.setTimeout(() => {
      window.location.replace(url);
    }, 500);
  };
  onMount(() => {
    redirect(url);
  });
</script>

<div>
  <h2 class="text-large text-headline">One moment&hellip;</h2>
  <p>Redirecting you to <a href={url}>{url}</a></p>
</div>
