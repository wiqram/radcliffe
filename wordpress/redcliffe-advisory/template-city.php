<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: The City of London
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'city.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>" data-rad="city.hero.1nfzvhp"><?php rad_html( 'city.hero.1nfzvhp' ); ?></a><span class="sep">·</span><span data-rad="city.hero.16oxrst"><?php rad_html( 'city.hero.16oxrst' ); ?></span></div>
      <h1 data-rad="city.hero.title"><?php rad_html( 'city.hero.title' ); ?></h1>
      <div class="hero-subhead" data-rad="city.hero.1mk478i"><?php rad_html( 'city.hero.1mk478i' ); ?></div>
      <p class="page-lede" data-rad="city.hero.0q6nxlk"><?php rad_html( 'city.hero.0q6nxlk' ); ?></p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'city.intro' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="prose reveal">
        <div class="prose-aside" data-rad="city.intro.0js6m2f"><?php rad_html( 'city.intro.0js6m2f' ); ?></div>
        <div class="prose-body">
          <p class="lede" data-rad="city.intro.1up01fm"><?php rad_html( 'city.intro.1up01fm' ); ?></p>
          <p data-rad="city.intro.000xexc"><?php rad_html( 'city.intro.000xexc' ); ?></p>
          <p data-rad="city.intro.1o40mqw"><?php rad_html( 'city.intro.1o40mqw' ); ?></p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'city.quantum' ) ) : ?>
<section class="section on-navy">
    <div class="container">
      <div class="section-head reveal">
        <div class="label" data-rad="city.quantum.1bs01hk"><?php rad_html( 'city.quantum.1bs01hk' ); ?></div>
        <h2 data-rad="city.quantum.01gyo0j"><?php rad_html( 'city.quantum.01gyo0j' ); ?></h2>
      </div>
      <div class="summit-feature" style="border-color:var(--rule-d);">
        <div class="text-side" style="padding-right:56px;">
          <p style="font-family:var(--serif);font-size:19px;line-height:1.6;color:var(--cream);max-width:46ch;" data-rad="city.quantum.0hfqdl6"><?php rad_html( 'city.quantum.0hfqdl6' ); ?></p>
          <p style="font-family:var(--serif);font-size:19px;line-height:1.6;color:rgba(246,248,251,0.82);max-width:46ch;" data-rad="city.quantum.0zgeraa"><?php rad_html( 'city.quantum.0zgeraa' ); ?></p>
        </div>
        <div class="photo-side" style="border-left-color:var(--rule-d);background:#06101F;">
          <img src="<?php echo esc_url( rad_image_url( 'city.feature.photo' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'city.feature.photo' ) ); ?>" style="object-position:center 30%;" data-rad-img="city.feature.photo" />
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'city.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3 data-rad="city.other.1mx1n5x"><?php rad_html( 'city.other.1mx1n5x' ); ?></h3>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t" data-rad="city.other.0jt4grq"><?php rad_html( 'city.other.0jt4grq' ); ?></span><span class="d" data-rad="city.other.10qyuye"><?php rad_html( 'city.other.10qyuye' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t" data-rad="city.other.1ozkbpl"><?php rad_html( 'city.other.1ozkbpl' ); ?></span><span class="d" data-rad="city.other.0ng9skz"><?php rad_html( 'city.other.0ng9skz' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'practice' ) ); ?>"><span class="n">03</span><span class="t" data-rad="city.other.0011heh"><?php rad_html( 'city.other.0011heh' ); ?></span><span class="d" data-rad="city.other.0br3zvz"><?php rad_html( 'city.other.0br3zvz' ); ?></span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
