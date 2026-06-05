<?php get_header(); ?>

<section class="section-pad section-dark">
  <div class="slate-header">
    <h1 class="slate-title">Bluebells Studios' Movies</h1>
  </div>

  <!-- Filter tabs -->
  <?php
  $statuses = get_terms(['taxonomy'=>'film_status','hide_empty'=>true]);
  $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
  ?>
  <?php if($statuses && !is_wp_error($statuses)): ?>
  <div class="filter-tabs">
    <a href="<?php echo get_post_type_archive_link('film'); ?>" class="filter-tab<?php echo !$current_status?' active':''; ?>">All</a>
    <?php foreach($statuses as $term): ?>
    <a href="<?php echo get_post_type_archive_link('film').'?status='.esc_attr($term->slug); ?>"
       class="filter-tab<?php echo ($current_status===$term->slug)?' active':''; ?>">
      <?php echo esc_html($term->name); ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Films grid -->
  <?php
  $args = ['post_type'=>'film','posts_per_page'=>-1,'orderby'=>'menu_order','order'=>'ASC'];
  if($current_status) {
    $args['tax_query'] = [['taxonomy'=>'film_status','field'=>'slug','terms'=>$current_status]];
  }
  $films = get_posts($args);
  ?>

  <?php if($films): ?>
  <div class="films-grid">
    <?php foreach($films as $film): ?>
    <a href="<?php echo get_permalink($film->ID); ?>" class="film-card-link">
    <article class="film-card">
      <div class="film-poster">
        <?php $poster = get_film_poster_url($film->ID,'film-card'); if($poster): ?>
          <img src="<?php echo esc_url($poster); ?>"
               alt="<?php echo esc_attr($film->post_title); ?>" loading="lazy">
        <?php endif; ?>
        <div class="film-poster-overlay"></div>
        <?php $s=get_film_status_label($film->ID); if($s): ?>
          <span class="film-poster-label <?php echo get_film_status_class($film->ID); ?>"><?php echo esc_html($s); ?></span>
        <?php endif; ?>
      </div>
      <div class="film-info">
        <h2 class="film-name"><?php echo esc_html($film->post_title); ?></h2>
        <?php $g=get_film_genre_string($film->ID); if($g): ?><p class="film-genre"><?php echo esc_html($g); ?></p><?php endif; ?>
      </div>
    </article>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <p style="color:#999;font-size:15px;">No films found.</p>
  <?php endif; ?>
</section>

<!-- CTA -->
<section class="contact-section section-gray section-border-top">
  <div class="contact-inner">
    <span class="contact-label">Partner With Us</span>
    <h2 class="contact-headline">For acquisition or partnership inquiries</h2>
    <p class="contact-sub">Contact the Bluebells Studio team for co-production, distribution rights, or brand partnerships.</p>
    <div class="contact-ctas">
      <a href="<?php echo home_url('/contact'); ?>" class="btn-primary">Contact Us</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
