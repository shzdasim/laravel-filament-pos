<!-- resources/views/vendor/filament/components/brand.blade.php -->

@php
    use App\Models\Application;
    
    // Retrieve the first application details
    $application = Application::first();
    $applicationName = $application ? $application->name : 'No Application';
    $applicationLogoUrl = $application ? asset('storage/' . $application->logo) : null;
    $applicationLicense = $application ? $application->licence_number : 'No License';

    // Concatenate the name and license for the brand name
    $brandName = $applicationName;
    if ($applicationLicense && $applicationLicense !== 'No License') {
        $brandName .= ' - ' . $applicationLicense;
    }
@endphp

<div class="filament-brand flex items-center space-x-2">
    @if ($applicationLogoUrl)
        <img src="{{ $applicationLogoUrl }}" alt="{{ $brandName }}" class="h-8 w-8 object-cover">
    @else
        <span>No Logo</span>
    @endif
    <span class="font-bold">{{ $brandName }}</span>
</div>
