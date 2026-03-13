<script lang="ts">
    import { onMount } from 'svelte';
    import QRCode from 'qrcode';

    export let data: string;
    export let size = 256;

    let canvas: HTMLCanvasElement;

    onMount(() => {
        if (data) {
            QRCode.toCanvas(canvas, data, {
                width: size,
                errorCorrectionLevel: 'H' // High error correction
            }, (error) => {
                if (error) console.error("QR Code generation error:", error);
            });
        }
    });
</script>

<canvas bind:this={canvas} width={size} height={size}></canvas>
