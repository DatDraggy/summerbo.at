<script>
  import { slide } from "svelte/transition";
  export let question;
  let isOpen = false;
  const toggle = () => (isOpen = !isOpen);
</script>

<div class="question">
  <button class="text-highlight" on:click={toggle} aria-expanded={isOpen}
    ><svg
      style="flex:none"
      width="20"
      height="20"
      fill="none"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
      viewBox="0 0 24 24"
      stroke="currentColor"><path d="M9 5l7 7-7 7" /></svg
    >
    {question}
  </button>
  {#if isOpen}
    <div transition:slide|local={{ duration: 300 }}>
      <slot />
    </div>
  {/if}
</div>

<style>
  button {
    border: none;
    background: none;
    font-size: inherit;
    font-family: inherit;
    cursor: pointer;
    margin: 0;
    padding-bottom: 0.5em;
    padding-top: 0.5em;
    text-align: left;
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0;
  }
  button > * {
    margin-right: 0.5rem;
  }
  button:hover {
    text-decoration: underline;
  }
  svg {
    transition: transform 0.2s ease-in;
  }
  .question {
    margin-bottom: 3rem;
  }
  [aria-expanded="true"] svg {
    transform: rotate(0.25turn);
  }
</style>
