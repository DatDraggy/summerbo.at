<script lang="ts">
    import {findGetParameter} from '../helper/uri.js';
    import {onMount} from "svelte";

    let error = '';
    let success = false;
    let isLoading = true;

    onMount(async () => {
        try {
            const token = findGetParameter('token') ?? '';
            const response = await fetch('https://api.summerbo.at/confirm?token=' + token, {
                method: 'POST',
                credentials: 'include',
            });

            const data = await response.json();

            if (!response.ok) {
                console.log()
                throw new Error(`API request failed with status ${response.status}: ${data.error}`);
            } else {
                success = true;
            }
        } catch (e: any) {
            error = e.message;
            console.log('Error confirming token:', e);
        } finally {
            isLoading = false;
        }
    });
</script>

<h2 class="text-headline">
    Email Confirmation
</h2>
{#if isLoading}
    <h2 class="text-headline-line">
        Loading
    </h2>
{:else if error}
    <p>
        {error}
    </p>
{:else if success}
    <p>
        Successfully confirmed!
    </p>
{/if}