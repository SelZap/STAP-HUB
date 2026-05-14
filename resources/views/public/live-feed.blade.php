@extends('layouts.public')

@section('title', 'Live Camera Feed')
@section('page-title', 'Live Camera Feed')

@push('styles')
<style>
    .live-shell {
        display: grid;
        gap: 1.25rem;
    }

    .live-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
    }

    .live-hero h2 {
        margin: 0 0 .4rem;
        font-size: 1.8rem;
        line-height: 1.15;
    }

    .live-hero p {
        margin: 0;
        max-width: 52rem;
        color: rgba(255, 255, 255, 0.78);
    }

    .live-status {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .55rem .8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: .85rem;
        white-space: nowrap;
    }

    .live-status-dot {
        width: .55rem;
        height: .55rem;
        border-radius: 999px;
        background: #22c55e;
        box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.18);
    }

    .live-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .live-card {
        border-radius: 1rem;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }

    .live-card-media {
        aspect-ratio: 16 / 9;
        background: linear-gradient(135deg, #dbeafe 0%, #e2e8f0 100%);
        display: grid;
        place-items: center;
        overflow: hidden;
        position: relative;
    }

    .live-card-media iframe,
    .live-card-media video,
    .live-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 0;
    }

    .live-card-body {
        padding: 1rem 1rem 1.1rem;
        display: grid;
        gap: .45rem;
    }

    .live-card-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }

    .live-card-meta {
        color: #475569;
        font-size: .93rem;
    }

    .live-link {
        color: #0f172a;
        font-weight: 600;
        text-decoration: none;
    }

    .live-link:hover {
        text-decoration: underline;
    }

    .live-empty {
        padding: 2.25rem 1.25rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.03);
        border: 1px dashed rgba(15, 23, 42, 0.14);
        color: #475569;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="live-shell">
    <section class="live-hero">
        <div>
            <h2>Active public camera feeds</h2>
            <p>
                Monitor connected cameras in real time. If a stream cannot be embedded,
                the card still provides the source link for direct access.
            </p>
        </div>

        <div class="live-status">
            <span class="live-status-dot"></span>
            <span id="camera-count">Loading cameras...</span>
        </div>
    </section>

    <section>
        <div id="camera-grid" class="live-grid"></div>
        <div id="camera-empty" class="live-empty" hidden>
            No active cameras are available right now.
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const grid = document.getElementById('camera-grid');
    const empty = document.getElementById('camera-empty');
    const counter = document.getElementById('camera-count');

    const renderEmpty = (message) => {
        grid.innerHTML = '';
        empty.textContent = message;
        empty.hidden = false;
        counter.textContent = message;
    };

    try {
        const response = await fetch(@json(route('public.live.cameras')), {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Unable to load cameras');
        }

        const cameras = await response.json();
        counter.textContent = `${cameras.length} active camera${cameras.length === 1 ? '' : 's'}`;

        if (!cameras.length) {
            renderEmpty('No active cameras are available right now.');
            return;
        }

        empty.hidden = true;
        grid.innerHTML = cameras.map((camera) => {
            const title = camera.label || `Camera ${camera.id}`;
            const nodeName = camera.node?.name || 'Unknown node';
            const position = camera.position || 'Unspecified position';
            const streamUrl = camera.stream_url || '';
            const streamEmbed = streamUrl
                ? `<iframe src="${streamUrl}" title="${title}" loading="lazy" referrerpolicy="no-referrer"></iframe>`
                : `<div class="live-empty" style="margin: 0; border-radius: 0; border: 0; background: transparent;">Stream unavailable</div>`;

            return `
                <article class="live-card">
                    <div class="live-card-media">
                        ${streamEmbed}
                    </div>
                    <div class="live-card-body">
                        <h3 class="live-card-title">${title}</h3>
                        <div class="live-card-meta">${nodeName} · ${position}</div>
                        ${streamUrl ? `<a class="live-link" href="${streamUrl}" target="_blank" rel="noopener">Open stream source</a>` : ''}
                    </div>
                </article>
            `;
        }).join('');
    } catch (error) {
        renderEmpty('Unable to load active cameras right now.');
    }
});
</script>
@endpush
