<script lang="ts">
  import Overlay from "./components/Overlay.svelte";
  import ComplianceNav from "./components/ComplianceNav.svelte";
  import Privacy from "./Privacy.svelte";
  import Conduct from "./Conduct.svelte";
  import { router } from "tinro";
  export let legalRoute;
</script>

<div>
  {#if legalRoute}
    <Overlay onClose={() => router.goto("/")}>
      <div>
        <ComplianceNav class="ma-1" />
        <Privacy />
        <Conduct />
      </div>
    </Overlay>
  {/if}
</div>
