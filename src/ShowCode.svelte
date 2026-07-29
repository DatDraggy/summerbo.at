<script lang="ts">
    import { onMount } from 'svelte';
    import QrCode from './components/QrCode.svelte';
    import { findGetParameter } from './helper/uri.js';

    let attendeeId: string | null = null;
    let ticket: string | null = null;

    onMount(() => {
        attendeeId = findGetParameter('id');
        // Check-in reads a bare number as a Eurofurence reg ID; the 's' prefix
        // selects lookup by summerbo.at attendee ID instead.
        ticket = attendeeId ? 's' + attendeeId : null;
    });
</script>

<style>
    .container {
        max-width: 400px;
        margin: 2rem auto;
        padding: 2rem;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    .qr-wrapper {
        margin: 2rem 0;
    }
</style>

<div class="text-content">
    <h2 class="text-headline">Your Check-in Code</h2>

    {#if ticket}
        <div class="container">
            <p>
                Present this code to a staff member when boarding the boat.
                Please have your legal ID/passport ready as well.
            </p>
            <div class="qr-wrapper">
                <QrCode data={ticket} />
            </div>
            <p>
                Your Attendee ID: <strong>{attendeeId}</strong>
            </p>
        </div>
    {:else}
        <p>No Attendee ID provided. Please use the link from your email or <a href="/register">log in to the registration system</a> to get your code.</p>
    {/if}
</div>
