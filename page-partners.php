<?php
/**
 * Template for the Partners page (slug: partners).
 * Intro copy comes from Settings → Site Content, so marketing can edit it
 * without touching the theme.
 */
get_header();
?>

<section class="section-pad section-dark">
  <div class="partners-header">
    <h1 class="section-heading"><?php bbs_e('Our Partners'); ?></h1>
  </div>
  <?php
    $intro = bbs_content(
        'partners_intro',
        'Bluebells Studios đồng hành cùng hệ thống rạp, đơn vị truyền thông, đối tác quốc tế và các thương hiệu trên từng dự án phim.',
        'Bluebells Studios works with cinema chains, media outlets, international partners and brands on every film we release.'
    );
    if ( $intro ):
  ?>
  <div class="partners-intro"><p><?php echo nl2br(esc_html($intro)); ?></p></div>
  <?php endif; ?>
</section>

<?php get_template_part('template-parts/section-partners', null, ['bg' => 'section-gray', 'grouped' => true, 'heading' => '']); ?>

<?php get_template_part('template-parts/section-contact', null, ['bg' => 'section-dark']); ?>

<?php get_footer(); ?>
