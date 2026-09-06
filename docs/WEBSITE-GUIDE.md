# Looking after the Redcliffe Advisory website

**This guide is for whoever looks after the website day to day. You do not need
any technical knowledge.** Everything is done in a web browser by clicking and
typing. Nothing you do by following this guide can break the design.

This same guide is available inside the website's dashboard, under **Website
guide** in the left-hand menu, so you never need to find this file again.

---

## 1. Logging in

The website is hosted by **Hostinger**, and it runs on **WordPress**. WordPress
is the program you use to edit the website. You will only ever need two
addresses:

| Where | Address | What it is for |
| --- | --- | --- |
| **Hostinger** | https://hpanel.hostinger.com | Backups, the domain name, and the button that takes you into WordPress |
| **WordPress** | `https://your-website-address/wp-admin` | Editing the website: words, pictures, pages, enquiries |

**The easy way in:**

1. Go to https://hpanel.hostinger.com and log in with the Hostinger account.
2. Click **Websites** in the left-hand menu.
3. Next to the website, click the **WP Admin** button.

That opens the WordPress dashboard without asking for another password.

**The direct way in:** go to `https://your-website-address/wp-admin` and enter
the WordPress username and password. If you have forgotten the password, click
**Lost your password?** on that screen and follow the email.

**You should see:** the WordPress dashboard, with a dark menu down the
left-hand side. Near the bottom of that menu is **Website guide**, which is this
document.

---

## 2. The three places you will use

Almost everything you do lives in one of three places in the left-hand menu.

| I want to… | Go to |
| --- | --- |
| Change the headline, opening text or a photograph in a part of the website that is already there | **Appearance → Customize** |
| **Add something new** to a page: a new section of text, a new photograph, a gallery, a quotation | **Pages** |
| Read the messages sent through the contact form | **Enquiries** |

The rest of this guide walks through each one.

---

## 3. Changing the words and pictures that are already there

The nine pages of the website (Home, Chair Advisory, Who's Who, The City of
London, Summit, Agenda, Articles, Ethics, Contact) have a fixed design. The
words and photographs inside that design are changed in one place:

1. In the left-hand menu, click **Appearance**, then **Customize**.
2. Click **Redcliffe Advisory**.
3. Click the page you want to change, for example **Homepage**.
4. Change the text in the boxes, or click **Change image** under a photograph
   and pick a new one.
5. Watch the preview on the right update as you type.
6. Click the blue **Publish** button at the top when you are happy.

Three useful things to know:

- **Clearing a box puts the original wording back.** Nothing can be left blank
  by accident.
- **Each page has switches** to show or hide whole sections of the page (for
  example, hiding the testimonials on the homepage). Flick the switch and click
  **Publish**.
- **Nothing changes on the live website until you click Publish.** If you get
  in a muddle, close the Customizer without publishing and nothing has
  happened.

---

## 4. Adding a new section to a page

Every page has room underneath its designed part for as many new sections as
you like. This is where you add news, a new photograph, a gallery of pictures
from an event, a quotation, or anything else.

1. In the left-hand menu, click **Pages**.
2. Hover over the page you want to add to, and click **Edit**.
3. You will see a mostly empty page with the page's name at the top. **That is
   normal.** The designed part of the page is not shown here. A blue note at the
   top reminds you that whatever you add here appears at the **bottom** of the
   page on the website.
4. Click the **+** button (top left, or in the middle of the page).
5. Click the **Patterns** tab, then choose **Redcliffe Advisory** from the list.
   You will see ready-made sections that match the design of the website:

   | Pattern | What it gives you |
   | --- | --- |
   | **Heading and text** | A small label, a large heading and a few paragraphs, like the sections already on the site |
   | **Photograph with caption** | One big photograph with a line of text underneath |
   | **Text beside a photograph** | A photograph on one side, heading and text on the other |
   | **Photo gallery** | A grid of photographs |
   | **Quotation** | A large quotation with the name of the person who said it |
   | **Two columns of text** | Two short pieces of text side by side |
   | **Button** | A button that links to another page |

6. Click the one you want. It appears on the page filled with example text and
   pictures.
7. Click on any text and type over it. Click on any picture and press
   **Replace** to swap it for one of your own (see the next section).
8. Click the blue **Update** button at the top right.
9. Click **View page** in the message that appears, and scroll to the bottom of
   the page to see your new section on the live website.

**You can add as many sections as you like.** Press **+** again to add another
underneath.

**To move a section up or down:** click on it, then use the **up and down
arrows** in the small toolbar that appears above it.

**To remove a section:** click on it, click the **three dots** at the right-hand
end of its toolbar, and choose **Delete**. Then click **Update**.

**To undo a mistake:** press the **undo arrow** at the very top left of the
editor (or press Ctrl+Z, or Cmd+Z on a Mac). If you have already clicked
Update, see [Undoing a published change](#8-undoing-a-published-change).

---

## 5. Adding or replacing a photograph

### In a new section

Any picture in a pattern from the previous section can be replaced:

1. Click on the picture.
2. In the toolbar above it, click **Replace**.
3. Choose **Upload** to use a photograph from your computer, or **Open Media
   Library** to use one that is already on the website.
4. Once the picture is in place, click **Update** at the top right.

To add a picture on its own, press **+**, type **Image**, and choose the
**Image** block. Then click **Upload**.

### In the designed part of a page

Photographs that are part of the design (the portrait on the homepage, the
Summit pictures, and so on) are changed in **Appearance → Customize**, as
described in section 3. Click **Change image** under the photograph you want to
replace.

### A few tips about photographs

- **Size:** photographs straight from a phone or camera are fine. WordPress
  makes smaller copies automatically. Very large files (over 10 MB) may be
  refused; if so, email the picture to yourself first, choosing a smaller size.
- **Shape:** the design uses landscape (wider than tall) photographs in most
  places. Portrait photographs work but may be cropped.
- **Describe the picture:** after adding a picture, there is a box on the
  right called **Alternative text**. Type a short description, such as
  "Karina Robinson speaking at Mansion House". This is read aloud to visitors
  who cannot see the picture, and helps Google.
- **All pictures are kept** under **Media** in the left-hand menu. You can
  reuse any of them on any page.

---

## 6. The menu along the top

1. In the left-hand menu, click **Appearance**, then **Menus**.
2. Drag items up and down to reorder them, or click the small arrow on an item
   to rename or remove it.
3. To add a page to the menu, tick it in the **Pages** box on the left and
   click **Add to Menu**.
4. Click **Save Menu**.

The gold **Enquire** button at the end of the menu is just an ordinary link
with a special marking. If it ever loses its gold colour: click the arrow on
that item, find the box called **CSS Classes** and type `cta` into it, then
**Save Menu**. (If you cannot see a CSS Classes box, click **Screen Options**
at the very top right of the screen and tick **CSS Classes**.)

---

## 7. Enquiries from the contact form

Every message sent through the Contact page is saved on the website **and**
emailed to you.

- **To read messages:** click **Enquiries** in the left-hand menu. Each one
  shows the sender's name, email address and topic. Click a name to read the
  full message.
- **To reply:** press **Reply** on the email you received. It goes straight
  back to the person who wrote it.
- **To change which email address receives the messages:** go to
  **Appearance → Customize → Redcliffe Advisory → Contact page**, change the
  address under **Send enquiries to**, and click **Publish**.

If a message is listed under Enquiries but no email arrived, check your spam
folder first. If emails never arrive, see [If something looks
wrong](#12-if-something-looks-wrong). The messages are safe under Enquiries
either way.

---

## 8. Undoing a published change

Every time you click **Update** on a page, WordPress keeps the old version.

1. Open the page under **Pages → Edit**.
2. On the right-hand side, under **Page**, click **Revisions**.
3. Drag the slider at the top to go back in time. The screen shows what
   changed.
4. Click **Restore This Revision**, then **Update**.

Changes made in **Appearance → Customize** do not keep old versions, but
clearing a box always puts the original wording back.

If the whole website needs to go back to an earlier day, use a Hostinger
backup — see the next section.

---

## 9. Backups

Hostinger takes a copy of the whole website automatically, every week. You do
not need to do anything for this to happen.

**To take a backup yourself** (a good idea before making a lot of changes):

1. Log in at https://hpanel.hostinger.com and click **Websites**.
2. Click **Dashboard** next to the website.
3. In the left-hand menu, click **Files**, then **Backups**.
4. Click **Generate new backup**.

**To go back to a backup:**

1. On the same **Backups** screen, choose the date you want under **Restore**.
2. Click **Restore** and confirm.

This puts the whole website back exactly as it was on that day, including any
messages, pages and pictures added since. Use it only when something has gone
badly wrong.

---

## 10. Keeping the website up to date

WordPress itself gets small updates from time to time. Hostinger installs
these for you automatically. You do not need to do anything.

If you ever see a number in a red circle next to **Updates** in the left-hand
menu, it is safe to click **Updates** and then **Update Now**. Take a backup
first if you want to be extra careful (section 9).

**Do not install extra plugins or themes unless someone technical has asked you
to.** The website does not need any, and each one is a possible way for things
to go wrong.

---

## 11. Putting a new version of the design live

Sometimes a designer or developer will give you a new version of the website's
design. It always arrives as **one file** called `redcliffe-advisory.zip`. You
do not unzip it. To put it live:

1. Save the zip file somewhere you can find it, such as your Desktop.
2. In WordPress, click **Appearance**, then **Themes**.
3. Click **Add New Theme** at the top (on some versions it says **Add New**).
4. Click **Upload Theme**.
5. Click **Choose File**, pick `redcliffe-advisory.zip`, and click
   **Install Now**.
6. WordPress notices that the design is already installed and shows you a
   comparison of the two versions. Click **Replace active with uploaded**.
7. Wait a few seconds.

**You should see:** a message saying the theme was installed successfully.
Open the website and check it looks right.

**What is kept:** all your words, pictures, extra sections, menu changes and
enquiries. A new version of the design never touches those.

**If you are unsure whether to do this:** take a backup first (section 9). If
anything looks wrong afterwards, restore that backup.

---

## 12. Pointing redcliffeadvisory.com at the website

Until this is done, the website lives at its temporary Hostinger address, and
the public still sees the old website. Do this only once you are happy with
the new one.

1. Log in at https://hpanel.hostinger.com.
2. Click **Websites**, then **Dashboard** next to the website.
3. In the left-hand menu, click **Domains** (or look for **Change domain** /
   **Connect domain** on the dashboard).
4. Choose **Connect an existing domain** and enter `redcliffeadvisory.com`.
5. Hostinger will tell you what to do next. If the domain is already with
   Hostinger, it connects on its own. If it is with another company, Hostinger
   shows you two or three settings (called **nameservers** or **DNS records**)
   to enter at that company. Their support desk can do this for you if you
   send them the settings.
6. Once connected, Hostinger sets up the padlock (HTTPS) automatically. There
   is nothing to buy.

It can take anywhere from a few minutes to a day for the change to show
everywhere. If in doubt, Hostinger's live chat (bottom right of hPanel) will
check it for you.

Afterwards, in WordPress go to **Settings → General** and make sure both
address boxes show `https://www.redcliffeadvisory.com`.

---

## 13. If something looks wrong

| What you see | What it means | What to do |
| --- | --- | --- |
| A new section does not appear on the website | It was not published | Open the page under **Pages → Edit** and click **Update** |
| A new section appears but looks unstyled | The browser is showing an old copy | Press Ctrl+F5 (Cmd+Shift+R on a Mac) to reload. If it persists, in WordPress hover **LiteSpeed Cache** in the top bar and click **Purge All** |
| Pages look plain, with no colours or fonts | The design is not switched on | **Appearance → Themes** → click **Activate** on **Redcliffe Advisory** |
| The menu at the top is empty | The menu was unassigned | **Appearance → Menus** → at the bottom tick **Primary menu** → **Save Menu** |
| A menu link goes to the homepage | That page was deleted | **Pages → Trash** → hover the page → **Restore**. Never delete the nine main pages |
| The homepage shows a list of blog posts | The front page setting changed | **Settings → Reading** → *Your homepage displays: A static page* → Homepage: **Home** → **Save Changes** |
| Enquiries arrive in WordPress but no email comes | Email sending is off | Check spam first. Then ask Hostinger live chat to "enable email sending from WordPress". The messages are safe under **Enquiries** in the meantime |
| A photograph you uploaded is not showing | The change was not published | Reopen where you changed it and click **Publish** (Customizer) or **Update** (page) |
| "The uploaded file exceeds the maximum size" | The picture is very large | Email the picture to yourself choosing a smaller size, then upload that |
| You cannot log in | Wrong password, or the WordPress user changed | Use **WP Admin** from https://hpanel.hostinger.com, which does not need the WordPress password |

**Nothing in this guide can break the design.** If an edit goes wrong, delete
the section or clear the box, and the original comes back.

---

## 14. Getting help

- **Hostinger** answers questions about logging in, backups, the domain name,
  email and anything about the hosting itself. Use the live chat at the bottom
  right of https://hpanel.hostinger.com, 24 hours a day.
- **The design and the theme file** come from whoever built the website. Ask
  them for a new `redcliffe-advisory.zip` if the design itself needs to
  change, and follow section 11 to put it live.
