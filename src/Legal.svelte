<script lang="ts">
  import { Router, Route, Link, navigate } from "svelte-navigator";
  import Overlay from "./components/Overlay.svelte";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Privacy from "./Privacy.svelte";
  export let legalRoute;
</script>

<Router>
  {#if legalRoute}
    <Overlay onClose={() => navigate("/")}>
      <Router>
        <ComplianceNav class="ma-1" />
        <Route path="privacy" component={Privacy} />
        <Route path="conduct">conductual</Route>
      </Router>
    </Overlay>
  {/if}
</Router>
