<?php
/**
 * Bluebells Studios — functions.php
 */

function bluebells_assets() {
    $theme_dir = get_stylesheet_directory();
    $css_ver = file_exists($theme_dir . '/style.css') ? filemtime($theme_dir . '/style.css') : '1.0.0';
    $js_ver  = file_exists($theme_dir . '/js/main.js') ? filemtime($theme_dir . '/js/main.js') : '1.0.0';

    wp_enqueue_style( 'bluebells-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&family=Inter:wght@300;400;500;600;700&display=swap',
        [], null );
    wp_enqueue_style( 'bluebells-style', get_stylesheet_uri(), ['bluebells-fonts'], $css_ver );
    wp_enqueue_script( 'bluebells-main', get_template_directory_uri() . '/js/main.js', [], $js_ver, true );
}
add_action( 'wp_enqueue_scripts', 'bluebells_assets' );

function bluebells_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_image_size( 'film-poster', 600, 900, true );
    add_image_size( 'film-hero', 1920, 1080, true );
    add_image_size( 'film-card', 800, 1200, true );
    register_nav_menus(['primary'=>'Primary Navigation','footer'=>'Footer Navigation']);
}
add_action( 'after_setup_theme', 'bluebells_setup' );

// Custom Post Type: Partners
function bluebells_register_partners() {
    register_post_type( 'partner', [
        'labels' => [
            'name'          => 'Partners',
            'singular_name' => 'Partner',
            'add_new_item'  => 'Thêm Partner',
            'edit_item'     => 'Sửa Partner',
            'view_item'     => 'Xem Partner',
            'not_found'     => 'Không có partner nào',
            'menu_name'     => 'Partners',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'supports'     => ['title'],
        'menu_icon'    => 'dashicons-groups',
        'show_in_rest' => true,
        'menu_position'=> 6,
    ]);
}
add_action( 'init', 'bluebells_register_partners' );

// Custom Post Type: Films
function bluebells_register_films() {
    register_post_type( 'film', [
        'labels'       => ['name'=>'Films','singular_name'=>'Film','add_new_item'=>'Add New Film','edit_item'=>'Edit Film','view_item'=>'View Film','not_found'=>'No films found'],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug'=>'films'],
        'supports'     => ['title','editor','thumbnail','excerpt'],
        'menu_icon'    => 'dashicons-video-alt2',
        'show_in_rest' => true,
    ]);
}
add_action( 'init', 'bluebells_register_films' );

// Taxonomies
function bluebells_register_taxonomies() {
    register_taxonomy( 'film_status', 'film', [
        'labels'       => ['name'=>'Film Status','singular_name'=>'Status'],
        'hierarchical' => true, 'show_in_rest'=>true, 'rewrite'=>['slug'=>'status'],
    ]);
    register_taxonomy( 'film_genre', 'film', [
        'labels'       => ['name'=>'Genres','singular_name'=>'Genre'],
        'hierarchical' => true, 'show_in_rest'=>true, 'rewrite'=>['slug'=>'genre'],
    ]);
}
add_action( 'init', 'bluebells_register_taxonomies' );

// ─── ACF FIELD GROUP (registered via PHP — no sync needed) ───
function bluebells_register_acf_fields() {
    if ( ! function_exists('acf_add_local_field_group') ) return;

    acf_add_local_field_group([
        'key'      => 'group_bluebells_film',
        'title'    => 'Film Details',
        'location' => [[['param'=>'post_type','operator'=>'==','value'=>'film']]],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'fields' => [
            // ── Tab: Thông tin cơ bản ──
            ['key'=>'field_tab_basic','label'=>'Thông tin cơ bản','type'=>'tab','placement'=>'top'],
            ['key'=>'field_film_vn_title','label'=>'Tên tiếng Việt','name'=>'film_vn_title','type'=>'text',
             'instructions'=>'Tên phim bằng tiếng Việt.','wrapper'=>['width'=>'50'],'placeholder'=>'vd: Phí Phồng'],
            ['key'=>'field_film_genre','label'=>'Thể loại','name'=>'film_genre_tax','type'=>'taxonomy',
             'taxonomy'=>'film_genre','add_term'=>1,'save_terms'=>1,'load_terms'=>1,
             'return_format'=>'object','field_type'=>'checkbox','wrapper'=>['width'=>'50']],
            ['key'=>'field_film_rating','label'=>'Rating','name'=>'film_rating','type'=>'text',
             'instructions'=>'vd: T18, P, K','wrapper'=>['width'=>'25'],'placeholder'=>'T18'],
            ['key'=>'field_film_runtime','label'=>'Runtime','name'=>'film_runtime','type'=>'text',
             'instructions'=>'Số phút','wrapper'=>['width'=>'25'],'placeholder'=>'112','append'=>'phút'],
            ['key'=>'field_film_release_date','label'=>'Ngày chiếu','name'=>'film_release_date','type'=>'text',
             'wrapper'=>['width'=>'25'],
             'instructions'=>'Định dạng: "2026" (chỉ năm), "08/2026" (tháng/năm), hoặc "24/04/2026" (đầy đủ). Càng ít chi tiết càng xếp trước.',
             'placeholder'=>'24/04/2026'],
            ['key'=>'field_film_format','label'=>'Định dạng','name'=>'film_format','type'=>'text',
             'instructions'=>'vd: 2D, 3D, IMAX','wrapper'=>['width'=>'25'],'placeholder'=>'2D'],
            ['key'=>'field_film_language','label'=>'Ngôn ngữ','name'=>'film_language','type'=>'text',
             'instructions'=>'vd: Tiếng Việt, English','wrapper'=>['width'=>'50'],'placeholder'=>'Tiếng Việt'],

            // ── Tab: Hình ảnh & Video ──
            ['key'=>'field_tab_media','label'=>'Hình ảnh & Video','type'=>'tab','placement'=>'top'],
            ['key'=>'field_film_featured_banner','label'=>'Hiển thị trên Banner trang chủ','name'=>'film_featured_banner',
             'type'=>'true_false','instructions'=>'Bật để phim xuất hiện trong slideshow banner ở trang chủ.',
             'default_value'=>0,'ui'=>1,'ui_on_text'=>'Có','ui_off_text'=>'Không'],
            ['key'=>'field_film_poster','label'=>'Poster','name'=>'film_poster','type'=>'image',
             'instructions'=>'Portrait (2:3). Min 600×900px.','wrapper'=>['width'=>'50'],
             'return_format'=>'id','library'=>'all','preview_size'=>'medium'],
            ['key'=>'field_film_banner','label'=>'Banner','name'=>'film_banner','type'=>'image',
             'instructions'=>'Landscape hero (16:9). Min 1920×1080px.','wrapper'=>['width'=>'50'],
             'return_format'=>'id','library'=>'all','preview_size'=>'medium'],
            ['key'=>'field_film_logo','label'=>'Logo phim','name'=>'film_logo','type'=>'image',
             'instructions'=>'Logo/title treatment (PNG trong suốt).','wrapper'=>['width'=>'50'],
             'return_format'=>'id','library'=>'all','preview_size'=>'medium'],
            ['key'=>'field_film_trailer','label'=>'Trailer URL','name'=>'film_trailer','type'=>'url',
             'instructions'=>'YouTube hoặc Vimeo URL.','wrapper'=>['width'=>'50'],
             'placeholder'=>'https://youtube.com/watch?v=...'],
            ['key'=>'field_film_videos','label'=>'Videos bổ sung','name'=>'film_videos','type'=>'textarea',
             'instructions'=>'Mỗi dòng 1 video. Định dạng: <code>URL</code> hoặc <code>URL|Tiêu đề</code>. Nếu không nhập tiêu đề, tự lấy từ YouTube.<br>VD:<br><code>https://youtu.be/abc123</code> ← tự fetch title<br><code>https://youtu.be/xyz789|Behind The Scenes</code> ← title custom',
             'wrapper'=>['width'=>'50'],'rows'=>6,'placeholder'=>"https://youtu.be/...\nhttps://youtu.be/...|Trailer chính"],

            // ── Tab: Nội dung ──
            ['key'=>'field_tab_content','label'=>'Nội dung','type'=>'tab','placement'=>'top'],
            ['key'=>'field_film_logline','label'=>'Logline','name'=>'film_logline','type'=>'textarea','rows'=>2,'new_lines'=>'br'],
            ['key'=>'field_film_synopsis_short','label'=>'Synopsis ngắn','name'=>'film_synopsis_short','type'=>'textarea','rows'=>4,'new_lines'=>'br'],
            ['key'=>'field_film_synopsis_full','label'=>'Synopsis đầy đủ','name'=>'film_synopsis_full','type'=>'wysiwyg',
             'tabs'=>'all','toolbar'=>'basic','media_upload'=>0,'delay'=>0],

            // ── Tab: Đoàn phim ──
            ['key'=>'field_tab_crew','label'=>'Đoàn phim','type'=>'tab','placement'=>'top'],
            ['key'=>'field_film_director','label'=>'Đạo diễn','name'=>'film_director','type'=>'text','wrapper'=>['width'=>'50']],
            ['key'=>'field_film_producer','label'=>'Nhà sản xuất','name'=>'film_producer','type'=>'text','wrapper'=>['width'=>'50']],
            ['key'=>'field_film_cast','label'=>'Diễn viên','name'=>'film_cast','type'=>'textarea','rows'=>3,'new_lines'=>'br'],

            // ── Tab: Liên kết ──
            ['key'=>'field_tab_links','label'=>'Liên kết','type'=>'tab','placement'=>'top'],
            ['key'=>'field_film_ticket_link','label'=>'Link mua vé','name'=>'film_ticket_link','type'=>'url',
             'wrapper'=>['width'=>'50'],'placeholder'=>'https://...'],
            ['key'=>'field_film_press_kit','label'=>'Press Kit','name'=>'film_press_kit','type'=>'url',
             'wrapper'=>['width'=>'50'],'placeholder'=>'https://...'],

        ],
    ]);
}
add_action( 'acf/init', 'bluebells_register_acf_fields' );

// ─── ACF FIELD GROUP for Partner CPT ───
function bluebells_register_partner_fields() {
    if ( ! function_exists('acf_add_local_field_group') ) return;

    acf_add_local_field_group([
        'key'      => 'group_bluebells_partner',
        'title'    => 'Partner Details',
        'location' => [[['param'=>'post_type','operator'=>'==','value'=>'partner']]],
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'fields' => [
            ['key'=>'field_partner_order','label'=>'Số thứ tự','name'=>'partner_order','type'=>'number',
             'instructions'=>'Số nhỏ hơn → hiển thị trước. VD: 1, 2, 3, 10, 20...','wrapper'=>['width'=>'25'],
             'default_value'=>10,'min'=>0,'step'=>1],
            ['key'=>'field_partner_logo','label'=>'Logo','name'=>'partner_logo','type'=>'image',
             'instructions'=>'Logo của partner. PNG trong suốt khuyến nghị. Min 300×300px.',
             'wrapper'=>['width'=>'75'],'return_format'=>'id','library'=>'all','preview_size'=>'medium'],
        ],
    ]);
}
add_action( 'acf/init', 'bluebells_register_partner_fields' );

// Helper: get partners sorted by order ASC
function get_partners_sorted() {
    $partners = get_posts([
        'post_type'      => 'partner',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);
    usort($partners, function($a, $b) {
        $oa = (int) get_post_meta($a->ID, 'partner_order', true);
        $ob = (int) get_post_meta($b->ID, 'partner_order', true);
        if ( !$oa ) $oa = 9999;
        if ( !$ob ) $ob = 9999;
        return $oa <=> $ob;
    });
    return $partners;
}

// Helper: film logo URL — reads ACF image ID for title treatment
function get_film_logo_url( $post_id = null, $size = 'large' ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $id = get_field( 'film_logo', $post_id );
    if ( $id && is_numeric( $id ) ) {
        $url = wp_get_attachment_image_url( (int) $id, $size );
        if ( $url ) return $url;
    }
    return '';
}

// Helper: banner URL — reads ACF image ID, returns sized URL or empty string
function get_film_banner_url( $post_id = null, $size = 'film-hero' ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $id = get_field( 'film_banner', $post_id );
    if ( $id && is_numeric( $id ) ) {
        $url = wp_get_attachment_image_url( (int) $id, $size );
        if ( $url ) return $url;
    }
    return '';
}

// Helper: poster URL — reads ACF image ID, falls back to Featured Image
function get_film_poster_url( $post_id = null, $size = 'film-poster' ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $id = get_field( 'film_poster', $post_id );
    if ( $id && is_numeric( $id ) ) {
        $url = wp_get_attachment_image_url( (int) $id, $size );
        if ( $url ) return $url;
    }
    return get_the_post_thumbnail_url( $post_id, $size ) ?: '';
}

// Helper: get any film field via ACF
function get_film_meta( $key, $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    return get_field( $key, $post_id );
}

// ─── RELEASE DATE PARSING & SORTING ───

// Parse release date string → { type: year|monthyear|date|none, sort: int }
function bluebells_parse_release( $val ) {
    if ( !$val ) return ['type'=>'none', 'sort'=>0];
    $val = trim((string)$val);
    // Year only "2026"
    if ( preg_match('#^(\d{4})$#', $val, $m) )
        return ['type'=>'year', 'sort'=>(int)$m[1]];
    // ACF date_picker legacy "20260424"
    if ( preg_match('#^(\d{4})(\d{2})(\d{2})$#', $val, $m) )
        return ['type'=>'date', 'sort'=>(int)($m[1].$m[2].$m[3])];
    // "24/04/2026" or "4/4/2026" (full date)
    if ( preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $val, $m) )
        return ['type'=>'date', 'sort'=>(int)sprintf('%04d%02d%02d', $m[3], $m[2], $m[1])];
    // "08/2026" or "8/2026" (month/year)
    if ( preg_match('#^(\d{1,2})/(\d{4})$#', $val, $m) )
        return ['type'=>'monthyear', 'sort'=>(int)sprintf('%04d%02d', $m[2], $m[1])];
    return ['type'=>'none', 'sort'=>0];
}

// Format release for display (legacy "20260424" → "24/04/2026", others as-is)
function get_film_release_display( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $val = trim((string) get_post_meta($post_id, 'film_release_date', true));
    if ( !$val ) return '';
    if ( preg_match('#^(\d{4})(\d{2})(\d{2})$#', $val, $m) )
        return $m[3] . '/' . $m[2] . '/' . $m[1];
    return $val;
}

// Comparator: year-only → month-year → full date → no date
// Within each group: year & monthyear sort ASC (soonest first), full date DESC, none by post date DESC
function bluebells_compare_films( $a, $b ) {
    $pa = bluebells_parse_release( get_post_meta($a->ID, 'film_release_date', true) );
    $pb = bluebells_parse_release( get_post_meta($b->ID, 'film_release_date', true) );
    $priority = ['year'=>0, 'monthyear'=>1, 'date'=>2, 'none'=>3];
    if ( $pa['type'] !== $pb['type'] )
        return $priority[$pa['type']] <=> $priority[$pb['type']];
    if ( $pa['type'] === 'year' )      return $pa['sort'] - $pb['sort'];   // asc
    if ( $pa['type'] === 'monthyear' ) return $pa['sort'] - $pb['sort'];   // asc
    if ( $pa['type'] === 'date' )      return $pb['sort'] - $pa['sort'];   // desc
    return strtotime($b->post_date) - strtotime($a->post_date);            // newest added first
}

// Fetch YouTube video title via oembed (cached 7 days)
function bluebells_get_youtube_title( $video_id ) {
    if ( !$video_id ) return '';
    $cache_key = 'yt_title_' . $video_id;
    $cached = get_transient($cache_key);
    if ( $cached !== false ) return $cached;

    $endpoint = 'https://www.youtube.com/oembed?url=' . urlencode('https://www.youtube.com/watch?v=' . $video_id) . '&format=json';
    $response = wp_remote_get($endpoint, ['timeout' => 5]);

    if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 ) {
        set_transient($cache_key, '', HOUR_IN_SECONDS);  // negative cache 1h
        return '';
    }

    $data = json_decode( wp_remote_retrieve_body($response), true );
    $title = isset($data['title']) ? $data['title'] : '';
    set_transient($cache_key, $title, WEEK_IN_SECONDS);
    return $title;
}

// Parse film_videos textarea into [['id'=>'abc','title'=>'...'], ...]
// Each line: "URL" or "URL|Title". Optionally include $trailer first.
function bluebells_parse_videos( $trailer = '', $extra_text = '' ) {
    $items = [];
    $seen_urls = [];

    if ( $trailer ) {
        $items[] = ['url' => $trailer, 'title' => 'Official Trailer'];
        $seen_urls[$trailer] = true;
    }
    if ( $extra_text ) {
        foreach ( array_filter(array_map('trim', explode("\n", $extra_text))) as $line ) {
            $parts = array_map('trim', explode('|', $line, 2));
            $url   = $parts[0] ?? '';
            $title = $parts[1] ?? '';
            if ( !$url || isset($seen_urls[$url]) ) continue;
            $items[] = ['url' => $url, 'title' => $title];
            $seen_urls[$url] = true;
        }
    }

    // Extract YouTube ID + ensure each has title
    $videos = [];
    foreach ( $items as $item ) {
        if ( preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $item['url'], $m) ) {
            $videos[] = [
                'id'    => $m[1],
                'title' => $item['title'] ?: bluebells_get_youtube_title($m[1]),
            ];
        }
    }
    return $videos;
}

// Get films sorted by the smart rule above
function bluebells_get_films_sorted( $extra_args = [] ) {
    $args = array_merge([
        'post_type'      => 'film',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ], $extra_args);
    $films = get_posts($args);
    usort($films, 'bluebells_compare_films');
    return $films;
}

// Helper: film status label
function get_film_status_label( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $terms = get_the_terms( $post_id, 'film_status' );
    return ($terms && !is_wp_error($terms)) ? $terms[0]->name : '';
}

// Helper: film genre string
function get_film_genre_string( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $terms = get_the_terms( $post_id, 'film_genre' );
    if ( !$terms || is_wp_error($terms) ) return '';
    return implode(' · ', array_map(fn($t)=>$t->name, $terms));
}

// Helper: status CSS class
function get_film_status_class( $post_id = null ) {
    $s = strtolower( get_film_status_label($post_id) );
    if ( strpos($s,'showing')!==false )     return 'now-showing';
    if ( strpos($s,'coming')!==false )      return 'coming-soon';
    if ( strpos($s,'development')!==false ) return 'in-development';
    return 'released';
}

// ─── MEDIA CATEGORIZATION BY FILM ───

// Register media_film taxonomy on attachments
function bluebells_register_media_film_tax() {
    register_taxonomy( 'media_film', 'attachment', [
        'labels' => [
            'name'          => 'Phim',
            'singular_name' => 'Phim',
            'search_items'  => 'Tìm phim',
            'all_items'     => 'Tất cả phim',
            'edit_item'     => 'Sửa phim',
            'update_item'   => 'Cập nhật',
            'add_new_item'  => 'Thêm phim',
            'menu_name'     => 'Phim (Media)',
        ],
        'hierarchical'    => true,
        'show_in_rest'    => true,
        'show_admin_column'=> true,
        'show_ui'         => true,
        'query_var'       => true,
        'rewrite'         => false,
    ]);
}
add_action( 'init', 'bluebells_register_media_film_tax' );

// Auto-create a media_film term whenever a film post is saved
function bluebells_sync_film_media_term( $post_id, $post ) {
    if ( $post->post_type !== 'film' || wp_is_post_revision($post_id) ) return;
    if ( $post->post_status === 'auto-draft' ) return;

    $slug = 'film-' . $post_id;
    $existing = get_term_by( 'slug', $slug, 'media_film' );

    if ( ! $existing ) {
        wp_insert_term( $post->post_title, 'media_film', ['slug' => $slug] );
    } else {
        wp_update_term( $existing->term_id, 'media_film', ['name' => $post->post_title] );
    }
}
add_action( 'save_post', 'bluebells_sync_film_media_term', 20, 2 );

// Auto-tag uploaded images with the film they were uploaded from
function bluebells_auto_tag_upload( $attachment_id ) {
    $parent_id = 0;

    // Check various sources for the parent film ID
    if ( !empty($_REQUEST['post_id']) ) {
        $parent_id = (int) $_REQUEST['post_id'];
    } elseif ( !empty($_REQUEST['post']) ) {
        $parent_id = (int) $_REQUEST['post'];
    }

    if ( ! $parent_id ) return;

    $parent = get_post( $parent_id );
    if ( ! $parent || $parent->post_type !== 'film' ) return;

    $slug = 'film-' . $parent_id;
    $term = get_term_by( 'slug', $slug, 'media_film' );

    if ( ! $term ) {
        $result = wp_insert_term( $parent->post_title, 'media_film', ['slug' => $slug] );
        if ( ! is_wp_error($result) ) {
            wp_set_object_terms( $attachment_id, [(int)$result['term_id']], 'media_film', true );
        }
    } else {
        wp_set_object_terms( $attachment_id, [(int)$term->term_id], 'media_film', true );
    }
}
add_action( 'add_attachment', 'bluebells_auto_tag_upload' );

// Add film dropdown filter in Media Library (list view)
function bluebells_media_filter_dropdown( $post_type ) {
    if ( $post_type !== 'attachment' ) return;

    $terms = get_terms(['taxonomy'=>'media_film','hide_empty'=>false]);
    if ( empty($terms) || is_wp_error($terms) ) return;

    $selected = isset($_GET['media_film']) ? $_GET['media_film'] : '';
    echo '<select name="media_film">';
    echo '<option value="">— Tất cả phim —</option>';
    foreach ( $terms as $t ) {
        printf( '<option value="%s"%s>%s (%d)</option>',
            esc_attr($t->slug),
            selected($selected, $t->slug, false),
            esc_html($t->name),
            $t->count
        );
    }
    echo '</select>';
}
add_action( 'restrict_manage_posts', 'bluebells_media_filter_dropdown' );

// Add film filter in Media Library grid view (modal)
function bluebells_media_modal_filter() {
    $terms = get_terms(['taxonomy'=>'media_film','hide_empty'=>false]);
    if ( empty($terms) || is_wp_error($terms) ) return;
    ?>
    <script>
    (function(){
      if ( typeof wp === 'undefined' || !wp.media ) return;

      var origBrowser = wp.media.view.AttachmentsBrowser;
      wp.media.view.AttachmentsBrowser = origBrowser.extend({
        createToolbar: function() {
          origBrowser.prototype.createToolbar.apply( this, arguments );

          var FilmFilter = wp.media.view.AttachmentFilters.extend({
            createFilters: function() {
              this.filters = {
                all: { text: '— Tất cả phim —', props: { media_film: '' }, priority: 10 }
                <?php foreach ( $terms as $t ): ?>,
                '<?php echo esc_js($t->slug); ?>': {
                  text: '<?php echo esc_js($t->name); ?>',
                  props: { media_film: '<?php echo esc_js($t->slug); ?>' },
                  priority: 20
                }
                <?php endforeach; ?>
              };
            }
          });

          this.toolbar.set( 'mediaFilmFilter', new FilmFilter({
            controller: this.controller,
            model:      this.collection.props,
            priority:   -75
          }).render() );
        }
      });
    })();
    </script>
    <?php
}
add_action( 'print_media_templates', 'bluebells_media_modal_filter' );

// Handle the media_film filter in AJAX media queries (grid view)
function bluebells_ajax_media_filter( $query ) {
    if ( ! empty($query['media_film']) ) {
        $query['tax_query'] = [[
            'taxonomy' => 'media_film',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($query['media_film']),
        ]];
    }
    return $query;
}
add_filter( 'ajax_query_attachments_args', 'bluebells_ajax_media_filter' );

// ─── FILM GALLERY (standalone metabox) ───

function bluebells_gallery_metabox() {
    add_meta_box( 'film_gallery_box', 'Film Gallery', 'bluebells_gallery_render', 'film', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'bluebells_gallery_metabox' );

function bluebells_gallery_render( $post ) {
    wp_nonce_field( 'film_gallery_save', 'film_gallery_nonce' );
    $ids = get_post_meta( $post->ID, '_film_gallery_ids', true );
    $id_array = $ids ? array_filter( array_map('intval', explode(',', $ids)) ) : [];
    ?>
    <div id="film-gallery-wrap">
        <div id="film-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
            <?php foreach ( $id_array as $img_id ):
                $thumb = wp_get_attachment_image_url( $img_id, 'thumbnail' );
                if ( !$thumb ) continue;
            ?>
            <div class="film-gallery-thumb" data-id="<?php echo $img_id; ?>" style="position:relative;width:100px;height:100px;border:1px solid #ddd;border-radius:4px;overflow:hidden;cursor:move;">
                <img src="<?php echo esc_url($thumb); ?>" style="width:100%;height:100%;object-fit:cover;">
                <button type="button" class="film-gallery-remove" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.7);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:14px;line-height:18px;text-align:center;">×</button>
            </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="film_gallery_ids" id="film-gallery-ids" value="<?php echo esc_attr($ids); ?>">
        <button type="button" id="film-gallery-add" class="button button-primary">Chọn ảnh cho Gallery</button>
        <button type="button" id="film-gallery-clear" class="button" style="margin-left:8px;">Xóa tất cả</button>
        <p class="description" style="margin-top:8px;">Bấm chọn ảnh → chọn nhiều ảnh (giữ Shift/Ctrl). Kéo thả để sắp xếp thứ tự.</p>
    </div>
    <script>
    jQuery(function($) {
        var $preview = $('#film-gallery-preview');
        var $input   = $('#film-gallery-ids');
        function updateInput() {
            var ids = [];
            $preview.find('.film-gallery-thumb').each(function() { ids.push($(this).data('id')); });
            $input.val(ids.join(','));
        }
        function addThumb(id, url) {
            if ($preview.find('[data-id="'+id+'"]').length) return;
            $preview.append(
                '<div class="film-gallery-thumb" data-id="'+id+'" style="position:relative;width:100px;height:100px;border:1px solid #ddd;border-radius:4px;overflow:hidden;cursor:move;">'+
                '<img src="'+url+'" style="width:100%;height:100%;object-fit:cover;">'+
                '<button type="button" class="film-gallery-remove" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.7);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:14px;line-height:18px;text-align:center;">×</button>'+
                '</div>'
            );
        }
        if ($.fn.sortable) { $preview.sortable({items:'.film-gallery-thumb',tolerance:'pointer',update:updateInput}); }
        $preview.on('click','.film-gallery-remove',function(e){ e.preventDefault(); $(this).closest('.film-gallery-thumb').remove(); updateInput(); });
        $('#film-gallery-clear').on('click',function(e){ e.preventDefault(); $preview.empty(); updateInput(); });
        $('#film-gallery-add').on('click',function(e){
            e.preventDefault();
            var frame = wp.media({
                title:'Chọn ảnh cho Gallery (giữ Shift/Ctrl để chọn nhiều)',
                button:{text:'Thêm vào Gallery'},
                multiple:'add', library:{type:'image'},
                states:[new wp.media.controller.Library({title:'Chọn ảnh cho Gallery',library:wp.media.query({type:'image'}),multiple:true,priority:20})]
            });
            frame.on('select',function(){
                frame.state().get('selection').each(function(att){
                    var url = (att.attributes.sizes && att.attributes.sizes.thumbnail) ? att.attributes.sizes.thumbnail.url : att.attributes.url;
                    addThumb(att.id, url);
                });
                updateInput();
            });
            frame.open();
        });
    });
    </script>
    <?php
}

function bluebells_gallery_save( $post_id ) {
    if ( ! isset($_POST['film_gallery_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['film_gallery_nonce'], 'film_gallery_save') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;

    $ids = isset($_POST['film_gallery_ids']) ? sanitize_text_field($_POST['film_gallery_ids']) : '';
    update_post_meta( $post_id, '_film_gallery_ids', $ids );
}
add_action( 'save_post_film', 'bluebells_gallery_save' );

// Enqueue WP media on film edit screens
function bluebells_admin_scripts( $hook ) {
    global $post;
    if ( ($hook === 'post.php' || $hook === 'post-new.php') && isset($post) && $post->post_type === 'film' ) {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
    }
}
add_action( 'admin_enqueue_scripts', 'bluebells_admin_scripts' );

// ─── DOCUMENT TITLE: <Page> | Bluebells Studios (home = just Bluebells Studios) ───
add_filter('document_title_parts', function( $parts ) {
    if ( is_front_page() ) {
        return [ 'title' => 'Bluebells Studios' ];
    }
    $parts['site'] = 'Bluebells Studios';
    unset($parts['tagline']);
    if ( is_post_type_archive('film') ) {
        $parts['title'] = 'Movies';
    }
    return $parts;
});
add_filter('document_title_separator', fn() => '|');

// Remove Gutenberg block styles
add_action('wp_print_styles', function(){
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
}, 100);

// ─── ADMIN BAR: Replace WP logo with BBS custom logo ───
add_action('admin_bar_menu', function( $wp_admin_bar ) {
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('wp-logo-external');

    $logo_url = '';
    if ( has_custom_logo() ) {
        $logo_id = get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($logo_id, 'thumbnail');
    }

    $wp_admin_bar->add_node([
        'id'    => 'bbs-logo',
        'title' => $logo_url
            ? '<img src="' . esc_url($logo_url) . '" alt="BBS" style="height:20px;width:auto;vertical-align:middle;padding:6px 0;">'
            : 'BBS',
        'href'  => home_url('/'),
        'meta'  => ['title' => 'Bluebells Studios'],
    ]);
}, 11);

// Make admin-bar logo cell wider so the image isn't cropped
add_action('admin_head', 'bluebells_adminbar_css');
add_action('wp_head',    'bluebells_adminbar_css');
function bluebells_adminbar_css() {
    if ( ! is_admin_bar_showing() ) return;
    echo '<style>
        #wpadminbar #wp-admin-bar-bbs-logo > .ab-item { padding: 0 12px; height: 32px; display: flex; align-items: center; }
        #wpadminbar #wp-admin-bar-bbs-logo > .ab-item img { display: block; }
    </style>';
}

// Use the custom logo as fallback site icon (favicon) if none set
add_filter('get_site_icon_url', function( $url, $size, $blog_id ) {
    if ( $url ) return $url;
    if ( has_custom_logo() ) {
        $logo_id = get_theme_mod('custom_logo');
        $fav = wp_get_attachment_image_url($logo_id, [$size, $size]);
        if ( $fav ) return $fav;
    }
    return $url;
}, 10, 3);
