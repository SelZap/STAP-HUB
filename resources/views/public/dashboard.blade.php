@extends('layouts.public')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')

{{-- Live Vehicle Count --}}
<section class="dash-section">
    <div class="dash-section-header">
        <div class="stap-live-badge"><span class="stap-pulse"></span> LIVE</div>
        <h2 class="dash-section-title">Live Vehicle Count</h2>
    </div>
    <div class="dash-los-grid">
        @forelse ($liveVehicleData ?? [] as $data)
            <div class="dash-los-card">
                <span class="dash-los-number" data-count="{{ $data['vehicle_count'] ?? 0 }}">
                    {{ number_format($data['vehicle_count'] ?? 0) }}
                </span>
                <div class="dash-los-badge los-{{ $data['los'] ?? 'A' }}">
                    {{ $data['los'] ?? '—' }}
                </div>
                <div class="dash-los-location">{{ $data['location'] ?? '' }}</div>
            </div>
        @empty
            <div class="stap-empty">No live data available.</div>
        @endforelse
    </div>
</section>

{{-- Vehicle Count Trend --}}
<section class="dash-section">
    <div class="stap-card">
        <div class="stap-card-header">
            <span class="stap-card-title">Vehicle Count — Last 7 Days</span>
        </div>
        <div class="stap-card-body">
            <div id="chart-trend" class="stap-chart-wrap"></div>
        </div>
    </div>
</section>

{{-- Traffic History --}}
<section class="dash-section">
    <h2 class="dash-section-title">Traffic History</h2>
    <div class="stap-card">
        <div class="stap-card-body" style="padding-top: 12px;">
            @if (!empty($trafficHistory) && count($trafficHistory) > 0)
                <div class="stap-table-wrap">
                    <table class="stap-table dash-history-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                @foreach ($locations ?? [] as $loc)
                                    <th>{{ $loc }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trafficHistory as $hour => $losPerLocation)
                                <tr>
                                    <td class="dash-history-time">{{ $hour }}</td>
                                    @foreach ($locations ?? [] as $loc)
                                        @php $los = $losPerLocation[$loc] ?? '—'; @endphp
                                        <td>
                                            @if ($los !== '—')
                                                <span class="stap-los stap-los-{{ $los }}">{{ $los }}</span>
                                            @else
                                                <span class="stap-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="stap-empty">No traffic history recorded for today yet.</div>
            @endif
        </div>
    </div>
</section>

{{-- Rain & Weather --}}
<section class="dash-section">
    <h2 class="dash-section-title">Rain &amp; Weather Log</h2>
    <div class="dash-weather-grid">

        <div class="stap-card">
            <div class="stap-card-header">
                <span class="stap-card-title">Today's Rain Log</span>
                <span class="stap-card-link">From STAP Nodes</span>
            </div>
            <div class="stap-card-body">
                @if (!empty($weatherData) && $weatherData->count() > 0)
                    <div class="dash-rain-list">
                        @foreach ($weatherData as $entry)
                            @php
                                $meta  = $entry['meta'] ?? [];
                                $pct   = $meta['pct']   ?? 0;
                                $color = $meta['color'] ?? '#ccc';
                                $label = $meta['label'] ?? '';
                                $time  = $entry['time'] ?? '';
                            @endphp
                            <div class="dash-rain-row">
                                <span class="dash-rain-time">{{ $time }}</span>
                                <div class="stap-bar-track dash-rain-bar">
                                    <div class="stap-bar-fill"
                                         data-width="{{ $pct }}"
                                         style="background: {{ $color }}; width: 0%">
                                    </div>
                                </div>
                                <span class="dash-rain-label" style="color: {{ $color }}">
                                    {{ $label }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="stap-empty">No weather data recorded today.</div>
                @endif
            </div>
        </div>

        <div class="stap-card">
            <div class="stap-card-header">
                <span class="stap-card-title">Weekly Weather Forecast</span>
                <span class="stap-card-link">Open-Meteo · Mayor Gil Fernando Ave</span>
            </div>
            <div class="stap-card-body">
                <div id="weather-forecast-wrap">
                    <div class="dash-forecast-loading">
                        <div class="stap-spinner" style="border-color: rgba(27,39,68,.2); border-top-color: var(--navy);"></div>
                        <span>Loading forecast...</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection

@push('scripts')
@php $trend = $trendData ?? []; @endphp
<script>
    const STAP = { trendData: {!! json_encode($trend) !!} };
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush