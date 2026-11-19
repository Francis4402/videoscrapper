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
            <video:thumbnail_loc>{{ $video->thumbnail_url ?: url('/favicon.ico') }}</video:thumbnail_loc>
            <video:title><![CDATA[{{ $video->title }}]]></video:title>
            <video:description><![CDATA[{{ Str::limit(strip_tags($video->description ?: 'Watch ' . $video->title . ' in full HD streaming on HentaiFlex.'), 200) }}]]></video:description>
            @if($video->video_url)
            <video:content_loc>{{ route('stream.proxy', $video->id) }}</video:content_loc>
            @endif
            <video:player_loc allow_embed="yes">{{ route('videos.watch', $video->slug ?: $video->id) }}</video:player_loc>
            <video:publication_date>{{ $video->created_at->toAtomString() }}</video:publication_date>
            <video:family_friendly>no</video:family_friendly>
        </video:video>
    </url>
    @endforeach
</urlset>
