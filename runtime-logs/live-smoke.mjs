const result = { startedAt: new Date().toISOString(), checks: [], refs: {}, warnings: [] };
const base = 'http://127.0.0.1:8000/api/v1';
const front = 'http://localhost:3000';
const password = 'ChangeMeLocalOnly123!';
const stamp = Date.now().toString().slice(-8);

async function api(method, path, body, token, expected = 200) {
  const res = await fetch(base + path, {
    method,
    headers: {
      Accept: 'application/json',
      ...(body == null ? {} : { 'Content-Type': 'application/json' }),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: body == null ? undefined : JSON.stringify(body),
  });
  const text = await res.text();
  let data;
  try {
    data = text ? JSON.parse(text) : null;
  } catch {
    data = text;
  }
  const ok = Array.isArray(expected) ? expected.includes(res.status) : res.status === expected;
  if (!ok) {
    const detail = typeof data === 'string' ? data.slice(0, 400) : JSON.stringify(data).slice(0, 700);
    throw new Error(`${method} ${path} expected ${expected} got ${res.status}: ${detail}`);
  }
  result.checks.push(`${method} ${path} ${res.status}`);
  return { status: res.status, data };
}

async function page(path) {
  const res = await fetch(front + path);
  if (res.status !== 200) {
    throw new Error(`FRONT ${path} got ${res.status}`);
  }
  result.checks.push(`FRONT ${path} 200`);
}

const dataOf = (response) => response?.data?.data ?? response?.data;
const listOf = (response) => {
  const data = dataOf(response);
  return Array.isArray(data) ? data : (data?.data ?? []);
};
const checkoutPayload = (sessionToken, phone = '01000000000', email = 'customer@example.test') => ({
  session_token: sessionToken,
  customer: { name: 'Customer One', phone, email },
  shipping_address: {
    recipient_name: 'Customer One',
    phone,
    governorate: 'Cairo',
    city: 'Cairo',
    street_address: 'Test street',
  },
});

await api('GET', '/health', null, null);
for (const path of ['/', '/login', '/dashboard', '/dates', '/oils', '/real-estate/real-estate', '/import-export/rfq']) {
  await page(path);
}

const adminToken = dataOf(await api('POST', '/auth/login', { email: 'admin@abuqasaa.test', password }, null)).token;
const me = dataOf(await api('GET', '/auth/me', null, adminToken));
result.refs.superAdminPermissions = me.permissions?.length ?? 0;

await api('GET', '/public/cms/pages', null, null);
await api('GET', '/public/cms/menus/main', null, null);
result.refs.contactInquiry = dataOf(await api('POST', '/public/contact-inquiries', {
  name: 'Runtime Tester',
  email: `runtime${stamp}@example.test`,
  phone: '01099990000',
  subject: 'runtime',
  message: 'local acceptance',
}, null, 201)).id;
await api('GET', '/cms/contact-inquiries', null, adminToken);

const dateProduct = listOf(await api('GET', '/public/dates/products', null, null)).find((product) => product.slug === 'premium-medjool-dates-1kg');
const oilProduct = listOf(await api('GET', '/public/oils/products', null, null)).find((product) => product.slug === 'premium-engine-oil-4l');
if (!dateProduct || !oilProduct) {
  throw new Error('Seeded products not found');
}
result.refs.dateProduct = dateProduct.slug;
result.refs.oilProduct = oilProduct.slug;

const datesCart = dataOf(await api('POST', '/public/dates/cart', {}, null));
await api('POST', `/public/dates/cart/${datesCart.session_token}/items`, { product_id: dateProduct.id, quantity: 1 }, null, 201);
const datesOrder = dataOf(await api('POST', '/public/dates/checkout', checkoutPayload(datesCart.session_token, '01000000000', `dates${stamp}@example.test`), null, 201));
result.refs.datesOrder = datesOrder.order_number;
result.refs.datesOrderId = datesOrder.id;
await api('GET', `/public/dates/orders/${datesOrder.order_number}?phone=01000000000`, null, null);
await api('GET', '/public/dates/payment-methods', null, null);
const paymob = dataOf(await api('POST', `/public/dates/orders/${datesOrder.order_number}/paymob/initiate`, { phone: '01000000000', method_key: 'paymob_card' }, null, 201));
result.refs.paymobFake = paymob.payment_reference || paymob.iframe_url || paymob.id;
await api('POST', '/payments/paymob/callback', { success: false }, null, [200, 403, 422]);
const proof = dataOf(await api('POST', `/public/dates/orders/${datesOrder.order_number}/manual-payment-proofs`, {
  phone: '01000000000',
  method_key: 'vodafone_cash',
  amount: datesOrder.grand_total,
  payer_name: 'Customer One',
  sender_account: '01011111111',
  transaction_reference: `VC-${stamp}`,
  notes: 'runtime proof',
}, null, 201));
result.refs.manualProof = proof.id;
await api('GET', '/payments/manual-proofs', null, adminToken);
await api('POST', `/payments/manual-proofs/${proof.id}/approve`, { admin_notes: 'runtime approved' }, adminToken);
await api('POST', `/payments/manual-proofs/${proof.id}/approve`, { admin_notes: 'duplicate idempotency check' }, adminToken);

await api('GET', '/inventory/summary', null, adminToken);
await api('GET', '/inventory/warehouses', null, adminToken);
const dateStock = listOf(await api('GET', '/inventory/stock-items', null, adminToken)).find((item) => item.product_id === dateProduct.id);
await api('POST', '/inventory/stock-items/receive', {
  business_unit_id: dateStock.business_unit_id,
  warehouse_id: dateStock.warehouse_id,
  product_id: dateStock.product_id,
  quantity: 2,
}, adminToken, 201);
await api('POST', '/inventory/stock-items/adjust', {
  business_unit_id: dateStock.business_unit_id,
  warehouse_id: dateStock.warehouse_id,
  product_id: dateStock.product_id,
  type: 'adjustment_out',
  quantity: 1,
}, adminToken);
await api('POST', `/commerce/orders/${datesOrder.id}/cancel`, { reason: 'runtime acceptance cleanup' }, adminToken, [200, 409]);

const oilsToken = dataOf(await api('POST', '/auth/login', { email: 'oils.admin@abuqasaa.test', password }, null)).token;
await api('GET', '/public/oils/wholesale/products', null, null, 403);
const wholesaleToken = dataOf(await api('POST', '/public/oils/wholesale/access', { phone: '01011111111' }, null)).token;
await api('GET', `/public/oils/wholesale/products?phone=01011111111&token=${encodeURIComponent(wholesaleToken)}`, null, null);
const oilsCart = dataOf(await api('POST', '/public/oils/cart', {}, null));
await api('POST', `/public/oils/cart/${oilsCart.session_token}/items`, {
  product_id: oilProduct.id,
  quantity: 1,
  wholesale_phone: '01011111111',
  wholesale_token: wholesaleToken,
}, null, 422);
await api('POST', `/public/oils/cart/${oilsCart.session_token}/items`, {
  product_id: oilProduct.id,
  quantity: 12,
  wholesale_phone: '01011111111',
  wholesale_token: wholesaleToken,
}, null, 201);
result.refs.wholesaleOrder = dataOf(await api('POST', '/public/oils/checkout', {
  ...checkoutPayload(oilsCart.session_token, '01011111111', `wh${stamp}@example.test`),
  wholesale_phone: '01011111111',
  wholesale_token: wholesaleToken,
}, null, 201)).order_number;
result.refs.wholesaleApplication = dataOf(await api('POST', '/public/oils/wholesale/apply', {
  applicant_name: 'Wholesale Applicant',
  phone: `0103333${stamp.slice(0, 4)}`,
  email: `applicant${stamp}@example.test`,
  company_name: 'Applicant Shop',
  shop_name: 'Applicant Shop',
  governorate: 'Cairo',
  city: 'Cairo',
  address: 'Test wholesale address',
  message: 'Please review my application.',
}, null, 201)).id;
await api('GET', '/wholesale/applications', null, oilsToken);

const realEstateProject = listOf(await api('GET', '/public/real-estate/real-estate/projects', null, null))[0];
const realEstateUnits = listOf(await api('GET', '/public/real-estate/real-estate/units', null, null));
const realEstateUnit = realEstateUnits.find((unit) => !['reserved', 'sold'].includes(unit.status)) || realEstateUnits[0];
const lead = dataOf(await api('POST', '/public/real-estate/real-estate/leads', {
  project_id: realEstateProject.id,
  unit_id: realEstateUnit?.id,
  source: 'public_project',
  name: 'Runtime Lead',
  phone: `0102222${stamp.slice(0, 4)}`,
  email: `lead${stamp}@example.test`,
  message: 'Interested',
}, null, 201));
result.refs.realEstateLead = lead.id;
result.refs.realEstateViewing = dataOf(await api('POST', '/public/real-estate/real-estate/viewing-requests', {
  lead_id: lead.id,
  project_id: realEstateProject.id,
  scheduled_at: '2026-08-01T10:00:00.000Z',
  duration_minutes: 45,
}, null, 201)).id;
if (realEstateUnit && !['reserved', 'sold'].includes(realEstateUnit.status)) {
  result.refs.realEstateReservation = dataOf(await api('POST', '/public/real-estate/real-estate/reservation-interests', {
    project_id: realEstateProject.id,
    unit_id: realEstateUnit.id,
    name: 'Runtime Buyer',
    phone: `0104444${stamp.slice(0, 4)}`,
  }, null, 201)).id;
  await api('POST', '/public/real-estate/real-estate/reservation-interests', {
    project_id: realEstateProject.id,
    unit_id: realEstateUnit.id,
    name: 'Runtime Buyer 2',
    phone: `0105555${stamp.slice(0, 4)}`,
  }, null, 409);
}
await api('GET', '/real-estate/leads', null, adminToken);

const service = listOf(await api('GET', '/public/import-export/services', null, null))[0];
const rfq = dataOf(await api('POST', '/public/import-export/rfq-requests', {
  service_id: service.id,
  company_name: 'Acme Import',
  contact_name: 'Omar Ali',
  phone: '01055556666',
  email: `omar${stamp}@example.test`,
  origin_country: 'Egypt',
  destination_country: 'UAE',
  items: [{ item_name: 'Dates boxes', quantity: 100, unit: 'carton' }],
}, null, 201));
result.refs.rfq = rfq.rfq_number;
await api('GET', `/public/import-export/rfq-requests/${rfq.rfq_number}/status?contact=01055556666`, null, null);
await api('GET', `/public/import-export/rfq-requests/${rfq.rfq_number}/status?contact=wrong`, null, null, 404);
const importExportToken = dataOf(await api('POST', '/auth/login', { email: 'importexport.admin@abuqasaa.test', password }, null)).token;
await api('GET', '/services-rfq/rfq-requests', null, importExportToken);
const quotation = dataOf(await api('POST', `/services-rfq/rfq-requests/${rfq.id}/quotations`, {
  currency: 'EGP',
  tax_total: 140,
  shipping_total: 250,
  items: [
    { description: 'Sea freight', quantity: 2, unit: 'container', unit_price: 1000 },
    { description: 'Docs handling', quantity: 1, unit: 'service', unit_price: 400 },
  ],
}, importExportToken, 201));
result.refs.rfqQuotation = quotation.id;
await api('POST', `/services-rfq/quotations/${quotation.id}/send`, {}, importExportToken);

await api('GET', '/reports/executive-summary', null, adminToken);
await api('GET', '/reports/commerce/orders/export', null, adminToken);
await api('GET', '/audit-logs', null, adminToken);
await api('GET', '/inventory/summary', null, null, 401);
await api('GET', '/wholesale/applications', null, null, 401);
await api('GET', '/services-rfq/rfq-requests', null, oilsToken);
await api('POST', '/auth/logout', {}, adminToken);
await api('POST', '/auth/login', { email: 'admin@abuqasaa.test', password }, null);

result.finishedAt = new Date().toISOString();
result.totalChecks = result.checks.length;
console.log(JSON.stringify(result, null, 2));
