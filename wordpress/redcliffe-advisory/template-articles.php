<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Articles
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'articles.hero' ) ) : ?>
<section class="page-hero page-hero--split">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>" data-rad="articles.hero.1nfzvhp"><?php rad_html( 'articles.hero.1nfzvhp' ); ?></a><span class="sep">·</span><span data-rad="articles.hero.05ous6t"><?php rad_html( 'articles.hero.05ous6t' ); ?></span></div>
      <div class="hero-split">
      <h1 data-rad="articles.hero.title"><?php rad_html( 'articles.hero.title' ); ?></h1>
      <p class="page-lede" data-rad="articles.hero.0jp2c80"><?php rad_html( 'articles.hero.0jp2c80' ); ?></p>
      </div>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'articles.journal' ) ) : ?>
<?php if ( rad_has_articles() ) : ?>
<section class="section rad-articles-dynamic">
<div class="container">
<?php get_template_part( 'template-parts/journal' ); ?>
</div>
</section>
<?php else : ?>
<section class="section">
    <div class="container">
      <article class="journal-feature reveal">
        <div class="photo"><img src="<?php echo esc_url( rad_image_url( 'articles.hero.photo' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'articles.hero.photo' ) ); ?>" loading="lazy" data-rad-img="articles.hero.photo" /></div>
        <div class="meta">
          <div class="kicker">Featured report · 2025</div>
          <h3>Race for Growth: the levers Britain still holds</h3>
          <p>On why the UK remains home to the second-largest number of quantum start-ups in the world — and what is required of the City of London, government and the sovereign capital around them.</p>
          <div class="byline">Karina Robinson · 2025</div>
        </div>
      </article>

      <div class="section-head reveal" style="margin-bottom:40px;">
        <div class="label">The index</div>
        <h2>Recent <em>writing</em></h2>
      </div>
      <div class="journal-list reveal">
        <a class="journal-item" href="#"><div class="kicker">The Quantum Insider</div><h4>Connections in Chaos: why the Sixth Summit chose its theme.</h4><div class="byline">The Editors · 2026</div></a>
        <a class="journal-item" href="#"><div class="kicker">Karina&rsquo;s Column</div><h4>Deep Tech meets Finance — a translation problem, not a funding one.</h4><div class="byline">Karina Robinson · 2025</div></a>
        <a class="journal-item" href="#"><div class="kicker">Karina&rsquo;s Column</div><h4>From the Inclusion Initiative: what behavioural science teaches the City of London.</h4><div class="byline">Karina Robinson, LSE · 2025</div></a>
        <a class="journal-item" href="#"><div class="kicker">Interview</div><h4>Inside the lab rewriting cryptographic assumptions.</h4><div class="byline">In conversation · 2025</div></a>
        <a class="journal-item" href="#"><div class="kicker">Karina&rsquo;s Column</div><h4>A sensible case for Diversity &amp; Inclusion in the City of London.</h4><div class="byline">Karina Robinson · 2025</div></a>
        <a class="journal-item" href="#"><div class="kicker">The Quantum Insider</div><h4>Quantum &amp; sovereign capital: the questions Boards should be asking.</h4><div class="byline">Redcliffe Advisory · 2026</div></a>
      </div>
    </div>
  </section>
<?php endif; ?>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'articles.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3 data-rad="articles.other.1mx1n5x"><?php rad_html( 'articles.other.1mx1n5x' ); ?></h3>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t" data-rad="articles.other.0jt4grq"><?php rad_html( 'articles.other.0jt4grq' ); ?></span><span class="d" data-rad="articles.other.10qyuye"><?php rad_html( 'articles.other.10qyuye' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t" data-rad="articles.other.1ozkbpl"><?php rad_html( 'articles.other.1ozkbpl' ); ?></span><span class="d" data-rad="articles.other.0ng9skz"><?php rad_html( 'articles.other.0ng9skz' ); ?></span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'city' ) ); ?>"><span class="n">04</span><span class="t" data-rad="articles.other.0pa6spk"><?php rad_html( 'articles.other.0pa6spk' ); ?></span><span class="d" data-rad="articles.other.1ngcrxc"><?php rad_html( 'articles.other.1ngcrxc' ); ?></span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
