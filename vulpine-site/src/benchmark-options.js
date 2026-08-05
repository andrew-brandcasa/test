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

/* ============================================================ option D
   Tier-led, the CIS Navigator pattern rendered rather than guessed.
   CIS does not put Implementation Groups in a column — IG1/IG2/IG3 are the
   primary control at the top of the page, and the standard reflows to show
   what the selected group demands. Each control is then a panel with its name
   left and "5/5 Safeguards" right.

   Applied here: the reader picks the authority they need to grant, and the
   instrument answers "then this is what you must clear." The four-segment
   meter is the ASVS idea — publish the level shape, not a bare count.

   T3 is rendered into the markup, so with no JS the panel still shows the
   hardest gate rather than going blank. Switching tiers is a state change,
   not an entrance animation. */
export function optionTiered() {
  const TIER_VIEW = [
    { key: 't01', id: 'T0 / T1', name: 'Observe, advise', meaning: TIERS[1].meaning, gate: TIERS[0].gate },
    { key: 't2', id: 'T2', name: 'Act, reversible', meaning: TIERS[2].meaning, gate: TIERS[2].gate },
    { key: 't3', id: 'T3', name: 'Act, consequential', meaning: TIERS[3].meaning, gate: TIERS[3].gate },
  ];

  /* Three segments for levels 1-2-3; level 0 is "Absent" and fills nothing.
     The ramp stays steel per the component spec — magnitude is not a decision
     — and orange marks only the level-3 step, which is the decision. */
  const meter = (lvl) => {
    if (lvl === null) return '<span class="mtr off" aria-hidden="true"><i></i><i></i><i></i></span>';
    const segs = [1, 2, 3].map((n) => {
      if (n > lvl) return '<i></i>';
      return `<i class="on${n === 3 && lvl === 3 ? ' hi' : ''}"></i>`;
    }).join('');
    return `<span class="mtr" aria-hidden="true">${segs}</span>`;
  };

  const body = (tk) => DOMAINS.map((d) => {
    const lvl = req(d)[tk];
    const lab = lvl === null ? 'Not gated' : `${LEVELS[lvl].name} (${lvl})`;
    return `<tr${lvl === 3 ? ' class="raised"' : ''}${lvl === null ? ' class="ungated"' : ''}>
      <th scope="row" class="td-dom"><code>${d.id}</code><span>${esc(d.name)}</span></th>
      <td class="td-n">${d.reqs}</td>
      <td class="td-m">${meter(lvl)}<span class="td-lab">${lab}</span></td>
    </tr>`;
  }).join('');

  const panels = TIER_VIEW.map((t, i) => `
    <div class="tpanel" data-tier="${t.key}"${i === 2 ? '' : ' hidden'}>
      <div class="tpanel-hd">
        <p class="tp-mean">${esc(t.meaning)}</p>
        <p class="tp-gate"><span>Gate</span>${esc(t.gate)}</p>
      </div>
      <table class="tmx">
        <thead><tr>
          <th scope="col">Control domain</th>
          <th scope="col" class="td-n">Reqs</th>
          <th scope="col">Maturity level this tier requires</th>
        </tr></thead>
        <tbody>${body(t.key)}</tbody>
      </table>
    </div>`).join('');

  const tabs = TIER_VIEW.map((t, i) => `
    <button type="button" class="ttab${i === 2 ? ' on' : ''}" data-tier="${t.key}"
            aria-pressed="${i === 2 ? 'true' : 'false'}">
      <span class="tt-id">${t.id}</span><span class="tt-nm">${esc(t.name)}</span>
    </button>`).join('');

  return `<div class="tiered">
    <div class="tbar">
      <p class="tbar-q">What authority do you need to grant?</p>
      <div class="ttabs" role="group" aria-label="Authority tier">${tabs}</div>
    </div>
    ${panels}
    <p class="opt-note">${esc(SCORING_RULE)}</p>
  </div>`;
}

/** Tier switching. Content for every tier is already in the DOM. */
export function wireTiers(root = document) {
  root.querySelectorAll('.tiered').forEach((box) => {
    const tabs = [...box.querySelectorAll('.ttab')];
    const panels = [...box.querySelectorAll('.tpanel')];
    tabs.forEach((tab) => tab.addEventListener('click', () => {
      const k = tab.dataset.tier;
      tabs.forEach((t) => {
        const on = t === tab;
        t.classList.toggle('on', on);
        t.setAttribute('aria-pressed', String(on));
      });
      panels.forEach((p) => { p.hidden = p.dataset.tier !== k; });
    }));
  });
}

/* ============================================================ option E
   Modelled on the Tailscale pricing comparison, rendered rather than guessed.
   Its mechanics, and why they fit:
   · Each row is a name with a small grey descriptor line beneath it. That is
     the fix for eleven domains arriving as eleven names a reader cannot
     evaluate — the descriptors already exist in the data and had nowhere to go.
   · Tier columns carry plain text values, not graphics: a literal value where
     there is one, an en dash where there is not.
   · Hairline dividers, generous row height, no card, no heavy border.
   No grouping headers: Tailscale groups its rows into named sections, but the
   benchmark defines eleven domains and no groups, and inventing them would be
   making something up. */
export function optionSpec() {
  /* data-t carries the tier label so the stacked mobile rows stay readable
     without duplicating the markup */
  const val = (lvl, tier) => lvl === null
    ? `<td class="sv none" data-t="${tier}">&ndash;</td>`
    : `<td class="sv${lvl === 3 ? ' hi' : ''}" data-t="${tier}">Level ${lvl}<span>${esc(LEVELS[lvl].name)}</span></td>`;

  const rows = DOMAINS.map((d) => {
    const r = req(d);
    return `<tr>
      <th scope="row" class="sd">
        <span class="sd-nm"><code>${d.id}</code>${esc(d.name)}</span>
        <span class="sd-de">${esc(d.descriptor)}</span>
      </th>
      <td class="sn">${d.reqs}</td>
      ${val(r.t01, 'T0 / T1')}${val(r.t2, 'T2')}${val(r.t3, 'T3')}
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
  put('o-e', optionSpec());
  put('o-d', optionTiered());
  put('o-a', optionMatrix());
  put('o-b', optionCards());
  put('o-c', optionRamp());
  wireTiers();
}
