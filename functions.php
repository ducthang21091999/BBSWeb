<?php
/**
 * Bluebells Studios — functions.php
 */

// SEO meta + Open Graph + JSON-LD (cookie-based i18n compatible)
require_once get_template_directory() . '/inc/seo-meta.php';

function bluebells_assets() {
    $theme_dir = get_stylesheet_directory();
    $css_ver = file_exists($theme_dir . '/style.css') ? filemtime($theme_dir . '/style.css') : '1.0.0';
    $js_ver  = file_exists($theme_dir . '/js/main.js') ? filemtime($theme_dir . '/js/main.js') : '1.0.0';

    wp_enqueue_style( 'bluebells-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&family=Inter:wght@300;400;500;600;700&display=swap',
        [], null );
    wp_enqueue_style( 'bluebells-style', get_stylesheet_uri(), ['bluebells-fonts'], $css_ver );
    wp_enqueue_script( 'bluebells-main', get_template_directory_uri() . '/js/main.js', [], $js_ver, true );

    // Expose AJAX URL + nonce to JS for contact form
    wp_localize_script( 'bluebells-main', 'bluebellsAjax', [
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bluebells_contact_form'),
    ]);
}
add_action( 'wp_enqueue_scripts', 'bluebells_assets' );

// ─── HOME FILMS CATALOG (chọn phim hiển thị banner trang chủ + sort order) ───
function bluebells_home_films_menu() {
    add_submenu_page(
        'edit.php?post_type=film',
        'Phim Trang Chủ',
        'Phim Trang Chủ',
        'manage_options',
        'bluebells-home-films',
        'bluebells_home_films_page'
    );
}
add_action( 'admin_menu', 'bluebells_home_films_menu' );

function bluebells_home_films_page() {
    // Handle POST save
    if ( isset($_POST['bluebells_home_films_nonce']) && wp_verify_nonce($_POST['bluebells_home_films_nonce'], 'bluebells_home_films') ) {
        $selected = $_POST['featured'] ?? [];
        $orders   = $_POST['menu_order'] ?? [];

        // Get all films to ensure we update unticked ones too
        $all_films = get_posts(['post_type'=>'film','posts_per_page'=>-1,'post_status'=>'any','fields'=>'ids']);
        foreach ( $all_films as $fid ) {
            $is_featured = isset($selected[$fid]) ? '1' : '0';
            update_post_meta($fid, 'film_featured_banner', $is_featured);
            $order = isset($orders[$fid]) ? (int) $orders[$fid] : 0;
            wp_update_post(['ID' => $fid, 'menu_order' => $order]);
        }
        echo '<div class="notice notice-success is-dismissible"><p>✓ Đã lưu cài đặt.</p></div>';
    }

    // Fetch all films, then sort: featured first (by menu_order ASC), unfeatured after (by title ASC)
    $films = get_posts([
        'post_type'      => 'film',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);
    usort($films, function($a, $b) {
        $fa = get_post_meta($a->ID, 'film_featured_banner', true) === '1' ? 1 : 0;
        $fb = get_post_meta($b->ID, 'film_featured_banner', true) === '1' ? 1 : 0;
        if ( $fa !== $fb ) return $fb - $fa;  // featured (1) before non-featured (0)
        if ( $fa === 1 ) return $a->menu_order - $b->menu_order;  // featured: by order ASC
        return strcasecmp($a->post_title, $b->post_title);  // non-featured: alphabetical
    });
    ?>
    <div class="wrap">
        <h1>Phim hiển thị trên trang chủ</h1>
        <p>Tick chọn phim → hiện trong <strong>banner slider</strong> trên trang chủ.
        Số thứ tự nhỏ hơn → hiện trước. Đồng bộ 2 chiều với ô "Hiển thị trên Banner trang chủ" trong từng phim.</p>

        <form method="post" action="">
            <?php wp_nonce_field('bluebells_home_films', 'bluebells_home_films_nonce'); ?>

            <table class="wp-list-table widefat striped" style="margin-top:16px;">
                <thead>
                    <tr>
                        <th style="width:80px;text-align:center;">Hiển thị</th>
                        <th style="width:100px;">Thứ tự</th>
                        <th>Tên phim</th>
                        <th style="width:160px;">Trạng thái</th>
                        <th style="width:120px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty($films) ): ?>
                        <tr><td colspan="5">Chưa có phim nào. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=film')); ?>">Thêm phim mới</a></td></tr>
                    <?php else: foreach ( $films as $f ):
                        $is_featured = get_post_meta($f->ID, 'film_featured_banner', true) === '1';
                        $status_terms = get_the_terms($f->ID, 'film_status');
                        $status = ($status_terms && !is_wp_error($status_terms)) ? $status_terms[0]->name : '—';
                        $en_title = get_post_meta($f->ID, 'film_en_title', true);
                    ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" name="featured[<?php echo $f->ID; ?>]" value="1" <?php checked($is_featured); ?>>
                            </td>
                            <td>
                                <input type="number" name="menu_order[<?php echo $f->ID; ?>]" value="<?php echo esc_attr($f->menu_order); ?>" min="0" step="1" style="width:80px;">
                            </td>
                            <td>
                                <strong><?php echo esc_html($f->post_title); ?></strong>
                                <?php if ( $en_title ): ?>
                                    <br><span style="color:#777;font-size:12px;"><?php echo esc_html($en_title); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($status); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link($f->ID)); ?>" class="button button-small">Edit</a>
                                <a href="<?php echo esc_url(get_permalink($f->ID)); ?>" class="button button-small" target="_blank">View ↗</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php submit_button('Lưu thay đổi'); ?>
        </form>
    </div>
    <?php
}

// ─── SITE CONTENT SETTINGS PAGE (Slogan + About) ───
function bluebells_site_content_menu() {
    add_options_page(
        'Site Content',
        'Site Content',
        'manage_options',
        'bluebells-site-content',
        'bluebells_site_content_page'
    );
}
add_action( 'admin_menu', 'bluebells_site_content_menu' );

function bluebells_register_site_content_settings() {
    foreach ([
        'bluebells_slogan_en','bluebells_slogan_vi',
        'bluebells_about_body_en','bluebells_about_body_vi',
        'bluebells_about_image',
    ] as $opt) {
        $cb = strpos($opt, 'body') !== false ? 'sanitize_textarea_field' : 'sanitize_text_field';
        register_setting('bluebells_site_content', $opt, ['sanitize_callback' => $cb]);
    }
}
add_action( 'admin_init', 'bluebells_register_site_content_settings' );

function bluebells_site_content_page() {
    wp_enqueue_media();
    ?>
    <div class="wrap">
        <h1>Bluebells — Site Content</h1>
        <p>Nội dung hiển thị trên website. Để trống → dùng giá trị mặc định trong code.</p>
        <form method="post" action="options.php">
            <?php settings_fields('bluebells_site_content'); ?>

            <h2 style="margin-top:24px;">Slogan (hiển thị ở Contact CTA cuối trang)</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="bluebells_slogan_vi">Slogan (Tiếng Việt)</label></th>
                    <td><input type="text" name="bluebells_slogan_vi" id="bluebells_slogan_vi"
                        value="<?php echo esc_attr(get_option('bluebells_slogan_vi')); ?>"
                        class="large-text" placeholder="vd: Cùng tạo nên những bộ phim ý nghĩa.">
                    </td>
                </tr>
                <tr>
                    <th><label for="bluebells_slogan_en">Slogan (English)</label></th>
                    <td><input type="text" name="bluebells_slogan_en" id="bluebells_slogan_en"
                        value="<?php echo esc_attr(get_option('bluebells_slogan_en')); ?>"
                        class="large-text" placeholder="e.g. Let's craft beautiful films together.">
                    </td>
                </tr>
            </table>

            <h2 style="margin-top:32px;">About / Our Story (section ở trang chủ)</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="bluebells_about_body_vi">Nội dung (Tiếng Việt)</label></th>
                    <td><textarea name="bluebells_about_body_vi" id="bluebells_about_body_vi"
                        rows="6" class="large-text" placeholder="Mỗi đoạn cách nhau bằng 1 dòng trống."><?php
                            echo esc_textarea(get_option('bluebells_about_body_vi'));
                        ?></textarea>
                        <p class="description">Mỗi dòng trống = 1 paragraph mới.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="bluebells_about_body_en">Nội dung (English)</label></th>
                    <td><textarea name="bluebells_about_body_en" id="bluebells_about_body_en"
                        rows="6" class="large-text" placeholder="Separate paragraphs with blank lines."><?php
                            echo esc_textarea(get_option('bluebells_about_body_en'));
                        ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label>Hình minh hoạ (tỷ lệ 4:3)</label></th>
                    <td>
                        <?php
                          $img_id  = (int) get_option('bluebells_about_image');
                          $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'medium') : '';
                        ?>
                        <input type="hidden" name="bluebells_about_image" id="bluebells_about_image" value="<?php echo esc_attr($img_id); ?>">
                        <div id="bluebells_about_image_preview" style="margin-bottom:8px;">
                            <?php if ($img_url): ?>
                                <img src="<?php echo esc_url($img_url); ?>" style="max-width:280px;aspect-ratio:4/3;object-fit:cover;border:1px solid #ddd;">
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="bluebells_about_image_select">
                            <?php echo $img_id ? 'Đổi ảnh' : 'Chọn ảnh'; ?>
                        </button>
                        <a href="<?php echo $img_id ? esc_url(admin_url('post.php?post='.$img_id.'&action=edit')) : '#'; ?>"
                           target="_blank" class="button" id="bluebells_about_image_edit"
                           style="<?php echo $img_id ? '' : 'display:none;'; ?>">Sửa ảnh ↗</a>
                        <button type="button" class="button" id="bluebells_about_image_remove" style="<?php echo $img_id ? '' : 'display:none;'; ?>">Xóa ảnh</button>
                        <p class="description">Khuyến nghị 1200×900px (4:3). Bấm "Sửa ảnh" để crop/xoay/resize trong WordPress.</p>
                    </td>
                </tr>
            </table>

            <script>
            jQuery(function($) {
                var frame;
                var adminUrl = '<?php echo esc_js(admin_url("post.php?action=edit&post=")); ?>';
                $('#bluebells_about_image_select').on('click', function(e){
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({ title:'Chọn ảnh', button:{text:'Chọn'}, multiple:false, library:{type:'image'} });
                    frame.on('select', function(){
                        var att = frame.state().get('selection').first().toJSON();
                        $('#bluebells_about_image').val(att.id);
                        $('#bluebells_about_image_preview').html('<img src="'+att.url+'" style="max-width:280px;aspect-ratio:4/3;object-fit:cover;border:1px solid #ddd;">');
                        $('#bluebells_about_image_select').text('Đổi ảnh');
                        $('#bluebells_about_image_edit').attr('href', adminUrl + att.id).show();
                        $('#bluebells_about_image_remove').show();
                    });
                    frame.open();
                });
                $('#bluebells_about_image_remove').on('click', function(e){
                    e.preventDefault();
                    $('#bluebells_about_image').val('');
                    $('#bluebells_about_image_preview').html('');
                    $('#bluebells_about_image_select').text('Chọn ảnh');
                    $('#bluebells_about_image_edit').hide();
                    $(this).hide();
                });
            });
            </script>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Helper: get content by current lang with fallback to default
function bbs_content( $key, $default_vi = '', $default_en = '' ) {
    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $opt  = "bluebells_{$key}_" . $lang;
    $val  = get_option($opt);
    if ( $val ) return $val;
    // Fallback to other lang if current is empty
    $alt_opt = "bluebells_{$key}_" . ($lang === 'vi' ? 'en' : 'vi');
    $val = get_option($alt_opt);
    if ( $val ) return $val;
    return $lang === 'vi' ? $default_vi : $default_en;
}

// ─── CONTACT SETTINGS PAGE ───
function bluebells_contact_settings_menu() {
    add_options_page(
        'Contact Info',
        'Contact Info',
        'manage_options',
        'bluebells-contact',
        'bluebells_contact_settings_page'
    );
}
add_action( 'admin_menu', 'bluebells_contact_settings_menu' );

function bluebells_register_contact_settings() {
    register_setting('bluebells_contact', 'bluebells_contact_email', ['sanitize_callback' => 'sanitize_email']);
    register_setting('bluebells_contact', 'bluebells_contact_phone', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('bluebells_contact', 'bluebells_contact_address', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('bluebells_contact', 'bluebells_contact_form_to', ['sanitize_callback' => 'sanitize_email']);
    register_setting('bluebells_contact', 'bluebells_social_facebook', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('bluebells_contact', 'bluebells_social_tiktok',   ['sanitize_callback' => 'esc_url_raw']);
    register_setting('bluebells_contact', 'bluebells_social_youtube',  ['sanitize_callback' => 'esc_url_raw']);
}
add_action( 'admin_init', 'bluebells_register_contact_settings' );

function bluebells_contact_settings_page() {
    ?>
    <div class="wrap">
        <h1>Bluebells — Contact Info</h1>
        <p>Thông tin liên hệ hiển thị ở section "Work With Us" trên trang chủ + email nhận form contact.</p>
        <form method="post" action="options.php">
            <?php settings_fields('bluebells_contact'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bluebells_contact_email">Email hiển thị</label></th>
                    <td><input type="email" name="bluebells_contact_email" id="bluebells_contact_email"
                        value="<?php echo esc_attr(get_option('bluebells_contact_email')); ?>"
                        class="regular-text" placeholder="contact@bluebells.vn">
                        <p class="description">Email công khai hiển thị cho khách thấy.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bluebells_contact_phone">Số điện thoại</label></th>
                    <td><input type="text" name="bluebells_contact_phone" id="bluebells_contact_phone"
                        value="<?php echo esc_attr(get_option('bluebells_contact_phone')); ?>"
                        class="regular-text" placeholder="+84 964 311 776">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bluebells_contact_address">Địa chỉ</label></th>
                    <td><input type="text" name="bluebells_contact_address" id="bluebells_contact_address"
                        value="<?php echo esc_attr(get_option('bluebells_contact_address')); ?>"
                        class="regular-text" placeholder="39 Đ. số 9, Tân Hưng, Quận 7, TP. HCM" style="width:480px;max-width:100%;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bluebells_contact_form_to">Email nhận form (đích)</label></th>
                    <td><input type="email" name="bluebells_contact_form_to" id="bluebells_contact_form_to"
                        value="<?php echo esc_attr(get_option('bluebells_contact_form_to')); ?>"
                        class="regular-text" placeholder="inbox@bluebells.vn">
                        <p class="description">Email từ form Contact khách gửi sẽ được forward về đây. Nếu trống → dùng email Admin của site.</p>
                    </td>
                </tr>
                <tr><th colspan="2"><h2 style="margin-top:24px;">Social Links</h2></th></tr>
                <tr>
                    <th scope="row"><label for="bluebells_social_facebook">Facebook URL</label></th>
                    <td><input type="url" name="bluebells_social_facebook" id="bluebells_social_facebook"
                        value="<?php echo esc_attr(get_option('bluebells_social_facebook')); ?>"
                        class="regular-text" placeholder="https://facebook.com/bluebells...">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bluebells_social_tiktok">TikTok URL</label></th>
                    <td><input type="url" name="bluebells_social_tiktok" id="bluebells_social_tiktok"
                        value="<?php echo esc_attr(get_option('bluebells_social_tiktok')); ?>"
                        class="regular-text" placeholder="https://tiktok.com/@bluebells...">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bluebells_social_youtube">YouTube URL</label></th>
                    <td><input type="url" name="bluebells_social_youtube" id="bluebells_social_youtube"
                        value="<?php echo esc_attr(get_option('bluebells_social_youtube')); ?>"
                        class="regular-text" placeholder="https://youtube.com/@bluebells...">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// ─── CONTACT FORM AJAX HANDLER ───
function bluebells_handle_contact_form() {
    if ( ! check_ajax_referer('bluebells_contact_form', 'nonce', false) ) {
        wp_send_json_error(['message' => 'Phiên đã hết hạn. Vui lòng tải lại trang.']);
    }

    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if ( ! $name || ! is_email($email) || ! $message ) {
        wp_send_json_error(['message' => 'Vui lòng điền đủ thông tin hợp lệ.']);
    }

    // Honeypot (anti-bot) — hidden field "website" should be empty
    if ( ! empty($_POST['website']) ) {
        wp_send_json_success(['message' => 'Cảm ơn bạn đã liên hệ!']);  // silently accept
    }

    $to = get_option('bluebells_contact_form_to') ?: get_option('admin_email');
    $subject = sprintf('[Bluebells Contact] %s', $name);
    $body  = "Liên hệ mới từ website:\n\n";
    $body .= "Tên: {$name}\n";
    $body .= "Email: {$email}\n\n";
    $body .= "Nội dung:\n{$message}\n";

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $name, $email),
    ];

    $sent = wp_mail($to, $subject, $body, $headers);

    if ( $sent ) {
        wp_send_json_success(['message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.']);
    } else {
        wp_send_json_error(['message' => 'Hệ thống email tạm thời gặp lỗi. Vui lòng thử lại sau hoặc liên hệ trực tiếp qua số điện thoại.']);
    }
}
add_action('wp_ajax_bluebells_contact', 'bluebells_handle_contact_form');
add_action('wp_ajax_nopriv_bluebells_contact', 'bluebells_handle_contact_form');

// ─── I18N HELPERS ───
// Allowed UI language codes
function bbs_allowed_langs() { return ['vi', 'en']; }

// Detect current language. Priority: ?lang= GET > cookie > Polylang > default 'vi'.
function bbs_current_lang() {
    static $lang = null;
    if ( $lang !== null ) return $lang;

    // 1. Explicit URL param (user just clicked switcher)
    if ( isset($_GET['lang']) && in_array($_GET['lang'], bbs_allowed_langs(), true) ) {
        return $lang = $_GET['lang'];
    }

    // 2. Cookie (persistent user choice)
    if ( isset($_COOKIE['bbs_lang']) && in_array($_COOKIE['bbs_lang'], bbs_allowed_langs(), true) ) {
        return $lang = $_COOKIE['bbs_lang'];
    }

    // 3. Polylang (if configured)
    if ( function_exists('pll_current_language') ) {
        $code = pll_current_language('slug');
        if ( $code && in_array($code, bbs_allowed_langs(), true) ) return $lang = $code;
    }

    return $lang = 'vi';
}

// Persist ?lang= param as cookie so it sticks across navigation
add_action('init', function() {
    if ( isset($_GET['lang']) && in_array($_GET['lang'], bbs_allowed_langs(), true) ) {
        // Use empty domain so cookie works for current host (localhost, .local, etc.)
        setcookie('bbs_lang', $_GET['lang'], time() + 30 * DAY_IN_SECONDS, '/', '', false, false);
        $_COOKIE['bbs_lang'] = $_GET['lang'];  // make immediately readable in this request
    }
}, 1);

// Build URL for switching to a target language (preserves current path + query)
function bbs_lang_switch_url( $target ) {
    if ( ! in_array($target, bbs_allowed_langs(), true) ) return '#';
    $request = $_SERVER['REQUEST_URI'] ?? '/';
    // Strip existing lang= param to avoid duplicates
    $request = remove_query_arg('lang', $request);
    return add_query_arg('lang', $target, $request);
}

// Translate UI string using languages/translations.php array. Falls back to original if missing or empty.
function bbs_t( $string ) {
    static $cache = null;
    if ( $cache === null ) {
        $file = get_template_directory() . '/languages/translations.php';
        $cache = file_exists($file) ? (include $file) : [];
    }
    $lang = bbs_current_lang();
    $translated = $cache[$lang][$string] ?? '';
    return $translated !== '' ? $translated : $string;
}

// Echo escaped translated UI string.
function bbs_e( $string ) { echo esc_html( bbs_t($string) ); }

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
    add_image_size( 'film-banner-preview', 320, 180, true );   // 16:9 for ACF admin preview
    add_image_size( 'film-poster-preview', 120, 180, true );   // 2:3 for ACF admin preview
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
        'labels'       => [
            'name'          => 'Movies',
            'singular_name' => 'Movie',
            'add_new_item'  => 'Add New Movie',
            'edit_item'     => 'Edit Movie',
            'view_item'     => 'View Movie',
            'not_found'     => 'No movies found',
            'menu_name'     => 'Movies',
        ],
        'public'       => true,
        'has_archive'  => 'movies',
        'rewrite'      => ['slug'=>'movies'],
        'supports'     => ['title','thumbnail'],
        'menu_icon'    => 'dashicons-video-alt2',
        'show_in_rest' => false,
    ]);
}
add_action( 'init', 'bluebells_register_films' );

// 301 redirect legacy /films/* URLs to /movies/* for backward compat
add_action('template_redirect', function() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ( preg_match('#^/films(/|$)#', $uri) ) {
        $new = preg_replace('#^/films#', '/movies', $uri);
        wp_redirect( home_url($new), 301 );
        exit;
    }
});

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

// ─── MOVIE AGE RATING (Vietnam classification system) ───
function bluebells_register_film_age_rating() {
    register_taxonomy('film_age_rating', 'film', [
        'labels' => [
            'name'          => 'Phân Loại Độ Tuổi',
            'singular_name' => 'Phân Loại',
            'menu_name'     => 'Phân loại tuổi',
            'edit_item'     => 'Sửa phân loại',
            'add_new_item'  => 'Thêm phân loại',
        ],
        'hierarchical' => false,
        'show_in_rest' => false,
        'show_ui'      => true,
        'show_in_menu' => 'edit.php?post_type=film',
        'public'       => false,
        'rewrite'      => false,
        'meta_box_cb'  => false,  // hide WP meta box — admin uses ACF radio in film edit
    ]);
}
add_action( 'init', 'bluebells_register_film_age_rating' );

// Auto-create the 6 Vietnam age rating terms on first load
function bluebells_seed_age_ratings() {
    $ratings = [
        'P'   => ['name'=>'P',   'desc'=>'Phổ biến — Phù hợp mọi lứa tuổi.'],
        'K'   => ['name'=>'K',   'desc'=>'Khán giả dưới 13 tuổi xem cùng người lớn.'],
        'T13' => ['name'=>'T13', 'desc'=>'Phim dành cho khán giả từ 13 tuổi trở lên.'],
        'T16' => ['name'=>'T16', 'desc'=>'Phim dành cho khán giả từ 16 tuổi trở lên.'],
        'T18' => ['name'=>'T18', 'desc'=>'Phim dành cho khán giả từ 18 tuổi trở lên.'],
        'C'   => ['name'=>'C',   'desc'=>'Phim không được phổ biến.'],
    ];
    foreach ( $ratings as $slug => $info ) {
        $slug_lc = strtolower($slug);
        if ( ! term_exists($slug_lc, 'film_age_rating') ) {
            wp_insert_term($info['name'], 'film_age_rating', [
                'slug'        => $slug_lc,
                'description' => $info['desc'],
            ]);
        }
    }
}
add_action( 'init', 'bluebells_seed_age_ratings', 20 );

// ACF: bilingual name field for film_genre terms (term name = VN primary, ACF holds EN)
function bluebells_register_genre_term_fields() {
    if ( ! function_exists('acf_add_local_field_group') ) return;
    acf_add_local_field_group([
        'key'      => 'group_film_genre_term',
        'title'    => 'Bản dịch',
        'location' => [[['param'=>'taxonomy','operator'=>'==','value'=>'film_genre']]],
        'fields'   => [
            ['key'=>'field_genre_name_en','label'=>'Tên tiếng Anh','name'=>'genre_name_en','type'=>'text',
             'instructions'=>'Tên thể loại bằng tiếng Anh. Vd: term name "Kinh Dị" → field "Horror".',
             'placeholder'=>'vd: Horror'],
        ],
    ]);
}
add_action( 'acf/init', 'bluebells_register_genre_term_fields' );

// One-time migration: convert existing English genre terms to Vietnamese + store EN in ACF field
function bluebells_migrate_genres() {
    if ( get_option('bluebells_genres_migrated') ) return;
    if ( ! function_exists('update_field') ) return;

    $map = [
        'horror'       => ['vi' => 'Kinh Dị',     'en' => 'Horror'],
        'spirituality' => ['vi' => 'Tâm Linh',    'en' => 'Spirituality'],
        'family'       => ['vi' => 'Gia Đình',    'en' => 'Family'],
        'romance'      => ['vi' => 'Lãng Mạn',    'en' => 'Romance'],
        'action'       => ['vi' => 'Hành Động',   'en' => 'Action'],
        'comedy'       => ['vi' => 'Hài',          'en' => 'Comedy'],
        'drama'        => ['vi' => 'Tâm Lý',       'en' => 'Drama'],
        'thriller'     => ['vi' => 'Giật Gân',     'en' => 'Thriller'],
        'adventure'    => ['vi' => 'Phiêu Lưu',    'en' => 'Adventure'],
        'sci-fi'       => ['vi' => 'Viễn Tưởng',   'en' => 'Sci-Fi'],
        'fantasy'      => ['vi' => 'Kỳ Ảo',        'en' => 'Fantasy'],
        'documentary'  => ['vi' => 'Tài Liệu',     'en' => 'Documentary'],
        'animation'    => ['vi' => 'Hoạt Hình',    'en' => 'Animation'],
        'mystery'      => ['vi' => 'Bí Ẩn',        'en' => 'Mystery'],
        'crime'        => ['vi' => 'Tội Phạm',     'en' => 'Crime'],
        'historical'   => ['vi' => 'Lịch Sử',      'en' => 'Historical'],
        'musical'      => ['vi' => 'Âm Nhạc',      'en' => 'Musical'],
        'war'          => ['vi' => 'Chiến Tranh',  'en' => 'War'],
    ];

    foreach ( $map as $slug => $names ) {
        $term = get_term_by('slug', $slug, 'film_genre');
        if ( $term && !is_wp_error($term) ) {
            wp_update_term($term->term_id, 'film_genre', ['name' => $names['vi']]);
            update_field('genre_name_en', $names['en'], 'film_genre_' . $term->term_id);
        }
    }
    update_option('bluebells_genres_migrated', 1);
}
add_action( 'acf/init', 'bluebells_migrate_genres', 99 );

// ACF: Image upload field for each age rating term (admin uploads logo)
function bluebells_register_age_rating_term_fields() {
    if ( ! function_exists('acf_add_local_field_group') ) return;
    acf_add_local_field_group([
        'key'      => 'group_film_age_rating_term',
        'title'    => 'Logo phân loại',
        'location' => [[['param'=>'taxonomy','operator'=>'==','value'=>'film_age_rating']]],
        'fields'   => [
            ['key'=>'field_rating_logo','label'=>'Logo','name'=>'rating_logo','type'=>'image',
             'instructions'=>'Upload logo phân loại (PNG trong suốt khuyến nghị, ~200×200px).',
             'return_format'=>'id','library'=>'all','preview_size'=>'thumbnail'],
        ],
    ]);
}
add_action( 'acf/init', 'bluebells_register_age_rating_term_fields' );

// Helper: get age rating info for a film — returns ['name'=>'T16', 'logo_url'=>'...', 'desc'=>'...'] or null
function get_film_age_rating( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $terms = get_the_terms($post_id, 'film_age_rating');
    if ( !$terms || is_wp_error($terms) ) {
        // Fallback: try old text field film_rating
        $text = get_post_meta($post_id, 'film_rating', true);
        if ( $text ) {
            $term = get_term_by('slug', strtolower($text), 'film_age_rating');
            if ( $term && !is_wp_error($term) ) $terms = [$term];
        }
    }
    if ( !$terms || is_wp_error($terms) ) return null;
    $term = $terms[0];
    $logo_id = get_field('rating_logo', 'film_age_rating_' . $term->term_id);
    $logo_url = $logo_id ? wp_get_attachment_image_url((int)$logo_id, 'thumbnail') : '';
    return [
        'name'     => $term->name,
        'slug'     => $term->slug,
        'logo_url' => $logo_url,
        'desc'     => $term->description,
    ];
}

// Hide WordPress's default taxonomy meta box for film_genre — ACF multi_select handles it
add_action('admin_menu', function() {
    remove_meta_box('film_genrediv', 'film', 'side');  // hierarchical → uses *div suffix
    remove_meta_box('tagsdiv-film_genre', 'film', 'side');  // non-hierarchical fallback
});

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
            ['key'=>'field_film_en_title','label'=>'Tên tiếng Anh','name'=>'film_en_title','type'=>'text',
             'instructions'=>'Tên phim bản tiếng Anh. Title ở trên là tiếng Việt.','wrapper'=>['width'=>'50'],
             'placeholder'=>'vd: Phi Phong: The Blood Demon'],
            ['key'=>'field_film_genre','label'=>'Thể loại','name'=>'film_genre_tax','type'=>'taxonomy',
             'taxonomy'=>'film_genre','add_term'=>1,'save_terms'=>1,'load_terms'=>1,
             'return_format'=>'id','field_type'=>'multi_select','wrapper'=>['width'=>'50'],
             'instructions'=>'Chọn thể loại theo thứ tự ưu tiên. Cái nào chọn trước sẽ hiển thị trước trên web.'],
            ['key'=>'field_film_rating','label'=>'Phân Loại Độ Tuổi','name'=>'film_rating_tax','type'=>'taxonomy',
             'taxonomy'=>'film_age_rating','add_term'=>0,'save_terms'=>1,'load_terms'=>1,
             'return_format'=>'object','field_type'=>'radio','wrapper'=>['width'=>'25'],
             'allow_null'=>1,'instructions'=>'Theo phân loại của Cục Điện Ảnh VN.'],
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
             'instructions'=>'Portrait (2:3). Min 600×900px.','wrapper'=>['width'=>'20'],
             'return_format'=>'id','library'=>'all','preview_size'=>'film-poster-preview'],
            ['key'=>'field_film_banner','label'=>'Banner','name'=>'film_banner','type'=>'image',
             'instructions'=>'Landscape hero (16:9). Min 1920×1080px.','wrapper'=>['width'=>'40'],
             'return_format'=>'id','library'=>'all','preview_size'=>'film-banner-preview'],
            ['key'=>'field_film_logo','label'=>'Logo phim (Tiếng Việt)','name'=>'film_logo','type'=>'image',
             'instructions'=>'PNG trong suốt.','wrapper'=>['width'=>'20'],
             'return_format'=>'id','library'=>'all','preview_size'=>'thumbnail'],
            ['key'=>'field_film_logo_en','label'=>'Logo phim (Tiếng Anh)','name'=>'film_logo_en','type'=>'image',
             'instructions'=>'Trống → fallback dùng logo VN.','wrapper'=>['width'=>'20'],
             'return_format'=>'id','library'=>'all','preview_size'=>'thumbnail'],
            ['key'=>'field_film_trailer','label'=>'Trailer URL (Tiếng Việt)','name'=>'film_trailer','type'=>'url',
             'instructions'=>'YouTube hoặc Vimeo URL. Bản tiếng Việt mặc định.','wrapper'=>['width'=>'100'],
             'placeholder'=>'https://youtube.com/watch?v=...'],
            ['key'=>'field_film_trailer_en','label'=>'Trailer URL (Tiếng Anh)','name'=>'film_trailer_en','type'=>'url',
             'instructions'=>'Trailer bản tiếng Anh. Nếu trống → fallback dùng trailer tiếng Việt.','wrapper'=>['width'=>'100'],
             'placeholder'=>'https://youtube.com/watch?v=...'],
            ['key'=>'field_film_videos','label'=>'Videos bổ sung','name'=>'film_videos','type'=>'textarea',
             'instructions'=>'Mỗi dòng 1 video. Trailer chính KHÔNG tự đưa vào đây.<br>Các định dạng:<br>'
                .'<code>URL</code> — tự fetch title từ YouTube cho cả 2 ngôn ngữ<br>'
                .'<code>URL|Tiêu đề VN</code> — dùng tiêu đề này cho cả 2 ngôn ngữ<br>'
                .'<code>URL|Tiêu đề VN|English Title</code> — có tiêu đề riêng cho mỗi ngôn ngữ<br>'
                .'VD: <code>https://youtu.be/abc|Hậu trường tập 1|Behind The Scenes Ep 1</code>',
             'wrapper'=>['width'=>'100'],'rows'=>8,
             'placeholder'=>"https://youtu.be/...\nhttps://youtu.be/...|Trailer chính\nhttps://youtu.be/...|Hậu trường|Behind The Scenes"],

            // ── Tab: Nội dung ──
            ['key'=>'field_tab_content','label'=>'Nội dung','type'=>'tab','placement'=>'top'],
            ['key'=>'field_film_logline','label'=>'Logline','name'=>'film_logline','type'=>'textarea','rows'=>2,'new_lines'=>'br',
             'instructions'=>'Câu mô tả siêu ngắn (1 dòng).'],
            ['key'=>'field_film_synopsis_short','label'=>'Synopsis ngắn (EN)','name'=>'film_synopsis_short','type'=>'textarea','rows'=>4,'new_lines'=>'br',
             'wrapper'=>['width'=>'50'],'instructions'=>'Tóm tắt ngắn — tiếng Anh.'],
            ['key'=>'field_film_synopsis_short_vn','label'=>'Synopsis ngắn (VN)','name'=>'film_synopsis_short_vn','type'=>'textarea','rows'=>4,'new_lines'=>'br',
             'wrapper'=>['width'=>'50'],'instructions'=>'Tóm tắt ngắn — tiếng Việt.'],
            ['key'=>'field_film_synopsis_full','label'=>'Synopsis đầy đủ (EN)','name'=>'film_synopsis_full','type'=>'wysiwyg',
             'tabs'=>'all','toolbar'=>'basic','media_upload'=>0,'delay'=>0,'wrapper'=>['width'=>'50'],
             'instructions'=>'Synopsis đầy đủ — tiếng Anh.'],
            ['key'=>'field_film_synopsis_full_vn','label'=>'Synopsis đầy đủ (VN)','name'=>'film_synopsis_full_vn','type'=>'wysiwyg',
             'tabs'=>'all','toolbar'=>'basic','media_upload'=>0,'delay'=>0,'wrapper'=>['width'=>'50'],
             'instructions'=>'Synopsis đầy đủ — tiếng Việt.'],

            // ── Tab: Đoàn phim ──
            ['key'=>'field_tab_crew','label'=>'Đoàn phim','type'=>'tab','placement'=>'top'],
            ['key'=>'field_film_director','label'=>'Đạo diễn','name'=>'film_director','type'=>'text','wrapper'=>['width'=>'50']],
            ['key'=>'field_film_writer','label'=>'Biên kịch','name'=>'film_writer','type'=>'text','wrapper'=>['width'=>'50']],
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
             'wrapper'=>['width'=>'45'],'return_format'=>'id','library'=>'all','preview_size'=>'thumbnail'],
            ['key'=>'field_partner_link','label'=>'Link website','name'=>'partner_link','type'=>'url',
             'instructions'=>'Khi click vào logo sẽ mở link này. Để trống = không click được.',
             'wrapper'=>['width'=>'30'],'placeholder'=>'https://...'],
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

// Helper: film logo URL — language-aware (VN default, EN fallback to VN)
function get_film_logo_url( $post_id = null, $size = 'large' ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $lang  = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $vi_id = get_field('film_logo', $post_id);
    $en_id = get_field('film_logo_en', $post_id);

    // Priority order: current-lang version first, then fallback to other lang
    $candidates = $lang === 'en' ? [$en_id, $vi_id] : [$vi_id, $en_id];
    foreach ( $candidates as $id ) {
        if ( $id && is_numeric($id) ) {
            $url = wp_get_attachment_image_url((int)$id, $size);
            if ( $url ) return $url;
        }
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

// Helper: language-aware film title pair → ['main' => ..., 'sub' => ...]
// post_title = Vietnamese (primary), film_en_title = English (ACF field).
function get_film_display_titles( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $vn = get_the_title($post_id);
    $en = get_post_meta($post_id, 'film_en_title', true);
    // Backward-compat: if film_en_title empty, try old field name
    if ( !$en ) $en = get_post_meta($post_id, 'film_vn_title', true);

    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';

    if ( $lang === 'vi' ) {
        return ['main' => $vn ?: $en, 'sub' => ($en && $en !== $vn) ? $en : ''];
    }
    // EN
    return ['main' => $en ?: $vn, 'sub' => ($vn && $vn !== $en) ? $vn : ''];
}

// Helper: just the main title for current language
function get_film_main_title( $post_id = null ) {
    $t = get_film_display_titles($post_id);
    return $t['main'];
}

// Helper: render title as HTML — split at ":" or "—" so wrapping happens at natural break point
function get_film_title_html( $post_id = null ) {
    $title = get_film_main_title($post_id);
    if ( !$title ) return '';
    // Match "Phần 1: Phần 2" or "Phần 1 — Phần 2"
    if ( preg_match('/^(.+?)\s*([:—–])\s*(.+)$/u', $title, $m) ) {
        return '<span class="title-part">' . esc_html(trim($m[1]) . $m[2]) . '</span> '
             . '<span class="title-part">' . esc_html(trim($m[3])) . '</span>';
    }
    return esc_html($title);
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

// Smart release label — "Coming Soon" if status is coming-soon, else actual date
function get_film_release_label( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $status_class = get_film_status_class($post_id);
    if ( $status_class === 'coming-soon' ) {
        return bbs_t('Coming Soon');
    }
    return get_film_release_display($post_id);
}

// Check if film is coming-soon
function bluebells_is_coming_soon( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    return get_film_status_class($post_id) === 'coming-soon';
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
// Each line: "URL" or "URL|TitleVN" or "URL|TitleVN|TitleEN".
// If only 1 title given, used for both languages.
// If no title, fetched from YouTube oembed.
function bluebells_parse_videos( $extra_text = '' ) {
    if ( !$extra_text ) return [];
    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    $items = [];
    $seen  = [];

    foreach ( array_filter(array_map('trim', explode("\n", $extra_text))) as $line ) {
        $parts    = array_map('trim', explode('|', $line, 3));
        $url      = $parts[0] ?? '';
        $title_vi = $parts[1] ?? '';
        $title_en = $parts[2] ?? '';
        if ( !$url || isset($seen[$url]) ) continue;

        // Pick title for current language. EN falls back to VI if not provided.
        $title = $lang === 'en' ? ( $title_en ?: $title_vi ) : $title_vi;

        $items[] = ['url' => $url, 'title' => $title];
        $seen[$url] = true;
    }

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

// Get the trailer URL for current language (en falls back to vi)
function get_film_trailer_url( $post_id = null ) {
    if ( !$post_id ) $post_id = get_the_ID();
    $vi = get_post_meta($post_id, 'film_trailer', true);
    $en = get_post_meta($post_id, 'film_trailer_en', true);
    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';
    if ( $lang === 'en' ) return $en ?: $vi;
    return $vi ?: $en;
}

// Normalize release date for comparison (Ymd integer; missing date = 0)
function bluebells_normalize_release_for_sort( $post_id ) {
    $val = trim((string) get_post_meta($post_id, 'film_release_date', true));
    if ( !$val ) return 0;
    // Ymd legacy "20260824"
    if ( preg_match('/^(\d{8})$/', $val, $m) ) return (int)$m[1];
    // DD/MM/YYYY
    if ( preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $m) )
        return (int)sprintf('%04d%02d%02d', $m[3], $m[2], $m[1]);
    // MM/YYYY → assume 1st of month
    if ( preg_match('/^(\d{1,2})\/(\d{4})$/', $val, $m) )
        return (int)sprintf('%04d%02d01', $m[2], $m[1]);
    // YYYY only → assume Jan 1
    if ( preg_match('/^(\d{4})$/', $val, $m) ) return (int)($m[1] . '0101');
    return 0;
}

// Get films: Now Showing first (by release DESC), then other films (by release DESC).
function bluebells_get_films_by_release( $limit = 5, $extra_args = [] ) {
    $args = array_merge([
        'post_type'      => 'film',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ], $extra_args);
    $films = get_posts($args);

    usort($films, function($a, $b) {
        // Priority 1: now-showing always comes first
        $a_ns = get_film_status_class($a->ID) === 'now-showing' ? 1 : 0;
        $b_ns = get_film_status_class($b->ID) === 'now-showing' ? 1 : 0;
        if ( $a_ns !== $b_ns ) return $b_ns - $a_ns;

        // Priority 2: within same group, DESC by release date
        $sa = bluebells_normalize_release_for_sort($a->ID);
        $sb = bluebells_normalize_release_for_sort($b->ID);
        if ( $sa !== $sb ) return $sb - $sa;

        // Tie-break: newest added
        return strtotime($b->post_date) - strtotime($a->post_date);
    });

    return array_slice($films, 0, $limit);
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
    $lang = function_exists('bbs_current_lang') ? bbs_current_lang() : 'vi';

    // Resolve a term's display name based on current lang (en uses ACF genre_name_en, fallback VN name)
    $get_name = function($term) use ($lang) {
        if ( $lang === 'en' ) {
            $en = function_exists('get_field') ? get_field('genre_name_en', 'film_genre_' . $term->term_id) : '';
            if ( $en ) return $en;
        }
        return $term->name;
    };

    // Prefer ACF-stored ordered list (multi_select preserves selection order)
    $ids = get_field('film_genre_tax', $post_id);
    if ( $ids && is_array($ids) ) {
        $names = [];
        foreach ( $ids as $id ) {
            $t = get_term((int)$id, 'film_genre');
            if ( $t && !is_wp_error($t) ) $names[] = $get_name($t);
        }
        if ( $names ) return implode(' · ', $names);
    }
    // Fallback to default term order
    $terms = get_the_terms( $post_id, 'film_genre' );
    if ( !$terms || is_wp_error($terms) ) return '';
    return implode(' · ', array_map($get_name, $terms));
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
