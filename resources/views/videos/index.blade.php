@extends('layouts.app')

@php
    $page = (int) request('page', 1);
    $pageSuffix = ($page > 1) ? " - Page {$page}" : "";

    $seoTitle = 'Hentai: Watch Free HD Hentai Videos & Anime Online | HentaiFlex' . $pageSuffix;
    $seoDesc = 'Watch free HD hentai, gangbang hentai, cartoon hentai, and 3D uncensored anime videos online on HentaiFlex. Stream thousands of full-length episodes with zero buffering.' . ($pageSuffix ? " (Browse page {$page})" : "");
    $seoCanonical = route('home');

    if ($currentCategory && isset($categories[$currentCategory])) {
        $catName = $categories[$currentCategory]['name'];
        $seoTitle = "{$catName} Videos - Watch Free HD {$catName} Online{$pageSuffix} | HentaiFlex";
        $seoDesc = "Stream the best {$catName} full-length videos and anime series in high definition on HentaiFlex. Fast playback, no lag, mobile-friendly free streaming." . ($pageSuffix ? " (Browse page {$page})" : "");
        $seoCanonical = route('videos.category', $currentCategory) . ($page > 1 ? '?page=' . $page : '');
    } elseif (request('q')) {
        $q = e(request('q'));
        $seoTitle = "{$q} Hentai Videos - Free HD Streaming{$pageSuffix} | HentaiFlex";
        $seoDesc = "Watch free {$q} hentai and anime streaming videos on HentaiFlex. High definition quality with ultra-fast direct playback." . ($pageSuffix ? " (Browse page {$page})" : "");
        $seoCanonical = route('home', array_filter(['q' => request('q'), 'page' => ($page > 1 ? $page : null)]));
    }
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_keywords', 'watch hentai, free hentai, hentai online, hentai streaming, best hentai sites, watch hentai online free, uncensored hentai, gangbang hentai, cartoon hentai, 3d hentai, full hentai episodes, english sub hentai, hentai tube, hd anime, hentai videos, hentaiflex')
@section('canonical_url', $seoCanonical)

@push('schema')
    @include('videos.partials.schema')
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Hero & Search Bar Section --}}
    @include('videos.partials.hero-search')

    {{-- Category Filter Navigation Bar --}}
    @include('videos.partials.category-bar')

    {{-- Video Grid, In-Feed Ads, and Pagination --}}
    @include('videos.partials.video-grid')

    {{-- Comprehensive SEO Content Hub & FAQ Section --}}
    @include('videos.partials.seo-hub')

</div>
@endsection
