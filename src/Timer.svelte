<script lang="ts">
    import { onMount } from "svelte";

    export let departTime;
    export let regTime;

    let timeTillReg = 1;

    let days;
    let hours;
    let minutes;
    let seconds;
    let distance;

    const getTimeOffset = (reference) => {
        let now = new Date().getTime();
        distance = Math.max(reference - now, 0);
        days = Math.floor(distance / (1000 * 60 * 60 * 24));
        hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        seconds = Math.floor((distance % (1000 * 60)) / 1000);
    };

    onMount(() => {
        const interval = setInterval(() => getTimeOffset(departTime), 1000);
        getTimeOffset(departTime);

        let now = new Date().getTime();
        timeTillReg = Math.max(regTime.getTime() - now, 0);

        return () => {
            clearInterval(interval);
        };
    });
</script>

<div class="footer-section {distance === 0 ? 'hidden' : ''}">
    <h3 class="text-headline-line">Boarding</h3>
    <div class="time-display tileSet">
        <div class="time-unit">
            <p>
                <span class="text-large">{days ? days : "0"}</span><br/>Days
            </p>
        </div>
        <div class="time-unit">
            <p>
                <span class="text-large">{hours ? hours : "0"}</span><br/>Hours
            </p>
        </div>
        <div class="time-unit">
            <p>
                <span class="text-large">{minutes ? minutes : "0"}</span><br
            />Minutes
            </p>
        </div>
        <div class="time-unit">
            <p>
                <span class="text-large">{seconds ? seconds : "0	"}</span><br
            />Seconds
            </p>
        </div>
    </div>
</div>
