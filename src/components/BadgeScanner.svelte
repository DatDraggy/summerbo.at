<script lang="ts">
    import { onDestroy, onMount } from 'svelte';
    import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

    export let onScan: (code: string) => void = () => {};
    // Parked while the parent shows a card, or the next badge lands mid-confirmation.
    export let paused = false;

    // html5-qrcode addresses its preview by element id; instances must not share one.
    const readerId = 'badge-scanner-' + Math.random().toString(36).slice(2, 8);

    let scanner: Html5Qrcode | null = null;
    let cameraError: string | null = null;
    let cameras: Array<{ id: string; label: string }> = [];
    let cameraIndex = -1;
    let switchingCamera = false;
    let mounted = false;
    let destroyed = false;
    let wasPaused = paused;

    // One at a time: a start that is still opening the camera cannot be cancelled,
    // and two instances on one element id tear out each other's <video>.
    let queue: Promise<void> = Promise.resolve();

    function enqueue(op: () => Promise<void>): Promise<void> {
        queue = queue
            .then(op)
            .catch(err => console.error('Badge scanner operation failed', err));

        return queue;
    }

    // Fresh per start: html5-qrcode clamps config.qrbox in place, so a shared one
    // measured while the preview is hidden sticks at 0 and fails every later start.
    function scanConfig(): any {
        return {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.DATA_MATRIX
            ],
            disableCanvasStreams: false
        };
    }

    onMount(() => {
        mounted = true;
        if (!paused) startScanner();
    });

    onDestroy(() => {
        destroyed = true;
        stopScanner();
    });

    // Tracks the last seen value so the block reacts to paused, not its own writes.
    $: if (mounted && paused !== wasPaused) {
        wasPaused = paused;
        if (paused) stopScanner();
        else startScanner();
    }

    function startScanner(): Promise<void> {
        return enqueue(doStart);
    }

    function stopScanner(): Promise<void> {
        return enqueue(doStop);
    }

    function startSources(): Array<string | MediaTrackConstraints> {
        const chosen = cameras[cameraIndex];
        if (chosen) return [chosen.id];

        const guessed = cameras.findIndex(c => /back|rear|environment/i.test(c.label));
        return [
            { facingMode: { exact: 'environment' } },
            ...(guessed >= 0 ? [cameras[guessed].id] : []),
            cameras[0].id
        ];
    }

    function rememberActiveCamera(active: Html5Qrcode) {
        if (cameras[cameraIndex]) return;

        let deviceId: string | undefined;
        try {
            deviceId = active.getRunningTrackSettings().deviceId;
        } catch {
            deviceId = undefined;
        }

        const i = cameras.findIndex(c => c.id === deviceId);
        cameraIndex = i >= 0 ? i : 0;
    }

    function cameraLabel(i: number): string {
        return cameras[i]?.label || `Camera ${i + 1}`;
    }

    async function doStart() {
        if (destroyed || paused || scanner) return;
        if (!document.getElementById(readerId)) return;

        if (cameras.length === 0) {
            try {
                cameras = await Html5Qrcode.getCameras();
            } catch (err) {
                cameraError = `Camera permission error: ${err}`;
                console.error(cameraError);
                return;
            }

            if (cameras.length === 0) {
                cameraError = 'No cameras found.';
                console.error(cameraError);
                return;
            }
        }

        // Asking for permission can take a while; the parent may have parked us.
        if (destroyed || paused) return;

        const local = new Html5Qrcode(readerId);
        scanner = local;

        let lastError: any = null;

        for (const source of startSources()) {
            try {
                await local.start(source, scanConfig(), handleDecoded, onScanFailure);
                cameraError = null;
                rememberActiveCamera(local);
                setTimeout(applyInversionWorkaround, 200);
                // A pause during warm-up has its stop queued behind this call.
                return;
            } catch (err) {
                lastError = err;
                console.error('Camera failed to start', source, err);
            }
        }

        // Nothing came up: drop the instance so the next attempt builds a fresh one.
        scanner = null;
        cameraError = `Unable to start scanner: ${lastError}`;
    }

    async function doStop() {
        const local = scanner;
        if (!local) return;

        scanner = null;

        // isScanning only flips once the video plays, but the camera is live from
        // start() on - so always try, or the light stays on.
        try {
            await local.stop();
        } catch {
            // Was not running.
        }

        try {
            local.clear();
        } catch (err) {
            console.error('Failed to clear scanner', err);
        }
    }

    export async function cycleCamera() {
        if (switchingCamera || cameras.length < 2) return;

        switchingCamera = true;
        cameraIndex = (cameraIndex + 1) % cameras.length;
        try {
            stopScanner();
            await startScanner();
        } finally {
            switchingCamera = false;
        }
    }

    function onPreviewKeydown(e: KeyboardEvent) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            cycleCamera();
        }
    }

    // Badges read mirrored or inverted, so cycle plain / mirrored / inverted frames.
    function applyInversionWorkaround() {
        const canvas = document.querySelector(`#${readerId} canvas`) as HTMLCanvasElement;
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

    function handleDecoded(decodedText: string) {
        if (paused) return;

        const code = (decodedText || '').trim();
        if (code) onScan(code);
    }

    function onScanFailure(error: any) {
        // Ignored
    }
</script>

<style>
    .preview {
        width: 100%;
        border: 2px solid var(--color-tertiary);
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .preview.tappable {
        cursor: pointer;
    }

    .camera-hint {
        margin-bottom: .5rem;
        font-size: .875rem;
        line-height: 1.25rem;
        color: #555;
    }

    .camera-retry {
        margin-bottom: 2rem;
    }
</style>

<div style:display={paused ? 'none' : 'block'}>
    {#if cameraError}
        <div class="error-message" role="alert">{cameraError}</div>
        {#if cameras.length > 1}
            <button type="button" class="button button-secondary camera-retry"
                    on:click={cycleCamera} disabled={switchingCamera}>
                Try another camera
            </button>
        {/if}
    {/if}

    {#if cameras.length > 1 && cameraIndex >= 0 && !cameraError}
        <p class="camera-hint">
            Camera {cameraIndex + 1} of {cameras.length} &mdash; {cameraLabel(cameraIndex)}.
            Tap the preview to switch.
        </p>
    {/if}

    <div id={readerId}
         class="preview"
         class:tappable={cameras.length > 1}
         role="button"
         tabindex="0"
         aria-label={cameras.length > 1 ? 'Switch camera' : 'Camera preview'}
         on:click={cycleCamera}
         on:keydown={onPreviewKeydown}></div>
</div>
