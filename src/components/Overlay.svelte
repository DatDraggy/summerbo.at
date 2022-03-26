<script lang="ts">
  import { fly } from "svelte/transition";
  export let onClose = () => {};
</script>

<div class="overlay" in:fly={{ x: 48, opacity: 1 }}>
  <button class="closeOverlay" on:click={onClose}>Close</button>
  <div class="overlayContent">
    <slot />
  </div>
</div>
