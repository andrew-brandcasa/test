/**
 * Problems We Solve · Question 06, "The Supervisory Gap"
 *
 * Source of truth: VulpineDiagramBriefs.UPDATED.docx (version 2) +
 * VulpineQ06DiagramStructure (1).pdf (V2), Ryan Fox, 30 Jul 2026.
 *
 * Do NOT build from VulpineQ06DiagramStructure.pdf (v1, "The Evidence
 * Crosswalk"). Version 1 built four of five rows on EU AI Act articles that
 * the Digital Omnibus deferred to 2 Dec 2027, and pointed at a jurisdiction
 * Vulpine does not sell into. Version 2 leads with the US supervisory
 * position and keeps the EU as a single forward-looking row.
 *
 * Version 2 also corrects the scope of the work: question 03 does NOT get a
 * new diagram. The T0–T3 authority model already covers it. Q06 is the only
 * new asset.
 *
 * The argument: published supervisory material either excludes agentic AI by
 * name, predates it by five years, or is explicitly non-binding. The dates
 * carry the argument, so they are set as typographic elements, not footnotes.
 */

export const GAP_ROWS = [
  {
    date: '17 Apr 2026',
    status: 'Non-enforceable',
    instrument: 'SR 26-2 / OCC 2026-13',
    note: 'The rulebook US bank examiners use for model risk, rewritten this year',
    gap: 'Generative and agentic AI, excluded by name. An RFI is promised, undated.',
    domains: [{ id: null, name: 'All eleven domains' }],
  },
  {
    date: 'Aug 2021',
    status: 'Operative',
    instrument: 'FFIEC Authentication and Access',
    note: 'Still the guidance examiners apply today',
    gap: 'Non-human and machine identity. Written for customers, employees and third parties.',
    domains: [{ id: 'VCA-1', name: 'Identity' }, { id: 'VCA-2', name: 'Authorization' }],
  },
  {
    date: '21 May 2026',
    status: 'Advisory',
    instrument: 'NYDFS Frontier AI letters',
    note: 'Written as advice. It adds no obligations.',
    gap: 'Agent authority and action provenance. Recommends, does not require.',
    domains: [{ id: 'VCA-7', name: 'Supply chain' }, { id: 'VCA-8', name: 'Provenance' }],
  },
  {
    date: 'Voluntary framework',
    status: null,
    instrument: 'NIST AI RMF and AI 600-1',
    note: 'Says what good looks like, not how to build it',
    gap: 'Enforcement at the action boundary. Describes what good looks like, not how it is held.',
    domains: [{ id: 'VCA-9', name: 'Adversarial evaluation' }, { id: 'VCA-8', name: 'Provenance' }],
  },
  {
    date: 'Deferred to 2 Dec 2027',
    status: null,
    instrument: 'EU AI Act Arts. 12, 14, 15, 26',
    note: 'The only rules anywhere written as numbered duties',
    gap: 'Not in force for most systems until December 2027, after the Digital Omnibus.',
    domains: [{ id: 'VCA-4', name: 'Gateway' }, { id: 'VCA-8', name: 'Provenance' }, { id: 'VCA-10', name: 'Governance' }],
  },
];

/**
 * Domains appearing against more than one gap. The V2 panel keeps this
 * distinction from V1's legend, but renders it in steel, both V2 sources
 * state that orange marks the gap and nothing else, so the accent is not
 * spent here. VCA-8 Provenance is the only multi-gap domain.
 */
export const MULTI_GAP = (() => {
  const seen = new Map();
  GAP_ROWS.forEach((r) => r.domains.forEach((d) => {
    if (d.id) seen.set(d.id, (seen.get(d.id) || 0) + 1);
  }));
  return new Set([...seen].filter(([, n]) => n > 1).map(([id]) => id));
})();

/** The line that carries the diagram, set beneath it in body size. */
export const CARRY_LINE =
  'No published supervisory framework currently covers agent authority. The evidence is being asked for anyway.';

/** Second beat, smaller. */
export const SECOND_BEAT =
  'The controls that answer the board, the examiner and the enterprise customer are the same controls. Built once, they produce the evidence each audience asks for, rather than three separate compliance projects.';

/** Required caption. The diagram must not claim Vulpine certifies compliance. */
export const CAPTION =
  'Regulatory position as of July 2026. Legal sufficiency remains the client’s counsel’s determination.';
