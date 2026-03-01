<script lang="ts">
  import { fade } from "svelte/transition";

  export let scrollTo: HTMLElement;

  let scrollY: number;
  let scrollX: number;
  let innerHeight: number;

  const scrollToTop = () => {
    scrollTo.scrollIntoView();
  };
</script>

<svelte:window bind:scrollY bind:scrollX bind:innerHeight />
{#if scrollY > innerHeight}
  <div in:fade={{ duration: 500 }}>
    <button on:click={scrollToTop}>Back To Top</button>
  </div>
{/if}

<style>
  div {
    z-index: 50;
    position: fixed;
    right: 2rem;
    bottom: 2rem;
  }
  button {
    font-family: inherit;
    font-size: 0.875rem;
    line-height: 1;
    text-transform: uppercase;
    font-weight: 800;
    background: var(--color-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 0;
    width: 4rem;
    height: 4rem;
    padding: 1rem;
    border-radius: 50%;
  }
</style>
