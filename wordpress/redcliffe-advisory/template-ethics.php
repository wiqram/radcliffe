<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Ethics
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'ethics.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>">Home</a><span class="sep">·</span><span>Ethics</span></div>
      <h1><?php rad_html( 'ethics.hero.title' ); ?></h1>
      <p class="page-lede">Influence, properly conducted, is a form of trust held over time. These are the principles by which the practice is held.</p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'ethics.principles' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="prose reveal">
        <div class="prose-aside">How we hold the work.</div>
        <div class="prose-body">
          <p class="lede">We are engaged through introduction rather than enquiry — and we keep our counsel, and our clients&rsquo;, in confidence.</p>
          <p>Our advice is independent. We do not accept a brief whose conclusion is already written, and we decline work where a conflict cannot be managed honestly. What we offer is judgement, access and continuity — not advocacy for hire.</p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'ethics.commitments' ) ) : ?>
<section class="section" style="padding-top:0;">
    <div class="container">
      <div class="section-head reveal">
        <div class="label">Principles</div>
        <h2>Three <em>commitments</em></h2>
      </div>
      <div class="practice-list reveal">
        <div class="practice-item"><div class="pi-n">01</div><h3>Discretion</h3><p>Conversations are held in confidence by default. We work quietly, and we do not publish a list of clients.</p></div>
        <div class="practice-item"><div class="pi-n">02</div><h3>Independence</h3><p>Our judgement is our own. We are candid with principals, including when candour is unwelcome.</p></div>
        <div class="practice-item"><div class="pi-n">03</div><h3>Stewardship</h3><p>We take a long view — of institutions, of technology, and of the public interest that surrounds both.</p></div>
      </div>

      <blockquote class="pullquote reveal">It is easier to see the game from the sidelines. Redcliffe Advisory offers a different perspective.<span class="cite">Redcliffe Advisory · on ethics</span></blockquote>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'ethics.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3>Other <em>rooms</em></h3>
        <div class="small">Continue</div>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t">Who&rsquo;s Who — <em>Karina Robinson</em></span><span class="d">Founder of The City Quantum &amp; AI Summit; champion of the City of London</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'practice' ) ); ?>"><span class="n">03</span><span class="t">Chair &amp; CEO <em>Counsel</em></span><span class="d">Counsel for Chairs and Chief Executives</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t">The City Quantum &amp; AI <em>Summit</em></span><span class="d">7 October 2026 — Mansion House.</span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php rad_extra_sections(); ?>


<?php get_footer(); ?>
