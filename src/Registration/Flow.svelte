<script lang="ts">
    import {onMount} from "svelte";
    import RegistrationForm from './RegistrationForm.svelte';
    import WaitlistForm from "./WaitlistForm.svelte";
    import SoldOutBlock from "./SoldOutBlock.svelte";
    import RegistrationClosed from "./RegistrationClosed.svelte";
    import WaitlistSpotFree from "./WaitlistSpotFree.svelte";
    import Login from "./Login.svelte";
    import {findGetParameter} from '../helper/uri.js';

    let isLoggedIn = false;
    let isLoading = true;
    let isRegistered = false;
    let isWaitlistOpen = false;
    let isRegistrationPossible = false;
    let isRegistrationOpen = false;
    let isWaitlisted = false;

    let id: number | null = null;
    let nickname = '';
    let isFursuiter = false;
    let isVIP = false;
    let country = '';
    let isPublic = true;

    let waitlistId: number|null = null;

    let email = '';

    let error: string | null = null;
    let loginUrl = 'https://identity.eurofurence.org/oauth2/auth?client_id=a6384576-d0f4-402f-8c58-dd2fb69e83cc&redirect_uri=https%3A%2F%2Fapi.summerbo.at%2Fauth%2Fcallback&response_type=code&scope=profile+email&state=';

    onMount(async () => {
        try {
            const secret = findGetParameter('secret') ?? '';
            const response = await fetch('https://api.summerbo.at/auth?secret=' + secret, {
                method: 'GET',
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error(`API request failed with status ${response.status}`);
            }

            const data = await response.json();
            if (data.is_logged_in) {
                isRegistered = !!data.is_registered;
                isWaitlisted = !!data.is_waitlisted;
                if (isRegistered) {
                    id = data.id;
                    nickname = data.nickname ?? '';
                    isFursuiter = !!data.is_fursuiter;
                    isVIP = !!data.is_vip;
                    country = data.country;
                    isPublic = !!data.is_public;
                } else if (isWaitlisted) {
                    waitlistId = data.waitlist_id;
                } else {
                    email = data.email;
                }
                isLoggedIn = true;
            } else {
                loginUrl += data.state;
            }
            isRegistrationPossible = !!data.is_registration_possible;
            isWaitlistOpen = !!data.is_waitlist_open;
            isRegistrationOpen = !!data.is_registration_open;
        } catch (e: any) {
            error = e.message;
            console.log('Error checking login status:', e);
        } finally {
            isLoading = false;
        }
    });
</script>

<div class="text-content">
    <h2 class="text-headline">Registration</h2>
    {#if isLoading}
        <h2 class="text-headline-line">
            Loading
        </h2>
    {:else if error}
        <p>Error: {error}</p>
    {:else}
        {#if !isRegistrationOpen && !isWaitlisted}
            <RegistrationClosed/>
        {:else if !isRegistrationPossible && !isWaitlistOpen && !isLoggedIn}
            <SoldOutBlock/>
        {/if}

        {#if isLoggedIn}
            {#if isWaitlisted && isRegistrationPossible}
                <WaitlistSpotFree />
            {/if}

            {#if isRegistered || isRegistrationPossible}
                <RegistrationForm id={id} isRegistered={isRegistered} nickname={nickname} isFursuiter={isFursuiter}
                                  isVIP={isVIP} country={country} isPublic={isPublic}/>
            {:else if isWaitlisted}
                <p>
                    Your waitlist number is {waitlistId}. This number will decrease if a spot before yours is freed.
                </p>
            {:else if isWaitlistOpen}
                <WaitlistForm/>
            {:else}
                <SoldOutBlock/>
            {/if}
        {:else}
            <Login loginUrl={loginUrl} />
        {/if}
    {/if}
</div>