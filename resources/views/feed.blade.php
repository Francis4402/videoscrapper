{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>HentaiFlex - Watch Free HD Anime &amp; Hentai Videos Online</title>
    <link>{{ url('/') }}</link>
    <description>Latest full-length HD hentai anime episodes, uncensored videos, and 3D animations on HentaiFlex.</description>
    <language>en-us</language>
    <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
    <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>

    @foreach($videos as $video)
    @php
        $thumb = $video->thumbnail_url;
        if ($thumb && Str::startsWith($thumb, '//')) {
            $thumb = 'https:' . $thumb;
        } elseif ($thumb && !Str::startsWith($thumb, 'http')) {
            $thumb = url($thumb);
        }
    @endphp
    <item>
        <title><![CDATA[{{ $video->seo_title }}]]></title>
        <link>{{ route('videos.watch', $video->slug ?: $video->id) }}</link>
        <guid isPermaLink="true">{{ route('videos.watch', $video->slug ?: $video->id) }}</guid>
        <pubDate>{{ $video->created_at->toRfc2822String() }}</pubDate>
        <description><![CDATA[{{ $video->seo_summary }}]]></description>
        @if($thumb)
        <media:thumbnail url="{{ $thumb }}" />
        @endif
        @if($video->video_url)
        <enclosure url="{{ route('stream.proxy', $video->id) }}" length="1048576" type="video/mp4" />
        <media:content url="{{ route('stream.proxy', $video->id) }}" type="video/mp4" medium="video" />
        @endif
        <media:player url="{{ route('videos.embed', $video->slug ?: $video->id) }}" />
        <category>Hentai</category>
        <category>Anime</category>
    </item>
    @endforeach
</channel>
</rss>
