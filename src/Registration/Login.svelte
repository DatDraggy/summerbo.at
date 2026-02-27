<script lang="ts">
    export let loginUrl: string;
    export let isUnauthenticated: boolean = false;
</script>

<h2 class="text-headline-line">
    Eurofurence Identity
</h2>

{#if isUnauthenticated}
    <p class="error-message">
        Your session has expired. Please log in again to continue.
    </p>
{/if}

<p>
    In order to sign up for our party you'll need a valid Eurofurence registration. Your ticket price will
    be
    charged through your Eurofurence registration.
</p>

<p>
    <a class="eurofurence-button"
       href={loginUrl}>
        <img alt="Eurofurence Logo" src="img/eurofurence.png"/>
        <span>Login with Eurofurence</span>
    </a>
</p>

<p>
    Once logged in you'll be able to register and change your details.
</p>