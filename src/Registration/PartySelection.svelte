<script lang="ts">
    import {createEventDispatcher} from "svelte";

    let party: string = '0';

    const dispatch = createEventDispatcher();

    function handleSelection() {
        if (party) {
            dispatch('selectedParty', parseInt(party));
        }
    }
</script>

<div class="party-selection-wrapper">
    <div class="party-selection-container">
        <input bind:group={party} type="radio" name="party" value="1" id="party1">
        <label class="party-selection-element" for="party1">
            <p><i>Space Ship</i>: Party</p>
            <h3>
                Tue, Sept 2nd, 18:30<br>
                The established party you all know and love
            </h3>
        </label>
        <input bind:group={party} type="radio" name="party" value="2" id="party2">
        <label class="party-selection-element" for="party2">
            <p><i>Space Ship</i>: Chill</p>
            <h3>
                Sun, Sept 7th, 14:00<br>
                Our new relaxed trip around different parts of Hamburg
            </h3>
        </label>
    </div>
    <div class="party-selection-container">
    <button on:click={handleSelection} class="button button-primary button-narrow">Continue</button>
    </div>
</div>

<style>
    .button-narrow {
        margin-top: 20px;
        width: fit-content;
    }
    .party-selection-wrapper {
        display: flex;
        flex-direction: column;
        align-content: center;
        justify-content: center;
    }

    .party-selection-container {
        display: flex;
        flex-direction: row;
        gap: 2rem;
        justify-content: center;
    }

    /* the interesting part follows below: */

    .party-selection-element {
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        --box-border--border: linear-gradient(105deg, rgb(255 46 144) 0%, rgb(61 35 185) 100%);

        /* classic 9-slide-scaling with 2px border 4px rounded corners. change `rx` and `ry` parameters to adjust border-radius */
        --box--border__top-left: url("data:image/svg+xml,<svg width='10' height='10' viewBox='0 0 10 10' fill='none' xmlns='http://www.w3.org/2000/svg'><rect x='1' y='1' width='18' height='18' rx='4' ry='4' stroke='%23000' stroke-width='2' /></svg>");
        --box--border__top: url("data:image/svg+xml,<svg preserveAspectRatio='none' width='100' height='10' viewBox='0 0 100 10' fill='none' xmlns='http://www.w3.org/2000/svg'><line x1='-1' y1='1' x2='101' y2='1' stroke='%23000' stroke-width='2'/></svg>");
        --box--border__top-right: url("data:image/svg+xml,<svg width='10' height='10' viewBox='0 0 10 10' fill='none' xmlns='http://www.w3.org/2000/svg'><rect x='-9' y='1' width='18' height='18' rx='4' ry='4' stroke='black' stroke-width='2' /></svg>");
        --box--border__left: url("data:image/svg+xml,<svg preserveAspectRatio='none' width='10' height='100' viewBox='0 0 10 100' fill='none' xmlns='http://www.w3.org/2000/svg'><line x1='1' y1='-1' x2='1' y2='101' stroke='%23000' stroke-width='2'/></svg>");
        --box--border__right: url("data:image/svg+xml,<svg preserveAspectRatio='none' width='10' height='100' viewBox='0 0 10 100' fill='none' xmlns='http://www.w3.org/2000/svg'><line x1='9' y1='-1' x2='9' y2='101' stroke='%23000' stroke-width='2'/></svg>");
        --box--border__bottom-left: url("data:image/svg+xml,<svg width='10' height='10' viewBox='0 0 10 10' fill='none' xmlns='http://www.w3.org/2000/svg'><rect x='1' y='-9' width='18' height='18' rx='4' ry='4' stroke='%23000' stroke-width='2' /></svg>");
        --box--border__bottom: url("data:image/svg+xml,<svg preserveAspectRatio='none' width='100' height='10' viewBox='0 0 100 10' fill='none' xmlns='http://www.w3.org/2000/svg'><line x1='-1' y1='9' x2='101' y2='9' stroke='%23000' stroke-width='2'/></svg>");
        --box--border__bottom-right: url("data:image/svg+xml,<svg width='10' height='10' viewBox='0 0 10 10' fill='none' xmlns='http://www.w3.org/2000/svg'><rect x='-9' y='-9' width='18' height='18' rx='4' ry='4' stroke='black' stroke-width='2' /></svg>");

        padding: 1rem;
        position: relative;
        overflow: hidden;
        font-weight: 700;
        width: 100%;
        max-width: 30ch;
    }

    .party-selection-element:hover {
        background-color: #ececec;
    }

    .party-selection-container input[type="radio"] {
        display: none;
    }

    .party-selection-container input[type="radio"]:checked+label {
        background-color: var(--color-secondary-weak);
    }

    .party-selection-element p:first-child {
        margin-bottom: 1rem;
    }

    .party-selection-element::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--box-border--border);
        mask:
                var(--box--border__top-left) 0 0 / .625rem .625rem,
                var(--box--border__top) .625rem 0 / calc(100% - 1.25rem) .625rem,
                var(--box--border__top-right) 100% 0 / .625rem .625rem,
                var(--box--border__left) 0 .625rem / .625rem calc(100% - 1.25rem),
                var(--box--border__right) 100% .625rem / .625rem calc(100% - 1.25rem),
                var(--box--border__bottom-left) 0 100% / .625rem .625rem,
                var(--box--border__bottom) .625rem 100% / calc(100% - 1.25rem) .625rem,
                var(--box--border__bottom-right) 100% 100% / .625rem .625rem;
        mask-repeat: no-repeat;
    }
</style>