<script lang="ts">
    import { onMount, onDestroy, tick } from 'svelte';
    import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

    // UI state
    let scanner: Html5Qrcode | null = null;
    let stopPromise: Promise<void> = Promise.resolve();
    let cameraError: string | null = null;
    let isLoading = false;
    let showConfirmation = false;

    // Outcome of the last scan. Stays on screen until staff acknowledge it, so a
    // failed check-in can't be mistaken for a successful one.
    let result: { ok: boolean; message: string; canRetry: boolean } | null = null;

    // Attendee data from API
    let attendee: {
        id: number;
        firstname: string;
        lastname: string;
        dob: string;
        isSponsor: boolean;
        boat: number;
    } | null = null;

    // Multi-boat selection
    const getIsMultiBoatDay = () => {
        const today = new Date();
        const cruiseDate = new Date('__CRUISE_DATE__');
        return today.getFullYear() === cruiseDate.getFullYear() &&
            today.getMonth() === cruiseDate.getMonth() &&
            today.getDate() === cruiseDate.getDate();
    };
    // Date-derived, but the debug panel can force it on for testing.
    const isActualCruiseDay = getIsMultiBoatDay();
    let isMultiBoatDay = isActualCruiseDay;
    let selectedBoat: number = 0;
    const boatName = (n: number) => (n === 1 ? 'Tunes' : 'Talky');

    // Sponsor gift confirmation
    let sponsorGiftHandedOut = false;

    // Passport verification status
    let passportNameVerified = false;
    let passportDobVerified = false;

    let confirmValidationError: string | null = null;
    let validationErrorEl: HTMLElement | null = null;

    // Inlined rather than extracted: `$:` only tracks variables read in the block
    // itself, not ones read inside a function it calls.
    $: missingChecks = (attendee ? [
        !passportNameVerified && 'Name',
        !passportDobVerified && 'Date of birth',
        attendee.isSponsor && !sponsorGiftHandedOut && 'Sponsor gift handed out',
    ].filter(Boolean) : []) as string[];

    $: if (missingChecks.length === 0) {
        confirmValidationError = null;
    }

    // --- Lifecycle ---

    onMount(() => {
        startScanner();
    });

    onDestroy(() => {
        stopScanner();
    });

    // --- QR Scanner Logic ---

    async function startScanner() {
        if (!document.getElementById('qr-reader')) {
            setTimeout(startScanner, 100);
            return;
        }

        // Fully release any previous camera session before starting a new one
        await stopScanner();

        const localScanner = new Html5Qrcode('qr-reader');
        scanner = localScanner;

        const config: any = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.DATA_MATRIX
            ],
            disableCanvasStreams: false
        };

        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                const cameraId = cameras.find(c => c.label.toLowerCase().includes('back'))?.id || cameras[0].id;

                localScanner.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    setTimeout(applyInversionWorkaround, 200);
                }).catch(err => {
                    cameraError = `Unable to start scanner: ${err}`;
                    console.error(cameraError);
                });
            } else {
                cameraError = "No cameras found.";
                console.error(cameraError);
            }
        }).catch(err => {
            cameraError = `Camera permission error: ${err}`;
            console.error(cameraError);
        });
    }

    // --- The Inverted/Mirrored Workaround ---
    function applyInversionWorkaround() {
        const canvas = document.querySelector('#qr-reader canvas') as HTMLCanvasElement;
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const originalDrawImage = ctx.drawImage;
        let frameCounter = 0;

        ctx.drawImage = function(this: CanvasRenderingContext2D, ...args: any[]) {
            frameCounter++;

            this.filter = 'none';
            this.setTransform(1, 0, 0, 1, 0, 0);

            const cycle = frameCounter % 3;

            if (cycle === 1) {
                this.translate(canvas.width, 0);
                this.scale(-1, 1);
            } else if (cycle === 2) {
                this.filter = 'invert(100%)';
            }

            return originalDrawImage.apply(this, args as any);
        };
    }

    function stopScanner(): Promise<void> {
        if (!scanner) return stopPromise;

        const localScanner = scanner;
        scanner = null;

        stopPromise = (async () => {
            try {
                if (localScanner.isScanning) {
                    await localScanner.stop();
                }
                localScanner.clear();
            } catch (err) {
                console.error("Failed to stop scanner", err);
            }
        })();

        return stopPromise;
    }

    function onScanSuccess(decodedText: string, decodedResult: any) {
        if (isLoading || result) return;
        stopScanner();
        fetchAttendeeDetails(decodedText);
    }

    function onScanFailure(error: any) {
        // Ignored
    }

    // --- API Logic ---

    // Venue connectivity is flaky; abort hung requests so the UI can recover.
    const REQUEST_TIMEOUT_MS = 15000;

    async function fetchWithTimeout(url: string, options: RequestInit = {}): Promise<Response> {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);
        try {
            return await fetch(url, { ...options, signal: controller.signal });
        } finally {
            clearTimeout(timer);
        }
    }

    async function fetchAttendeeDetails(ticket: string) {
        isLoading = true;

        if (isMultiBoatDay && selectedBoat === 0) {
            finish(false, 'Select a boat before scanning.');
            isLoading = false;
            return;
        }

        try {
            const response = await fetchWithTimeout(`https://api.summerbo.at/auth/checkin?ticket=${ticket}&party=${isMultiBoatDay ? 2 : 1}`, {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `API Error: ${response.status}`);
            }

            const data = await response.json();
            if (isMultiBoatDay && data.boat !== selectedBoat) {
                finish(false, `Wrong boat. This attendee is on Boat ${boatName(data.boat)}, `
                    + `you have Boat ${boatName(selectedBoat)} selected.`);
                return;
            }

            attendee = data;
            showConfirmation = true;

        } catch (e: any) {
            finish(false, errorMessage(e));
        } finally {
            isLoading = false;
        }
    }

    async function confirmCheckin() {
        if (!attendee) return;

        if (missingChecks.length > 0) {
            confirmValidationError = 'Still to confirm: ' + missingChecks.join(', ') + '.';
            await tick();
            validationErrorEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        confirmValidationError = null;
        isLoading = true;
        try {
            const response = await fetchWithTimeout('https://api.summerbo.at/auth/checkin', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: attendee.id,
                    party: isMultiBoatDay ? 2 : 1,
                    sponsor_gift_handed_out: sponsorGiftHandedOut
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `API Error: ${response.status}`);
            }

            finish(true, `${attendee.firstname} ${attendee.lastname} is checked in.`);
            attendee = null;

        } catch (e: any) {
            // attendee is kept so the write can be retried without rescanning.
            finish(false, errorMessage(e), true);
        } finally {
            isLoading = false;
        }
    }

    function errorMessage(e: any): string {
        return e.name === 'AbortError' ? 'Request timed out. Please try again.' : e.message;
    }

    // Parks the flow on a result screen. The scanner stays stopped until staff
    // acknowledge it, so no outcome can scroll past unseen.
    function finish(ok: boolean, message: string, canRetry = false) {
        result = { ok, message, canRetry };
        showConfirmation = false;
    }

    // --- UI Actions ---

    function cancelCheckin() {
        showConfirmation = false;
        attendee = null;
        resetScanner();
    }

    function acknowledgeResult() {
        resetScanner();
    }

    function retryCheckin() {
        result = null;
        showConfirmation = true;
    }

    function resetScanner() {
        result = null;
        confirmValidationError = null;
        attendee = null;
        showConfirmation = false;
        sponsorGiftHandedOut = false;
        passportNameVerified = false;
        passportDobVerified = false;
        startScanner();
    }

    // The party is baked into both API calls, so drop any scan in progress rather
    // than let a party 1 lookup be confirmed as party 2.
    function toggleCruiseOverride() {
        isMultiBoatDay = !isMultiBoatDay;
        selectedBoat = 0;
        resetScanner();
    }

</script>

<style>
    /* .content already pads 1rem (mobile) / 3rem (desktop); don't double-pad. */
    .container {
        max-width: 32rem;
        margin: 0 auto 4rem;
    }

    #qr-reader {
        width: 100%;
        border: 2px solid var(--color-tertiary);
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .error-message {
        padding: .75rem;
        font-weight: 800;
    }

    /* Global .checkbox-group has no flex sizing, so the boats would cluster left. */
    .checkbox-wrapper-horizontal .checkbox-group {
        flex: 1;
    }

    .verify-card {
        border: 2px solid var(--color-tertiary);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 2rem;
    }

    .result-card {
        border: 2px solid var(--color-secondary-dark);
        border-radius: 8px;
        background-color: #ffc8c8;
        padding: 1rem;
        margin-bottom: 2rem;
    }

    .result-card.ok {
        border-color: #036000;
        background-color: #8fff94;
    }

    .result-message {
        margin-bottom: 1.5rem;
        font-size: 1.125rem;
        font-weight: 800;
    }

    /* .text-headline carries 2rem/4rem of bottom margin - too much inside a card. */
    .verify-title {
        margin-bottom: .5rem;
    }

    .ticket-badge {
        display: inline-block;
        margin-bottom: 1.5rem;
        padding: .25rem .75rem;
        border-radius: 2rem;
        background-color: #f3f3f3;
        font-size: .75rem;
        line-height: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .125ch;
    }

    .ticket-badge.vip {
        background-color: var(--color-gold);
        color: var(--color-vip-text);
    }

    /* The global label assumes a single 1.125rem line; these hold two. */
    .verify-card .checkbox-group label {
        line-height: 1.2;
        padding-top: .875rem;
        padding-bottom: .875rem;
    }

    .verify-card .checkbox-wrapper {
        margin-bottom: 1.5rem;
    }

    .check-caption {
        display: block;
        margin-bottom: .25rem;
        font-size: .75rem;
        line-height: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .125ch;
    }

    /* Read against a passport at arm's length. */
    .check-value {
        display: block;
        font-size: 1.75rem;
        line-height: 1.15;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .check-action {
        display: block;
        font-size: 1.125rem;
        line-height: 1.2;
        font-weight: 800;
    }

    .checkbox-group.verified input[type=checkbox]:checked + label {
        background-color: var(--color-tertiary);
    }

    .actions {
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    /* Muted and dashed so it is never mistaken for part of the staff flow. */
    .debug-panel {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 1rem;
    }

    .debug-state {
        margin-bottom: .75rem;
        font-size: .875rem;
        line-height: 1.25rem;
        color: darkgrey;
    }

    .debug-state .overridden {
        display: block;
        color: var(--color-secondary-dark);
        font-weight: 800;
    }

    .debug-btn {
        width: fit-content;
        padding: .5rem 1rem;
        font-size: .75rem;
    }
</style>

<div class="container">
    <h2 class="text-headline">Attendee Check-in</h2>

    {#if cameraError}
        <div class="error-message" role="alert">{cameraError}</div>
    {/if}

    {#if isLoading}
        <h2 class="text-headline-line">Loading&hellip;</h2>
    {/if}

    {#if result}
        <section class="result-card" class:ok={result.ok} role="alert">
            <h3 class="text-headline verify-title">
                {result.ok ? '✓ Checked in' : '✗ Not checked in'}
            </h3>
            <p class="result-message">{result.message}</p>
            <div class="actions">
                {#if result.canRetry}
                    <button type="button" class="button button-primary"
                            on:click={retryCheckin} disabled={isLoading}>
                        Try again
                    </button>
                    <button type="button" class="button button-secondary"
                            on:click={acknowledgeResult} disabled={isLoading}>
                        Discard and scan next
                    </button>
                {:else}
                    <button type="button" class="button button-primary"
                            on:click={acknowledgeResult} disabled={isLoading}>
                        Scan next
                    </button>
                {/if}
            </div>
        </section>
    {/if}

    {#if isMultiBoatDay && !showConfirmation && !result}
        <h3 class="text-headline-line">Select Boat</h3>
        <div class="checkbox-wrapper-horizontal">
            <div class="checkbox-group">
                <input type="radio" name="boat" id="boatTunes" value={1} bind:group={selectedBoat}>
                <label for="boatTunes">Boat Tunes</label>
            </div>
            <div class="checkbox-group">
                <input type="radio" name="boat" id="boatTalky" value={2} bind:group={selectedBoat}>
                <label for="boatTalky">Boat Talky</label>
            </div>
        </div>
    {/if}

    <div id="qr-reader" style:display={showConfirmation || isLoading || result ? 'none' : 'block'}></div>

    {#if showConfirmation && attendee}
        <section class="verify-card">
            <h3 class="text-headline verify-title">Confirm Check-in</h3>

            <span class="ticket-badge" class:vip={attendee.isSponsor}>
                {attendee.isSponsor ? '★ VIP Sponsor' : 'Standard ticket'}
            </span>

            <h4 class="text-headline-line">Check against passport</h4>

            <div class="checkbox-wrapper">
                <div class="checkbox-group verified">
                    <input type="checkbox" id="verify-name" bind:checked={passportNameVerified}>
                    <label for="verify-name">
                        <span class="check-caption">Name</span>
                        <span class="check-value">{attendee.firstname} {attendee.lastname}</span>
                    </label>
                </div>
                <div class="checkbox-group verified">
                    <input type="checkbox" id="verify-dob" bind:checked={passportDobVerified}>
                    <label for="verify-dob">
                        <span class="check-caption">Date of birth</span>
                        <span class="check-value">{attendee.dob}</span>
                    </label>
                </div>
            </div>

            {#if attendee.isSponsor}
                <h4 class="text-headline-line">Sponsor gift</h4>
                <div class="checkbox-wrapper">
                    <div class="checkbox-group VIP">
                        <input type="checkbox" id="sponsor-gift" bind:checked={sponsorGiftHandedOut}>
                        <label for="sponsor-gift">
                            <span class="check-action">Gift was handed out</span>
                        </label>
                    </div>
                </div>
            {/if}

            {#if confirmValidationError}
                <div class="error-message" role="alert" bind:this={validationErrorEl}>
                    {confirmValidationError}
                </div>
            {/if}

            <div class="actions">
                <button type="button" class="button button-primary"
                        on:click={confirmCheckin} disabled={isLoading}>
                    Confirm Check-in
                </button>
                <button type="button" class="button button-secondary"
                        on:click={cancelCheckin} disabled={isLoading}>
                    Cancel
                </button>
            </div>
        </section>
    {/if}

    {#if !showConfirmation && !result}
        <div class="debug-panel">
            <h4 class="text-headline-line">Debug</h4>
            <p class="debug-state">
                Scanning as <strong>party {isMultiBoatDay ? 2 : 1}</strong> &mdash;
                {isMultiBoatDay ? 'Sunday cruise, two boats' : 'main party, single boat'}.
                {#if isMultiBoatDay !== isActualCruiseDay}
                    <span class="overridden">Overridden &mdash; today is not the cruise date.</span>
                {/if}
            </p>
            <button type="button" class="button button-secondary debug-btn"
                    on:click={toggleCruiseOverride}>
                Switch to {isMultiBoatDay ? 'main party' : 'Sunday cruise'}
            </button>
        </div>
    {/if}

</div>