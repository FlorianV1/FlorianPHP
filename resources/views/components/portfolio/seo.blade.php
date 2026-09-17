@props([
    'title',
    'description' => '',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'person' => null,
])

@php
    use App\Support\SiteBranding;
    use Illuminate\Support\Facades\Storage;

    $canonical ??= url()->current();

    $ogImage = $image ?: SiteBranding::get('og_image');
    if ($ogImage && ! str_starts_with($ogImage, 'http')) {
        $ogImage = url(Storage::url($ogImage));
    }

    $description = trim((string) $description);
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:site_name" content="{{ SiteBranding::get('full_name') }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif

@if($person)
    @php
        $sameAs = collect($person->social_links ?? [])
            ->pluck('url')
            ->filter()
            ->values()
            ->all();

        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => SiteBranding::get('full_name'),
            'jobTitle' => $person->role,
            'email' => $person->email ? 'mailto:' . $person->email : null,
            'url' => route('home'),
            'address' => $person->location ? [
                '@type' => 'PostalAddress',
                'addressLocality' => $person->location,
            ] : null,
            'sameAs' => $sameAs ?: null,
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
