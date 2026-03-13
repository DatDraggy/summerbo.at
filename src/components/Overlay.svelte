<script lang="ts">
  import { fly } from "svelte/transition";
  export let onClose = () => {};
</script>

<div class="overlay" in:fly={{ x: 48, opacity: 1 }}>
  <button class="closeOverlay" on:click={onClose}>×</button>
  <div class="overlayContent">
    <slot />
  </div>
</div>

<style>
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .closeOverlay {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
  }

  .overlayContent {
    position: relative;
  }
</style>
