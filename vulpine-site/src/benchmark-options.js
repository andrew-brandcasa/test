/**
 * Three alternative presentations of the benchmark instrument, for review.
 *
 * All three render from benchmark.js — no hand-keyed values — so whichever is
 * chosen stays correct when a domain name or requirement count changes.
 *
 * References (real, checked, not invented):
 * · CIS Critical Security Controls v8.1 Navigator — 18 controls, each headed
 *   with a safeguard count, every safeguard tagged with the nested
 *   Implementation Groups it belongs to (IG1 ⊂ IG2 ⊂ IG3). Structurally
 *   identical to eleven domains under nested T0/T1 ⊂ T2 ⊂ T3 gates.
 * · OWASP ASVS 5.0 — 17 chapters, 345 requirements, published as a per-chapter
 *   count plus the distribution of requirement levels. It never prints a bare
 *   count; the level shape is the information.
 *
 * The current instrument prints "6 requirements · T2 · T3" and stops. The
 * gating rule already defines a required maturity level for every domain at
 * every tier, and none of that reaches the page. Each option below puts that
 * matrix on screen.
 */

import { DOMAINS, LEVELS, TIERS, BENCHMARK, SCORING_RULE } from './benchmark.js';

const esc = (s) => String(s).replace(/[&<>"]/g, (c) =>
  ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

/* The four T0/T1-gated domains, read off the tier definitions rather than
   restated, so the two can never drift apart. */
const T01 = new Set(TIERS[0].gateIds);

/** Required maturity level for a domain at each tier: null = not gated. */
const req = (d) => ({
  t01: T01.has(d.id) ? 2 : null,
  t2: 2,
  t3: d.t3 ? 3 : 2,
});

/* ============================================================ option A
   The gate matrix. Domains down, authority tiers across, the cell is the
   maturity level that tier demands. This is the instrument the data already
   describes: it shows at a glance that T2 is a flat bar across all eleven and
   T3 is where six of them step up. Orange marks only the level-3 step. */
export function optionMatrix() {
  /* eslint-disable no-use-before-define -- optionCards is the mobile fallback */
  const rows = DOMAINS.map((d) => {
    const r = req(d);
    const cell = (lvl, isStep) => lvl === null
      ? '<td class="mx-c"><span class="mx-none">—</span></td>'
      : `<td class="mx-c"><span class="mx-lvl${isStep ? ' step' : ''}">${lvl}</span></td>`;
    return `<tr>
      <th scope="row" class="mx-dom"><code>${d.id}</code><span>${esc(d.name)}</span></th>
      <td class="mx-n">${d.reqs}</td>
      ${cell(r.t01, false)}${cell(r.t2, false)}${cell(r.t3, r.t3 === 3)}
    </tr>`;
  }).join('');

  return `<div class="opt-inst">
    <table class="gmx">
      <thead>
        <tr>
          <th scope="col" class="gmx-h1">Control domain</th>
          <th scope="col" class="gmx-hn">Reqs</th>
          <th scope="col" colspan="3" class="gmx-group">Maturity level required to grant</th>
        </tr>
        <tr class="gmx-sub">
          <th></th><th></th>
          <th scope="col">T0 / T1<em>observe, advise</em></th>
          <th scope="col">T2<em>act, reversible</em></th>
          <th scope="col">T3<em>act, consequential</em></th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
    <div class="gmx-key">
      <div><span class="mx-lvl">2</span> Enforced by default inside a declared boundary</div>
      <div><span class="mx-lvl step">3</span> Verified — adversarially tested, monitored, evidenced</div>
      <div><span class="mx-none">—</span> Not gated at this tier</div>
    </div>
    <p class="opt-note">${esc(SCORING_RULE)}</p>
  </div>
  <!-- A matrix cannot compress to a phone, and the component spec forbids
       trying: "Mobile is a list that opens, never a compressed table." Below
       860px the same data renders as the option-B cards instead. -->
  <div class="opt-mob">${optionCards()}</div>`;
}

/* ============================================================ option B
   Control cards, the CIS Navigator pattern. Each domain is a unit: identifier,
   name, what it means in one line, its requirement count, and a three-step
   ramp showing where its bar rises. Reads well on a phone, and the descriptor
   earns its place — the current table has nowhere to put it. */
export function optionCards() {
  const cards = DOMAINS.map((d, i) => {
    const r = req(d);
    const pip = (lvl, lab) => lvl === null
      ? `<span class="pip off"><i></i>${lab}</span>`
      : `<span class="pip${lvl === 3 ? ' hi' : ''}"><i>${lvl}</i>${lab}</span>`;
    return `<article class="ccard">
      <div class="cc-top">
        <code>${d.id}</code>
        <span class="cc-reqs">${d.reqs} requirements</span>
      </div>
      <h4>${esc(d.name)}</h4>
      <p>${esc(d.descriptor)}</p>
      <div class="cc-ramp">
        ${pip(r.t01, 'T0/T1')}${pip(r.t2, 'T2')}${pip(r.t3, 'T3')}
      </div>
    </article>`;
  }).join('');
  /* eleven cards in a three-up grid leaves a hole; the twelfth cell carries
     the totals rather than sitting empty */
  const totals = `<article class="ccard cc-sum">
    <div class="cc-top"><code>Totals</code></div>
    <dl>
      <div><dt>Control domains</dt><dd>${DOMAINS.length}</dd></div>
      <div><dt>Requirements</dt><dd>${DOMAINS.reduce((n, d) => n + d.reqs, 0)}</dd></div>
      <div><dt>Gate T3 at level 3</dt><dd>${DOMAINS.filter((d) => d.t3).length}</dd></div>
    </dl>
  </article>`;
  return `<div class="ccards">${cards}${totals}</div>
    <p class="opt-note">${esc(SCORING_RULE)}</p>`;
}

/* ============================================================ option C
   The current editorial list, kept, with the gate column replaced by the same
   three-step ramp. Smallest change from what is built: same rhythm, same
   restraint, but a reader can now see the shape of the gating instead of
   decoding "T2 · T3". */
export function optionRamp() {
  const rows = DOMAINS.map((d) => {
    const r = req(d);
    const seg = (lvl) => lvl === null
      ? '<i class="sg off"></i>'
      : `<i class="sg l${lvl}"></i>`;
    return `<tr>
      <th scope="row" class="rp-dom"><code>${d.id}</code><span>${esc(d.name)}</span></th>
      <td class="rp-n">${d.reqs} requirements</td>
      <td class="rp-ramp">
        <span class="ramp" role="img" aria-label="Gated at T2 level 2${d.t3 ? ', T3 level 3' : ''}">
          ${seg(r.t01)}${seg(r.t2)}${seg(r.t3)}
        </span>
        <span class="rp-lab">${d.t3 ? 'level 3 at T3' : 'level 2'}</span>
      </td>
    </tr>`;
  }).join('');
  return `<div class="opt-inst">
    <table class="rmp">
      <thead><tr>
        <th scope="col">Control domain</th>
        <th scope="col">Requirements</th>
        <th scope="col">T0/T1 · T2 · T3</th>
      </tr></thead>
      <tbody>${rows}</tbody>
    </table>
    <p class="opt-note">${esc(SCORING_RULE)}</p>
  </div>`;
}

export function provenance() {
  return `<div class="prov">
    <b>Vulpine Control Architecture Benchmark ${BENCHMARK.version}</b><br>
    ${BENCHMARK.published} · eleven control domains · ${BENCHMARK.requirementTotal} requirements<br>
    Four maturity levels · three authority gates<br>
    ${BENCHMARK.frameworks.join(' · ')}
  </div>`;
}

export function levelKey() {
  return `<div class="lvlkey">${LEVELS.map((l) =>
    `<div><b>${l.n}</b> ${esc(l.name)}<span>${esc(l.short)}</span></div>`).join('')}</div>`;
}

export function mountOptions() {
  const put = (id, html) => { const el = document.getElementById(id); if (el) el.innerHTML = html; };
  put('o-prov', provenance());
  put('o-levels', levelKey());
  put('o-a', optionMatrix());
  put('o-b', optionCards());
  put('o-c', optionRamp());
}
