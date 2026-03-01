<script lang="ts">
  import { onMount } from "svelte";
  import { router } from "tinro";
  export let url = "";
  export let external = true;

  const redirect = (url: string) => {
    window.setTimeout(() => {
      if (external) {
        window.location.replace(url);
      } else {
        router.goto(url);
      }
    }, 500);
  };
  onMount(() => {
    redirect(url);
  });
</script>

<svelte:head>
  <meta http-equiv="refresh" content="0; URL={url}">
</svelte:head>

<div>
  <h2 class="text-large text-headline">One moment&hellip;</h2>
  <p>Redirecting you to <a href={url}>{url}</a></p>
</div>
