@extends('layouts.public')
@section('title', 'Incident Report')
@section('page-title', 'Incident / Accident Report')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/incident-report.css') }}">
@endpush

@section('content')

<div class="ir-wrap">

    <p class="ir-intro">
        Use this form to report a traffic incident or accident around
        <strong>Mayor Gil Fernando Avenue / Sumulong Highway</strong>.
        A confirmation email will be sent to you upon submission.
    </p>

    {{-- Success --}}
    <div class="ir-banner ir-banner-success" id="irSuccess" style="display:none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Your report has been submitted. Please check your email for confirmation.
    </div>

    {{-- Error --}}
    <div class="ir-banner ir-banner-error" id="irErrorBanner" style="display:none;"></div>

    <form id="irForm" novalidate>
        @csrf

        {{-- 01 Fundamental Information --}}
        <div class="ir-card">
            <div class="ir-card-header">
                <span class="ir-num">01</span>
                <span class="ir-card-title">Fundamental Information</span>
            </div>

            <div class="ir-grid-3">
                <div class="stap-form-group">
                    <label class="stap-form-label" for="incident_date">Date <span class="ir-req">*</span></label>
                    <input type="date" id="incident_date" name="incident_date" class="stap-form-input" max="{{ date('Y-m-d') }}" required>
                    <span class="ir-err" id="err_incident_date"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="incident_time">Time <span class="ir-req">*</span></label>
                    <input type="time" id="incident_time" name="incident_time" class="stap-form-input" required>
                    <span class="ir-err" id="err_incident_time"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="environmental_condition">Environmental Condition <span class="ir-req">*</span></label>
                    <select id="environmental_condition" name="environmental_condition" class="stap-form-input" required>
                        <option value="" disabled selected>Select condition</option>
                        <option value="clear">☀️ Clear</option>
                        <option value="cloudy">⛅ Cloudy</option>
                        <option value="rainy">🌧️ Rainy</option>
                        <option value="foggy">🌫️ Foggy</option>
                        <option value="night">🌙 Night</option>
                    </select>
                    <span class="ir-err" id="err_environmental_condition"></span>
                </div>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label" for="location_description">Location Description <span class="ir-req">*</span></label>
                <input type="text" id="location_description" name="location_description"
                       class="stap-form-input"
                       placeholder="e.g. Near Petron station along Mayor Gil Fernando Ave., Marikina"
                       maxlength="500" required>
                <span class="ir-err" id="err_location_description"></span>
            </div>
        </div>

        {{-- 02 Parties Involved --}}
        <div class="ir-card">
            <div class="ir-card-header">
                <span class="ir-num">02</span>
                <span class="ir-card-title">Parties Involved</span>
                <span class="ir-optional">(if applicable)</span>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label">Vehicle Type</label>
                <div class="ir-checkbox-group">
                    @foreach ([
                        'car'               => 'Car',
                        'truck'             => 'Truck',
                        'motorcycle'        => 'Motorcycle',
                        'bus'               => 'Bus',
                        'mini_bus'          => 'Mini Bus',
                        'tricycle'          => 'Tricycle',
                        'jeepney'           => 'Jeepney',
                        'ambulance'         => 'Ambulance',
                        'fire_truck'        => 'Fire Truck',
                        'emergency_vehicle' => 'Emergency Vehicle',
                    ] as $value => $label)
                    <label class="ir-checkbox-btn">
                        <input type="checkbox" name="vehicle_type[]" value="{{ $value }}">
                        <span>{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="ir-grid-2">
                <div class="stap-form-group">
                    <label class="stap-form-label" for="vehicle_count">Number of Vehicles</label>
                    <input type="number" id="vehicle_count" name="vehicle_count"
                           class="stap-form-input" min="1" max="255" placeholder="e.g. 2">
                    <span class="ir-err" id="err_vehicle_count"></span>
                </div>

                <div class="stap-form-group">
                    <label class="stap-form-label">Are there people hurt? <span class="ir-req">*</span></label>
                    <div class="ir-toggle-group">
                        <label class="ir-toggle-btn">
                            <input type="radio" name="people_hurt" value="1" required>
                            <span>Yes</span>
                        </label>
                        <label class="ir-toggle-btn">
                            <input type="radio" name="people_hurt" value="0">
                            <span>No</span>
                        </label>
                    </div>
                    <span class="ir-err" id="err_people_hurt"></span>
                </div>
            </div>

            <div class="stap-form-group" id="injuredCountGroup" style="display:none;">
                <label class="stap-form-label" for="injured_count">How many people are hurt? <span class="ir-req">*</span></label>
                <input type="number" id="injured_count" name="injured_count"
                       class="stap-form-input ir-input-sm" min="1" max="255" placeholder="e.g. 3">
                <span class="ir-err" id="err_injured_count"></span>
            </div>
        </div>

        {{-- 03 Description --}}
        <div class="ir-card">
            <div class="ir-card-header">
                <span class="ir-num">03</span>
                <span class="ir-card-title">Description</span>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label" for="description">Detailed Narrative <span class="ir-req">*</span></label>
                <textarea id="description" name="description" class="stap-form-input ir-textarea"
                          placeholder="Describe what happened in detail — sequence of events, road conditions, any relevant observations..."
                          minlength="20" required></textarea>
                <div class="ir-char-count"><span id="descCount">0</span> characters <span class="ir-muted">(minimum 20)</span></div>
                <span class="ir-err" id="err_description"></span>
            </div>
        </div>

        {{-- 04 Witness & Authorities --}}
        <div class="ir-card">
            <div class="ir-card-header">
                <span class="ir-num">04</span>
                <span class="ir-card-title">Witness &amp; Authorities</span>
            </div>

            <div class="ir-grid-2">
                <div class="stap-form-group">
                    <label class="stap-form-label" for="reporting_party_name">Name of Reporting Party <span class="ir-req">*</span></label>
                    <input type="text" id="reporting_party_name" name="reporting_party_name"
                           class="stap-form-input" placeholder="Full name" required>
                    <span class="ir-err" id="err_reporting_party_name"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="reporter_email">Email Address <span class="ir-req">*</span></label>
                    <input type="email" id="reporter_email" name="reporter_email"
                           class="stap-form-input" placeholder="your@email.com" required>
                    <span class="ir-err" id="err_reporter_email"></span>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="ir-submit-row">
            <button type="submit" class="stap-btn-primary ir-submit-btn" id="irSubmitBtn">
                <span id="irBtnText">Submit Report</span>
                <span id="irBtnSpinner" class="stap-spinner" style="display:none;"></span>
            </button>
            <p class="ir-submit-note">A confirmation email will be sent to the address provided above.</p>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/incident-report.js') }}"></script>
@endpush