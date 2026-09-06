/**
 * Field metadata for the WordPress theme's Customizer.
 *
 * The keys mirror the `data-cms-key`, `data-cms-image` and `data-cms-section`
 * attributes in the HTML pages, so the WordPress theme exposes exactly the same
 * editable content as the Node CMS it replaces. The default *values* are not
 * listed here — the build script reads them straight out of the HTML, so the
 * Customizer always starts from what the design actually says.
 */

// Which page each HTML file becomes in WordPress.
const PAGES = [
  { file: 'Homepage.html', slug: 'home', title: 'Home', template: 'front-page.php', isFront: true },
  { file: 'Practice.html', slug: 'practice', title: 'Chair Advisory', template: 'template-practice.php' },
  { file: 'Whos-Who.html', slug: 'who', title: 'Who’s Who', template: 'template-who.php' },
  { file: 'The-City.html', slug: 'city', title: 'The City of London', template: 'template-city.php' },
  { file: 'Summit.html', slug: 'summit', title: 'Summit', template: 'template-summit.php' },
  // The programme is edited as blocks: the page's content replaces this
  // section when it has any, and is seeded from the HTML on first run.
  { file: 'Agenda.html', slug: 'agenda', title: 'Agenda', template: 'template-agenda.php', blockSection: 'agenda.programme' },
  { file: 'Articles.html', slug: 'articles', title: 'Articles', template: 'template-articles.php' },
  { file: 'Ethics.html', slug: 'ethics', title: 'Ethics', template: 'template-ethics.php' },
  { file: 'Contact.html', slug: 'contact', title: 'Contact', template: 'template-contact.php' },
];

// The primary navigation, used as the menu created on activation and as the
// fallback when no menu is assigned.
const NAV = [
  { slug: 'practice', label: 'Chair Advisory' },
  { slug: 'who', label: 'Who’s Who' },
  { slug: 'city', label: 'The City of London' },
  { slug: 'summit', label: 'Summit' },
  { slug: 'articles', label: 'Articles' },
  { slug: 'ethics', label: 'Ethics' },
  { slug: 'contact', label: 'Enquire', classes: 'cta' },
];

/**
 * Customizer layout. Each entry becomes one section under the "Redcliffe
 * Advisory" panel, in this order.
 *   text     — rich text fields (a limited set of inline HTML is allowed)
 *   images   — image pickers
 *   toggles  — show/hide switches for the page's sections
 */
const PANELS = [
  {
    id: 'global',
    title: 'Top of every page',
    description: 'The announcement strip and the logo, which appear on every page.',
    text: [
      ['global.announcement.text', 'Announcement text'],
      ['global.announcement.cta', 'Announcement link text'],
    ],
    images: [],
    toggles: [['global.announcement', 'the announcement strip']],
  },
  {
    id: 'home',
    title: 'Homepage',
    text: [
      ['home.hero.eyebrow', 'Small line above the headline'],
      ['home.hero.title', 'Headline'],
      ['home.hero.lede', 'Opening paragraph'],
      ['home.testimonial.1.quote', 'Testimonial quote'],
      ['home.testimonial.1.attribution', 'Testimonial attribution'],
    ],
    images: [
      ['home.hero.portrait', 'Portrait photograph'],
      ['home.entry.summit', 'Summit image'],
      ['home.entry.who', 'Profile image'],
    ],
    toggles: [
      ['home.hero', 'Hero'],
      ['home.rooms', 'Six rooms index'],
      ['home.testimonials', 'Testimonials'],
    ],
  },
  {
    id: 'practice',
    title: 'Chair Advisory page',
    text: [['practice.hero.title', 'Page headline']],
    images: [],
    toggles: [
      ['practice.hero', 'Hero'],
      ['practice.intro', 'Introduction'],
      ['practice.areas', 'Practice areas'],
      ['practice.other', 'Other rooms'],
    ],
  },
  {
    id: 'who',
    title: 'Who’s Who page',
    text: [['who.hero.title', 'Page headline']],
    images: [['who.hero.photo', 'Hero photograph']],
    toggles: [
      ['who.hero', 'Hero'],
      ['who.profile', 'Profile'],
      ['who.other', 'Other rooms'],
    ],
  },
  {
    id: 'city',
    title: 'The City of London page',
    text: [['city.hero.title', 'Page headline']],
    images: [['city.feature.photo', 'Feature photograph']],
    toggles: [
      ['city.hero', 'Hero'],
      ['city.intro', 'Introduction'],
      ['city.quantum', 'Quantum future'],
      ['city.other', 'Other rooms'],
    ],
  },
  {
    id: 'summit',
    title: 'Summit page',
    text: [
      ['summit.hero.title', 'Page headline'],
      ['summit.hero.sub', 'Subtitle'],
      ['summit.gallery.1.caption', 'Gallery photo 1 caption'],
      ['summit.gallery.2.caption', 'Gallery photo 2 caption'],
      ['summit.gallery.3.caption', 'Gallery photo 3 caption'],
      ['summit.gallery.4.caption', 'Gallery photo 4 caption'],
      ['summit.gallery.5.caption', 'Gallery photo 5 caption'],
      ['summit.gallery.6.caption', 'Gallery photo 6 caption'],
    ],
    images: [
      ['summit.hero.medallion', 'Medallion'],
      ['summit.feature.photo', 'Feature photograph'],
      ['summit.gallery.1', 'Gallery photo 1 (wide)'],
      ['summit.gallery.2', 'Gallery photo 2 (tall)'],
      ['summit.gallery.3', 'Gallery photo 3'],
      ['summit.gallery.4', 'Gallery photo 4'],
      ['summit.gallery.5', 'Gallery photo 5'],
      ['summit.gallery.6', 'Gallery photo 6 (full width)'],
    ],
    toggles: [
      ['summit.hero', 'Hero'],
      ['summit.feature', 'Feature'],
      ['summit.audience', 'Audience'],
      ['summit.collaborators', 'Collaborators'],
      ['summit.gallery', 'Gallery'],
      ['summit.other', 'Other rooms'],
    ],
  },
  {
    id: 'agenda',
    title: 'Agenda page',
    text: [['agenda.hero.title', 'Page headline']],
    images: [],
    toggles: [
      ['agenda.hero', 'Hero'],
      ['agenda.programme', 'Programme'],
    ],
  },
  {
    id: 'articles',
    title: 'Articles page',
    text: [['articles.hero.title', 'Page headline']],
    images: [['articles.hero.photo', 'Hero photograph']],
    toggles: [
      ['articles.hero', 'Hero'],
      ['articles.journal', 'Journal'],
      ['articles.other', 'Other rooms'],
    ],
  },
  {
    id: 'ethics',
    title: 'Ethics page',
    text: [['ethics.hero.title', 'Page headline']],
    images: [],
    toggles: [
      ['ethics.hero', 'Hero'],
      ['ethics.principles', 'Principles'],
      ['ethics.commitments', 'Commitments'],
      ['ethics.other', 'Other rooms'],
    ],
  },
  {
    id: 'contact',
    title: 'Contact page',
    text: [
      ['contact.hero.title', 'Page headline'],
      ['contact.hero.lede', 'Opening paragraph'],
      ['contact.details.email', 'Email address shown on the page'],
      ['contact.details.location', 'Location shown on the page'],
      ['contact.details.response', 'Note about responses'],
    ],
    images: [],
    toggles: [
      ['contact.hero', 'Hero'],
      ['contact.form', 'Contact form'],
    ],
    // Not present in the HTML, so it needs an explicit default.
    extra: [
      {
        key: 'contact.recipient.email',
        label: 'Send enquiries to',
        description: 'Contact form messages are emailed to this address.',
        type: 'email',
        default: 'karina.robinson@redcliffeadvisory.com',
      },
    ],
  },
  {
    id: 'footer',
    title: 'Bottom of every page',
    description: 'The footer that appears on every page.',
    text: [],
    images: [],
    toggles: [],
  },
];

module.exports = { PAGES, NAV, PANELS };
