<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr( rad_page_slug() ); ?>">
<?php wp_body_open(); ?>
<div class="mobile-bar">
    <a class="ghost" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span>Summit · 7 Oct</span><span class="arr">→</span></a>
    <a class="primary" href="<?php echo esc_url( rad_url( 'contact' ) ); ?>"><span>Enquire</span><span class="arr">↗</span></a>
  </div>

  <a class="announce" href="<?php echo esc_url( rad_url( 'agenda' ) ); ?>" aria-label="The City Quantum and AI Summit, 7 October 2026">
    <span class="a-dot" aria-hidden="true"></span>
    <span class="a-text"><?php rad_html( 'global.announcement.text' ); ?></span>
    <span class="a-cta"><?php rad_html( 'global.announcement.cta' ); ?></span>
  </a>

  <header class="header" id="header">
    <div class="container header-inner">
      <a class="brand" href="<?php echo esc_url( rad_url( 'home' ) ); ?>" aria-label="Redcliffe Advisory — home">
        <img class="brand-logo" src="<?php echo esc_url( get_theme_file_uri( 'images/redcliffe-logo.webp' ) ); ?>" width="132" height="37" alt="Redcliffe Advisory" />
      </a>
      <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-label="Open menu">
        <span>Menu</span><span class="lines" aria-hidden="true"><span></span><span></span></span>
      </button>
      <?php get_template_part( 'template-parts/navigation' ); ?>
    </div>
  </header>
