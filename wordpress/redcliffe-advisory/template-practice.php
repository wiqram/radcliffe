<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php
/**
 * Template Name: Chair Advisory
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'practice.hero' ) ) : ?>
<section class="page-hero">
    <div class="container">
      <div class="crumb"><a href="<?php echo esc_url( rad_url( 'home' ) ); ?>">Home</a><span class="sep">·</span><span>Chair Advisory</span></div>
      <h1><?php rad_html( 'practice.hero.title' ); ?></h1>
      <div class="hero-subhead">A wise word</div>
      <p class="page-lede">Redcliffe Advisory advises companies with a global outlook — from conventional strategic counsel to forging the connections between Deep Tech, Finance and Defence.</p>
    </div>
  </section>
<?php endif; ?>
  <?php if ( rad_section_enabled( 'practice.intro' ) ) : ?>
<section class="section">
    <div class="container">
      <div class="prose reveal">
        <div class="prose-aside">On the discipline of quiet authority.</div>
        <div class="prose-body">
          <p class="lede">We work principally with Chairs and Chief Executives — at the point where geopolitical exposure, technological direction and board composition begin to ask the same question.</p>
          <p>Our work is measured in conversations rather than campaigns. We convene chairs, founders, ministers and investors who would not otherwise share a room, and hold the conversation long enough for something to be decided.</p>
          <p><em>ESG and a sensible Diversity &amp; Inclusion underpin all our work.</em> Through The Inclusion Initiative at the LSE — of which our CEO is Co-Founder — we apply behavioural science to the culture of City of London and Deep-Tech firms.</p>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'practice.areas' ) ) : ?>
<section class="section" style="padding-top:0;">
    <div class="container">
      <div class="section-head reveal">
        <div class="label">Practice areas</div>
        <h2>Four <em>practices</em> held in private</h2>
      </div>
      <div class="practice-list reveal">
        <div class="practice-item">
          <div class="pi-n">01</div>
          <h3>Chair &amp; CEO Advisory</h3>
          <p>Counsel to the principals who have the final say on succession, board composition and geopolitical exposure.</p>
        </div>
        <div class="practice-item">
          <div class="pi-n">02</div>
          <h3>Deep Tech &times; Finance</h3>
          <p>Forging connections between frontier laboratories — quantum, AI, defence and the bio-adjacent industries — and the sovereign, pension and primary capital that stands behind them.</p>
        </div>
        <div class="practice-item">
          <div class="pi-n">03</div>
          <h3>ESG as a strategic question</h3>
          <p>Held not as compliance but as foresight — underpinning every conversation we hold in board rooms, working groups and at the City Quantum &amp; AI Summit.</p>
        </div>
        <div class="practice-item">
          <div class="pi-n">04</div>
          <h3>The Redcliffe Advisory Salon</h3>
          <p>The City Quantum &amp; AI Summit and the working rooms around it — where the people who decide and the people who know close the distance between them.</p>
        </div>
      </div>

      <blockquote class="pullquote reveal">Connecting the world&rsquo;s most independent minds<span class="cite">Redcliffe Advisory in a single sentence</span></blockquote>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'practice.other' ) ) : ?>
<section class="other">
    <div class="container">
      <div class="other-head">
        <h3>Other <em>rooms</em></h3>
        <div class="small">Continue</div>
      </div>
      <div class="other-list reveal">
        <a class="other-row" href="<?php echo esc_url( rad_url( 'who' ) ); ?>"><span class="n">02</span><span class="t">Who&rsquo;s Who — <em>Karina Robinson</em></span><span class="d">Founder of The City Quantum &amp; AI Summit; champion of the City of London</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span class="n">01</span><span class="t">The City Quantum &amp; AI <em>Summit</em></span><span class="d">7 October 2026 — Mansion House.</span><span class="arr">→</span></a>
        <a class="other-row" href="<?php echo esc_url( rad_url( 'city' ) ); ?>"><span class="n">04</span><span class="t">The City &amp; <em>Our Quantum Future</em></span><span class="d">Where the City of London meets the laboratory</span><span class="arr">→</span></a>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php get_footer(); ?>
