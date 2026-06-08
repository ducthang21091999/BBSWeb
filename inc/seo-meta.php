<?php
/**
 * Bluebells Studios — SEO Meta
 *
 * Cookie-based i18n compatible. All meta is rendered per current language
 * detected by bbs_current_lang(). Both EN and VI variants of every URL are
 * advertised via hreflang using ?lang= variants so Google can crawl them
 * independently (the bot ignores cookies between requests).
 *
 * Production domain: bluebells.vn (canonical/JSON-LD use home_url() so they
 * adapt automatically between local dev and prod).
 *
 * Brand strings, OG titles and descriptions follow the SEO Brief
 * (SEO-Brief-Bluebells-Studios.md, Section 5).
 */

defined('ABSPATH') || exit;

/* ─────────────────────────────────────────────────────────────────────────
 * Context detection
 * ──────────────────────────────────────────────────────────────────────── */

/**
 * Identify which page bucket we're rendering. Drives meta lookup.
 *
 * @return string  One of: home, movies, contact, film_single, other
 */
function bbs_seo_context() {
    if ( is_front_page() || is_home() )            return 'home';
    if ( is_post_type_archive('film') )            return 'movies';
    if ( is_singular('film') )                     return 'film_single';
    if ( is_page() ) {
        $slug = get_post_field('post_name', get_queried_object_id());
        if ( $slug === 'contact' )                 return 'contact';
    }
    return 'other';
}

/* ─────────────────────────────────────────────────────────────────────────
 * Static meta table — EN + VI for Home / Movies / Contact (Phase 1)
 * ──────────────────────────────────────────────────────────────────────── */

function bbs_seo_meta_table() {
    return [
        'home' => [
            'en' => [
                'title' => 'Bluebells Studios | Vietnamese Feature Film Production House',
                'desc'  => 'Bluebells Studios is a Ho Chi Minh City-based film production house creating Vietnamese feature films with cinematic depth and authentic storytelling. Based in District 1, Saigon.',
                'og_title' => 'Bluebells Studios | Vietnamese Feature Film Production',
                'og_desc'  => 'Crafting Vietnamese feature films with cinematic depth. Based in Saigon, District 1.',
                'og_image_slug' => 'home-en',
            ],
            'vi' => [
                'title' => 'Bluebells Studios | Hãng Phim Điện Ảnh Việt Nam tại Sài Gòn',
                'desc'  => 'Bluebells Studios là hãng phim điện ảnh Việt Nam có trụ sở tại Quận 1, TPHCM, kiến tạo những tác phẩm điện ảnh giàu chiều sâu và mang đậm bản sắc Việt.',
                'og_title' => 'Bluebells Studios | Hãng Phim Điện Ảnh Việt Nam',
                'og_desc'  => 'Kiến tạo những tác phẩm điện ảnh Việt Nam giàu chiều sâu. Trụ sở tại Quận 1, TPHCM.',
                'og_image_slug' => 'home-vi',
            ],
        ],
        'movies' => [
            'en' => [
                'title' => 'Our Films | Bluebells Studios — Vietnamese Cinema Filmography',
                'desc'  => 'Explore the complete filmography of Bluebells Studios — Vietnamese feature films, shorts, and upcoming productions from one of Saigon\'s emerging studios.',
                'og_title' => 'Our Films | Bluebells Studios',
                'og_desc'  => 'Vietnamese feature films, shorts, and current productions by Bluebells Studios.',
                'og_image_slug' => 'movies-en',
            ],
            'vi' => [
                'title' => 'Tác Phẩm | Bluebells Studios — Phim Điện Ảnh Việt Nam',
                'desc'  => 'Khám phá danh sách tác phẩm của Bluebells Studios — các bộ phim điện ảnh Việt Nam, phim ngắn và những dự án đang sản xuất từ hãng phim trẻ tại Sài Gòn.',
                'og_title' => 'Tác Phẩm | Bluebells Studios',
                'og_desc'  => 'Phim điện ảnh Việt Nam, phim ngắn và các dự án đang sản xuất bởi Bluebells Studios.',
                'og_image_slug' => 'movies-vi',
            ],
        ],
        'contact' => [
            'en' => [
                'title' => 'Contact | Bluebells Studios — Saigon Film Production House',
                'desc'  => 'Get in touch with Bluebells Studios in District 1, Ho Chi Minh City for film inquiries, casting calls, press, distribution, and creative collaborations.',
                'og_title' => 'Contact Bluebells Studios | District 1, Saigon',
                'og_desc'  => 'Inquiries, casting, press, and collaborations.',
                'og_image_slug' => 'contact-en',
            ],
            'vi' => [
                'title' => 'Liên Hệ | Bluebells Studios — Hãng Phim tại Quận 1, TPHCM',
                'desc'  => 'Liên hệ Bluebells Studios tại Quận 1, TPHCM cho các nhu cầu hợp tác sản xuất phim, casting, báo chí, phát hành và các dự án sáng tạo.',
                'og_title' => 'Liên Hệ Bluebells Studios | Quận 1, TPHCM',
                'og_desc'  => 'Hợp tác sản xuất, casting, báo chí, phát hành.',
                'og_image_slug' => 'contact-vi',
            ],
        ],
    ];
}

/**
 * Resolve meta for current request. Falls back to Home meta if context
 * not in table (other pages, search, 404, etc.).
 */
function bbs_seo_current_meta() {
    $ctx  = bbs_seo_context();
    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $table = bbs_seo_meta_table();

    // Single film page — pull from the post itself
    if ( $ctx === 'film_single' ) {
        return bbs_seo_film_meta(get_queried_object_id(), $lang);
    }

    if ( isset($table[$ctx][$lang]) ) return $table[$ctx][$lang];

    // Fallback: Home meta in current lang (or 'other' contexts during Phase 1)
    return $table['home'][$lang] ?? $table['home']['vi'];
}

/**
 * Meta for /movies/<slug>/ single film. Phase 2 will refine; for now we
 * compose from post title + excerpt so single films don't inherit Home meta.
 */
function bbs_seo_film_meta( $post_id, $lang ) {
    $title = get_post_field('post_title', $post_id);
    $excerpt = trim( wp_strip_all_tags( get_post_field('post_excerpt', $post_id) ) );
    if ( ! $excerpt ) {
        $excerpt = trim( wp_strip_all_tags( get_post_field('post_content', $post_id) ) );
        $excerpt = mb_substr($excerpt, 0, 155);
    }
    $suffix = $lang === 'vi' ? 'Bluebells Studios | Hãng Phim Việt Nam' : 'Bluebells Studios | Vietnamese Film';
    return [
        'title' => $title . ' | ' . $suffix,
        'desc'  => $excerpt ?: ( $lang === 'vi'
            ? 'Tác phẩm điện ảnh của Bluebells Studios.'
            : 'A film by Bluebells Studios.' ),
        'og_title' => $title . ' | Bluebells Studios',
        'og_desc'  => $excerpt ?: '',
        'og_image_slug' => '', // Will use poster instead — handled in bbs_seo_og_image_url()
    ];
}

/* ─────────────────────────────────────────────────────────────────────────
 * URL helpers — canonical + hreflang variants
 * ──────────────────────────────────────────────────────────────────────── */

/**
 * Clean URL for current request (no ?lang param, no trailing junk).
 */
function bbs_seo_clean_current_url() {
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    // Strip lang param if user landed on a switch URL — we want clean canonical
    $query = $_GET;
    unset($query['lang']);
    $qs = $query ? '?' . http_build_query($query) : '';
    return home_url( $path . $qs );
}

/**
 * URL variant for a specific language (adds ?lang=xx to canonical).
 * Used for hreflang alternates so Google can crawl each language version.
 */
function bbs_seo_lang_variant_url( $lang ) {
    $base = bbs_seo_clean_current_url();
    return add_query_arg('lang', $lang, $base);
}

/* ─────────────────────────────────────────────────────────────────────────
 * Open Graph image URL — uploads/og/<slug>.jpg, falls back to logo
 * ──────────────────────────────────────────────────────────────────────── */

function bbs_seo_og_image_url( $meta ) {
    // Single film: use poster as OG image
    if ( bbs_seo_context() === 'film_single' && function_exists('get_film_poster_url') ) {
        $poster = get_film_poster_url( get_queried_object_id(), 'film-hero' );
        if ( $poster ) return $poster;
    }

    $slug = $meta['og_image_slug'] ?? '';
    if ( $slug ) {
        $upload = wp_upload_dir();
        $candidate_path = trailingslashit($upload['basedir']) . 'og/' . $slug . '.jpg';
        if ( file_exists($candidate_path) ) {
            return trailingslashit($upload['baseurl']) . 'og/' . $slug . '.jpg';
        }
    }

    // Fallback: custom logo (1200×630 not guaranteed, but better than nothing)
    if ( has_custom_logo() ) {
        $logo_id  = get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        if ( $logo_url ) return $logo_url;
    }
    return '';
}

/* ─────────────────────────────────────────────────────────────────────────
 * <title> tag — override WP's title-tag support
 * ──────────────────────────────────────────────────────────────────────── */

/**
 * Short-circuit WP's title generation entirely. Using pre_get_document_title
 * (not document_title_parts) so Polylang and other plugins can't overwrite us.
 */
add_filter('pre_get_document_title', function( $title ) {
    $meta = bbs_seo_current_meta();
    if ( ! empty($meta['title']) ) return $meta['title'];
    return $title;
}, 99);

/* ─────────────────────────────────────────────────────────────────────────
 * <html lang="..."> attribute — reflect current bbs lang
 * ──────────────────────────────────────────────────────────────────────── */

add_filter('language_attributes', function( $output ) {
    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $tag  = $lang === 'vi' ? 'vi-VN' : 'en-US';
    // Replace whatever WP wrote with our value
    return preg_replace('/lang="[^"]*"/', 'lang="' . esc_attr($tag) . '"', $output);
});

/* ─────────────────────────────────────────────────────────────────────────
 * Main <head> meta output
 * ──────────────────────────────────────────────────────────────────────── */

add_action('wp_head', function() {
    // Skip admin / feed / 404 noise
    if ( is_admin() || is_feed() ) return;

    $meta = bbs_seo_current_meta();
    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $canonical = bbs_seo_clean_current_url();
    $og_locale = $lang === 'vi' ? 'vi_VN' : 'en_US';
    $og_locale_alt = $lang === 'vi' ? 'en_US' : 'vi_VN';
    $site_name = get_bloginfo('name');
    $og_image  = bbs_seo_og_image_url($meta);

    // ── Standard meta ─────────────────────────────────────────────────────
    echo "\n<!-- Bluebells SEO -->\n";
    if ( ! empty($meta['desc']) ) {
        printf('<meta name="description" content="%s">' . "\n", esc_attr($meta['desc']));
    }
    printf('<link rel="canonical" href="%s">' . "\n", esc_url($canonical));

    // ── hreflang (cookie-based i18n: advertise ?lang= variants) ──────────
    $url_vi = bbs_seo_lang_variant_url('vi');
    $url_en = bbs_seo_lang_variant_url('en');
    printf('<link rel="alternate" hreflang="vi" href="%s">' . "\n", esc_url($url_vi));
    printf('<link rel="alternate" hreflang="en" href="%s">' . "\n", esc_url($url_en));
    // x-default → clean URL (defaults to VI per bbs_current_lang())
    printf('<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url($canonical));

    // ── Open Graph ────────────────────────────────────────────────────────
    printf('<meta property="og:site_name" content="%s">' . "\n", esc_attr($site_name));
    printf('<meta property="og:type" content="%s">' . "\n",
        bbs_seo_context() === 'film_single' ? 'video.movie' : 'website');
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr($meta['og_title'] ?? $meta['title']));
    if ( ! empty($meta['og_desc']) ) {
        printf('<meta property="og:description" content="%s">' . "\n", esc_attr($meta['og_desc']));
    }
    printf('<meta property="og:url" content="%s">' . "\n", esc_url($canonical));
    printf('<meta property="og:locale" content="%s">' . "\n", esc_attr($og_locale));
    printf('<meta property="og:locale:alternate" content="%s">' . "\n", esc_attr($og_locale_alt));
    if ( $og_image ) {
        printf('<meta property="og:image" content="%s">' . "\n", esc_url($og_image));
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }

    // ── Twitter Card ─────────────────────────────────────────────────────
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    printf('<meta name="twitter:title" content="%s">' . "\n", esc_attr($meta['og_title'] ?? $meta['title']));
    if ( ! empty($meta['og_desc']) ) {
        printf('<meta name="twitter:description" content="%s">' . "\n", esc_attr($meta['og_desc']));
    }
    if ( $og_image ) {
        printf('<meta name="twitter:image" content="%s">' . "\n", esc_url($og_image));
    }

    // ── Theme color + mobile niceties ────────────────────────────────────
    echo '<meta name="theme-color" content="#2438D7">' . "\n";

    echo '<!-- /Bluebells SEO -->' . "\n";
}, 2);

/* ─────────────────────────────────────────────────────────────────────────
 * JSON-LD Organization + LocalBusiness — Home only
 * ──────────────────────────────────────────────────────────────────────── */

add_action('wp_head', function() {
    if ( is_admin() || is_feed() ) return;
    if ( bbs_seo_context() !== 'home' ) return;

    $home = home_url('/');
    $logo = '';
    if ( has_custom_logo() ) {
        $logo = wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full');
    }
    $og_image = bbs_seo_og_image_url( bbs_seo_current_meta() );

    $schema = [
        '@context'     => 'https://schema.org',
        '@type'        => ['Organization', 'LocalBusiness'],
        '@id'          => $home . '#organization',
        'name'         => 'Bluebells Studios',
        'alternateName'=> 'Hãng phim Bluebells',
        'url'          => $home,
        'description'  => 'Vietnamese feature film production house based in Ho Chi Minh City',
        'address'      => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Quận 1',
            'addressRegion'   => 'TPHCM',
            'addressCountry'  => 'VN',
            // TODO: add streetAddress + postalCode when finalized
        ],
        'areaServed'   => 'VN',
        'knowsAbout'   => [
            'Vietnamese cinema',
            'Feature film production',
            'Independent film',
        ],
        // TODO: replace with real social profile URLs
        'sameAs'       => [
            'https://www.facebook.com/bluebellsstudios',
            'https://www.instagram.com/bluebellsstudios',
            'https://www.youtube.com/@bluebellsstudios',
        ],
    ];
    if ( $logo )     $schema['logo']  = $logo;
    if ( $og_image ) $schema['image'] = $og_image;

    echo "\n<script type=\"application/ld+json\">"
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . "</script>\n";
}, 3);

/* ─────────────────────────────────────────────────────────────────────────
 * Cleanup noise tags (per SEO Brief §10.3)
 * ──────────────────────────────────────────────────────────────────────── */

remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/* ─────────────────────────────────────────────────────────────────────────
 * Suppress Polylang's hreflang output (we use cookie-based i18n, not URL).
 * Polylang remains installed but unconfigured — these filters stop its
 * default hreflang advertisement so we don't get duplicates.
 * ──────────────────────────────────────────────────────────────────────── */

add_filter('pll_rel_hreflang_attributes', '__return_empty_array');
add_filter('pll_the_language_link', '__return_empty_string');

/* ─────────────────────────────────────────────────────────────────────────
 * XML Sitemap (WP core, auto at /wp-sitemap.xml) — strip noise.
 *
 * Default would include: posts (Hello World etc.), pages, films, all
 * taxonomies, users. Studio doesn't blog → strip the news/category stack
 * so Google sees only URLs with real landing value:
 *  - Drop users provider (no author archives)
 *  - Drop "post" post type (blog isn't used)
 *  - Drop "category" taxonomy (only "Uncategorized" exists)
 *  - Drop "media_film" taxonomy (internal admin classification only)
 *  - Drop "film_status" + "film_age_rating" (just labels, no SEO value)
 *  - Keep "film_genre" (users search "phim hành động Bluebells" etc.)
 * ──────────────────────────────────────────────────────────────────────── */

add_filter('wp_sitemaps_add_provider', function( $provider, $name ) {
    if ( $name === 'users' ) return false;
    // Polylang wraps each provider in PLL_Multilingual_Sitemaps_Provider,
    // which forks every sitemap URL by language (causes /en/ duplicates
    // even when Polylang isn't configured). Unwrap to the original.
    if ( is_object($provider) && $provider instanceof PLL_Multilingual_Sitemaps_Provider ) {
        $ref = new ReflectionClass($provider);
        if ( $ref->hasProperty('provider') ) {
            $prop = $ref->getProperty('provider');
            $prop->setAccessible(true);
            return $prop->getValue($provider);
        }
    }
    return $provider;
}, 99, 2);

add_filter('wp_sitemaps_post_types', function( $post_types ) {
    unset($post_types['post']);
    return $post_types;
});

add_filter('wp_sitemaps_taxonomies', function( $taxonomies ) {
    unset($taxonomies['category']);
    unset($taxonomies['post_tag']);
    unset($taxonomies['media_film']);
    unset($taxonomies['film_status']);
    unset($taxonomies['film_age_rating']);
    return $taxonomies;
});

// Polylang injects duplicate sitemap entries with /en/ prefix even when
// unconfigured. Filter the final index to strip any URL containing /<lang>/
// before the wp-sitemap filename — keeps the root sitemap clean.
add_filter('wp_sitemaps_index_entries', function( $entries ) {
    return array_values( array_filter($entries, function( $entry ) {
        $loc = $entry['loc'] ?? '';
        // Match /vi/wp-sitemap-... or /en/wp-sitemap-... (lang-prefixed dupes)
        return ! preg_match('#/(vi|en)/wp-sitemap-#', $loc);
    }) );
});
