<script lang="ts">
    import { onMount, onDestroy, tick } from 'svelte';
    import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';
    import Overlay from './components/Overlay.svelte';

    let scanner: Html5Qrcode | null = null;
    let stopPromise: Promise<void> = Promise.resolve();
    let cameraError: string | null = null;
    let isLoading = false;
    let showConfirmation = false;

    let result: { ok: boolean; message: string; canRetry: boolean } | null = null;

    let attendee: {
        id: number;
        firstname: string;
        lastname: string;
        dob: string;
        isSponsor: boolean;
        boat: number;
    } | null = null;

    type SearchResult = {
        id: number;
        nickname: string;
        firstname: string;
        lastname: string;
        dob: string;
        efregid: number;
        boat: number;
        isSponsor: boolean;
        state: 'ok' | 'not_billed' | 'unpaid' | 'checked_in';
    };

    let showSearch = false;
    let searchTerm = '';
    let searchResults: SearchResult[] | null = null;
    let searchTruncated = false;
    let searchError: string | null = null;
    let searchLoading = false;
    let searchInputEl: HTMLInputElement | null = null;

    const getIsMultiBoatDay = () => {
        const today = new Date();
        const cruiseDate = new Date('__CRUISE_DATE__');
        return today.getFullYear() === cruiseDate.getFullYear() &&
            today.getMonth() === cruiseDate.getMonth() &&
            today.getDate() === cruiseDate.getDate();
    };
    const isActualCruiseDay = getIsMultiBoatDay();
    let isMultiBoatDay = isActualCruiseDay;
    let selectedBoat: number = 0;
    // Party 1 carries boat 0, which must not fall through to a real name.
    const boatName = (n: number) => (n === 1 ? 'Tunes' : n === 2 ? 'Talky' : 'unassigned');

    let sponsorGiftHandedOut = false;

    let passportNameVerified = false;
    let passportDobVerified = false;

    let confirmValidationError: string | null = null;
    let validationErrorEl: HTMLElement | null = null;

    function missingChecks(): string[] {
        if (!attendee) return [];
        return [
            !passportNameVerified && 'Name',
            !passportDobVerified && 'Date of birth',
            attendee.isSponsor && !sponsorGiftHandedOut && 'Sponsor gift handed out',
        ].filter(Boolean) as string[];
    }

    function clearValidation() {
        confirmValidationError = null;
    }

    onMount(() => {
        startScanner();
    });

    onDestroy(() => {
        stopScanner();
    });

    async function startScanner() {
        if (!document.getElementById('qr-reader')) {
            setTimeout(startScanner, 100);
            return;
        }

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
        if (isLoading || result || showSearch) return;
        stopScanner();
        fetchAttendeeDetails(decodedText);
    }

    function onScanFailure(error: any) {
        // Ignored
    }

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
            finish(false, 'Select a boat first.');
            isLoading = false;
            return;
        }

        try {
            const response = await fetchWithTimeout(`__API_BASE__/auth/checkin?ticket=${ticket}&party=${isMultiBoatDay ? 2 : 1}`, {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(await readError(response));
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

        const missing = missingChecks();
        if (missing.length > 0) {
            confirmValidationError = 'Still to confirm: ' + missing.join(', ') + '.';
            await tick();
            validationErrorEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        confirmValidationError = null;
        isLoading = true;
        try {
            const response = await fetchWithTimeout('__API_BASE__/auth/checkin', {
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
                throw new Error(await readError(response));
            }

            finish(true, `${attendee.firstname} ${attendee.lastname} is checked in.`);
            attendee = null;

        } catch (e: any) {
            finish(false, errorMessage(e), true);
        } finally {
            isLoading = false;
        }
    }

    async function runSearch() {
        const term = searchTerm.trim();
        if (term.length < 2) {
            searchError = 'Enter at least 2 characters.';
            return;
        }

        searchLoading = true;
        searchError = null;
        try {
            const response = await fetchWithTimeout(`__API_BASE__/auth/checkin/search?q=${encodeURIComponent(term)}&party=${isMultiBoatDay ? 2 : 1}`, {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(await readError(response));
            }

            const data = await response.json();
            searchResults = data.results ?? [];
            searchTruncated = data.truncated ?? false;
            searchInputEl?.blur();

        } catch (e: any) {
            searchError = errorMessage(e);
            searchResults = null;
        } finally {
            searchLoading = false;
        }
    }

    // Router 404s are text/plain and PHP fatals are HTML, so response.json()
    // can throw over the real status.
    async function readError(response: Response): Promise<string> {
        try {
            const data = await response.json();
            return data.error || `API Error: ${response.status}`;
        } catch {
            return `API Error: ${response.status}`;
        }
    }

    function errorMessage(e: any): string {
        return e.name === 'AbortError' ? 'Request timed out. Please try again.' : e.message;
    }

    function finish(ok: boolean, message: string, canRetry = false) {
        result = { ok, message, canRetry };
        showConfirmation = false;
    }

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

    async function openSearch() {
        stopScanner();
        showSearch = true;
        searchTerm = '';
        searchResults = null;
        searchError = null;
        searchTruncated = false;
        await tick();
        searchInputEl?.focus();
    }

    function closeSearch() {
        showSearch = false;
        startScanner();
    }

    function pickResult(r: SearchResult) {
        showSearch = false;
        fetchAttendeeDetails('s' + r.id);
    }

    function onWindowKeydown(e: KeyboardEvent) {
        if (showSearch && e.key === 'Escape') closeSearch();
    }

    // Joined in script, not markup: Svelte trims whitespace at {#if} boundaries.
    function resultMeta(r: SearchResult): string {
        const parts: string[] = [];
        if (r.nickname) parts.push('“' + r.nickname + '”');
        parts.push(r.dob, 'EF ' + r.efregid);
        if (r.boat === 1 || r.boat === 2) parts.push('Boat ' + boatName(r.boat));
        return parts.join(' · ');
    }

    function resultChip(r: SearchResult): string | null {
        if (r.state === 'checked_in') return 'Checked in';
        if (r.state === 'unpaid') return 'Unpaid';
        if (r.state === 'not_billed') return 'Not billed';
        return r.isSponsor ? 'VIP' : null;
    }

    function resetScanner() {
        result = null;
        showSearch = false;
        confirmValidationError = null;
        attendee = null;
        showConfirmation = false;
        sponsorGiftHandedOut = false;
        passportNameVerified = false;
        passportDobVerified = false;
        startScanner();
    }

    function toggleCruiseOverride() {
        isMultiBoatDay = !isMultiBoatDay;
        selectedBoat = 0;
        resetScanner();
    }

</script>

<style>
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

    .search-open {
        margin-bottom: 2rem;
    }

    /* Overlay caps nothing; 85vh keeps a long list scrollable and the card
       top below Overlay's white close button. */
    .search-card {
        width: min(28rem, calc(100vw - 2rem));
        max-height: 85vh;
        overflow-y: auto;
        padding: 1rem;
        border-radius: 8px;
        background-color: white;
        color: black;
    }

    .search-form {
        margin-bottom: 1rem;
    }

    .search-form .input-wrapper {
        margin-bottom: .5rem;
    }

    .search-hint {
        margin-bottom: 1rem;
        font-size: .875rem;
        line-height: 1.25rem;
        color: #555;
    }

    .search-empty {
        margin-bottom: 1rem;
        font-weight: 800;
    }

    .search-results {
        list-style: none;
        margin: 0 0 1rem;
        padding: 0;
    }

    .search-result {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        width: 100%;
        margin-bottom: .5rem;
        padding: .75rem 1rem;
        border: 2px solid transparent;
        border-radius: .5rem;
        background-color: #f3f3f3;
        color: black;
        text-align: left;
        cursor: pointer;
    }

    .search-result:focus {
        border-color: var(--color-tertiary);
    }

    .search-result-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .search-result-name {
        font-size: 1.125rem;
        line-height: 1.2;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .search-chip {
        flex: none;
        padding: .25rem .5rem;
        border-radius: 2rem;
        background-color: #ddd;
        color: #555;
        font-size: .6875rem;
        line-height: .875rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .125ch;
    }

    .search-chip.vip {
        background-color: var(--color-gold);
        color: var(--color-vip-text);
    }

    .search-result-meta {
        font-size: .875rem;
        line-height: 1.25rem;
        color: #555;
    }

    .search-close {
        margin-top: .5rem;
    }

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

<svelte:window on:keydown={onWindowKeydown} />

{#if showSearch}
    <Overlay onClose={closeSearch}>
        <div class="search-card">
            <h3 class="text-headline verify-title">Find attendee</h3>

            <form class="search-form" on:submit|preventDefault={runSearch}>
                <div class="input-wrapper">
                    <label for="search-term"><span>Search</span></label>
                    <!-- Not type=search: WebKit adds native searchfield chrome. -->
                    <input id="search-term" type="text" inputmode="search" enterkeyhint="search"
                           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                           placeholder="Nickname, name, birthdate or EF reg ID"
                           bind:this={searchInputEl} bind:value={searchTerm}>
                </div>
                <p class="search-hint">Birthdate as <strong>YYYY-MM-DD</strong>. Names and nicknames match partially.</p>
                <button type="submit" class="button button-primary" disabled={searchLoading}>
                    {searchLoading ? 'Searching…' : 'Search'}
                </button>
            </form>

            {#if searchError}
                <div class="error-message" role="alert">{searchError}</div>
            {/if}

            {#if searchResults}
                {#if searchResults.length === 0}
                    <p class="search-empty">No attendee matched.</p>
                {:else}
                    <ul class="search-results">
                        {#each searchResults as r (r.id)}
                            <li>
                                <button type="button" class="search-result" on:click={() => pickResult(r)}>
                                    <span class="search-result-top">
                                        <span class="search-result-name">{r.firstname} {r.lastname}</span>
                                        {#if resultChip(r)}
                                            <span class="search-chip" class:vip={r.state === 'ok'}>{resultChip(r)}</span>
                                        {/if}
                                    </span>
                                    <span class="search-result-meta">{resultMeta(r)}</span>
                                </button>
                            </li>
                        {/each}
                    </ul>
                    {#if searchTruncated}
                        <p class="search-hint">Showing the first 25 matches &mdash; narrow your search.</p>
                    {/if}
                {/if}
            {/if}

            <button type="button" class="button button-secondary search-close" on:click={closeSearch}>
                Close
            </button>
        </div>
    </Overlay>
{/if}

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

    {#if isMultiBoatDay && !showConfirmation && !result && !showSearch}
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

    <div id="qr-reader" style:display={showConfirmation || isLoading || result || showSearch ? 'none' : 'block'}></div>

    {#if !showConfirmation && !result && !isLoading && !showSearch}
        <button type="button" class="button button-secondary search-open" on:click={openSearch}>
            Can't scan? Search manually
        </button>
    {/if}

    {#if showConfirmation && attendee}
        <section class="verify-card">
            <h3 class="text-headline verify-title">Confirm Check-in</h3>

            <span class="ticket-badge" class:vip={attendee.isSponsor}>
                {attendee.isSponsor ? 'VIP' : 'Standard ticket'}
            </span>

            <h4 class="text-headline-line">Check against passport</h4>

            <div class="checkbox-wrapper">
                <div class="checkbox-group verified">
                    <input type="checkbox" id="verify-name" bind:checked={passportNameVerified}
                           on:change={clearValidation}>
                    <label for="verify-name">
                        <span class="check-caption">Name</span>
                        <span class="check-value">{attendee.firstname} {attendee.lastname}</span>
                    </label>
                </div>
                <div class="checkbox-group verified">
                    <input type="checkbox" id="verify-dob" bind:checked={passportDobVerified}
                           on:change={clearValidation}>
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
                        <input type="checkbox" id="sponsor-gift" bind:checked={sponsorGiftHandedOut}
                               on:change={clearValidation}>
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

    {#if !showConfirmation && !result && !showSearch}
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