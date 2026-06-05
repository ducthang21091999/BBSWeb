<?php get_header(); ?>
<main style="padding:140px 48px 96px;max-width:900px;">
  <?php while(have_posts()): the_post(); ?>
    <h1 style="font-family:var(--font-display);font-size:clamp(32px,4vw,52px);font-weight:300;margin-bottom:40px;"><?php the_title(); ?></h1>
    <div style="font-size:14px;color:rgba(245,243,239,0.6);line-height:1.9;"><?php the_content(); ?></div>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>
