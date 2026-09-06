<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Contact
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'contact.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>" data-rad="contact.hero.1nfzvhp"><?php rad_html( 'contact.hero.1nfzvhp' ); ?></a><span class="sep">·</span><span data-rad="contact.hero.1w6w2ua"><?php rad_html( 'contact.hero.1w6w2ua' ); ?></span></div>
      <h1 data-rad="contact.hero.title"><?php rad_html( 'contact.hero.title' ); ?></h1>
      <p class="page-lede" data-rad="contact.hero.lede"><?php rad_html( 'contact.hero.lede' ); ?></p>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'contact.form' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="contact-layout reveal">
        <aside class="contact-panel">
          <div class="label" data-rad="contact.form.159ghb4"><?php rad_html( 'contact.form.159ghb4' ); ?></div>
          <dl>
            <div>
              <dt data-rad="contact.form.17w067i"><?php rad_html( 'contact.form.17w067i' ); ?></dt>
              <dd data-rad="contact.details.email"><?php rad_html( 'contact.details.email' ); ?></dd>
            </div>
            <div>
              <dt data-rad="contact.form.1sy0ftr"><?php rad_html( 'contact.form.1sy0ftr' ); ?></dt>
              <dd data-rad="contact.details.location"><?php rad_html( 'contact.details.location' ); ?></dd>
            </div>
            <div>
              <dt data-rad="contact.form.0mjc61t"><?php rad_html( 'contact.form.0mjc61t' ); ?></dt>
              <dd data-rad="contact.details.response"><?php rad_html( 'contact.details.response' ); ?></dd>
            </div>
          </dl>
        </aside>

        <?php get_template_part( 'template-parts/contact-form' ); ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
