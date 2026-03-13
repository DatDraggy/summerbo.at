<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

    // UI state
    let scanner: Html5Qrcode | null = null;
    let cameraError: string | null = null;
    let scanResult: string | null = null;
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

    // --- Debug Toggle ---
    let useNativeScanner = false;

    function restartScanner() {
        stopScanner();
        setTimeout(() => {
            startScanner();
        }, 100);
    }

    // --- Lifecycle ---

    onMount(() => {
        startScanner();
    });

    onDestroy(() => {
        stopScanner();
    });

    // --- QR Scanner Logic ---

    function startScanner() {
        if (!document.getElementById('qr-reader')) {
            setTimeout(startScanner, 100);
            return;
        }

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

        if (useNativeScanner) {
            config.experimentalFeatures = {
                useBarCodeDetectorIfSupported: true
            };
        }

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

    function stopScanner() {
        if (scanner && scanner.isScanning) {
            scanner.stop().catch(err => console.error("Failed to stop scanner", err));
        }
    }

    function onScanSuccess(decodedText: string, decodedResult: any) {
        if (isLoading) return;
        scanResult = decodedText; // Captures the exact string read
        stopScanner();
        fetchAttendeeDetails(decodedText);
    }

    function onScanFailure(error: any) {
        // Ignored
    }

    // --- API Logic ---

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
            const response = await fetch(`https://api.summerbo.at/checkin?ticket=${ticket}${isMultiBoatDay ? '&boat=' + selectedBoat : ''}`, {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `API Error: ${response.status}`);
            }

            attendee = await response.json();
            showConfirmation = true;

        } catch (e: any) {
            checkinError = e.message;
            setTimeout(resetScanner, 3000);
        } finally {
            isLoading = false;
        }
    }

    async function confirmCheckin() {
        if (!attendee) return;

        if (attendee.isSponsor && !sponsorGiftHandedOut) {
            alert('Please confirm the sponsor gift was handed out.');
            return;
        }

        isLoading = true;
        checkinError = null;
        try {
            const response = await fetch('https://api.summerbo.at/checkin', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: attendee.id,
                    sponsor_gift_handed_out: sponsorGiftHandedOut
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `API Error: ${response.status}`);
            }

            const result = await response.json();
            checkinSuccess = `Successfully checked in ${attendee.firstname} ${attendee.lastname}.`;
            showConfirmation = false;
            attendee = null;
            setTimeout(resetScanner, 2000);

        } catch (e: any) {
            checkinError = e.message;
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
        scanResult = null;
        checkinError = null;
        checkinSuccess = null;
        attendee = null;
        showConfirmation = false;
        sponsorGiftHandedOut = false;
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
    .debug-data {
        margin-bottom: 1rem;
        padding: 0.75rem;
        background-color: #e2e8f0;
        border-left: 4px solid #4a5568;
        border-radius: 4px;
        font-family: monospace;
        word-break: break-all;
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

    <div class="debug-toggle" style="margin-bottom: 1rem; padding: 0.5rem; background: #f0f0f0; border-radius: 4px;">
        <label>
            <input type="checkbox" bind:checked={useNativeScanner} on:change={restartScanner}>
            Use native scanner (experimental)
        </label>
    </div>

    {#if scanResult}
        <div class="debug-data">
            <strong>Last Scanned Data:</strong><br>
            {scanResult}
        </div>
    {/if}

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

    <div id="qr-reader" style:display={showConfirmation || isLoading ? 'none' : 'block'}></div>

    {#if showConfirmation && attendee}
        <div class="confirmation-dialog">
            <h3>Confirm Check-in</h3>

            <div class="attendee-details">
                <p><strong>Name:</strong> {attendee.firstname} {attendee.lastname}</p>
                <p><strong>Date of Birth:</strong> {attendee.dob}</p>
                <p><strong>Sponsor:</strong> {attendee.isSponsor ? 'Yes' : 'No'}</p>
            </div>

            <p>Are you sure you want to check this person in?</p>

            {#if attendee.isSponsor}
                <div>
                    <label>
                        <input type="checkbox" bind:checked={sponsorGiftHandedOut}>
                        Sponsor gift was handed out
                    </label>
                </div>
            {/if}

            <div class="actions">
                <button class="confirm-btn" on:click={confirmCheckin} disabled={isLoading || (attendee.isSponsor && !sponsorGiftHandedOut)}>
                    Confirm
                </button>
                <button class="cancel-btn" on:click={cancelCheckin} disabled={isLoading}>
                    Cancel
                </button>
            </div>
        </div>
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
</div>