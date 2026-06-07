<?php get_header(); ?>

<section class="section-pad section-dark">
  <div class="slate-header">
    <h1 class="section-heading"><?php bbs_e("Bluebells Studios' Movies"); ?></h1>
  </div>

  <!-- Filter tabs -->
  <?php
  $statuses = get_terms(['taxonomy'=>'film_status','hide_empty'=>true]);
  $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
  ?>
  <?php if($statuses && !is_wp_error($statuses)): ?>
  <div class="filter-tabs">
    <a href="<?php echo get_post_type_archive_link('film'); ?>" class="filter-tab<?php echo !$current_status?' active':''; ?>"><?php bbs_e('All'); ?></a>
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
  $args = ['posts_per_page'=>-1];
  if($current_status) {
    $args['tax_query'] = [['taxonomy'=>'film_status','field'=>'slug','terms'=>$current_status]];
  }
  $films = bluebells_get_films_sorted($args);
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
        <?php
          $s = get_film_status_label($film->ID);
          $sc = get_film_status_class($film->ID);
          if ( $s && in_array($sc, ['now-showing','coming-soon'], true) ):
        ?>
          <span class="film-poster-label <?php echo $sc; ?>"><?php echo esc_html($s); ?></span>
        <?php endif; ?>
      </div>
      <div class="film-info">
        <h2 class="film-name"><?php echo esc_html(get_film_main_title($film->ID)); ?></h2>
      </div>
    </article>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <p style="color:#999;font-size:15px;"><?php bbs_e('No movies found.'); ?></p>
  <?php endif; ?>
</section>

<!-- CTA -->
<section class="contact-section section-gray section-border-top">
  <div class="contact-inner">
    <span class="contact-label"><?php bbs_e('Partner With Us'); ?></span>
    <h2 class="contact-headline"><?php bbs_e('For acquisition or partnership inquiries'); ?></h2>
    <p class="contact-sub"><?php bbs_e('Contact the Bluebells Studios team for co-production, distribution rights, or brand partnerships.'); ?></p>
    <div class="contact-ctas">
      <a href="<?php echo home_url('/contact'); ?>" class="btn-primary"><?php bbs_e('Contact Us'); ?></a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
