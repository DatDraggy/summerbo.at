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

        // Compare year, month, and day, ignoring time
        return today.getFullYear() === cruiseDate.getFullYear() &&
            today.getMonth() === cruiseDate.getMonth() &&
            today.getDate() === cruiseDate.getDate();
    };
    const isMultiBoatDay = getIsMultiBoatDay();
    let selectedBoat: number = 0; // Default to 0 to force selection

    // Sponsor gift confirmation
    let sponsorGiftHandedOut = false;

    // --- Debug Toggle ---
    let useNativeScanner = false;

    function restartScanner() {
        stopScanner();
        // A small delay to ensure everything is reset before starting again
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
            setTimeout(startScanner, 100); // Wait for the element to be in the DOM
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
            inversionAttempts: 'invertNegatives', // Attempt to invert colors for better detection of low-contrast codes
            disableCanvasStreams: true // Performance optimization
        };

        if (useNativeScanner) {
            config.experimentalFeatures = {
                useBarCodeDetectorIfSupported: true
            };
        }
        
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                // prefer back camera
                const cameraId = cameras.find(c => c.label.toLowerCase().includes('back'))?.id || cameras[0].id;
                localScanner.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanFailure
                ).catch(err => {
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

    function stopScanner() {
        if (scanner) {
            if (scanner.isScanning) {
                scanner.stop().catch(err => console.error("Failed to stop scanner", err));
            }
        }
    }

    function onScanSuccess(decodedText: string, decodedResult: any) {
        if (isLoading) return;
        scanResult = decodedText;
        stopScanner();
        fetchAttendeeDetails(decodedText);
    }

    function onScanFailure(error: any) {
        // ignore, this is called frequently when no code is found
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
            setTimeout(resetScanner, 3000); // Show error for 3s then reset
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
            setTimeout(resetScanner, 2000); // Show success for 2s then reset

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

    <div class="debug-toggle" style="margin-bottom: 1rem; padding: 0.5rem; background: #f0f0f0; border-radius: 4px;">
        <label>
            <input type="checkbox" bind:checked={useNativeScanner} on:change={restartScanner}>
            Use native scanner (experimentala)
        </label>
    </div>

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
