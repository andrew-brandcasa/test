/**
 * Vulpine Control Architecture Benchmark — v1.1, July 2026
 *
 * Source of truth: VulpineBenchmarkPublicationSpec.pdf + VulpineComponentSpec.pdf
 * (Ryan Fox, The Vulpine Group, 30 Jul 2026).
 *
 * Do NOT build from vulpine-home-with-instrument_1.html. That concept file
 * predates v1.1: it shows ten domains, the old prefixes, the old level names
 * and a five-domain T3 gate. The publication spec supersedes it explicitly.
 *
 * Requirement identifiers are stable strings cited by downstream Vulpine
 * artifacts (decks, proposals, assessment reports). Treat them as fixed —
 * never abbreviate, restyle or regenerate them.
 *
 * Public surface only. Requirement text, per-domain rubrics, the evidence
 * standard and the regulatory crosswalk are deliberately absent and must
 * never be added to this file.
 */

export const BENCHMARK = {
  version: 'v1.1',
  published: 'July 2026',
  requirementTotal: 71,
  cadence: 'Reviewed quarterly and on major framework releases',
  frameworks: [
    'OWASP ASI Top 10 2026',
    'OWASP LLM Top 10 2025',
    'MITRE ATLAS',
    'NIST AI RMF and AI 600-1',
    'EU AI Act',
  ],
};

/** Four maturity levels. Ordinal ramp — steel, never orange. Magnitude is not a decision. */
export const LEVELS = [
  { n: 0, name: 'Absent', short: 'Policy text with no enforcement', full: 'The control does not exist, or exists only as policy text with no technical enforcement.' },
  { n: 1, name: 'Partial', short: 'Some paths, or manual', full: 'Implemented for some agents or paths, or enforced manually, or enforceable but not enforced by default.' },
  { n: 2, name: 'Enforced', short: 'By default inside a declared boundary', full: 'Technically enforced by default for every agent and every path inside a declared boundary, with deviations impossible without detection.' },
  { n: 3, name: 'Verified', short: 'Continuously proven', full: 'Enforced and continuously proven: adversarially tested, monitored for drift, with evidence generated automatically.' },
];

/**
 * The eleven control domains.
 * `t3` marks the six domains carrying a level-3 requirement for T3 authority:
 * VCA-1, 2, 3, 4, 8, 9. That column is the only orange in the public panel.
 */
export const DOMAINS = [
  { id: 'VCA-1',  name: 'Agent identity & credentialing',      prefix: 'IDC',   reqs: 6, t3: true,  descriptor: 'Every agent action traces to a unique, short-lived identity bound to the human it acts for.' },
  { id: 'VCA-2',  name: 'Authorization & delegation',          prefix: 'AUTHZ', reqs: 6, t3: true,  descriptor: 'Authority evaluated per action, at the moment of action, with the least scope that lets the agent work.' },
  { id: 'VCA-3',  name: 'Instruction-data separation',         prefix: 'INJ',   reqs: 7, t3: true,  descriptor: 'Untrusted content can never elevate authority, and the privileged path never ingests it raw.' },
  { id: 'VCA-4',  name: 'Deterministic action gateway',        prefix: 'GATE',  reqs: 9, t3: true,  descriptor: 'One mediation layer every consequential action passes through, enforced outside the agent’s influence.' },
  { id: 'VCA-5',  name: 'Execution containment & blast radius', prefix: 'EXEC',  reqs: 6, t3: false, descriptor: 'A runtime whose worst case is survivable. No ambient network, no secrets, hard ceilings.' },
  { id: 'VCA-6',  name: 'Memory & knowledge integrity',        prefix: 'MEM',   reqs: 5, t3: false, descriptor: 'What the agent remembers is provenance-tagged, validated on write, and reversible.' },
  { id: 'VCA-7',  name: 'Multi-agent & supply chain',          prefix: 'CHAIN', reqs: 6, t3: false, descriptor: 'Every component and every inter-agent channel is authenticated, integrity-protected and inventoried.' },
  { id: 'VCA-8',  name: 'Provenance & observability',          prefix: 'PROV',  reqs: 6, t3: true,  descriptor: 'Reconstruct any action afterward without relying on the agent’s own account of it.' },
  { id: 'VCA-9',  name: 'Adversarial evaluation',              prefix: 'EVAL',  reqs: 7, t3: true,  descriptor: 'The system is attacked continuously, and the results gate deployment.' },
  { id: 'VCA-10', name: 'Governance & operating model',        prefix: 'GOV',   reqs: 7, t3: false, descriptor: 'Every agent has an accountable human owner, a risk tier and a rehearsed incident response.' },
  { id: 'VCA-11', name: 'Action correctness & error containment', prefix: 'COR', reqs: 6, t3: false, descriptor: 'The right action on the right object, and errors caught before they become consequence.' },
];

/** Four action risk tiers, with the gate that must clear before authority widens. */
export const TIERS = [
  { id: 'T0', name: 'Observe',           meaning: 'Read-only access to non-sensitive data. No writes, no externally visible effect.', gate: 'Domains 1 · 3 · 5 · 8 at level 2', gateIds: ['VCA-1', 'VCA-3', 'VCA-5', 'VCA-8'] },
  { id: 'T1', name: 'Advise',            meaning: 'Drafts, recommendations and analysis a human reviews and executes. The human remains the actor of record.', gate: 'Domains 1 · 3 · 5 · 8 at level 2', gateIds: ['VCA-1', 'VCA-3', 'VCA-5', 'VCA-8'] },
  { id: 'T2', name: 'Act, reversible',   meaning: 'Bounded, reversible or staged effect, where a rollback path exists and has been tested.', gate: 'All eleven domains at level 2', gateIds: [] },
  { id: 'T3', name: 'Act, consequential', meaning: 'Irreversible, material or externally binding. Money movement, customer communication at scale, production configuration, data deletion.', gate: 'Plus VCA-1 · 2 · 3 · 4 · 8 · 9 at level 3', gateIds: ['VCA-1', 'VCA-2', 'VCA-3', 'VCA-4', 'VCA-8', 'VCA-9'] },
];

/** Summary figures for the mobile "headline figures first" pattern. */
export const SUMMARY = {
  domains: DOMAINS.length,
  requirements: DOMAINS.reduce((n, d) => n + d.reqs, 0),
  t3Gated: DOMAINS.filter((d) => d.t3).length,
};

/** A domain scores at the minimum of its gating requirements, never the average. */
export const SCORING_RULE = 'A domain scores at the minimum of its gating requirements, not the average.';
