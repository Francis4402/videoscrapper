{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
    <!-- Homepage -->
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Categories -->
    @foreach($categories as $slug => $cat)
    <url>
        <loc>{{ url('/?category=' . $slug) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    <!-- Video Pages with Google Video Schema -->
    @foreach($videos as $video)
    <url>
        <loc>{{ route('videos.watch', $video->slug ?: $video->id) }}</loc>
        <lastmod>{{ $video->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
        <video:video>
            @php
                $thumb = $video->thumbnail_url;
                if ($thumb && Str::startsWith($thumb, '//')) {
                    $thumb = 'https:' . $thumb;
                } elseif ($thumb && !Str::startsWith($thumb, 'http')) {
                    $thumb = url($thumb);
                }
                $thumb = $thumb ?: url('/favicon.ico');
            @endphp
            <video:thumbnail_loc>{{ $thumb }}</video:thumbnail_loc>
            <video:title><![CDATA[{{ $video->title }}]]></video:title>
            <video:description><![CDATA[{{ Str::limit(strip_tags($video->description ?: 'Watch ' . $video->title . ' in full HD streaming on HentaiFlex.'), 200) }}]]></video:description>
            @if($video->video_url)
            <video:content_loc>{{ route('stream.proxy', $video->id) }}</video:content_loc>
            @endif
            <video:player_loc allow_embed="yes">{{ route('videos.watch', $video->slug ?: $video->id) }}</video:player_loc>
            @if($video->duration)
            @php
                $parts = array_map('intval', explode(':', $video->duration));
                $sec = count($parts) === 3 ? ($parts[0] * 3600 + $parts[1] * 60 + $parts[2]) : (count($parts) === 2 ? ($parts[0] * 60 + $parts[1]) : ($parts[0] ?? 600));
            @endphp
            <video:duration>{{ $sec }}</video:duration>
            @endif
            <video:publication_date>{{ $video->created_at->toAtomString() }}</video:publication_date>
            <video:family_friendly>no</video:family_friendly>
            <video:tag>hentai</video:tag>
            <video:tag>watch hentai</video:tag>
            <video:tag>watch hentai online</video:tag>
            <video:tag>free hentai</video:tag>
            <video:tag>hentai videos</video:tag>
            <video:tag>hentai streaming</video:tag>
            <video:tag>best hentai sites</video:tag>
            <video:tag>gangbang hentai</video:tag>
            <video:tag>cartoon hentai</video:tag>
            <video:tag>3d hentai</video:tag>
            <video:tag>uncensored hentai</video:tag>
            <video:tag>full hentai episodes</video:tag>
            <video:tag>english sub hentai</video:tag>
            <video:tag>hentai tube</video:tag>
            <video:tag>anime</video:tag>
            <video:tag>hd anime</video:tag>
            <video:tag>hentaiflex</video:tag>
            <video:category>Anime &amp; Hentai</video:category>
            <video:view_count>12500</video:view_count>
            <video:rating>4.9</video:rating>
        </video:video>
    </url>
    @endforeach
</urlset>
