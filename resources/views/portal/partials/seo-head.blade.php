{{-- Metadata SEO (Google Search) & GEO (AI Overviews / LLM Search) untuk portal publik --}}
<link rel="canonical" href="{{ $seo['canonical'] }}">
<link rel="alternate" hreflang="{{ $seo['hreflang'] }}" href="{{ $seo['canonical'] }}">
<link rel="alternate" hreflang="x-default" href="{{ $seo['canonical'] }}">
<link rel="sitemap" type="application/xml" title="Sitemap" href="{{ $seo['sitemap_url'] }}">
<link rel="alternate" type="text/plain" title="LLMs.txt" href="{{ $seo['llms_url'] }}">

<meta name="robots" content="{{ $seo['robots'] }}">
<meta name="googlebot" content="{{ $seo['robots'] }}">
<meta name="keywords" content="{{ $seo['keywords'] }}">
<meta name="author" content="{{ $seo['author'] }}">
<meta name="geo.region" content="{{ $seo['geo_region'] }}">
<meta name="geo.placename" content="{{ $seo['geo_placename'] }}">
<meta name="geo.position" content="{{ $seo['geo_position'] }}">
<meta name="ICBM" content="{{ $seo['icbm'] }}">

@if(!empty($seo['verification']['google']))
  <meta name="google-site-verification" content="{{ $seo['verification']['google'] }}">
@endif
@if(!empty($seo['verification']['bing']))
  <meta name="msvalidate.01" content="{{ $seo['verification']['bing'] }}">
@endif

{{-- Open Graph --}}
<meta property="og:type" content="website">
<meta property="og:locale" content="{{ $seo['locale'] }}">
<meta property="og:site_name" content="{{ $seo['site_name'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $seo['og_image'] }}">
<meta property="og:image:width" content="{{ $seo['og_image_width'] }}">
<meta property="og:image:height" content="{{ $seo['og_image_height'] }}">
<meta property="og:image:alt" content="{{ $seo['og_image_alt'] }}">

{{-- Twitter / X Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['og_image'] }}">
<meta name="twitter:image:alt" content="{{ $seo['og_image_alt'] }}">

{{-- Schema.org JSON-LD (@graph) --}}
<script type="application/ld+json">{!! json_encode($seo['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
