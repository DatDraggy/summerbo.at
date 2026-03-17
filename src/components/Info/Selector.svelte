<script>
    import Chill from "./Chill.svelte";
    import Party from "./Party.svelte";
    import {formatDate, formatTime} from "../../helper/date.js";

    export let departTime;

    const departTimeChill = new Date(departTime);
    departTimeChill.setUTCDate(departTimeChill.getUTCDate() + 5);
    departTimeChill.setUTCHours(departTimeChill.getUTCHours() - 5);

    let selected = 'party';

    function handleParty() {
        selected = 'party';
    }
    function handleChill() {
        selected = 'chill';
    }

    const partyDateString = `${formatDate(departTime, {month: 'short', day: 'numeric'})}, ${formatTime(departTime, {hour: '2-digit', minute: '2-digit'})}`;
    const chillDateString = `${formatDate(departTimeChill, {month: 'short', day: 'numeric'})}, ${formatTime(departTimeChill, {hour: '2-digit', minute: '2-digit'})}`;
</script>

<div class="selector-container">
    <div role="radio" tabindex="0" on:click={handleParty} on:keydown={handleParty} class="selector selector-left" aria-checked={selected === 'party'} class:selector-active={selected === 'party'}>
        <h3><i>__PARTY_SLOGAN__</i>: Party</h3>
        <p>{partyDateString}</p>
    </div>
    <div role="radio" tabindex="0" on:click={handleChill} on:keydown={handleChill} class="selector selector-right " aria-checked={selected === 'chill'} class:selector-active={selected === 'chill'}>
        <h3><i>__PARTY_SLOGAN__</i>: Chill</h3>
        <p>{chillDateString}</p>
    </div>
</div>
<div class="selector-container-extender">
    {#if selected === 'party'}
        <Party {departTime}/>
    {:else}
        <Chill {departTimeChill}/>
    {/if}
</div>