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
      $trailer = get_film_meta('film_trailer',$film->ID);
    ?>
    <a href="<?php echo get_permalink($film->ID); ?>" class="hero-slide<?php echo $i===0?' active':''; ?>">
      <div class="hero-bg-image" style="background-image:url('<?php echo esc_url($bg); ?>')"></div>
      <div class="hero-content">
        <?php if($status): ?>
          <p class="hero-eyebrow"><?php echo esc_html($status); ?> — <?php echo date('Y'); ?></p>
        <?php endif; ?>
        <h2 class="hero-title"><?php echo esc_html($film->post_title); ?></h2>
        <div class="hero-ctas">
          <?php if($trailer): ?>
            <span class="btn-primary hero-trailer-btn" data-trailer="<?php echo esc_url($trailer); ?>">Watch Trailer</span>
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
$featured = bluebells_get_films_sorted(['posts_per_page'=>-1]);
?>
<?php if ( $featured ): ?>
<section class="section-pad section-dark section-border-top">
  <div class="slate-header">
    <h2 class="section-heading">Featured Movies</h2>
    <a href="<?php echo get_post_type_archive_link('film'); ?>" class="view-all">View all movies</a>
  </div>
  <div class="featured-movies-grid" data-batch="10">
    <?php foreach ( $featured as $i => $film ): ?>
    <a href="<?php echo get_permalink($film->ID); ?>" class="film-card-link<?php echo $i >= 10 ? ' is-hidden' : ''; ?>">
    <article class="film-card">
      <div class="film-poster">
        <?php $poster = get_film_poster_url($film->ID,'film-card'); if($poster): ?>
          <img src="<?php echo esc_url($poster); ?>"
               alt="<?php echo esc_attr($film->post_title); ?>" loading="<?php echo $i===0?'eager':'lazy'; ?>">
        <?php endif; ?>
        <div class="film-poster-overlay"></div>
        <?php $status = get_film_status_label($film->ID); if($status): ?>
          <span class="film-poster-label <?php echo get_film_status_class($film->ID); ?>"><?php echo esc_html($status); ?></span>
        <?php endif; ?>
      </div>
      <div class="film-info">
        <h3 class="film-name"><?php echo esc_html($film->post_title); ?></h3>
      </div>
    </article>
    </a>
    <?php endforeach; ?>
  </div>
  <?php if ( count($featured) > 10 ): ?>
    <button type="button" class="more-movies-btn">More movies</button>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- NOW SHOWING -->
<?php
$now_showing = get_posts(['post_type'=>'film','posts_per_page'=>1,
    'tax_query'=>[['taxonomy'=>'film_status','field'=>'slug','terms'=>'now-showing']]]);
$ns = $now_showing ? $now_showing[0] : null;
?>
<?php if ( $ns ): ?>
<section class="section-pad section-gray section-border-top">
  <h2 class="section-heading">Now in Cinemas</h2>
  <div class="showing-inner">
    <?php
    $ns_banner = get_film_banner_url($ns->ID, 'film-hero');
    $rating = get_film_meta('film_rating',$ns->ID);
    ?>
    <a href="<?php echo get_permalink($ns->ID); ?>" class="showing-visual">
      <?php if ( $ns_banner ): ?>
        <div class="showing-banner-wrap">
          <img src="<?php echo esc_url($ns_banner); ?>"
               alt="<?php echo esc_attr($ns->post_title); ?>" loading="lazy">
          <?php if($rating): ?>
            <span class="poster-rating"><?php echo esc_html($rating); ?></span>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="showing-poster-wrap">
          <?php $ns_poster = get_film_poster_url($ns->ID,'film-poster'); if($ns_poster): ?>
            <img src="<?php echo esc_url($ns_poster); ?>"
                 alt="<?php echo esc_attr($ns->post_title); ?>" loading="lazy">
          <?php endif; ?>
          <?php if($rating): ?>
            <span class="poster-rating"><?php echo esc_html($rating); ?></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>
    <div class="showing-details">
      <p class="showing-eyebrow">Now Showing</p>
      <h2 class="showing-title"><?php echo esc_html($ns->post_title); ?></h2>
      <div class="film-meta-row">
        <?php $genre = get_film_genre_string($ns->ID); if($genre): ?>
          <div class="meta-item"><strong>Genre</strong><?php echo esc_html($genre); ?></div>
        <?php endif; ?>
        <?php $runtime = get_film_meta('film_runtime',$ns->ID); if($runtime): ?>
          <div class="meta-item"><strong>Runtime</strong><?php echo esc_html($runtime); ?> min</div>
        <?php endif; ?>
        <?php if($rating): ?>
          <div class="meta-item"><strong>Rating</strong><?php echo esc_html($rating); ?></div>
        <?php endif; ?>
        <?php $format = get_film_meta('film_format',$ns->ID); if($format): ?>
          <div class="meta-item"><strong>Format</strong><?php echo esc_html($format); ?></div>
        <?php endif; ?>
      </div>
      <?php $syn = get_film_meta('film_synopsis_short',$ns->ID) ?: $ns->post_excerpt; if($syn): ?>
        <p class="showing-synopsis"><?php echo esc_html($syn); ?></p>
      <?php endif; ?>
      <div class="showing-ctas">
        <?php $ticket = get_film_meta('film_ticket_link',$ns->ID); if($ticket): ?>
          <a href="<?php echo esc_url($ticket); ?>" class="btn-primary" target="_blank" rel="noopener">Buy Tickets</a>
        <?php endif; ?>
        <?php $trailer = get_film_meta('film_trailer',$ns->ID); if($trailer): ?>
          <a href="<?php echo esc_url($trailer); ?>" class="btn-ghost" target="_blank" rel="noopener">Watch Trailer</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- COMING SOON -->
<?php
$coming = bluebells_get_films_sorted([
    'posts_per_page'=>4,
    'tax_query'=>[['taxonomy'=>'film_status','field'=>'slug','terms'=>'coming-soon']],
]);
?>
<?php if ( $coming ): ?>
<section class="section-pad section-dark section-border-top">
  <h2 class="section-heading">Coming Soon</h2>
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
        <?php $rel = get_film_release_display($film->ID); if($rel): ?>
          <p class="coming-date"><?php echo esc_html($rel); ?></p>
        <?php endif; ?>
        <h3 class="coming-title"><?php echo esc_html($film->post_title); ?></h3>
      </div>
    </article>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ABOUT + CAPABILITIES -->
<section class="section-pad section-gray section-border-top">
  <div class="about-grid">
    <div class="about-text">
      <h2 class="section-heading">Our Story</h2>
      <p class="about-body">
        Founded in 2022, Bluebells Studios was built on the foundation of Mockingbird Pictures, one of Vietnam's leading international film distribution companies.
      </p>
      <p class="about-body">
        Our mission is to create films that celebrate Vietnamese identity, blend it with contemporary values, and bring Vietnamese stories to the global stage.
      </p>
      <a href="<?php echo home_url('/contact'); ?>" class="btn-ghost">Partner With Us</a>
    </div>
    <div class="caps-list">
      <div class="cap-item">
        <span class="cap-num">01</span>
        <p class="cap-title">Film Production</p>
        <p class="cap-desc">IP development, script, production, post-production.</p>
      </div>
      <div class="cap-item">
        <span class="cap-num">02</span>
        <p class="cap-title">Theatrical Distribution</p>
        <p class="cap-desc">Nationwide cinema network. Release strategy & booking.</p>
      </div>
      <div class="cap-item">
        <span class="cap-num">03</span>
        <p class="cap-title">PR & Media</p>
        <p class="cap-desc">Press, premiere events, social campaigns, KOL.</p>
      </div>
      <div class="cap-item">
        <span class="cap-num">04</span>
        <p class="cap-title">International</p>
        <p class="cap-desc">Acquisition, co-production, festival & sales representation.</p>
      </div>
    </div>
  </div>
</section>

<!-- PARTNERS -->
<?php $partners = function_exists('get_partners_sorted') ? get_partners_sorted() : []; ?>
<?php if ( $partners ): ?>
<section class="section-pad section-dark section-border-top">
  <h2 class="section-heading">Our Partners</h2>
  <div class="partners-gallery">
    <?php foreach ( $partners as $p ):
      $logo_id  = get_field('partner_logo', $p->ID);
      $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
      if ( !$logo_url ) continue;
    ?>
    <div class="partner-tile" title="<?php echo esc_attr($p->post_title); ?>">
      <img src="<?php echo esc_url($logo_url); ?>"
           alt="<?php echo esc_attr($p->post_title); ?>" loading="lazy">
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- CONTACT CTA -->
<section class="contact-section section-gray section-border-top">
  <div class="contact-inner">
    <span class="contact-label">Work With Us</span>
    <h2 class="contact-headline">Let's craft beautiful films together.</h2>
    <p class="contact-sub">Whether you're a producer, sales agent, cinema partner, or brand — we'd love to hear from you.</p>
    <div class="contact-ctas">
      <a href="<?php echo home_url('/contact'); ?>" class="btn-primary">Contact Us</a>
      <a href="<?php echo home_url('/contact'); ?>" class="btn-ghost">Partnership Inquiry</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
