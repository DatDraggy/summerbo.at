<script lang="ts">
    import { tick } from 'svelte';
    import BadgeScanner from './components/BadgeScanner.svelte';
    import Overlay from './components/Overlay.svelte';
    import { formatTime } from './helper/date.js';

    type Loan = {
        key: string;
        id: string;
        pickedUpAt: string;
        returnedAt: string | null;
    };

    type Result = {
        ok: boolean;
        title: string;
        id: string | null;
        detail: string | null;
        undo: { kind: 'pickup' | 'return'; key: string } | null;
    };

    type Confirm =
        | { kind: 'clear' }
        | { kind: 'remove'; loan: Loan };

    // No login, no API: the list lives in this browser so handouts survive no signal.
    const STORAGE_KEY = 'summerboat:headsets:v1';

    let loans: Loan[] = [];
    // Sticky: a bad payload stays worth mentioning.
    let storageError: string | null = null;
    // Transient: cleared on the next good write.
    let saveError: string | null = null;

    let pendingReturn: Loan | null = null;
    let result: Result | null = null;
    let confirmAction: Confirm | null = null;

    let onlyOutstanding = false;

    let showManual = false;
    let manualId = '';
    let manualError: string | null = null;
    let manualInputEl: HTMLInputElement | null = null;

    $: scannerPaused = !!(pendingReturn || result || confirmAction || showManual);

    // Newest first; the log is read top-down.
    $: sorted = [...loans].sort((a, b) => Date.parse(b.pickedUpAt) - Date.parse(a.pickedUpAt));
    $: visible = onlyOutstanding ? sorted.filter(l => !l.returnedAt) : sorted;
    $: outCount = loans.filter(l => !l.returnedAt).length;

    load();

    function uid(): string {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    }

    function norm(id: string): string {
        return id.trim().toLowerCase();
    }

    function sanitise(entry: any): Loan | null {
        if (!entry || typeof entry.id !== 'string') return null;

        const id = entry.id.trim();
        if (!id) return null;
        if (typeof entry.pickedUpAt !== 'string' || isNaN(Date.parse(entry.pickedUpAt))) return null;

        const returnedAt = typeof entry.returnedAt === 'string' && !isNaN(Date.parse(entry.returnedAt))
            ? entry.returnedAt
            : null;

        return {
            key: typeof entry.key === 'string' && entry.key ? entry.key : uid(),
            id,
            pickedUpAt: entry.pickedUpAt,
            returnedAt
        };
    }

    function load() {
        let raw: string | null = null;

        try {
            raw = localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            storageError = 'This browser blocks local storage, so nothing can be saved. '
                + 'Check private-browsing or site settings before handing anything out.';
            return;
        }

        if (!raw) {
            loans = [];
            return;
        }

        let parsed: any;
        try {
            parsed = JSON.parse(raw);
        } catch {
            // Keep the unreadable payload instead of overwriting it on the next scan.
            try {
                localStorage.setItem(STORAGE_KEY + ':corrupt', raw);
            } catch {}
            storageError = 'The saved list could not be read and was set aside as '
                + `"${STORAGE_KEY}:corrupt". Starting from an empty list.`;
            loans = [];
            return;
        }

        const entries = Array.isArray(parsed) ? parsed : parsed?.entries;
        loans = (Array.isArray(entries) ? entries : []).map(sanitise).filter(Boolean) as Loan[];
    }

    function save() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({ version: 1, entries: loans }));
            saveError = null;
        } catch (e) {
            saveError = 'Could not save to this browser. The list will be lost when '
                + 'the page closes — export it now.';
        }
    }

    // Another tab wrote; re-read so both stay level.
    function onStorage(e: StorageEvent) {
        if (e.key !== STORAGE_KEY && e.key !== null) return;
        load();
    }

    function findActive(id: string): Loan | null {
        const wanted = norm(id);

        for (let i = loans.length - 1; i >= 0; i--) {
            if (!loans[i].returnedAt && norm(loans[i].id) === wanted) return loans[i];
        }

        return null;
    }

    // Not scannerPaused: it is derived, and the manual form calls in before it updates.
    function handleCode(raw: string) {
        if (pendingReturn || result || confirmAction) return;

        const id = raw.trim();
        if (!id) return;

        const active = findActive(id);
        if (active) pendingReturn = active;
        else checkOut(id);
    }

    function checkOut(id: string) {
        const loan: Loan = { key: uid(), id, pickedUpAt: new Date().toISOString(), returnedAt: null };
        loans = [...loans, loan];
        save();

        result = {
            ok: true,
            title: 'Headset handed out',
            id: loan.id,
            detail: `Picked up ${stamp(loan.pickedUpAt)}.`,
            undo: { kind: 'pickup', key: loan.key }
        };
    }

    function confirmReturn() {
        const pending = pendingReturn;
        if (!pending) return;

        const current = loans.find(l => l.key === pending.key);
        pendingReturn = null;

        if (!current) {
            result = {
                ok: false,
                title: 'Entry is gone',
                id: pending.id,
                detail: 'It was removed or cleared somewhere else. Scan again to hand out a headset.',
                undo: null
            };
            return;
        }

        if (current.returnedAt) {
            result = {
                ok: false,
                title: 'Already returned',
                id: current.id,
                detail: `Marked returned ${stamp(current.returnedAt)}.`,
                undo: null
            };
            return;
        }

        const returnedAt = new Date().toISOString();
        loans = loans.map(l => (l.key === current.key ? { ...l, returnedAt } : l));
        save();

        result = {
            ok: true,
            title: 'Headset returned',
            id: current.id,
            detail: `Out for ${humanDuration(current.pickedUpAt, returnedAt)}.`,
            undo: { kind: 'return', key: current.key }
        };
    }

    function cancelReturn() {
        pendingReturn = null;
    }

    function acknowledgeResult() {
        result = null;
    }

    function undoResult() {
        const undo = result?.undo;
        result = null;
        if (!undo) return;

        if (undo.kind === 'pickup') loans = loans.filter(l => l.key !== undo.key);
        else loans = loans.map(l => (l.key === undo.key ? { ...l, returnedAt: null } : l));

        save();
    }

    // Fallback for a badge that will not scan; same path as the camera.
    async function openManual() {
        showManual = true;
        manualId = '';
        manualError = null;
        await tick();
        manualInputEl?.focus();
    }

    function closeManual() {
        showManual = false;
        manualError = null;
    }

    function submitManual() {
        const id = manualId.trim();
        if (!id) {
            manualError = 'Enter a badge ID.';
            return;
        }

        showManual = false;
        manualError = null;
        handleCode(id);
    }

    function markReturnedFromList(loan: Loan) {
        if (loan.returnedAt) return;

        loans = loans.map(l => (l.key === loan.key ? { ...l, returnedAt: new Date().toISOString() } : l));
        save();
    }

    function askRemove(loan: Loan) {
        confirmAction = { kind: 'remove', loan };
    }

    function askClear() {
        confirmAction = { kind: 'clear' };
    }

    function cancelConfirm() {
        confirmAction = null;
    }

    function runConfirm() {
        if (!confirmAction) return;

        if (confirmAction.kind === 'clear') {
            loans = [];
            pendingReturn = null;
            result = null;
        } else {
            const key = confirmAction.loan.key;
            loans = loans.filter(l => l.key !== key);
            if (pendingReturn?.key === key) pendingReturn = null;
            if (result?.undo?.key === key) result = { ...result, undo: null };
        }

        save();
        confirmAction = null;
    }

    function exportCsv() {
        if (loans.length === 0) return;

        const header = [
            'badge_id',
            'picked_up_iso',
            'picked_up_local',
            'returned_iso',
            'returned_local',
            'status'
        ];

        const rows = sorted.map(l => [
            l.id,
            l.pickedUpAt,
            stamp(l.pickedUpAt),
            l.returnedAt ?? '',
            l.returnedAt ? stamp(l.returnedAt) : '',
            l.returnedAt ? 'returned' : 'out'
        ]);

        // BOM for Excel; every cell quoted so a comma or quote in an ID is safe.
        const bom = String.fromCharCode(0xFEFF);
        const csv = bom + [header, ...rows]
            .map(row => row.map(cell => '"' + String(cell).replace(/"/g, '""') + '"').join(','))
            .join('\r\n') + '\r\n';

        download(`headsets-${fileStamp(new Date())}.csv`, csv);
    }

    function download(filename: string, contents: string) {
        const blob = new Blob([contents], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();

        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }

    function fileStamp(d: Date): string {
        const pad = (n: number) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
            + `-${pad(d.getHours())}${pad(d.getMinutes())}`;
    }

    function stamp(iso: string): string {
        const d = new Date(iso);
        if (isNaN(d.getTime())) return '—';

        return formatTime(d, { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
    }

    function clearWarning(): string {
        const noun = loans.length === 1 ? 'entry' : 'entries';
        const stillOut = outCount > 0
            ? ` — including ${outCount} headset${outCount === 1 ? '' : 's'} still out`
            : '';

        return `This deletes all ${loans.length} ${noun} from this browser${stillOut}. `
            + 'Export first if you need a record; this cannot be undone.';
    }

    // Joined in script, not markup: Svelte trims whitespace at {#if} boundaries.
    function loanMeta(loan: Loan): string {
        const parts = ['Out ' + stamp(loan.pickedUpAt)];

        if (loan.returnedAt) {
            parts.push('back ' + stamp(loan.returnedAt));
            parts.push(humanDuration(loan.pickedUpAt, loan.returnedAt));
        }

        return parts.join(' · ');
    }

    function humanDuration(fromIso: string, toIso: string): string {
        const ms = Date.parse(toIso) - Date.parse(fromIso);
        if (!isFinite(ms) || ms < 0) return 'an unknown time';

        const minutes = Math.round(ms / 60000);
        if (minutes < 1) return 'less than a minute';
        if (minutes < 60) return `${minutes} min`;

        const hours = Math.floor(minutes / 60);
        const rest = minutes % 60;
        return rest ? `${hours} h ${rest} min` : `${hours} h`;
    }

    function onWindowKeydown(e: KeyboardEvent) {
        if (e.key !== 'Escape') return;

        if (confirmAction) cancelConfirm();
        else if (showManual) closeManual();
    }
</script>

<style>
    .container {
        max-width: 32rem;
        margin: 0 auto 4rem;
    }

    .intro {
        margin-bottom: 2rem;
        font-size: .875rem;
        line-height: 1.25rem;
        color: #555;
    }

    .stats {
        display: flex;
        gap: .5rem;
        margin-bottom: 2rem;
    }

    .stat {
        flex: 1;
        padding: .75rem 1rem;
        border-radius: 8px;
        background-color: #f3f3f3;
        color: black;
    }

    .stat.out {
        background-color: var(--color-vip);
    }

    .stat-value {
        display: block;
        font-size: 1.75rem;
        line-height: 1.15;
        font-weight: 800;
    }

    .stat-label {
        display: block;
        font-size: .75rem;
        line-height: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .125ch;
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

    .verify-title {
        margin-bottom: .5rem;
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
        margin-bottom: 1rem;
        font-size: 1.75rem;
        line-height: 1.15;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .card-detail {
        margin-bottom: 1.5rem;
        font-size: 1.125rem;
        font-weight: 800;
    }

    .actions {
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    .manual-open {
        margin-bottom: 2rem;
    }

    .list-section {
        border-top: 1px solid var(--color-primary);
        padding-top: 2rem;
    }

    .filter-row {
        margin-bottom: 1rem;
    }

    .filter-row .checkbox-group label {
        margin-bottom: 0;
    }

    .list-empty {
        margin-bottom: 2rem;
        color: #555;
    }

    .loan-list {
        list-style: none;
        margin: 0 0 2rem;
        padding: 0;
    }

    .loan {
        margin-bottom: .5rem;
        padding: .75rem 1rem;
        border-radius: .5rem;
        background-color: #f3f3f3;
        color: black;
    }

    .loan.out {
        background-color: var(--color-vip);
    }

    .loan-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .loan-id {
        font-size: 1.125rem;
        line-height: 1.2;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .loan-chip {
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

    .loan-chip.out {
        background-color: var(--color-secondary);
        color: black;
    }

    .loan-meta {
        font-size: .875rem;
        line-height: 1.25rem;
        color: #555;
    }

    .loan-actions {
        display: flex;
        gap: .5rem;
        margin-top: .5rem;
    }

    .loan-btn {
        width: auto;
        padding: .375rem .875rem;
        font-size: .75rem;
        border-radius: 2rem;
        border: 0;
        background-color: #ddd;
        color: black;
        cursor: pointer;
    }

    .loan-btn:hover {
        background-color: var(--color-tertiary);
    }

    .list-actions {
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    .confirm-card,
    .manual-card {
        width: min(28rem, calc(100vw - 2rem));
        max-height: 85vh;
        overflow-y: auto;
        padding: 1rem;
        border-radius: 8px;
        background-color: white;
        color: black;
    }

    .confirm-text {
        margin-bottom: 1.5rem;
    }

    .manual-hint {
        margin-bottom: 1rem;
        font-size: .875rem;
        line-height: 1.25rem;
        color: #555;
    }

    .storage-warning {
        margin-bottom: 2rem;
        padding: .75rem;
        border-radius: 5px;
        background-color: #ffc8c8;
        color: var(--color-secondary-dark);
        font-weight: 800;
    }
</style>

<svelte:window on:keydown={onWindowKeydown} on:storage={onStorage} />

{#if confirmAction}
    <Overlay onClose={cancelConfirm}>
        <div class="confirm-card">
            {#if confirmAction.kind === 'clear'}
                <h3 class="text-headline verify-title">Clear the list?</h3>
                <p class="confirm-text">{clearWarning()}</p>
            {:else}
                <h3 class="text-headline verify-title">Remove this entry?</h3>
                <p class="confirm-text">
                    <strong>{confirmAction.loan.id}</strong>, picked up {stamp(confirmAction.loan.pickedUpAt)}.
                    Removing it drops it from the list and the export.
                </p>
            {/if}

            <div class="actions">
                <button type="button" class="button button-primary" on:click={runConfirm}>
                    {confirmAction.kind === 'clear' ? 'Delete everything' : 'Remove entry'}
                </button>
                <button type="button" class="button button-secondary" on:click={cancelConfirm}>
                    Cancel
                </button>
            </div>
        </div>
    </Overlay>
{/if}

{#if showManual}
    <Overlay onClose={closeManual}>
        <div class="manual-card">
            <h3 class="text-headline verify-title">Enter badge ID</h3>

            <form on:submit|preventDefault={submitManual}>
                <div class="input-wrapper">
                    <label for="manual-id"><span>Badge ID</span></label>
                    <input id="manual-id" type="text" inputmode="text" enterkeyhint="done"
                           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                           placeholder="As printed on the badge"
                           bind:this={manualInputEl} bind:value={manualId}>
                </div>
                <p class="manual-hint">
                    Same as scanning: a new ID hands out a headset, an ID that already has one
                    asks for it back.
                </p>

                {#if manualError}
                    <div class="error-message" role="alert">{manualError}</div>
                {/if}

                <div class="actions">
                    <button type="submit" class="button button-primary">Continue</button>
                    <button type="button" class="button button-secondary" on:click={closeManual}>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </Overlay>
{/if}

<div class="container">
    <h2 class="text-headline">Silent Disco Headsets</h2>

    <p class="intro">
        Scan a badge to hand out a headset, scan it again to take it back. Everything is kept
        in this browser only &mdash; use the same device and browser all evening, and export
        the list before clearing it.
    </p>

    {#if storageError}
        <div class="storage-warning" role="alert">{storageError}</div>
    {/if}

    {#if saveError}
        <div class="storage-warning" role="alert">{saveError}</div>
    {/if}

    <div class="stats">
        <div class="stat out">
            <span class="stat-value">{outCount}</span>
            <span class="stat-label">Out</span>
        </div>
        <div class="stat">
            <span class="stat-value">{loans.length - outCount}</span>
            <span class="stat-label">Returned</span>
        </div>
        <div class="stat">
            <span class="stat-value">{loans.length}</span>
            <span class="stat-label">Total</span>
        </div>
    </div>

    {#if result}
        <section class="result-card" class:ok={result.ok} role="alert">
            <h3 class="text-headline verify-title">
                {result.ok ? '✓' : '✗'} {result.title}
            </h3>

            {#if result.id}
                <span class="check-caption">Badge</span>
                <span class="check-value">{result.id}</span>
            {/if}

            {#if result.detail}
                <p class="card-detail">{result.detail}</p>
            {/if}

            <div class="actions">
                <button type="button" class="button button-primary" on:click={acknowledgeResult}>
                    Scan next
                </button>
                {#if result.undo}
                    <button type="button" class="button button-secondary" on:click={undoResult}>
                        {result.undo.kind === 'pickup' ? 'Undo handout' : 'Undo return'}
                    </button>
                {/if}
            </div>
        </section>
    {/if}

    {#if pendingReturn}
        <section class="verify-card">
            <h3 class="text-headline verify-title">Received back headset?</h3>

            <span class="check-caption">Badge</span>
            <span class="check-value">{pendingReturn.id}</span>

            <p class="card-detail">
                Picked up {stamp(pendingReturn.pickedUpAt)} &mdash;
                out for {humanDuration(pendingReturn.pickedUpAt, new Date().toISOString())}.
            </p>

            <div class="actions">
                <button type="button" class="button button-primary" on:click={confirmReturn}>
                    Yes, headset is back
                </button>
                <button type="button" class="button button-secondary" on:click={cancelReturn}>
                    Cancel
                </button>
            </div>
        </section>
    {/if}

    <BadgeScanner paused={scannerPaused} onScan={handleCode} />

    {#if !scannerPaused}
        <button type="button" class="button button-secondary manual-open" on:click={openManual}>
            Can't scan? Enter ID manually
        </button>
    {/if}

    <section class="list-section">
        <h3 class="text-headline-line">Log</h3>

        <div class="checkbox-wrapper filter-row">
            <div class="checkbox-group">
                <input type="checkbox" id="only-outstanding" bind:checked={onlyOutstanding}>
                <label for="only-outstanding">Only show headsets still out</label>
            </div>
        </div>

        {#if visible.length === 0}
            <p class="list-empty">
                {loans.length === 0 ? 'Nothing scanned yet.' : 'Every headset is back.'}
            </p>
        {:else}
            <ul class="loan-list">
                {#each visible as loan (loan.key)}
                    <li class="loan" class:out={!loan.returnedAt}>
                        <div class="loan-top">
                            <span class="loan-id">{loan.id}</span>
                            <span class="loan-chip" class:out={!loan.returnedAt}>
                                {loan.returnedAt ? 'Returned' : 'Out'}
                            </span>
                        </div>
                        <div class="loan-meta">{loanMeta(loan)}</div>
                        <div class="loan-actions">
                            {#if !loan.returnedAt}
                                <button type="button" class="loan-btn"
                                        on:click={() => markReturnedFromList(loan)}>
                                    Mark returned
                                </button>
                            {/if}
                            <button type="button" class="loan-btn" on:click={() => askRemove(loan)}>
                                Remove
                            </button>
                        </div>
                    </li>
                {/each}
            </ul>
        {/if}

        <div class="list-actions">
            <button type="button" class="button button-secondary"
                    on:click={exportCsv} disabled={loans.length === 0}>
                Export CSV
            </button>
            <button type="button" class="button button-secondary"
                    on:click={askClear} disabled={loans.length === 0}>
                Clear list
            </button>
        </div>
    </section>
</div>
