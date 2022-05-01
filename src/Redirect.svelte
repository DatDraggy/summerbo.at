<script lang="ts">
  import { onMount } from "svelte";
  import { useNavigate } from "svelte-navigator";
  export let url = "";
  export let external = true;

  const navigate = useNavigate();

  const redirect = (url) => {
    window.setTimeout(() => {
      if (external) {
        window.location.replace(url);
      } else {
        navigate(url);
      }
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
