<script lang="ts">
  import { Link, Router } from "svelte-navigator";
  export let className = "";
  export { className as class };
  export let style = "";
  export let primary = false;
</script>

<Router {primary}>
  <nav class={`headerNavigation ${className}`} {style}>
    <Link class="headerNavItem" to="/privacy">Privacy</Link>
    <Link class="headerNavItem" to="/conduct">Conduct</Link>
  </nav>
</Router>
