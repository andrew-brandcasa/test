# Vulpine site — audit and rebuild record

Working copy for Brand Casa. Andrew's brief (4 Aug): audit the entire staging site,
eliminate AI slop via taste-skill, align to Ryan's emailed requirements and
attachments, use copywriting discipline where necessary, and reference top-tier
sites for any section of concern. Nothing built blind.

---

## 1 · Source-of-truth map

The build traces every element to an authoritative source. Where sources
conflict, the later one wins; the supersede chain matters because three
documents are stale.

| Source | Date | Status |
|---|---|---|
| Deployed staging site (JS bundle, asset manifest) | 30 Jul | Copy + assets baseline |
| Ryan · "Website WIP comments" | 28 Jul | Feedback; three launch blockers |
| `vulpine-home-with-instrument_1.html` (mock) | 28 Jul | **Stale for benchmark data** (10 domains, v1.0) |
| Ryan · "Follow up — Vulpine Site" decision log | 30 Jul | Decisions, binding |
| `VulpineComponentSpec.pdf` + `VulpineBenchmarkPublicationSpec.pdf` | 30 Jul | Benchmark v1.1, authoritative |
| `VulpineQ06DiagramStructure.pdf` (v1) | 30 Jul 16:18 | **Superseded** |
| `VulpineDiagramBriefs.docx` (v1) | 30 Jul 16:58 | **Superseded** |
| `VulpineWhoWeAreCopy.docx` | 30 Jul 16:58 | Current, but *unapproved internal draft* |
| `VulpineQ06DiagramStructure (1).pdf` + `VulpineDiagramBriefs.UPDATED.docx` (v2) | 30 Jul 17:10 | Q06 authoritative |

Key supersessions honored:
- Benchmark is **v1.1: eleven domains, 71 requirements, six-domain T3 gate** —
  not the mock's ten domains and five-domain gate.
- Q06 is **"The Supervisory Gap"** (US-led) — not v1's EU-article crosswalk,
  whose obligations were deferred to Dec 2027.
- **Q03 gets no new diagram** — the authority model covers it (briefs v2).

## 2 · Ryan's decision log — compliance

| Decision | Status |
|---|---|
| Light primary; dark secondary | Done — light-first; accent bands are Cold Steel, on palette |
| Cold Steel structures, orange decides | Done — orange only on CTAs, gates, active states, the gap |
| Remove employer logos from stat cards | N/A — module deleted entirely (below) |
| Delete "Experience Behind the Advice" incl. figures | Done — replaced by the 09-library-texture band, per WhoWeAre doc |
| Counters render final values | N/A — no counters exist in the rebuild |
| Fix "Seven questions"/rotator copy errors | Done — verified absent |
| Show the instrument (benchmark) | Done — v1.1, unscored public state, provenance verbatim |
| Authority model on Q03 | Done |
| Provenance line beside instrument | Done — exact copy from publication spec |
| Promote photography, full-bleed breaks | Done — video hero (per newest staging), breaks per real mapping |
| Retire isometric line illustrations | Done — zero SVG illustrations site-wide |
| Break the section rhythm | Done — quiet/dense/full-bleed variants; single-line steel bands |
| Hero headline: HOLD | Held — "Making AI deployable in high-trust industries." |
| Six questions kept; visuals on 03 + 06 only | Done |
| No motion/gradients/3D/particles | Done — only motion is the site's own hero loop, with reduced-motion fallback |
| Mock ships unscored, no invented data | Done — scored variant exists only in the components workbench, flagged internal |

## 3 · Slop audit (taste-skill: redesign-existing-projects)

Run mechanically against the build. Findings and dispositions:

| Finding | Disposition |
|---|---|
| AI copy clichés (elevate/seamless/unleash/…) | **None found.** The old prototype's slop ("AI ambition to AI advantage", the broken rotator) predates this build and was not carried over |
| 3-equal-card feature grid (home insights) | **Fixed** — lead + list editorial index (see §5 references) |
| 4-column footer link farm | **Fixed** — 3 columns: brand, explore, contact + legal strip |
| Near-black band inside a light page | **Fixed** — bands are Cold Steel `#2B3B4B`, the brand structural color |
| No focus rings / skip link / `<main>` | **Fixed** — `:focus-visible` site-wide, skip link + one landmark per page |
| No `og:image` | **Fixed** — `vulpine-og-social.jpg` on all content pages |
| No reduced-motion handling | **Fixed** — hero video hidden, poster shown |
| No tabular figures / text-wrap | **Fixed** — `tabular-nums` on figures, `text-wrap: balance` on display type |
| No pressed state on buttons | **Fixed** — `scale(.98)` on `:active` |
| Dead links | None — footer legal links route to real stub pages pending counsel copy |

## 4 · Copy pass (copywriting skill)

Discipline applied: clarity over cleverness, specificity, honest CTAs.
**Outcome: no rewrites.** Line-by-line tracing shows the surviving copy is
Ryan-sourced — the deployed site he reviewed, his mock, his briefs, or his
Who We Are document. Per Andrew's direction, Ryan-approved copy is not
rewritten. The two copy errors Ryan flagged are confirmed absent. CTAs
already follow the "say what they get" rule ("Book a 30-minute call").

Bios: the four figure-removals from `VulpineWhoWeAreCopy.docx` are applied
($200M portfolio, tens of billions, 50M customers, acquisition-revenue /
three-billion-communications, named OCC/CFPB pairing softened). That document
is marked "Ryan and Melissa approve before this goes to Brand Casa" — final
prose remains theirs to sign off.

## 5 · Reference basis (sections rebuilt against real sites)

Ryan's steer: light reads as an institution; every competitor is already dark.
References chosen accordingly — institutional, not AI/security.

| Section | Reference | Pattern borrowed |
|---|---|---|
| Home insights | Lazard.com; McKinsey Featured Insights | Lead story treated larger + text-only secondary rows with category/date metadata; hierarchy by scale and position, not equal cards |
| Footer | Brunswick Group | Compact 3-column footer + separate legal strip |
| Typography/case | Lazard | Sans throughout, sentence case, hierarchy by size/weight |
| Section dividers | Lazard | Hairlines + whitespace, not decorated separators |

(Teneo.com blocks automated fetch; not used.)

The two proprietary components follow Ryan's own component spec, which is a
stronger reference than any external site — they are the elements no
competitor can have.

## 6 · Branding restoration (regression caught 4 Aug, fixed)

An earlier iteration substituted a text wordmark from Ryan's mock and guessed
image placement. Restored from the deployed bundle's asset manifest:

- `vulpine-logo-primary-light.svg` is the real mark — white glyphs + orange,
  built for the dark site. A mechanically recolored **`primary-dark.svg`**
  (fills `#FFFFFF`→`#15151B`, orange untouched, paths identical) now serves
  the light chrome. **Jhonny should bless or replace this variant.**
- Hero: `hero-loop.mp4` + `06-problems-band.jpg` poster (the newest staging
  build's actual hero).
- `art-strategic.jpg` on the Problems hero; `08-library-landscape-2.jpg`
  after Q03; `09-library-texture.jpg` band on Who We Are;
  `ryan-fox.jpeg` / `melissa-heng.jpeg` headshots.
- `art-control` / `art-evidence` / `art-production` confined to their
  articles. Exception: `art-evidence` as the home full-bleed break follows
  Ryan's mock exactly.

## 6b · Measured against the live site (5 Aug)

The live build at `vulpine-website.vercel.app` is a client-rendered SPA, so
grepping its bundle was not enough — it was mirrored locally, served, and
rendered in Chromium. Everything below was measured off that render rather
than inferred, and my build was corrected to match.

| Element | Live site | Was | Now |
|---|---|---|---|
| Hero treatment | Light: image at `.62` opacity, warm-lifted filter, cream scrims, ink type | Dark: image at `.66` brightness, near-black scrims, cream type | Light, matching the measured filter and both scrim gradients |
| Hero headline | "deployable" set in orange | All one colour | Orange, via `.hero h1 em` |
| Section label | Small brand chevron before every label | Chevron stripped | Restored as `.eyebrow::before`, mask-based so it recolours |
| Watermark | Hero 560px/.12, steel band 620px/.06, closing band 520px/.07 | Absent, then a single 340px/.06 guess | Per-band sizes and opacities as measured |
| Brand mark geometry | `viewBox 0 0 68.785 63.113`, three paths | An extraction with translated coordinates | Normalised geometry from the deployed bundle |
| Primary CTA | "Start a conversation" / "Get in touch", envelope icon | "Book a 30-minute call", no icon | Live labels and icon |
| Secondary CTA | Ruled text link with an arrow | A second button | `.tlink` |
| Contact address | `hello@vulpine.ai` | `hello@thevulpinegroup.com` | `hello@vulpine.ai` site-wide |
| Question sections | Three columns: number, argument, "How we help" list | Argument only, plus two chips; 60% of the row empty | Full engagement lists (6–7 items each), verbatim from the bundle |

Deliberately **not** copied from the live build: the `.bloom` radial glows
behind each watermark. Ryan's 30 Jul decision log refuses gradients
("we sell control and restraint to people who are professionally suspicious
of flash"), and that decision post-dates the build. The isometric line
illustrations in its thesis band are refused on the same grounds.

## 6c · Layout defects found by rendering, and fixed

| Defect | Fix |
|---|---|
| `VCA-10`/`VCA-11` pushed their domain names out of column | Identifier set to a fixed 56px inline-block |
| Authority gate column orphaned "2" onto its own line | Column widened to 300px, `text-wrap:balance` on the sentence |
| Who We Are origin: five paragraphs stacked left, half the page empty | Editorial split — sticky heading column, prose column |
| Bio credential rules sat at different heights | Bios are flex columns, credentials pinned with `margin-top:auto` |
| Photo-break caption illegible over light photography | Scrim runs deeper, three stops instead of two |
| Experience band: ink type over a bare 35%-opacity photo | Same cream scrim the hero uses |
| Mobile menu repeated the CTA already pinned in the sticky header | Panel carries links only |
| Scoring rule rendered above the mobile instrument | Moved below both variants |

## 7 · Open items — external dependencies, marked in-page, not invented

| Item | Owner |
|---|---|
| Scheduler URL (CTAs resolve to mailto until supplied) | Andrew/Ryan |
| Privacy/terms copy ("will ping legal", 4 Aug) | Ryan → counsel |
| Final bio prose sign-off | Ryan + Melissa |
| Light-chrome logo variant blessing | Jhonny |
| Article routing + hosting/CMS answer (Ryan's 30 Jul question) | Andrew ↔ Ryan |
| Vercel: project-creation permission or `VERCEL_TOKEN` for CLI deploys | Andrew |

## 8 · Verification record

All seven pages rendered in Chromium at 1440px and 390px and checked
mechanically, not by eye alone. Last run, 5 Aug — clean on every page at
both widths:

- components mount: 11 benchmark rows, 11 mobile rows, 4 authority tiers,
  5 supervisory-gap rows
- no horizontal scroll, no element escaping the viewport
- exactly one `<main>` and one `<h1>` per page
- logo, headshots and imagery load; every internal `href` resolves to a file
- no empty links, no console errors, no 4xx responses
- mobile navigation opens and closes (panel height 0 → 252 → 0)

The one request that fails in this sandbox is the Google Fonts stylesheet,
which the agent proxy blocks. It is not a site fault; Geist and Geist Mono
resolve normally from Vercel.
