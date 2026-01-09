<script lang="ts">
    import {onMount} from "svelte";
    import RegistrationForm from './RegistrationForm.svelte';
    import WaitlistForm from "./WaitlistForm.svelte";
    import SoldOutBlock from "./SoldOutBlock.svelte";
    import RegistrationClosed from "./RegistrationClosed.svelte";
    import WaitlistSpotFree from "./WaitlistSpotFree.svelte";
    import Login from "./Login.svelte";
    import {findGetParameter} from '../helper/uri.js';
    import PartySelection from "./PartySelection.svelte";
    import BackArrow from "../components/BackArrow.svelte";

    export let departTime;

    let party: number = 2;

    let isLoggedIn = false;
    let isLoading = true;
    let isRegistered = false;
    let isWaitlistOpen = false;
    let isRegistrationPossible = false;
    let isRegistrationOpen = false;
    let isWaitlisted = false;
    let boatSlotsA = 0;
    let boatSlotsB = 0;

    let id: number | null = null;
    let nickname = '';
    let isFursuiter = false;
    let isVIP = false;
    let country = '';
    let list = true;
    let boat: number|null = null;
    let status: number|null = null;

    let waitlistId: number|null = null;

    let email = '';

    let error: string | null = null;
    let loginUrl = 'https://identity.eurofurence.org/oauth2/auth?client_id=a6384576-d0f4-402f-8c58-dd2fb69e83cc&redirect_uri=https%3A%2F%2Fapi.summerbo.at%2Fauth%2Fcallback&response_type=code&scope=profile+email&state=';

    function handleStatusUpdate() {
        status = 0;
    }

    onMount(fetchDetails);

    async function fetchDetails() {
        isLoading = true;
        try {
            const secret = findGetParameter('secret') ?? '';
            const response = await fetch('https://api.summerbo.at/auth?secret=' + secret + '&party=' + party, {
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
                nickname = data.nickname ?? '';
                if (party !== 0) {
                    if (isRegistered) {
                        id = data.id;
                        isFursuiter = !!data.fursuiter;
                        isVIP = !!data.sponsor;
                        country = data.country;
                        list = !!data.list;
                        boat = data.boat;
                        status = data.status;
                    } else if (isWaitlisted) {
                        waitlistId = data.waitlist_id;
                    } else {
                        email = data.email;
                    }
                }
                isLoggedIn = true;
            } else {
                loginUrl += data.state;
            }
            isRegistrationPossible = !!data.is_registration_possible;
            isWaitlistOpen = !!data.is_waitlist_open;
            isRegistrationOpen = !!data.is_registration_open;
            boatSlotsA = data.boat_slots_a;
            boatSlotsB = data.boat_slots_b;
        } catch (e: any) {
            error = e.message;
            console.log('Error checking login status:', e);
        } finally {
            isLoading = false;
        }
    }

    function handleParty(value: CustomEvent) {
        if (value.detail == 1 || value.detail == 2) {
            party = value.detail;
            fetchDetails();
        }
    }

    function resetParty() {
        party = 0;
        id = null;
        isFursuiter = false;
        isVIP = false;
        country = '';
        list = true;
        status = null;
        boat = null;
        waitlistId = null;
        email = '';

        fetchDetails();
    }
</script>

<div class="text-content">
    {#if party}
        <span class="back-button" role="button" tabindex="0" on:keydown={resetParty} on:click={resetParty}><BackArrow/> Change Party</span>
    {/if}

    <h2 class="text-headline">Registration</h2>

    {#if isLoading}
        <h2 class="text-headline-line">
            Loading
        </h2>
    {:else if error}
        <p>Error: {error}</p>
    {:else}
        {#if party}
            {#if !isRegistrationOpen && !isWaitlisted}
                <RegistrationClosed/>
            {:else if !isRegistrationPossible && !isWaitlistOpen && !isLoggedIn}
                <SoldOutBlock/>
            {/if}
        {/if}

        {#if isLoggedIn}
            <!-- Logged in -->
            {#if !party}
                <PartySelection {departTime} on:selectedParty="{handleParty}"/>
            {:else}
                <!-- Party Selected -->
                {#if isWaitlisted && isRegistrationPossible}
                    <WaitlistSpotFree />
                {/if}

                {#if isRegistered || isRegistrationPossible}
                    {#if status === 0}
                        <h2 class="text-headline-line">
                            Pending Confirmation
                        </h2>
                        <p>
                            You have not yet confirmed your Email address. Please do so now! Otherwise, you will not be billed and won't be able to partake on the day of the party.
                        </p>
                    {/if}

                    <RegistrationForm party={party} id={id} isRegistered={isRegistered} nickname={nickname} isFursuiter={isFursuiter}
                                      isVIP={isVIP} country={country} list={list} boat={boat} boatSlotsA={boatSlotsA} boatSlotsB={boatSlotsB} on:updateStatus={handleStatusUpdate} />
                {:else if isWaitlisted}
                    <p>
                        Your waitlist number is {waitlistId}. This number will decrease if a spot before yours is freed.
                    </p>
                {:else if isWaitlistOpen}
                    <WaitlistForm party={party} email={email}/>
                {:else if isRegistrationOpen}
                    <SoldOutBlock/>
                {/if}
                <!-- /Party Selected -->
            {/if}

            <!-- /Logged in -->
        {:else}
            <Login loginUrl={loginUrl} />
        {/if}
    {/if}
</div>