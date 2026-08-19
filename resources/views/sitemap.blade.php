<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Main Static Pages --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/ban-tin') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/checkin') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/food-tours') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/tuyen-duong-40') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/tim-kiem') }}</loc>
        <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    {{-- Category Filters --}}
    @foreach (['dong-anh-food-map', 'hanh-trinh-di-san', 'stay-in-dong-anh', 'wellness-care', 'dong-anh-market', 'traditional-market', 'smart-education-map', 'discover-dong-anh-community-culture-hub', 'co-so-kinh-doanh'] as $catSlug)
        <url>
            <loc>{{ url('/?cat=' . $catSlug) }}</loc>
            <lastmod>{{ now()->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach

    {{-- Eateries & Business Establishments --}}
    @if(isset($eateries))
        @foreach ($eateries as $eatery)
            @if(!empty($eatery->slug))
                <url>
                    <loc>{{ url('/dia-diem/' . $eatery->slug) }}</loc>
                    <lastmod>{{ $eatery->updated_at ? $eatery->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString() }}</lastmod>
                    <changefreq>weekly</changefreq>
                    <priority>0.9</priority>
                </url>
            @endif
        @endforeach
    @endif

    {{-- OCOP Products & Business Goods --}}
    @if(isset($ocopProducts))
        @foreach ($ocopProducts as $prod)
            @if(!empty($prod->slug))
                @if(!empty($prod->star_rating))
                    <url>
                        <loc>{{ url('/san-pham-ocop/' . $prod->slug) }}</loc>
                        <lastmod>{{ $prod->updated_at ? $prod->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString() }}</lastmod>
                        <changefreq>weekly</changefreq>
                        <priority>0.85</priority>
                    </url>
                @else
                    <url>
                        <loc>{{ url('/san-pham/' . $prod->slug) }}</loc>
                        <lastmod>{{ $prod->updated_at ? $prod->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString() }}</lastmod>
                        <changefreq>weekly</changefreq>
                        <priority>0.8</priority>
                    </url>
                @endif
            @endif
        @endforeach
    @endif

    {{-- Dishes & Menu Items --}}
    @if(isset($dishes))
        @foreach ($dishes as $dish)
            <url>
                <loc>{{ url('/san-pham/' . $dish->id) }}</loc>
                <lastmod>{{ $dish->updated_at ? $dish->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString() }}</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.75</priority>
            </url>
        @endforeach
    @endif
</urlset>
