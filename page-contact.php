<?php
/**
 * Template for the Contact page (slug: contact).
 * Header + footer wrap the same contact section used on home.
 *
 * The H1 is visually hidden: the design leads with the slogan, but the page
 * still needs one heading that names its subject for search engines and
 * screen readers.
 */
get_header();
?>
<h1 class="sr-only"><?php bbs_e('Contact'); ?></h1>
<?php
get_template_part('template-parts/section-contact');
get_footer();
