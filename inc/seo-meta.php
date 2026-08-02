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
                'desc'  => 'Bluebells Studios là hãng phim điện ảnh Việt Nam có trụ sở tại Quận 1, TPHCM, sản xuất những bộ phim điện ảnh giàu chiều sâu và mang đậm bản sắc Việt.',
                'og_title' => 'Bluebells Studios | Hãng Phim Điện Ảnh Việt Nam',
                'og_desc'  => 'Sản xuất những bộ phim điện ảnh Việt Nam giàu chiều sâu. Trụ sở tại Quận 1, TPHCM.',
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
                'title' => 'Phim | Bluebells Studios — Hãng Phim Điện Ảnh Việt Nam',
                'desc'  => 'Khám phá các bộ phim của Bluebells Studios — phim điện ảnh Việt Nam, phim ngắn và những dự án đang sản xuất từ hãng phim trẻ tại Sài Gòn.',
                'og_title' => 'Phim | Bluebells Studios',
                'og_desc'  => 'Các bộ phim điện ảnh Việt Nam, phim ngắn và dự án đang sản xuất bởi Bluebells Studios.',
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
 * Meta for /movies/<slug>/ single film. Picks bilingual synopsis from ACF
 * so VI users see Vietnamese description, EN users see English.
 *
 * Synopsis priority:
 *  - lang=vi: film_synopsis_short_vn → film_synopsis_short → post_excerpt → post_content (155 chars)
 *  - lang=en: film_synopsis_short → film_synopsis_short_vn → post_excerpt → post_content (155 chars)
 */
function bbs_seo_film_meta( $post_id, $lang ) {
    $title = get_post_field('post_title', $post_id);

    $syn_en = trim( wp_strip_all_tags( get_post_meta($post_id, 'film_synopsis_short', true) ) );
    $syn_vn = trim( wp_strip_all_tags( get_post_meta($post_id, 'film_synopsis_short_vn', true) ) );

    if ( $lang === 'vi' ) {
        $desc = $syn_vn ?: $syn_en;
    } else {
        $desc = $syn_en ?: $syn_vn;
    }

    if ( ! $desc ) {
        $desc = trim( wp_strip_all_tags( get_post_field('post_excerpt', $post_id) ) );
    }
    if ( ! $desc ) {
        $desc = mb_substr( trim( wp_strip_all_tags( get_post_field('post_content', $post_id) ) ), 0, 155);
    }

    $suffix = $lang === 'vi'
        ? 'Bluebells Studios | Hãng Phim Việt Nam'
        : 'Bluebells Studios | Vietnamese Film';

    return [
        'title'         => $title . ' | ' . $suffix,
        'desc'          => $desc ?: ( $lang === 'vi' ? 'Bộ phim của Bluebells Studios.' : 'A film by Bluebells Studios.' ),
        'og_title'      => $title . ' | Bluebells Studios',
        'og_desc'       => $desc,
        'og_image_slug' => '', // poster fallback handled in bbs_seo_og_image_url()
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

/**
 * Real pixel size of an OG image, so we never advertise 1200×630 for an image
 * that isn't (the logo fallback and film banners are other ratios — a wrong
 * declared size makes Facebook/Zalo crop or reject the preview).
 *
 * @return array|null [width, height], or null when the size can't be resolved.
 */
function bbs_seo_og_image_size( $url ) {
    if ( ! $url ) return null;

    // Resized WP images carry their dimensions in the filename: name-1920x1080.jpg
    if ( preg_match('/-(\d+)x(\d+)\.[a-zA-Z]{3,4}$/', $url, $m) ) {
        return [ (int) $m[1], (int) $m[2] ];
    }

    // Files we author under uploads/og/ are built to the 1200×630 OG spec
    $upload = wp_upload_dir();
    if ( strpos($url, trailingslashit($upload['baseurl']) . 'og/') === 0 ) {
        return [1200, 630];
    }

    // Otherwise ask the media library for the original's size
    $id = attachment_url_to_postid($url);
    if ( $id ) {
        $meta = wp_get_attachment_metadata($id);
        if ( ! empty($meta['width']) && ! empty($meta['height']) ) {
            return [ (int) $meta['width'], (int) $meta['height'] ];
        }
    }

    return null;
}

function bbs_seo_og_image_url( $meta ) {
    // Single film: prefer banner (16:9 ratio matches OG spec better than 2:3 poster)
    if ( bbs_seo_context() === 'film_single' ) {
        $film_id = get_queried_object_id();
        if ( function_exists('get_film_banner_url') ) {
            $banner = get_film_banner_url( $film_id, 'film-hero' );
            if ( $banner ) return $banner;
        }
        if ( function_exists('get_film_poster_url') ) {
            $poster = get_film_poster_url( $film_id, 'film-hero' );
            if ( $poster ) return $poster;
        }
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
        $og_size = bbs_seo_og_image_size($og_image);
        if ( $og_size ) {
            printf('<meta property="og:image:width" content="%d">' . "\n", $og_size[0]);
            printf('<meta property="og:image:height" content="%d">' . "\n", $og_size[1]);
        }
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
 * og:video tag on single film pages — Facebook/X recognize this for
 * video card embedding (when trailer is present and is YouTube/Vimeo).
 * ──────────────────────────────────────────────────────────────────────── */

add_action('wp_head', function() {
    if ( bbs_seo_context() !== 'film_single' ) return;
    if ( ! function_exists('get_film_trailer_url') ) return;

    $trailer = get_film_trailer_url( get_queried_object_id() );
    if ( ! $trailer ) return;

    // Convert YouTube watch URLs to embed URLs for og:video
    $embed = $trailer;
    if ( preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_\-]+)#', $trailer, $m) ) {
        $embed = 'https://www.youtube.com/embed/' . $m[1];
    }
    printf('<meta property="og:video" content="%s">' . "\n", esc_url($embed));
    printf('<meta property="og:video:url" content="%s">' . "\n", esc_url($embed));
    printf('<meta property="og:video:secure_url" content="%s">' . "\n", esc_url($embed));
    echo '<meta property="og:video:type" content="text/html">' . "\n";
}, 4);

/* ─────────────────────────────────────────────────────────────────────────
 * Movie schema (JSON-LD) on single film pages
 *
 * Includes: name, description, image (poster + banner), director, actor[],
 * genre[], duration (ISO 8601 if parsable), datePublished, contentRating,
 * trailer (VideoObject), inLanguage, productionCompany.
 *
 * Boosts Google rich results for film queries ("Bluebells phi phong" etc.)
 * ──────────────────────────────────────────────────────────────────────── */

add_action('wp_head', function() {
    if ( bbs_seo_context() !== 'film_single' ) return;

    $film_id = get_queried_object_id();
    $lang    = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $meta    = bbs_seo_film_meta($film_id, $lang);
    $url     = get_permalink($film_id);

    // Images — provide both banner + poster so Google picks the best
    $images = [];
    if ( function_exists('get_film_banner_url') ) {
        $b = get_film_banner_url($film_id, 'film-hero');
        if ( $b ) $images[] = $b;
    }
    if ( function_exists('get_film_poster_url') ) {
        $p = get_film_poster_url($film_id, 'film-hero');
        if ( $p ) $images[] = $p;
    }

    // Director / writer / producer / cast — split cast on commas + newlines
    $director = trim( get_post_meta($film_id, 'film_director', true) );
    $writer   = trim( get_post_meta($film_id, 'film_writer', true) );
    $producer = trim( get_post_meta($film_id, 'film_producer', true) );
    $cast_raw = trim( wp_strip_all_tags( get_post_meta($film_id, 'film_cast', true) ) );
    $cast = $cast_raw
        ? array_values(array_filter(array_map('trim', preg_split('/[,;\r\n]+/', $cast_raw))))
        : [];

    // Genres from taxonomy
    $genre_terms = wp_get_post_terms($film_id, 'film_genre', ['fields' => 'names']);
    $genres = is_array($genre_terms) ? $genre_terms : [];

    // Release date — best effort ISO format
    $release_raw = get_post_meta($film_id, 'film_release_date', true);
    $release_iso = '';
    if ( $release_raw ) {
        $ts = strtotime($release_raw);
        if ( $ts ) $release_iso = date('Y-m-d', $ts);
        elseif ( preg_match('/(\d{4})/', $release_raw, $m) ) $release_iso = $m[1];
    }

    // Runtime — try to parse "120 min" / "1h 45m" / "105" → ISO 8601 duration
    $runtime_raw = trim( get_post_meta($film_id, 'film_runtime', true) );
    $duration_iso = '';
    if ( $runtime_raw ) {
        if ( preg_match('/(\d+)\s*h(?:our)?s?\s*(\d+)?/i', $runtime_raw, $m) ) {
            $duration_iso = 'PT' . (int)$m[1] . 'H' . (isset($m[2]) ? (int)$m[2] . 'M' : '');
        } elseif ( preg_match('/(\d+)\s*(?:min|phút|m)/i', $runtime_raw, $m) ) {
            $duration_iso = 'PT' . (int)$m[1] . 'M';
        } elseif ( preg_match('/^(\d+)$/', $runtime_raw, $m) ) {
            $duration_iso = 'PT' . (int)$m[1] . 'M';
        }
    }

    // Content rating from age_rating taxonomy
    $rating_terms = wp_get_post_terms($film_id, 'film_age_rating', ['fields' => 'names']);
    $content_rating = ( ! is_wp_error($rating_terms) && ! empty($rating_terms) ) ? $rating_terms[0] : '';

    // Original language
    $orig_lang = trim( get_post_meta($film_id, 'film_language', true) );

    // Trailer URL → VideoObject (Google understands this nested)
    $trailer_url = function_exists('get_film_trailer_url') ? get_film_trailer_url($film_id) : '';

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Movie',
        '@id'         => $url . '#movie',
        'url'         => $url,
        'name'        => get_the_title($film_id),
        'description' => $meta['desc'] ?: '',
    ];
    if ( $images )           $schema['image']           = count($images) === 1 ? $images[0] : $images;
    if ( $director )         $schema['director']        = ['@type' => 'Person', 'name' => $director];
    if ( $writer )           $schema['author']          = ['@type' => 'Person', 'name' => $writer];
    if ( $producer )         $schema['producer']        = ['@type' => 'Person', 'name' => $producer];
    if ( $cast ) {
        $schema['actor'] = array_map(function( $name ) {
            return ['@type' => 'Person', 'name' => $name];
        }, $cast);
    }
    if ( $genres )           $schema['genre']           = $genres;
    if ( $release_iso )      $schema['datePublished']   = $release_iso;
    if ( $duration_iso )     $schema['duration']        = $duration_iso;
    if ( $content_rating )   $schema['contentRating']   = $content_rating;
    if ( $orig_lang )        $schema['inLanguage']      = $orig_lang;

    // Production company → reference the Organization on Home
    $schema['productionCompany'] = [
        '@type' => 'Organization',
        '@id'   => home_url('/') . '#organization',
        'name'  => 'Bluebells Studios',
    ];

    if ( $trailer_url ) {
        $embed = $trailer_url;
        if ( preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_\-]+)#', $trailer_url, $m) ) {
            $embed = 'https://www.youtube.com/embed/' . $m[1];
        }
        $schema['trailer'] = [
            '@type'       => 'VideoObject',
            'name'        => sprintf('%s — Trailer', get_the_title($film_id)),
            'description' => $meta['desc'] ?: '',
            'thumbnailUrl' => $images ?: [],
            'uploadDate'  => $release_iso ?: date('Y-m-d', strtotime( get_the_date('Y-m-d', $film_id) )),
            'embedUrl'    => $embed,
            'contentUrl'  => $trailer_url,
        ];
    }

    echo "\n<script type=\"application/ld+json\">"
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . "</script>\n";
}, 5);

/* ─────────────────────────────────────────────────────────────────────────
 * BreadcrumbList JSON-LD on single film + movies archive
 * Helps Google show breadcrumb path in search results.
 * ──────────────────────────────────────────────────────────────────────── */

add_action('wp_head', function() {
    $ctx = bbs_seo_context();
    if ( ! in_array($ctx, ['film_single', 'movies'], true) ) return;

    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $home_label   = $lang === 'vi' ? 'Trang chủ' : 'Home';
    $movies_label = $lang === 'vi' ? 'Phim' : 'Movies';

    $items = [
        [
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => $home_label,
            'item'     => home_url('/'),
        ],
        [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => $movies_label,
            'item'     => get_post_type_archive_link('film'),
        ],
    ];

    if ( $ctx === 'film_single' ) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => 3,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        ];
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];

    echo "\n<script type=\"application/ld+json\">"
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . "</script>\n";
}, 6);

/* ─────────────────────────────────────────────────────────────────────────
 * Search Console + Bing Webmaster verification meta tags
 *
 * To activate after deploy:
 *  1. Go to https://search.google.com/search-console → Add property `bluebells.vn`
 *  2. Choose "HTML tag" verification → copy the content="..." value
 *  3. Paste into BBS_GOOGLE_SITE_VERIFICATION constant below
 *  4. Same for Bing Webmaster Tools (https://www.bing.com/webmasters)
 * ──────────────────────────────────────────────────────────────────────── */

// TODO: paste the verification token from Search Console here (the `content` value only, not the full tag).
define('BBS_GOOGLE_SITE_VERIFICATION', '');
define('BBS_BING_SITE_VERIFICATION', '');

add_action('wp_head', function() {
    if ( defined('BBS_GOOGLE_SITE_VERIFICATION') && BBS_GOOGLE_SITE_VERIFICATION ) {
        printf('<meta name="google-site-verification" content="%s">' . "\n",
            esc_attr(BBS_GOOGLE_SITE_VERIFICATION));
    }
    if ( defined('BBS_BING_SITE_VERIFICATION') && BBS_BING_SITE_VERIFICATION ) {
        printf('<meta name="msvalidate.01" content="%s">' . "\n",
            esc_attr(BBS_BING_SITE_VERIFICATION));
    }
}, 1);

/* ─────────────────────────────────────────────────────────────────────────
 * Favicon fallback — use WP custom logo if no site_icon is configured.
 * (When Customize → Site Icon is set, WP auto-emits the proper links.)
 * ──────────────────────────────────────────────────────────────────────── */

add_action('wp_head', function() {
    if ( has_site_icon() ) return; // WP handles it
    if ( ! has_custom_logo() ) return;

    $logo_id  = get_theme_mod('custom_logo');
    $logo_url = wp_get_attachment_image_url($logo_id, 'full');
    if ( ! $logo_url ) return;

    printf('<link rel="icon" href="%s">' . "\n", esc_url($logo_url));
    printf('<link rel="apple-touch-icon" href="%s">' . "\n", esc_url($logo_url));
}, 8);

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

// Exclude well-known WP default pages from sitemap. Filtering at query level
// is more reliable than name-matching after-the-fact.
add_filter('wp_sitemaps_posts_query_args', function( $args, $post_type ) {
    if ( $post_type !== 'page' ) return $args;
    $exclude_slugs = ['sample-page', 'privacy-policy'];
    foreach ( $exclude_slugs as $slug ) {
        $p = get_page_by_path($slug);
        if ( $p ) $args['post__not_in'][] = $p->ID;
    }
    return $args;
}, 10, 2);

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
