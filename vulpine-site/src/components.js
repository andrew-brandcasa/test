/**
 * Shared renderers: site chrome plus the three proprietary components.
 *
 * The components render from the data modules rather than from markup, so the
 * benchmark exists once and the public and internal states are the same
 * component. Changing a domain name or a requirement count is a data edit.
 */

import { BENCHMARK, LEVELS, DOMAINS, TIERS, SCORING_RULE } from './benchmark.js';
import { GAP_ROWS, MULTI_GAP, CARRY_LINE, SECOND_BEAT, CAPTION } from './supervisory-gap.js';

export const esc = (s) => String(s).replace(/[&<>"]/g, (c) =>
  ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

/* Chrome CTAs jump to the closing band, which carries the real mailto. Label
   and address match the live site: "Get in touch" / hello@vulpine.ai. */
export const CTA_HREF = '#contact';
export const CTA_LABEL = 'Get in touch';
export const CONTACT_EMAIL = 'hello@vulpine.ai';

/* ---------------------------------------------------------------- chrome */

export function header(current) {
  const link = (href, label, key) =>
    `<a class="lnk" href="${href}"${current === key ? ' aria-current="page"' : ''}>${label}</a>`;
  return `<header><div class="wrap">
    <a class="logo" href="/" aria-label="Vulpine, home"><img src="/assets/logos/vulpine-logo-primary-dark.svg" alt="Vulpine"></a>
    <nav>
      ${link('/who-we-are', 'Who we are', 'who')}
      ${link('/problems-we-solve', 'Problems we solve', 'problems')}
      ${link('/insights', 'Insights', 'insights')}
      <a class="btn" href="${CTA_HREF}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>Get in touch</a>
      <button class="menu-btn" type="button" aria-label="Menu" aria-expanded="false" aria-controls="mnav">
        <span></span><span></span>
      </button>
    </nav>
  </div>
  <!-- links only: the header is sticky, so the orange CTA beside the toggle
       is always on screen and repeating it in the panel is noise -->
  <nav id="mnav" class="mnav" hidden aria-label="Site">
    <a href="/who-we-are">Who we are</a>
    <a href="/problems-we-solve">Problems we solve</a>
    <a href="/insights">Insights</a>
  </nav></header>`;
}

export function footer() {
  return `<footer><div class="wrap">
    <div class="fgrid">
      <div>
        <a class="logo logo--footer" href="#top" aria-label="Vulpine, back to top"><img src="/assets/logos/vulpine-logo-primary-dark.svg" alt="Vulpine"></a>
        <p style="margin-top:14px">Independent AI security and strategy advisory. Securing enterprise
          AI into production, where the stakes are highest.</p>
      </div>
      <div><h5>Explore</h5>
        <a href="/who-we-are">Who we are</a>
        <a href="/problems-we-solve">Problems we solve</a>
        <a href="/insights">Insights</a>
        <a href="/benchmark">The benchmark</a>
      </div>
      <div><h5>Contact</h5>
        <a href="mailto:${CONTACT_EMAIL}">${CONTACT_EMAIL}</a>
        <a href="https://www.linkedin.com/company/the-vulpine-group/" rel="noopener">LinkedIn</a>
      </div>
    </div>
    <div class="fbot">
      <span>© 2026 The Vulpine Group. The Vulpine Group is a d/b/a of Fox Strategy Co. LLC.</span>
      <span><a href="/privacy">Privacy</a> · <a href="/terms">Terms</a></span>
    </div>
  </div></footer>`;
}

/* ------------------------------------------- component A · the benchmark */

/**
 * The benchmark, stated rather than tabulated.
 *
 * This replaced an eleven-row, five-column matrix. That table was correct and
 * unreadable: forty of its cells said "Level 2 · Enforced", so it spent the
 * most valuable section on the page proving it had a lot of rows. The
 * differentiating fact is not the size of the benchmark, it is which controls
 * have to be proven before an agent is allowed to do something irreversible.
 * That is six named domains and a sentence, so it is published as six named
 * domains and a sentence.
 *
 * Still unscored: no client data, no invented organization, no requirement
 * text. Only the structure is public.
 */
const T3_GATED = DOMAINS.filter((d) => d.t3);

export function benchmarkInstrument() {
  /* the whitespace between these spans is load-bearing: without it the figure
     and its label ran together as "11control domains" for anyone copying the
     text or reading it with a screen reader */
  /* three floating figures used to sit here. They stated scale and nothing
     else, and the 11 contradicted the six domains listed underneath. */

  /* unordered: these six are a set, not a sequence. Numbering them implied a
     first and a sixth that do not exist. */
  const gated = T3_GATED.map((d) => `<li>
    <span class="gd-id">${d.id}</span>
    <span class="gd-nm">${esc(d.name)}.</span>
    <span class="gd-de">${esc(d.descriptor)}</span>
  </li>`).join('');

  return `<div class="bench">
    <div class="gate-block">
      <p class="lead gate-lead">The Vulpine Control Architecture is ${DOMAINS.length} areas of
        control and ${BENCHMARK.requirementTotal} requirements. Before an agent can do anything it
        cannot take back, six of those eleven have to be proven, not just switched on. Moving money. Contacting customers at
        scale. Changing production. Deleting data.</p>
      <ul class="gated">${gated}</ul>
      <p class="rule-note">The other five have to be enforced rather than proven, which is a
        lower bar. <a class="tlink tlink--sm" href="/benchmark">See all eleven, and what VCA
        means<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a></p>
    </div>
  </div>`;
}

/* ------------------------------- benchmark page · levels and all eleven */

export function levelScale() {
  return `<ol class="levels">${LEVELS.map((l) => `<li>
    <span class="lv-n">Level ${l.n}</span>
    <span class="lv-nm">${esc(l.name)}.</span>
    <span class="lv-de">${esc(l.short)}</span>
  </li>`).join('')}</ol>`;
}

/** All eleven, with the higher bar marked. Answers "why am I shown six of 11". */
export function allDomains() {
  return `<ul class="domlist">${DOMAINS.map((d) => `<li${d.t3 ? ' class="hi"' : ''}>
    <span class="dl-id">${d.id}</span>
    <span class="dl-nm">${esc(d.name)}.</span>
    <span class="dl-de">${esc(d.descriptor)}</span>
    <span class="dl-bar">${d.reqs} requirements &middot;
      ${d.t3 ? 'must be independently proven' : 'must be enforced'}</span>
  </li>`).join('')}</ul>`;
}

/* -------------------------------------- component B · the authority model */

export function authorityModel() {
  const rungs = TIERS.map((t) => {
    const gate = t.gateIds.length
      ? esc(t.gate).replace(/(VCA-\d+(\s·\s\d+)*|Domains\s1\s·\s3\s·\s5\s·\s8)/, '<em>$1</em>')
      : esc(t.gate);
    return `<div class="rung">
      <div class="rid-col"><code>${t.id}</code><span class="bar"></span></div>
      <div class="b"><h4>${esc(t.name)}</h4><p>${esc(t.meaning)}</p></div>
      <div class="g"><span class="lab">Gate</span><span class="req">${gate}</span></div>
    </div>`;
  }).join('');
  return `<div class="ladder">${rungs}</div>`;
}

/* --------------------------------------- question 06 · the supervisory gap */

export function supervisoryGap() {
  const rows = GAP_ROWS.map((r) => {
    const when = r.status
      ? `${esc(r.date)} · <b>${esc(r.status)}</b>`
      : `<b>${esc(r.date)}</b>`;
    const chips = r.domains.map((d) => d.id
      ? `<span class="chip${MULTI_GAP.has(d.id) ? ' multi' : ''}"><span class="cid">${d.id}</span>${esc(d.name)}</span>`
      : `<span class="chip">${esc(d.name)}</span>`).join('');
    return `<div class="gaprow">
      <div>
        <div class="when">${when}</div>
        <div class="iname">${esc(r.instrument)}</div>
        <div class="inote">${esc(r.note)}</div>
      </div>
      <p class="miss">${esc(r.gap)}</p>
      <div class="doms">${chips}</div>
    </div>`;
  }).join('');

  return `
    <div class="gapc">
      <div class="gaphead">
        <span>What the supervisor has published</span>
        <span>What it does not cover</span>
        <span>The domain that covers it</span>
      </div>
      ${rows}
      <div class="gaplegend">
        <div><i class="sw-multi"></i>Domain that answers more than one gap</div>
        <div><i class="sw-one"></i>Answers one</div>
      </div>
    </div>
    <p class="carry">${esc(CARRY_LINE)}</p>
    <p class="beat">${esc(SECOND_BEAT)}</p>
    <p class="caption">${esc(CAPTION)}</p>`;
}

/* ------------------------------------------------------------------ mount */

/* ------------------------------------------------------- contact form */

/**
 * The form posts JSON to our own endpoint, which relays it through Resend.
 * The API key stays server-side; nothing sensitive reaches the browser.
 *
 * Every failure path says what went wrong and repeats the mailto, rather than
 * clearing the fields and letting the sender believe an enquiry arrived. A
 * form that silently swallows a new-business enquiry is worse than no form.
 */
export const FORM_ENDPOINT = '/api/contact';

export function contactForm() {
  const field = (id, label, type, extra = '') => `<p class="fld">
    <label for="${id}">${label}</label>
    ${type === 'textarea'
      ? `<textarea id="${id}" name="${id}" rows="4" ${extra}></textarea>`
      : `<input id="${id}" name="${id}" type="${type}" ${extra}>`}
  </p>`;

  return `<form class="cform" method="post" action="${FORM_ENDPOINT}" novalidate>
    <div class="cgrid">
      ${field('name', 'Your name', 'text', 'required autocomplete="name"')}
      ${field('email', 'Work email', 'email', 'required autocomplete="email"')}
      ${field('org', 'Organization', 'text', 'autocomplete="organization"')}
      ${field('role', 'Your role', 'text', 'autocomplete="organization-title"')}
    </div>
    ${field('message', 'What are you trying to deploy, and what is blocking it?', 'textarea', 'required')}
    <p class="hp" aria-hidden="true">
      <label for="company">Company</label>
      <input id="company" name="company" type="text" tabindex="-1" autocomplete="off">
    </p>
    <div class="cfoot">
      <button class="btn" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>Send</button>
      <span class="cnote">Or email <a href="mailto:${CONTACT_EMAIL}">${CONTACT_EMAIL}</a> directly.</span>
    </div>
    <p class="cstatus" role="status" aria-live="polite"></p>
  </form>`;
}

/* Every one of these names the next action. "Something went wrong" leaves a
   prospective client with nowhere to go. */
const FALLBACK = ` Please email <a href="mailto:${CONTACT_EMAIL}">${CONTACT_EMAIL}</a> and we will reply the same day.`;
const MESSAGES = {
  not_configured: 'This form is not connected yet.' + FALLBACK,
  send_failed: 'We could not send that just now.' + FALLBACK,
  offline: 'That did not reach us, which usually means the connection dropped.' + FALLBACK,
};

function wireForm() {
  const form = document.querySelector('.cform');
  if (!form) return;
  const status = form.querySelector('.cstatus');
  const button = form.querySelector('button[type="submit"]');

  const say = (kind, html) => { status.className = 'cstatus ' + kind; status.innerHTML = html; };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (button.disabled) return;

    const data = Object.fromEntries(new FormData(form).entries());
    if (!data.name?.trim() || !data.email?.trim() || !data.message?.trim()) {
      return say('err', 'Please fill in your name, work email and a message.');
    }

    button.disabled = true;
    const label = button.lastChild;
    const original = label.textContent;
    label.textContent = 'Sending';
    say('', '');

    try {
      const r = await fetch(FORM_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      /* a non-JSON body means the request never reached the function —
         usually the endpoint is not deployed rather than the send failing */
      const out = await r.json().catch(() => ({}));

      if (r.ok) {
        form.querySelectorAll('input, textarea').forEach((el) => { el.value = ''; });
        say('ok', 'Thank you. That has reached us, and we will reply the same day.');
      } else {
        say('err', MESSAGES[out.error] || out.error || MESSAGES.send_failed);
      }
    } catch {
      say('err', MESSAGES.offline);
    } finally {
      button.disabled = false;
      label.textContent = original;
    }
  });
}

export function mount(current) {
  const slot = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML = html; };
  slot('site-header', header(current));
  slot('site-footer', footer());
  slot('c-benchmark', benchmarkInstrument());
  slot('c-levels', levelScale());
  slot('c-domains', allDomains());
  slot('c-authority', authorityModel());
  slot('c-gap', supervisoryGap());
  slot('c-form', contactForm());
  wireForm();

  const btn = document.querySelector('.menu-btn');
  const mnav = document.getElementById('mnav');
  if (btn && mnav) {
    btn.addEventListener('click', () => {
      const open = mnav.hidden;
      mnav.hidden = !open;
      btn.setAttribute('aria-expanded', String(open));
      btn.classList.toggle('open', open);
    });
    mnav.addEventListener('click', (e) => {
      if (e.target.closest('a')) {
        mnav.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
        btn.classList.remove('open');
      }
    });
  }
}
