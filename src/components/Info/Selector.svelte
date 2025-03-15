<script>
import Chill from "./Chill.svelte";
import Party from "./Party.svelte";

let selected = 'party';

function handleParty() {
    selected = 'party';
}
function handleChill() {
    selected = 'chill';
}
</script>

<div class="selector-container">
    <div role="radio" tabindex="0" on:click={handleParty} on:keydown={handleParty} class="selector selector-left" aria-checked={selected === 'party'} class:selector-active={selected === 'party'}>
        <h3><i>Space Ship</i>: Party</h3>
        <p>Sept 2nd, 18:30</p>
    </div>
    <div role="radio" tabindex="0" on:click={handleChill} on:keydown={handleChill} class="selector selector-right " aria-checked={selected === 'chill'} class:selector-active={selected === 'chill'}>
        <h3><i>Space Ship</i>: Chill</h3>
        <p>Sept 7th, 14:30</p>
    </div>
</div>
<div class="selector-container-extender">
    {#if selected === 'party'}
        <Party/>
    {:else}
        <Chill/>
    {/if}
</div>