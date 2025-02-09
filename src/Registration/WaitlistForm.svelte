<script lang="ts">
    import {onMount} from "svelte";

    export let email: string;

    let isLoading = false;
    let error = '';
    let id: int|null = null;

    onMount(() => {
        const form = document.getElementById('waitlist-form') as HTMLFormElement;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            isLoading = true;
            error = '';

            try {
                const response = await fetch('https://api.summerbo.at/auth/waitlist', {
                    method: 'POST',
                    body: JSON.stringify({
                        email: email,
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || 'Unknown error during registration');
                }

                if (data.id) {
                    id = data.id;
                } else {
                    throw new Error('No ID received from API, please try reloading the page.');
                }
            } catch (err: any) {
                console.error('Error submitting details:', err);
                error = err.message;
            } finally {
                isLoading = false;
            }
        });
    });
</script>

{#if id}
    <p>
        Your waitlist spot is {id}
    </p>
{/if}

<p>Sadly we do not have any more slots available for the party. But sign up for the waitinglist!</p>

<form class="form-wrapper" id="waitlist-form">
    <div class="input-wrapper">
        <label for="email"><span>Email</span></label>
        <input name="email" id="email" class="input" placeholder="Email" autocomplete="email" required bind:value={email} />
    </div>
    <div style="display: flex">
        <button type="submit" style="margin-right: auto" class="button button-primary">Submit</button>
    </div>
</form>