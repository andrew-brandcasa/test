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
      ${link('/who-we-are.html', 'Who we are', 'who')}
      ${link('/problems-we-solve.html', 'Problems we solve', 'problems')}
      ${link('/insights.html', 'Insights', 'insights')}
      <a class="btn" href="${CTA_HREF}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>Get in touch</a>
      <button class="menu-btn" type="button" aria-label="Menu" aria-expanded="false" aria-controls="mnav">
        <span></span><span></span>
      </button>
    </nav>
  </div>
  <!-- links only: the header is sticky, so the orange CTA beside the toggle
       is always on screen and repeating it in the panel is noise -->
  <nav id="mnav" class="mnav" hidden aria-label="Site">
    <a href="/who-we-are.html">Who we are</a>
    <a href="/problems-we-solve.html">Problems we solve</a>
    <a href="/insights.html">Insights</a>
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
        <a href="/who-we-are.html">Who we are</a>
        <a href="/problems-we-solve.html">Problems we solve</a>
        <a href="/insights.html">Insights</a>
        <a href="/#benchmark">The benchmark</a>
      </div>
      <div><h5>Contact</h5>
        <a href="mailto:${CONTACT_EMAIL}">${CONTACT_EMAIL}</a>
        <a href="https://www.linkedin.com/company/the-vulpine-group/" rel="noopener">LinkedIn</a>
      </div>
    </div>
    <div class="fbot">
      <span>© 2026 The Vulpine Group. The Vulpine Group is a d/b/a of Fox Strategy Co. LLC.</span>
      <span><a href="/privacy.html">Privacy</a> · <a href="/terms.html">Terms</a></span>
    </div>
  </div></footer>`;
}

/* ------------------------------------------- component A · the benchmark */

export function provenanceBlock() {
  return `<div class="prov">
    <b>Vulpine Control Architecture Benchmark ${BENCHMARK.version}</b><br>
    ${BENCHMARK.published} · eleven control domains · ${BENCHMARK.requirementTotal} requirements<br>
    Four maturity levels · three authority gates<br>
    ${BENCHMARK.frameworks.slice(0, 3).join(' · ')}<br>
    ${BENCHMARK.frameworks.slice(3).join(' · ')}<br>
    ${BENCHMARK.cadence}
  </div>`;
}

/**
 * The public instrument — a published spec table.
 *
 * Layout follows the Tailscale pricing comparison: every row is a name with a
 * grey descriptor beneath it, and the tier columns carry plain text values.
 * The domain descriptors exist in the data and the previous table had nowhere
 * to put them, so eleven domains arrived as eleven names a reader could not
 * evaluate.
 *
 * The gating rule already defines a required maturity level for every domain
 * at every tier; that matrix is what the table publishes. Unscored: no client
 * data, no invented organization.
 */
const T01_GATED = new Set(TIERS[0].gateIds);
const required = (d) => ({ t01: T01_GATED.has(d.id) ? 2 : null, t2: 2, t3: d.t3 ? 3 : 2 });

export function benchmarkInstrument() {
  const cell = (lvl, tier) => lvl === null
    ? `<td class="sv none" data-t="${tier}">&ndash;</td>`
    : `<td class="sv${lvl === 3 ? ' hi' : ''}" data-t="${tier}">Level ${lvl}`
      + `<span>${esc(LEVELS[lvl].name)}</span></td>`;

  const rows = DOMAINS.map((d) => {
    const r = required(d);
    return `<tr>
      <th scope="row" class="sd">
        <span class="sd-nm"><code>${d.id}</code>${esc(d.name)}</span>
        <span class="sd-de">${esc(d.descriptor)}</span>
      </th>
      <td class="sn">${d.reqs}</td>
      ${cell(r.t01, 'T0 / T1')}${cell(r.t2, 'T2')}${cell(r.t3, 'T3')}
    </tr>`;
  }).join('');

  return `<div class="spec">
    <table class="stbl">
      <thead>
        <tr>
          <th scope="col" class="sh-dom">Control domain</th>
          <th scope="col" class="sh-n">Reqs</th>
          <th scope="col"><b>T0 / T1</b><em>observe, advise</em></th>
          <th scope="col"><b>T2</b><em>act, reversible</em></th>
          <th scope="col"><b>T3</b><em>act, consequential</em></th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
    <p class="lead rule-note">${esc(SCORING_RULE)}</p>
  </div>`;
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

export function mount(current) {
  const slot = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML = html; };
  slot('site-header', header(current));
  slot('site-footer', footer());
  slot('c-provenance', provenanceBlock());
  slot('c-benchmark', benchmarkInstrument());
  slot('c-authority', authorityModel());
  slot('c-gap', supervisoryGap());

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
