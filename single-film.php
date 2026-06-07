<?php get_header(); the_post(); ?>

<?php
$lang        = bbs_current_lang();  // 'vi' or 'en'
$trailer     = get_film_trailer_url();
$ticket      = get_film_meta('film_ticket_link');
$status      = get_film_status_label();
$genre       = get_film_genre_string();
$runtime     = get_film_meta('film_runtime');
$rating      = get_film_meta('film_rating');
$release     = get_film_release_label();
$format      = get_film_meta('film_format');
$language    = get_film_meta('film_language');
$logline     = get_film_meta('film_logline');

// Dual-language synopsis — pick by current lang, fallback to whichever exists
$syn_full_en  = get_film_meta('film_synopsis_full');
$syn_full_vn  = get_film_meta('film_synopsis_full_vn');
$syn_short_en = get_film_meta('film_synopsis_short');
$syn_short_vn = get_film_meta('film_synopsis_short_vn');
$syn_full  = $lang === 'vi' ? ($syn_full_vn  ?: $syn_full_en)  : ($syn_full_en  ?: $syn_full_vn);
$syn_short = $lang === 'vi' ? ($syn_short_vn ?: $syn_short_en) : ($syn_short_en ?: $syn_short_vn);

$director  = get_film_meta('film_director');
$writer    = get_film_meta('film_writer');
$producer  = get_film_meta('film_producer');
$cast      = get_film_meta('film_cast');

// Dual-language title — post_title is Vietnamese (primary), film_en_title is English
$titles = get_film_display_titles();
$main_title = $titles['main'];
$sub_title  = $titles['sub'];

$press_kit = get_film_meta('film_press_kit');
$poster_url = get_film_poster_url(null,'large');
$film_logo = get_film_logo_url(null,'large');
?>

<!-- ① FILM HERO BANNER -->
<section class="film-hero">
  <?php $film_bg = get_film_banner_url(null,'film-hero') ?: get_film_poster_url(null,'film-hero'); ?>
  <div class="hero-bg-image"
    <?php if($film_bg): ?>style="background-image:url('<?php echo esc_url($film_bg); ?>')"<?php endif; ?>>
  </div>
  <div class="hero-content film-hero-content">
    <?php if($film_logo): ?>
      <img src="<?php echo esc_url($film_logo); ?>" alt="<?php the_title_attribute(); ?>" class="film-hero-logo">
    <?php endif; ?>
    <p class="film-hero-title"><?php echo esc_html($main_title); ?></p>
    <div class="hero-ctas film-hero-ctas">
      <?php if($trailer): ?>
        <a href="<?php echo esc_url($trailer); ?>" class="btn-primary" target="_blank" rel="noopener">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="margin-right:8px"><path d="M8 5v14l11-7z"/></svg>
          <?php bbs_e('Watch Trailer'); ?>
        </a>
      <?php endif; ?>
      <?php if($ticket): ?>
        <a href="<?php echo esc_url($ticket); ?>" class="btn-ghost" target="_blank" rel="noopener"><?php bbs_e('Buy Tickets'); ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ② POSTER + FILM INFO -->
<section class="film-overview section-dark section-border-top">
  <div class="film-overview-grid">

    <?php if($poster_url): ?>
    <div class="film-overview-poster">
      <img src="<?php echo esc_url($poster_url); ?>" alt="<?php the_title_attribute(); ?>">
    </div>
    <?php endif; ?>

    <div class="film-overview-info">
    <div class="film-overview-meta-col">
      <h2 class="film-overview-title"><?php echo esc_html($main_title); ?></h2>
      <?php if($sub_title): ?>
        <p class="film-overview-vn"><?php echo esc_html($sub_title); ?></p>
      <?php endif; ?>
      <div class="film-overview-meta">
        <?php if($runtime): ?>
          <div class="meta-row"><span class="meta-label"><?php bbs_e('Runtime'); ?></span><span class="meta-value"><?php echo esc_html($runtime); ?> <?php bbs_e('min'); ?></span></div>
        <?php endif; ?>
        <?php if($release): ?>
          <div class="meta-row"><span class="meta-label"><?php bbs_e('Release Date'); ?></span><span class="meta-value"><?php echo esc_html($release); ?></span></div>
        <?php endif; ?>
        <?php if($genre): ?>
          <div class="meta-row"><span class="meta-label"><?php bbs_e('Genre'); ?></span><span class="meta-value"><?php echo esc_html($genre); ?></span></div>
        <?php endif; ?>
        <?php $rating_info = get_film_age_rating(); if($rating_info): ?>
          <div class="meta-row">
            <span class="meta-label"><?php bbs_e('Rating'); ?></span>
            <span class="meta-value">
              <?php if ( $rating_info['logo_url'] ): ?>
                <img src="<?php echo esc_url($rating_info['logo_url']); ?>" alt="<?php echo esc_attr($rating_info['name']); ?>" class="rating-logo" title="<?php echo esc_attr($rating_info['desc']); ?>">
              <?php else: ?>
                <?php echo esc_html($rating_info['name']); ?>
              <?php endif; ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="film-overview-crew">
      <?php if($director): ?>
      <div class="crew-block"><strong><?php bbs_e('Directed By'); ?></strong><span><?php echo esc_html($director); ?></span></div>
      <?php endif; ?>
      <?php if($writer): ?>
      <div class="crew-block"><strong><?php bbs_e('Written By'); ?></strong><span><?php echo esc_html($writer); ?></span></div>
      <?php endif; ?>
      <?php if($producer): ?>
      <div class="crew-block"><strong><?php bbs_e('Produced By'); ?></strong><span><?php echo esc_html($producer); ?></span></div>
      <?php endif; ?>
      <?php if($cast): ?>
      <div class="crew-block crew-cast"><strong><?php bbs_e('Cast'); ?></strong><span><?php echo esc_html($cast); ?></span></div>
      <?php endif; ?>
    </div>
    </div><!-- /.film-overview-info -->

    <?php if($syn_short || $syn_full || $logline): ?>
    <div class="film-overview-synopsis">
      <?php echo wp_kses_post($syn_full ?: $syn_short ?: $logline); ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ③ VIDEOS -->
<?php $videos = bluebells_parse_videos(get_film_meta('film_videos')); ?>
<?php if ( $videos ): ?>
<section class="section-pad section-dark section-border-top">
  <h2 class="section-heading"><?php bbs_e('Videos'); ?></h2>
  <div class="video-carousel" data-count="<?php echo count($videos); ?>">
    <button class="carousel-arrow carousel-prev" aria-label="Previous">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 4l-8 8 8 8"/></svg>
    </button>
    <div class="carousel-viewport">
      <div class="carousel-track<?php echo count($videos) <= 3 ? ' centered' : ''; ?>">
        <?php foreach ( $videos as $v ): ?>
        <div class="carousel-slide">
          <div class="video-thumb" data-vid="<?php echo esc_attr($v['id']); ?>">
            <img src="https://img.youtube.com/vi/<?php echo esc_attr($v['id']); ?>/hqdefault.jpg"
                 alt="<?php echo esc_attr($v['title'] ?: 'Video'); ?>" loading="lazy">
            <span class="video-play">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </span>
          </div>
          <?php if ( $v['title'] ): ?>
            <p class="video-title"><?php echo esc_html($v['title']); ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <button class="carousel-arrow carousel-next" aria-label="Next">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 4l8 8-8 8"/></svg>
    </button>
  </div>
</section>
<?php endif; ?>

<!-- ④ PHOTOS -->
<?php
// Only show hand-picked gallery images (no auto poster/banner)
$gallery_ids = get_post_meta(get_the_ID(), '_film_gallery_ids', true);
if ( $gallery_ids && is_string($gallery_ids) ) {
    $gallery_ids = array_filter(array_map('intval', explode(',', $gallery_ids)));
}
if ( $gallery_ids ): ?>
<section class="section-pad section-dark section-border-top">
  <h2 class="section-heading"><?php bbs_e('Photos'); ?></h2>
  <div class="photo-carousel" data-count="<?php echo count($gallery_ids); ?>">
    <button class="carousel-arrow carousel-prev" aria-label="Previous">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 4l-8 8 8 8"/></svg>
    </button>
    <div class="carousel-viewport">
      <div class="photo-track">
        <?php foreach($gallery_ids as $img_id):
          $img_url = wp_get_attachment_image_url($img_id, 'large');
          $full_url = wp_get_attachment_image_url($img_id, 'full');
          if(!$img_url) continue;
          // Detect landscape vs portrait
          $meta = wp_get_attachment_metadata($img_id);
          $w = isset($meta['width']) ? $meta['width'] : 0;
          $h = isset($meta['height']) ? $meta['height'] : 0;
          $orient = ($w > $h) ? 'landscape' : 'portrait';
        ?>
        <div class="photo-slide">
          <div class="photo-item <?php echo $orient; ?>" data-full="<?php echo esc_url($full_url); ?>">
            <div class="photo-blur" style="background-image:url('<?php echo esc_url($img_url); ?>')"></div>
            <img src="<?php echo esc_url($img_url); ?>" alt="" loading="lazy">
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <button class="carousel-arrow carousel-next" aria-label="Next">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 4l8 8-8 8"/></svg>
    </button>
  </div>
  <div class="photo-counter"></div>
</section>
<?php endif; ?>

<!-- ⑤ MORE FILMS — upcoming + now showing, soonest first -->
<?php
$current_id = get_the_ID();
// Priority: now-showing + coming-soon first
$related = bluebells_get_films_sorted([
    'posts_per_page' => -1,
    'post__not_in'   => [$current_id],
    'tax_query'      => [[
        'taxonomy' => 'film_status',
        'field'    => 'slug',
        'terms'    => ['now-showing','coming-soon'],
    ]],
]);
// If fewer than 5, fill with other films
if ( count($related) < 5 ) {
    $exclude = array_map(fn($f) => $f->ID, $related);
    $exclude[] = $current_id;
    $more = bluebells_get_films_sorted([
        'posts_per_page' => -1,
        'post__not_in'   => $exclude,
    ]);
    $related = array_merge($related, $more);
}
$related = array_slice($related, 0, 12);
if($related):?>
<section class="section-pad section-gray section-border-top">
  <h2 class="section-heading"><?php bbs_e('More Movies'); ?></h2>
  <div class="films-carousel" data-count="<?php echo count($related); ?>">
    <button class="carousel-arrow carousel-prev" aria-label="Previous">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 4l-8 8 8 8"/></svg>
    </button>
    <div class="carousel-viewport">
      <div class="carousel-track">
        <?php foreach($related as $film): ?>
        <div class="carousel-slide">
          <a href="<?php echo get_permalink($film->ID); ?>" class="film-card-link">
          <article class="film-card">
            <div class="film-poster">
              <?php $rel_poster = get_film_poster_url($film->ID,'film-card'); if($rel_poster): ?>
                <img src="<?php echo esc_url($rel_poster); ?>"
                     alt="<?php echo esc_attr($film->post_title); ?>" loading="lazy">
              <?php endif; ?>
              <div class="film-poster-overlay"></div>
              <?php $s=get_film_status_label($film->ID); if($s): ?>
                <span class="film-poster-label <?php echo get_film_status_class($film->ID); ?>"><?php echo esc_html($s); ?></span>
              <?php endif; ?>
            </div>
            <div class="film-info">
              <h3 class="film-name"><?php echo esc_html(get_film_main_title($film->ID)); ?></h3>
            </div>
          </article>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <button class="carousel-arrow carousel-next" aria-label="Next">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 4l8 8-8 8"/></svg>
    </button>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
