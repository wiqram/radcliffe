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
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>">Home</a><span class="sep">·</span><span>Articles</span></div>
      <h1><?php rad_html( 'articles.hero.title' ); ?></h1>
      <p class="page-lede">A reading list for the people shaping the next decade — Karina&rsquo;s Column, dispatches for The Quantum Insider, and on-the-record conversations, published under the Redcliffe Advisory name.</p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'articles.journal' ) ) : ?>
<section class="section">
    <div class="container">
      <article class="journal-feature reveal">
        <div class="photo"><img src="<?php echo esc_url( rad_image_url( 'articles.hero.photo' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'articles.hero.photo' ) ); ?>" loading="lazy" /></div>
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

  <?php if ( rad_section_enabled( 'articles.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3>Other <em>rooms</em></h3>
        <div class="small">Continue</div>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t">The City Quantum &amp; AI <em>Summit</em></span><span class="d">7 October 2026 — Mansion House.</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t">Who&rsquo;s Who — <em>Karina Robinson</em></span><span class="d">Founder of The City Quantum &amp; AI Summit; champion of the City of London</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'city' ) ); ?>"><span class="n">04</span><span class="t">The City &amp; <em>Our Quantum Future</em></span><span class="d">Where the City of London meets the laboratory</span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php get_footer(); ?>
