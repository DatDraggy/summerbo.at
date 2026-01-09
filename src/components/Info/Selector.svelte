<script>
import Chill from "./Chill.svelte";
import Party from "./Party.svelte";

export let departTime;

const departTimeChill = new Date(departTime);
departTimeChill.setDate(departTimeChill.getDate() + 5);
departTimeChill.setHours(departTimeChill.getHours() - 5);

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
        <h3><i>Placeholder</i>: Party</h3>
        <p>{departTime.toLocaleDateString('en-US', {month: 'short'})} {departTime.getDate()}, {departTime.toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})}</p>
    </div>
    <div role="radio" tabindex="0" on:click={handleChill} on:keydown={handleChill} class="selector selector-right " aria-checked={selected === 'chill'} class:selector-active={selected === 'chill'}>
        <h3><i>Placeholder</i>: Chill</h3>
        <p>{departTimeChill.toLocaleDateString('en-US', {month: 'short'})} {departTimeChill.getDate()}, {departTimeChill.toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})}</p>
    </div>
</div>
<div class="selector-container-extender">
    {#if selected === 'party'}
        <Party {departTime}/>
    {:else}
        <Chill {departTimeChill}/>
    {/if}
</div>