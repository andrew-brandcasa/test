/**
 * Contact form endpoint.
 *
 * Posts the enquiry to Resend, which delivers it to CONTACT_TO. The API key
 * never reaches the browser, which is why this runs server-side rather than
 * calling Resend from the form directly.
 *
 * Required environment variable, set in Vercel → Settings → Environment
 * Variables:
 *   RESEND_API_KEY   re_...  (from resend.com → API Keys)
 *
 * Optional, both have working defaults:
 *   CONTACT_TO       where enquiries land        (default hello@vulpine.ai)
 *   CONTACT_FROM     the From: address           (default onboarding@resend.dev)
 *
 * CONTACT_FROM has to be an address on a domain verified in Resend. Until
 * vulpine.ai is verified there, the default resend.dev sender works but will
 * only deliver to the Resend account owner's own address. Verifying the
 * domain and setting CONTACT_FROM to something like site@vulpine.ai is the
 * one step that makes this production-real.
 */

const RESEND_ENDPOINT = 'https://api.resend.com/emails';

/* Generous enough for a real enquiry, tight enough that the endpoint is not
   a free relay for anyone who finds it. */
const LIMITS = { name: 120, email: 200, org: 160, role: 160, message: 5000 };

const esc = (s) => String(s).replace(/[&<>]/g, (c) =>
  ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));

/* Deliberately permissive. Rejecting valid addresses because they look odd is
   worse than passing one bad address to Resend, which validates properly. */
const looksLikeEmail = (s) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);

/* CommonJS on purpose. There is no package.json in this project, so Node
   treats a bare .js file as CommonJS and an `export default` here would fail
   to load at runtime. fetch is global on the Node 24 runtime. */
module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST');
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const key = process.env.RESEND_API_KEY;
  if (!key) {
    /* Say so plainly rather than returning a success the sender would trust.
       A form that swallows a new-business enquiry is worse than no form. */
    console.error('contact: RESEND_API_KEY is not set');
    return res.status(503).json({ error: 'not_configured' });
  }

  let body = req.body;
  if (typeof body === 'string') {
    try { body = JSON.parse(body); } catch { body = null; }
  }
  if (!body || typeof body !== 'object') {
    return res.status(400).json({ error: 'Malformed request' });
  }

  const field = (k) => String(body[k] ?? '').trim().slice(0, LIMITS[k]);
  const name = field('name');
  const email = field('email');
  const org = field('org');
  const role = field('role');
  const message = field('message');

  /* Honeypot: a hidden input real people never fill in. Accept and discard,
     so the bot sees success and does not retry. */
  if (String(body.company ?? '').trim()) {
    return res.status(200).json({ ok: true });
  }

  if (!name || !email || !message) {
    return res.status(400).json({ error: 'Name, work email and a message are required.' });
  }
  if (!looksLikeEmail(email)) {
    return res.status(400).json({ error: 'That email address does not look right.' });
  }

  const to = process.env.CONTACT_TO || 'hello@vulpine.ai';
  const from = process.env.CONTACT_FROM || 'Vulpine site <onboarding@resend.dev>';

  const rows = [
    ['Name', name],
    ['Work email', email],
    ['Organization', org || '—'],
    ['Role', role || '—'],
  ].map(([k, v]) =>
    `<tr><td style="padding:4px 16px 4px 0;color:#5A5E69">${k}</td>` +
    `<td style="padding:4px 0;color:#15151B">${esc(v)}</td></tr>`).join('');

  const html = `<div style="font:15px/1.55 -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#15151B">
    <p style="margin:0 0 18px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#8A8D97">
      Enquiry from vulpine.ai</p>
    <table style="border-collapse:collapse;margin-bottom:22px">${rows}</table>
    <div style="border-top:1px solid rgba(21,21,27,.14);padding-top:18px;white-space:pre-wrap">${esc(message)}</div>
  </div>`;

  const text = `Enquiry from vulpine.ai

Name:         ${name}
Work email:   ${email}
Organization: ${org || '-'}
Role:         ${role || '-'}

${message}`;

  try {
    const r = await fetch(RESEND_ENDPOINT, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${key}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        from,
        to: [to],
        /* so hitting reply in the inbox answers the sender, not the robot */
        reply_to: email,
        subject: `Enquiry from ${name}${org ? ` · ${org}` : ''}`,
        html,
        text,
      }),
    });

    if (!r.ok) {
      const detail = await r.text();
      console.error('contact: resend rejected', r.status, detail);
      return res.status(502).json({ error: 'send_failed' });
    }
    return res.status(200).json({ ok: true });
  } catch (err) {
    console.error('contact: resend unreachable', err);
    return res.status(502).json({ error: 'send_failed' });
  }
}
