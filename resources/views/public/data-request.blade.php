@extends('layouts.public')
@section('title', 'Data Request')
@section('page-title', 'Footage / Data Request')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/data-request.css') }}">
@endpush

@section('content')

<div class="dr-wrap">

    <p class="dr-intro">
        Request CCTV footage from <strong>Mayor Gil Fernando Avenue / Sumulong Highway</strong>.
        Fill in the form below and our team will review your request and contact you via email.
    </p>

    {{-- Success --}}
    <div class="dr-banner dr-banner-success" id="drSuccess" style="display:none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span id="drSuccessText">Your request has been submitted. We will contact you via email.</span>
    </div>

    {{-- Error --}}
    <div class="dr-banner dr-banner-error" id="drErrorBanner" style="display:none;"></div>

    <form id="drForm" novalidate>
        @csrf

        {{-- 01 Requester Information --}}
        <div class="dr-card">
            <div class="dr-card-header">
                <span class="dr-num">01</span>
                <span class="dr-card-title">Requester Information</span>
            </div>

            <div class="dr-grid-2">
                <div class="stap-form-group">
                    <label class="stap-form-label" for="requester_name">Full Name <span class="dr-req">*</span></label>
                    <input type="text" id="requester_name" name="requester_name"
                           class="stap-form-input" placeholder="e.g. Juan dela Cruz" required>
                    <span class="dr-err" id="err_requester_name"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="requester_organization">Organization / Institution</label>
                    <input type="text" id="requester_organization" name="requester_organization"
                           class="stap-form-input" placeholder="e.g. University of the Philippines">
                    <span class="dr-err" id="err_requester_organization"></span>
                </div>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label" for="requester_address">Address</label>
                <input type="text" id="requester_address" name="requester_address"
                       class="stap-form-input" placeholder="Street, City, Province">
                <span class="dr-err" id="err_requester_address"></span>
            </div>

            <div class="dr-grid-2">
                <div class="stap-form-group">
                    <label class="stap-form-label" for="requester_email">Email Address <span class="dr-req">*</span></label>
                    <input type="email" id="requester_email" name="requester_email"
                           class="stap-form-input" placeholder="your@email.com" required>
                    <span class="dr-err" id="err_requester_email"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="requester_contact">Contact Number <span class="dr-req">*</span></label>
                    <input type="tel" id="requester_contact" name="requester_contact"
                           class="stap-form-input" placeholder="e.g. 09171234567" required>
                    <span class="dr-err" id="err_requester_contact"></span>
                </div>
            </div>
        </div>

        {{-- 02 Nature & Purpose --}}
        <div class="dr-card">
            <div class="dr-card-header">
                <span class="dr-num">02</span>
                <span class="dr-card-title">Nature &amp; Purpose of Request</span>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label">Request Nature <span class="dr-req">*</span></label>
                <div class="dr-nature-group">
                    @foreach ([
                        'academic' => ['icon' => '🎓', 'label' => 'Academic'],
                        'personal' => ['icon' => '👤', 'label' => 'Personal'],
                        'legal'    => ['icon' => '⚖️', 'label' => 'Legal'],
                        'media'    => ['icon' => '📺', 'label' => 'Media'],
                        'other'    => ['icon' => '📋', 'label' => 'Other'],
                    ] as $value => $item)
                    <label class="dr-nature-btn">
                        <input type="radio" name="request_nature" value="{{ $value }}" required>
                        <span>
                            <span class="dr-nature-icon">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                        </span>
                    </label>
                    @endforeach
                </div>
                <span class="dr-err" id="err_request_nature"></span>
            </div>
        </div>

        {{-- 03 Footage Details --}}
        <div class="dr-card">
            <div class="dr-card-header">
                <span class="dr-num">03</span>
                <span class="dr-card-title">Footage Details</span>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label" for="camera_id">Camera / Direction <span class="dr-req">*</span></label>
                <select id="camera_id" name="camera_id" class="stap-form-input" required>
                    <option value="" disabled selected>Loading cameras...</option>
                </select>
                <span class="dr-err" id="err_camera_id"></span>
            </div>

            <div class="dr-grid-3">
                <div class="stap-form-group">
                    <label class="stap-form-label" for="footage_date">Date of Footage <span class="dr-req">*</span></label>
                    <input type="date" id="footage_date" name="footage_date"
                           class="stap-form-input" max="{{ date('Y-m-d') }}" required>
                    <span class="dr-err" id="err_footage_date"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="footage_time_start">Time From <span class="dr-req">*</span></label>
                    <input type="time" id="footage_time_start" name="footage_time_start"
                           class="stap-form-input" required>
                    <span class="dr-err" id="err_footage_time_start"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="footage_time_end">Time To <span class="dr-req">*</span></label>
                    <input type="time" id="footage_time_end" name="footage_time_end"
                           class="stap-form-input" required>
                    <span class="dr-err" id="err_footage_time_end"></span>
                </div>
            </div>
        </div>

        {{-- 04 Incident Details (optional) --}}
        <div class="dr-card">
            <div class="dr-card-header">
                <span class="dr-num">04</span>
                <span class="dr-card-title">Incident Details</span>
                <span class="dr-optional">(optional — fill if related to an incident)</span>
            </div>

            <div class="dr-grid-2">
                <div class="stap-form-group">
                    <label class="stap-form-label" for="incident_date">Incident Date</label>
                    <input type="date" id="incident_date" name="incident_date"
                           class="stap-form-input" max="{{ date('Y-m-d') }}">
                    <span class="dr-err" id="err_incident_date"></span>
                </div>
                <div class="stap-form-group">
                    <label class="stap-form-label" for="incident_time">Incident Time</label>
                    <input type="time" id="incident_time" name="incident_time"
                           class="stap-form-input">
                    <span class="dr-err" id="err_incident_time"></span>
                </div>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label" for="names_involved">Names Involved</label>
                <input type="text" id="names_involved" name="names_involved"
                       class="stap-form-input" placeholder="e.g. Pedro Manalo, driver of white Toyota">
                <span class="dr-err" id="err_names_involved"></span>
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label" for="incident_description">Incident Description</label>
                <textarea id="incident_description" name="incident_description"
                          class="stap-form-input dr-textarea"
                          placeholder="Briefly describe what happened..."></textarea>
                <span class="dr-err" id="err_incident_description"></span>
            </div>
        </div>

        {{-- Submit --}}
        <div class="dr-submit-row">
            <button type="submit" class="stap-btn-primary dr-submit-btn" id="drSubmitBtn">
                <span id="drBtnText">Submit Request</span>
                <span id="drBtnSpinner" class="stap-spinner" style="display:none;"></span>
            </button>
            <p class="dr-submit-note">
                Our team will review your request and respond to your email within 3–5 business days.
            </p>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
    window.STAP_DR_ROUTES = {
        store:   '{{ route("public.request.store") }}',
        cameras: '{{ route("public.cameras") }}',
    };
</script>
<script src="{{ asset('js/data-request.js') }}"></script>
@endpush