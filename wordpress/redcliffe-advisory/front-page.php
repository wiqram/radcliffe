<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run `npm run build:wp` to regenerate.
 */
?>
<?php get_header(); ?>

<?php if ( rad_section_enabled( 'home.hero' ) ) : ?>
<section class="hero">
    <div class="container hero-grid">
      <div class="hero-left">
        <div class="eyebrow"><?php rad_html( 'home.hero.eyebrow' ); ?></div>
        <h1 class="display"><?php rad_html( 'home.hero.title' ); ?></h1>
        <p class="lede"><?php rad_html( 'home.hero.lede' ); ?></p>
        <div class="cta-row">
          <a class="btn" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>"><span>The Summit · 7 Oct 2026</span><span class="arr">→</span></a>
          <a class="btn-link" href="<?php echo esc_url( rad_url( 'contact' ) ); ?>"><span>Write to Karina</span><span class="arr">↗</span></a>
        </div>
        <div class="hero-facts">
          <span><b>Chair &amp; CEO</b> Counsel</span>
          <span class="dot" aria-hidden="true"></span>
          <span><b>City Quantum &amp; AI</b> Summit</span>
          <span class="dot" aria-hidden="true"></span>
          <span><b>London</b> &amp; Madrid</span>
        </div>
      </div>
      <figure class="hero-portrait">
        <div class="hp-card">
          <div class="hp-frame">
            <img src="<?php echo esc_url( rad_image_url( 'home.hero.portrait' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'home.hero.portrait' ) ); ?>" />
          </div>
          <figcaption class="hp-cap">
            <span class="nm">Karina Robinson</span>
            <span class="rl">CEO · Redcliffe Advisory</span>
          </figcaption>
        </div>
      </figure>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'home.rooms' ) ) : ?>
<section class="index-section">
    <div class="container">
      <div class="index-head reveal">
        <h2>The practice,<br />in <em>six rooms</em></h2>
        <div class="small">London &amp; Madrid · MMXXVI</div>
      </div>
      <div class="entries">

        <a class="entry reveal" href="<?php echo esc_url( rad_url( 'summit' ) ); ?>">
          <div class="e-num">01</div>
          <div class="e-meta">
            <div class="e-kicker">The Convening</div>
            <div class="e-title">The City Quantum &amp; AI <em>Summit</em></div>
            <div class="e-dek">The Sixth Anniversary Summit — 7 October 2026, at Mansion House. Frontier science and the City of London, in one room. No lingo, no jargon.</div>
            <span class="e-open"><span>Open</span><span class="arr">→</span></span>
          </div>
          <div class="e-photo"><img src="<?php echo esc_url( rad_image_url( 'home.entry.summit' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'home.entry.summit' ) ); ?>" loading="lazy" /></div>
        </a>

        <a class="entry reveal" href="<?php echo esc_url( rad_url( 'practice' ) ); ?>">
          <div class="e-num">02</div>
          <div class="e-meta">
            <div class="e-kicker">The Practice</div>
            <div class="e-title">Chair &amp; CEO <em>Counsel</em></div>
            <div class="e-dek">Counsel for the principals who have the final say — and the bridge between Deep Tech, Finance and Defence.</div>
            <span class="e-open"><span>Open</span><span class="arr">→</span></span>
          </div>
          <div class="e-photo"><img src="<?php echo esc_url( get_theme_file_uri( 'images/city-interior.webp' ) ); ?>" alt="A City of London interior" loading="lazy" /></div>
        </a>

        <a class="entry portrait reveal" href="<?php echo esc_url( rad_url( 'who' ) ); ?>">
          <div class="e-num">03</div>
          <div class="e-meta">
            <div class="e-kicker">Who&rsquo;s Who</div>
            <div class="e-title">Karina Robinson — a <em>benign disruptor</em></div>
            <div class="e-dek">CEO of Redcliffe Advisory. Founder of The City Quantum &amp; AI Summit. Champion of the City of London.</div>
            <span class="e-open"><span>Open</span><span class="arr">→</span></span>
          </div>
          <div class="e-photo"><img src="<?php echo esc_url( rad_image_url( 'home.entry.who' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'home.entry.who' ) ); ?>" loading="lazy" /></div>
        </a>

        <a class="entry reveal" href="<?php echo esc_url( rad_url( 'city' ) ); ?>">
          <div class="e-num">04</div>
          <div class="e-meta">
            <div class="e-kicker">The City of London</div>
            <div class="e-title">The City &amp; <em>Our Quantum Future</em></div>
            <div class="e-dek">Of the City of London by temperament — and the point at which the Square Mile meets the laboratory.</div>
            <span class="e-open"><span>Open</span><span class="arr">→</span></span>
          </div>
          <div class="e-photo"><img src="<?php echo esc_url( get_theme_file_uri( 'images/city-at-dusk.webp' ) ); ?>" alt="The City of London at dusk" loading="lazy" /></div>
        </a>

        <a class="entry reveal" href="<?php echo esc_url( rad_url( 'articles' ) ); ?>">
          <div class="e-num">05</div>
          <div class="e-meta">
            <div class="e-kicker">Articles</div>
            <div class="e-title">Race for Growth, &amp; <em>other long reads</em></div>
            <div class="e-dek">Karina&rsquo;s Column, The Quantum Insider and on-the-record conversations, published under the Redcliffe Advisory name.</div>
            <span class="e-open"><span>Open</span><span class="arr">→</span></span>
          </div>
          <div class="e-photo"><img src="<?php echo esc_url( get_theme_file_uri( 'images/working-desk.webp' ) ); ?>" alt="A working desk with papers" loading="lazy" /></div>
        </a>

        <a class="entry reveal" href="<?php echo esc_url( rad_url( 'ethics' ) ); ?>">
          <div class="e-num">06</div>
          <div class="e-meta">
            <div class="e-kicker">Ethics</div>
            <div class="e-title">Ethics &amp; <em>independence</em></div>
            <div class="e-dek">The principles by which the practice is held — discretion, independence and stewardship.</div>
            <span class="e-open"><span>Open</span><span class="arr">→</span></span>
          </div>
          <div class="e-photo"><img src="<?php echo esc_url( get_theme_file_uri( 'images/architectural-detail.webp' ) ); ?>" alt="Architectural detail" loading="lazy" /></div>
        </a>

      </div>
    </div>
  </section>
<?php endif; ?>

  <?php if ( rad_section_enabled( 'home.testimonials' ) ) : ?>
<section class="test">
    <div class="container">
      <blockquote class="test-quote" id="t-quote"><?php rad_html( 'home.testimonial.1.quote' ); ?></blockquote>
      <div class="test-attrib" id="t-attrib"><?php rad_html( 'home.testimonial.1.attribution' ); ?></div>
      <div class="test-controls">
        <button id="t-prev" aria-label="Previous">‹</button>
        <span id="t-count">01 / 04</span>
        <button id="t-next" aria-label="Next">›</button>
      </div>
    </div>
  </section>
<?php endif; ?>
  <script>
    window.__TESTIMONIALS = [
      {q:'Karina Robinson is, in the best sense, a benign disruptor. Redcliffe Advisory arrive with the people, the patience and the questions — not with a thesis.', name:'A City Chair', org:'Under Chatham House rule'},
      {q:'The Summit is the only convening I attend where physicists, naval officers, treasurers and founders speak to each other without an interpreter.', name:'A NATO advisor', org:'Quantum Strategy working group'},
      {q:'Clear language. Gender balance. Accessible pricing. A standard of room that everyone else now quietly tries to copy.', name:'A sovereign fund principal', org:'Mansion House · returning delegate'},
      {q:'Redcliffe Advisory connects worlds that, on paper, do not share a vocabulary — and somehow leaves them having decided things.', name:'A frontier-technology CEO', org:'European Innovation Council portfolio'}
    ];
  </script>

<?php get_footer(); ?>
