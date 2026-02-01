@php
    // Recupera i settaggi dell'utente loggato, se esistono
    $settings = \App\Models\CompanySetting::where('user_id', auth()->id())->first();
@endphp

@if($settings && $settings->logo_path)
    <img src="{{ asset('storage/' . $settings->logo_path) }}" {{ $attributes }} style="max-height: 50px; width: auto;">
@else
   <img src="{{ asset('logo.svg') }}" {{ $attributes }} alt="Logo CloudGest">
@endif