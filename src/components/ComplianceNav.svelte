<script lang="ts">
  export let className = "";
  export { className as class };
  export let style = "";
</script>

<nav class={`headerNavigation ${className}`} {style}>
  <a class="headerNavItem" href="/privacy">Privacy</a>
  <a class="headerNavItem" href="/conduct">Conduct</a>
</nav>
