<?php get_header(); ?>

<!-- HERO SLIDER -->
<?php
$banner_films = get_posts([
    'post_type'      => 'film',
    'posts_per_page' => 6,
    'meta_query'     => [['key' => 'film_featured_banner', 'value' => '1', 'compare' => '=']],
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
]);
if ( empty($banner_films) ) {
    $banner_films = get_posts(['post_type'=>'film','posts_per_page'=>1]);
}
?>

<section class="hero-slider" id="hero-slider">
  <div class="hero-track">
    <?php foreach ( $banner_films as $i => $film ):
      $bg      = get_film_banner_url($film->ID,'film-hero') ?: get_film_poster_url($film->ID,'film-hero');
      $status  = get_film_status_label($film->ID);
      $trailer = get_film_trailer_url($film->ID);
      $is_soon = bluebells_is_coming_soon($film->ID);
    ?>
    <a href="<?php echo get_permalink($film->ID); ?>" class="hero-slide<?php echo $i===0?' active':''; ?>">
      <div class="hero-bg-image" style="background-image:url('<?php echo esc_url($bg); ?>')"></div>
      <div class="hero-content">
        <?php
          // Only show eyebrow for now-showing / coming-soon. Other statuses hide it.
          $status_class = get_film_status_class($film->ID);
          $show_eyebrow = in_array($status_class, ['now-showing', 'coming-soon'], true);
          $eyebrow_year = '';
          if ( $is_soon ) {
              $rel_raw = get_post_meta($film->ID, 'film_release_date', true);
              if ( preg_match('/(\d{4})/', $rel_raw, $m) ) {
                  $eyebrow_year = $m[1];
              } else {
                  // No release date: estimate year as today + 3 months
                  $eyebrow_year = date('Y', strtotime('+3 months'));
              }
          }
        ?>
        <?php if ( $show_eyebrow && $status ): ?>
          <p class="hero-eyebrow"><?php echo esc_html($status); ?><?php if($eyebrow_year): ?> — <?php echo esc_html($eyebrow_year); ?><?php endif; ?></p>
        <?php endif; ?>
        <h2 class="hero-title"><?php echo get_film_title_html($film->ID); ?></h2>
        <div class="hero-ctas">
          <?php if ( $trailer ): ?>
            <span class="btn-primary hero-trailer-btn" data-trailer="<?php echo esc_url($trailer); ?>"><?php bbs_e('Watch Trailer'); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if ( count($banner_films) > 1 ): ?>
  <button class="hero-arrow hero-arrow-prev" aria-label="Previous">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 4l-8 8 8 8"/></svg>
  </button>
  <button class="hero-arrow hero-arrow-next" aria-label="Next">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 4l8 8-8 8"/></svg>
  </button>
  <div class="hero-dots">
    <?php foreach ( $banner_films as $i => $film ): ?>
      <button class="hero-dot<?php echo $i===0?' active':''; ?>" data-index="<?php echo $i; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<!-- FEATURED SLATE -->
<?php
$featured = bluebells_get_films_by_release(5);
?>
<?php if ( $featured ): ?>
<section class="section-pad section-gray section-border-top">
  <div class="slate-header">
    <h2 class="section-heading"><?php bbs_e('Featured Movies'); ?></h2>
    <a href="<?php echo get_post_type_archive_link('film'); ?>" class="view-all"><?php bbs_e('View all movies'); ?></a>
  </div>
  <div class="featured-movies-grid" data-batch="10">
    <?php foreach ( $featured as $i => $film ): ?>
    <a href="<?php echo get_permalink($film->ID); ?>" class="film-card-link">
    <article class="film-card">
      <div class="film-poster">
        <?php $poster = get_film_poster_url($film->ID,'film-card'); if($poster): ?>
          <img src="<?php echo esc_url($poster); ?>"
               alt="<?php echo esc_attr($film->post_title); ?>" loading="<?php echo $i===0?'eager':'lazy'; ?>">
        <?php endif; ?>
        <div class="film-poster-overlay"></div>
        <?php
          $status = get_film_status_label($film->ID);
          $status_class = get_film_status_class($film->ID);
          if ( $status && in_array($status_class, ['now-showing','coming-soon'], true) ):
        ?>
          <span class="film-poster-label <?php echo $status_class; ?>"><?php echo esc_html($status); ?></span>
        <?php endif; ?>
      </div>
      <div class="film-info">
        <h3 class="film-name"><?php echo esc_html(get_film_main_title($film->ID)); ?></h3>
      </div>
    </article>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- NOW SHOWING -->
<?php
$now_showing = get_posts(['post_type'=>'film','posts_per_page'=>1,
    'tax_query'=>[['taxonomy'=>'film_status','field'=>'slug','terms'=>'now-showing']]]);
$ns = $now_showing ? $now_showing[0] : null;
?>
<?php if ( $ns ): ?>
<section class="section-pad section-dark section-border-top">
  <h2 class="section-heading"><?php bbs_e('Now in Cinemas'); ?></h2>
  <div class="showing-inner">
    <?php
    $ns_poster = get_film_poster_url($ns->ID, 'film-poster');
    $rating    = get_film_meta('film_rating', $ns->ID);
    $ns_lang   = bbs_current_lang();
    ?>
    <a href="<?php echo get_permalink($ns->ID); ?>" class="showing-visual">
      <div class="showing-poster-wrap">
        <?php if ( $ns_poster ): ?>
          <img src="<?php echo esc_url($ns_poster); ?>"
               alt="<?php echo esc_attr($ns->post_title); ?>" loading="lazy">
        <?php endif; ?>
      </div>
    </a>
    <div class="showing-details">
      <h2 class="showing-title"><?php echo get_film_title_html($ns->ID); ?></h2>
      <div class="film-meta-row">
        <?php $director = get_film_meta('film_director',$ns->ID); if($director): ?>
          <div class="meta-item"><strong><?php bbs_e('Director'); ?></strong><?php echo esc_html($director); ?></div>
        <?php endif; ?>
        <?php $genre = get_film_genre_string($ns->ID); if($genre): ?>
          <div class="meta-item"><strong><?php bbs_e('Genre'); ?></strong><?php echo esc_html($genre); ?></div>
        <?php endif; ?>
        <?php $runtime = get_film_meta('film_runtime',$ns->ID); if($runtime): ?>
          <div class="meta-item"><strong><?php bbs_e('Runtime'); ?></strong><?php echo esc_html($runtime); ?> <?php bbs_e('min'); ?></div>
        <?php endif; ?>
        <?php $rating_info = get_film_age_rating($ns->ID); if($rating_info): ?>
          <div class="meta-item rating-item">
            <strong><?php bbs_e('Rating'); ?></strong>
            <?php if ( $rating_info['logo_url'] ): ?>
              <img src="<?php echo esc_url($rating_info['logo_url']); ?>" alt="<?php echo esc_attr($rating_info['name']); ?>" class="rating-logo" title="<?php echo esc_attr($rating_info['desc']); ?>">
            <?php else: ?>
              <span class="rating-text"><?php echo esc_html($rating_info['name']); ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php
        // Language-aware synopsis: VN site uses VN field, EN site uses EN field, fallback to other lang
        $syn_en = get_film_meta('film_synopsis_short', $ns->ID);
        $syn_vn = get_film_meta('film_synopsis_short_vn', $ns->ID);
        $syn = $ns_lang === 'vi' ? ($syn_vn ?: $syn_en) : ($syn_en ?: $syn_vn);
        $syn = $syn ?: $ns->post_excerpt;
        if ( $syn ):
      ?>
        <p class="showing-synopsis"><?php echo esc_html($syn); ?></p>
      <?php endif; ?>
      <div class="showing-ctas">
        <?php $ticket = get_film_meta('film_ticket_link',$ns->ID); if($ticket): ?>
          <a href="<?php echo esc_url($ticket); ?>" class="btn-primary" target="_blank" rel="noopener"><?php bbs_e('Buy Tickets'); ?></a>
        <?php endif; ?>
        <?php $trailer = get_film_trailer_url($ns->ID); if($trailer): ?>
          <a href="<?php echo esc_url($trailer); ?>" class="btn-ghost" target="_blank" rel="noopener"><?php bbs_e('Watch Trailer'); ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php /* Coming Soon section — currently hidden. Uncomment to restore.
<?php
$coming = bluebells_get_films_sorted([
    'posts_per_page'=>4,
    'tax_query'=>[['taxonomy'=>'film_status','field'=>'slug','terms'=>'coming-soon']],
]);
?>
<?php if ( $coming ): ?>
<section class="section-pad section-gray section-border-top">
  <h2 class="section-heading"><?php bbs_e('Coming Soon'); ?></h2>
  <div class="coming-grid">
    <?php foreach ( $coming as $film ): ?>
    <a href="<?php echo get_permalink($film->ID); ?>" class="film-card-link">
    <article class="coming-card">
      <div class="coming-poster">
        <?php $poster = get_film_poster_url($film->ID,'film-poster'); if($poster): ?>
          <img src="<?php echo esc_url($poster); ?>"
               alt="<?php echo esc_attr($film->post_title); ?>" loading="lazy">
        <?php endif; ?>
      </div>
      <div class="coming-info">
        <?php $rel = get_film_release_label($film->ID); if($rel): ?>
          <p class="coming-date"><?php echo esc_html($rel); ?></p>
        <?php endif; ?>
        <h3 class="coming-title"><?php echo esc_html(get_film_main_title($film->ID)); ?></h3>
      </div>
    </article>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
*/ ?>

<!-- ABOUT + CAPABILITIES -->
<section class="section-pad section-dark section-border-top">
  <?php
    $about_img_id  = (int) get_option('bluebells_about_image');
    $about_img_url = $about_img_id ? wp_get_attachment_image_url($about_img_id, 'large') : '';
  ?>
  <div class="about-grid<?php echo $about_img_url ? ' has-image' : ''; ?>">
    <div class="about-text">
      <h2 class="section-heading"><?php bbs_e('Our Story'); ?></h2>
      <?php
        $about_body = bbs_content(
            'about_body',
            "Thành lập năm 2022, Bluebells Studios được xây dựng trên nền tảng của Mockingbird Pictures — một trong những công ty phân phối phim quốc tế hàng đầu Việt Nam.\n\nSứ mệnh của chúng tôi là kiến tạo những bộ phim tôn vinh bản sắc Việt, hòa quyện cùng giá trị đương đại và đưa câu chuyện Việt Nam vươn ra thế giới.",
            "Founded in 2022, Bluebells Studios was built on the foundation of Mockingbird Pictures, one of Vietnam's leading international film distribution companies.\n\nOur mission is to create movies that celebrate Vietnamese identity, blend it with contemporary values, and bring Vietnamese stories to the global stage."
        );
        foreach ( preg_split("/\r\n\r\n|\r\r|\n\n/", trim($about_body)) as $para ):
            $para = trim($para);
            if ( !$para ) continue;
        ?>
        <p class="about-body"><?php echo nl2br(esc_html($para)); ?></p>
      <?php endforeach; ?>
    </div>
    <?php if ( $about_img_url ): ?>
    <div class="about-image">
      <img src="<?php echo esc_url($about_img_url); ?>" alt="" loading="lazy">
    </div>
    <?php endif; ?>

    <?php /* Capabilities list — currently hidden. Uncomment to restore.
    <div class="caps-list">
      <div class="cap-item">
        <span class="cap-num">01</span>
        <p class="cap-title"><?php bbs_e('Film Production'); ?></p>
        <p class="cap-desc"><?php bbs_e('IP development, script, production, post-production.'); ?></p>
      </div>
      <div class="cap-item">
        <span class="cap-num">02</span>
        <p class="cap-title"><?php bbs_e('Theatrical Distribution'); ?></p>
        <p class="cap-desc"><?php bbs_e('Nationwide cinema network. Release strategy & booking.'); ?></p>
      </div>
      <div class="cap-item">
        <span class="cap-num">03</span>
        <p class="cap-title"><?php bbs_e('PR & Media'); ?></p>
        <p class="cap-desc"><?php bbs_e('Press, premiere events, social campaigns, KOL.'); ?></p>
      </div>
      <div class="cap-item">
        <span class="cap-num">04</span>
        <p class="cap-title"><?php bbs_e('International'); ?></p>
        <p class="cap-desc"><?php bbs_e('Acquisition, co-production, festival & sales representation.'); ?></p>
      </div>
    </div>
    */ ?>
  </div>
</section>

<!-- PARTNERS -->
<?php $partners = function_exists('get_partners_sorted') ? get_partners_sorted() : []; ?>
<?php if ( $partners ): ?>
<section class="section-pad section-gray section-border-top">
  <h2 class="section-heading"><?php bbs_e('Our Partners'); ?></h2>
  <div class="partners-gallery">
    <?php foreach ( $partners as $p ):
      $logo_id  = get_field('partner_logo', $p->ID);
      $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
      $link     = get_field('partner_link', $p->ID);
      if ( !$logo_url ) continue;
      $tag = $link ? 'a' : 'div';
      $attr = $link
        ? ' href="' . esc_url($link) . '" target="_blank" rel="noopener"'
        : '';
    ?>
    <<?php echo $tag; ?> class="partner-tile<?php echo $link ? ' has-link' : ''; ?>"<?php echo $attr; ?> title="<?php echo esc_attr($p->post_title); ?>">
      <img src="<?php echo esc_url($logo_url); ?>"
           alt="<?php echo esc_attr($p->post_title); ?>" loading="lazy">
    </<?php echo $tag; ?>>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- CONTACT CTA -->
<?php get_template_part('template-parts/section-contact'); ?>

<?php get_footer(); ?>
