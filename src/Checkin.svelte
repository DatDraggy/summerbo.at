<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

    // UI state
    let scanner: Html5Qrcode | null = null;
    let stopPromise: Promise<void> = Promise.resolve();
    let cameraError: string | null = null;
    let checkinError: string | null = null;
    let checkinSuccess: string | null = null;
    let isLoading = false;
    let showConfirmation = false;

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
    const isMultiBoatDay = getIsMultiBoatDay();
    let selectedBoat: number = 0;

    // Sponsor gift confirmation
    let sponsorGiftHandedOut = false;

    // Passport verification status
    let passportNameVerified = false;
    let passportDobVerified = false;

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
        if (isLoading) return;
        stopScanner();
        fetchAttendeeDetails(decodedText);
    }

    function onScanFailure(error: any) {
        // Ignored
    }

    // --- API Logic ---

    // Venue connectivity can be flaky; abort hung requests so the UI recovers
    // instead of getting stuck on the loading state forever.
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
        checkinError = null;

        if (isMultiBoatDay && selectedBoat === 0) {
            checkinError = "Please select a boat before scanning.";
            setTimeout(resetScanner, 3000);
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
                alert(`Attendee is assigned to a different boat! (Registered: Boat ${data.boat === 1 ? 'Tunes' : 'Talky'}, Selected: Boat ${selectedBoat === 1 ? 'Tunes' : 'Talky'})`);
                resetScanner();
                return;
            }

            attendee = data;
            showConfirmation = true;

        } catch (e: any) {
            checkinError = e.name === 'AbortError' ? 'Request timed out. Please try again.' : e.message;
            setTimeout(resetScanner, 3000);
        } finally {
            isLoading = false;
        }
    }

    async function confirmCheckin() {
        if (!attendee) return;

        if (!passportNameVerified || !passportDobVerified) {
            alert('Please verify the attendee passport details.');
            return;
        }

        if (attendee.isSponsor && !sponsorGiftHandedOut) {
            alert('Please confirm the sponsor gift was handed out.');
            return;
        }

        isLoading = true;
        checkinError = null;
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

            const result = await response.json();
            checkinSuccess = `Successfully checked in ${attendee.firstname} ${attendee.lastname}.`;
            showConfirmation = false;
            attendee = null;
            setTimeout(resetScanner, 2000);

        } catch (e: any) {
            checkinError = e.name === 'AbortError' ? 'Request timed out. Please try again.' : e.message;
        } finally {
            isLoading = false;
        }
    }

    // --- UI Actions ---

    function cancelCheckin() {
        showConfirmation = false;
        attendee = null;
        resetScanner();
    }

    function resetScanner() {
        checkinError = null;
        checkinSuccess = null;
        attendee = null;
        showConfirmation = false;
        sponsorGiftHandedOut = false;
        passportNameVerified = false;
        passportDobVerified = false;
        startScanner();
    }

</script>

<style>
    .container {
        max-width: 500px;
        margin: 2rem auto;
        padding: 1rem;
    }
    #qr-reader {
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 8px;
        overflow: hidden;
    }
    .error, .success {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 8px;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
    }
    .confirmation-dialog {
        margin-top: 1rem;
        padding: 1.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }
    .confirmation-dialog h3 {
        margin-top: 0;
    }
    .confirmation-dialog p {
        font-size: 1.1rem;
    }
    .attendee-details {
        background-color: #fff;
        padding: 1rem;
        border-radius: 4px;
        border: 1px solid #eee;
        margin-bottom: 1rem;
    }
    .actions {
        margin-top: 1rem;
        display: flex;
        gap: 1rem;
    }
    button {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
    }
    .confirm-btn {
        background-color: #28a745;
        color: white;
    }
    .cancel-btn {
        background-color: #dc3545;
        color: white;
    }
    .boat-selector {
        margin-bottom: 1rem;
    }
</style>

<div class="container">
    <h2 class="text-headline">Attendee Check-in</h2>

    {#if cameraError}
        <div class="error">{cameraError}</div>
    {/if}

    {#if checkinError}
        <div class="error">{checkinError}</div>
    {/if}

    {#if checkinSuccess}
        <div class="success">{checkinSuccess}</div>
    {/if}

    {#if isLoading}
        <h2 class="text-headline-line">Loading...</h2>
    {/if}

    {#if isMultiBoatDay && !showConfirmation}
        <div class="boat-selector">
            <h3>Select Boat</h3>
            <label>
                <input type="radio" bind:group={selectedBoat} name="boat" value={1}>
                Boat Tunes
            </label>
            <label>
                <input type="radio" bind:group={selectedBoat} name="boat" value={2}>
                Boat Talky
            </label>
        </div>
    {/if}

    <div id="qr-reader" style:display={showConfirmation || isLoading ? 'none' : 'block'}></div>

    {#if showConfirmation && attendee}
        <div class="confirmation-dialog">
            <h3>Confirm Check-in</h3>

            <div class="attendee-details">
                <p><strong>Sponsor:</strong> {attendee.isSponsor ? '★ Yes ★' : 'No'}</p>
                
                <h4 style="margin: 1rem 0 0.5rem 0;">Passport Verification</h4>
                <div style="background: #f0f4f8; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #d0e0f0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 1.1rem; margin-bottom: 0.5rem;">
                        <input type="checkbox" bind:checked={passportNameVerified}>
                        Verify Name: <strong style="color: #2b6cb0;">{attendee.firstname} {attendee.lastname}</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 1.1rem;">
                        <input type="checkbox" bind:checked={passportDobVerified}>
                        Verify Date of Birth: <strong style="color: #2b6cb0;">{attendee.dob}</strong>
                    </label>
                </div>
            </div>

            {#if attendee.isSponsor}
                <div style="margin-bottom: 1rem; background: #fffaf0; padding: 0.75rem; border-radius: 6px; border: 1px solid #feebc8;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 1.1rem;">
                        <input type="checkbox" bind:checked={sponsorGiftHandedOut}>
                        <strong>Sponsor gift was handed out</strong>
                    </label>
                </div>
            {/if}

            <p style="margin-top: 1rem; font-weight: 500;">Are you sure you want to check this person in?</p>

            <div class="actions">
                <button class="confirm-btn" on:click={confirmCheckin} disabled={isLoading || !passportNameVerified || !passportDobVerified || (attendee.isSponsor && !sponsorGiftHandedOut)}>
                    Confirm
                </button>
                <button class="cancel-btn" on:click={cancelCheckin} disabled={isLoading}>
                    Cancel
                </button>
            </div>
        </div>
    {/if}

</div>