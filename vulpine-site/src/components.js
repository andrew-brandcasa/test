/**
 * Shared renderers: site chrome plus the three proprietary components.
 *
 * The components render from the data modules rather than from markup, so the
 * benchmark exists once and the public and internal states are the same
 * component. Changing a domain name or a requirement count is a data edit.
 */

import { BENCHMARK, LEVELS, DOMAINS, TIERS, SUMMARY, SCORING_RULE } from './benchmark.js';
import { GAP_ROWS, MULTI_GAP, CARRY_LINE, SECOND_BEAT, CAPTION } from './supervisory-gap.js';

export const esc = (s) => String(s).replace(/[&<>"]/g, (c) =>
  ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

/* Every call to action points here. Ryan's note: every CTA on the current
   draft is a mailto with no capture. The scheduler URL has not been supplied,
   so the link is marked rather than invented. */
export const CTA_HREF = '#contact';
export const CTA_LABEL = 'Book a 30-minute call';

const range = (d) => `${d.prefix}-01 <span>…</span> ${d.prefix}-${String(d.reqs).padStart(2, '0')}`;
const gateTag = (d) => `<span class="tag${d.t3 ? ' t3' : ''}">${d.t3 ? 'T2 · T3' : 'T2'}</span>`;

/* ---------------------------------------------------------------- chrome */

export function header(current) {
  const link = (href, label, key) =>
    `<a class="lnk" href="${href}"${current === key ? ' aria-current="page"' : ''}>${label}</a>`;
  return `<header><div class="wrap">
    <a class="logo" href="/">Vulpine<sup>◤</sup></a>
    <nav>
      ${link('/who-we-are.html', 'Who we are', 'who')}
      ${link('/problems-we-solve.html', 'Problems we solve', 'problems')}
      ${link('/insights.html', 'Insights', 'insights')}
      <a class="btn" href="${CTA_HREF}">Get in touch</a>
    </nav>
  </div></header>`;
}

export function footer() {
  return `<footer><div class="wrap">
    <div class="fgrid">
      <div>
        <span class="logo" style="font-size:17px">Vulpine<sup>◤</sup></span>
        <p style="margin-top:14px">Independent AI security and strategy advisory. Securing enterprise
          AI into production, where the stakes are highest.</p>
      </div>
      <div><h5>Firm</h5>
        <a href="/who-we-are.html">Who we are</a>
        <a href="/#benchmark">Control architecture</a>
      </div>
      <div><h5>Thinking</h5>
        <a href="/insights.html">Insights</a>
        <a href="/problems-we-solve.html">Problems we solve</a>
      </div>
      <div><h5>Contact</h5>
        <a href="${CTA_HREF}">${CTA_LABEL}</a>
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

export function maturityKey() {
  return `<div class="mkey">${LEVELS.map((l) =>
    `<div><i class="l${l.n}"></i>${l.n} ${esc(l.name)} · <b>${esc(l.short)}</b></div>`).join('')}</div>`;
}

/** The public instrument. Unscored: no client data, no invented organization. */
export function benchmarkInstrument() {
  const rows = DOMAINS.map((d) => `
    <tr>
      <th scope="row" class="dom"><code>${d.id}</code>${esc(d.name)}</th>
      <td class="rid">${range(d)}</td>
      <td class="rcount">${d.reqs} requirements</td>
      <td class="gate">${gateTag(d)}</td>
    </tr>`).join('');

  const gates = `<div class="gates">
    <div><div class="lab">T0 / T1 · observe, advise</div>
      <div class="v">Domains 1, 3, 5, 8 at level 2</div>
      <p>The human remains the actor of record.</p></div>
    <div><div class="lab">T2 · act, reversible</div>
      <div class="v">All eleven at level 2</div>
      <p>Bounded, reversible or staged, with a tested rollback path.</p></div>
    <div><div class="lab">T3 · act, consequential</div>
      <div class="v">Plus six at level 3</div>
      <p>Irreversible, material or externally binding.</p></div>
  </div>`;

  const mobileRows = DOMAINS.map((d) => `
    <details class="mrow">
      <summary>
        <span class="top">${d.id}</span>${gateTag(d)}
        <span class="nm">${esc(d.name)}</span>
        <span class="rng">${range(d)}</span>
        <span class="pm">+</span>
      </summary>
      <div class="mbody">${esc(d.descriptor)}
        <div class="meta">${d.reqs} requirements${d.t3
          ? ' · <span class="t3">Carries a level-3 bar for T3 authority</span>' : ''}</div>
      </div>
    </details>`).join('');

  return `
    <div class="keyrow">
      ${maturityKey()}
      <p class="lead" style="font-size:13.5px;max-width:46ch">${SCORING_RULE}</p>
    </div>

    <div class="inst desk">
      <div class="inst-top">
        <span class="t">Control domains · published structure, unscored</span>
      </div>
      <table class="mx">
        <thead><tr>
          <th scope="col">Control domain</th>
          <th scope="col" class="mid">Requirement set</th>
          <th scope="col" class="mid">Requirements</th>
          <th scope="col">Gates</th>
        </tr></thead>
        <tbody>${rows}</tbody>
      </table>
      ${gates}
    </div>

    <div class="mob">
      <div class="summary">
        <div><div class="fig">${SUMMARY.domains}</div><div class="cap">Control domains</div></div>
        <div><div class="fig">${SUMMARY.requirements}</div><div class="cap">Requirements</div></div>
        <div><div class="fig">${SUMMARY.t3Gated}</div><div class="cap">Gate T3 at level 3</div></div>
      </div>
      <div class="mlist">${mobileRows}</div>
      <div class="inst" style="margin-top:16px;border-radius:var(--r-md)">
        ${gates.replace('class="gates"', 'class="gates" style="border-top:0"')}
      </div>
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
}
