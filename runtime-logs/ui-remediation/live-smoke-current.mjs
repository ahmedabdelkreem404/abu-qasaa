const result = { startedAt: new Date().toISOString(), checks: [] };
const apiBase = "http://127.0.0.1:8000/api/v1";
const frontBase = "http://127.0.0.1:3000";
const token = process.env.AQ_VISUAL_QA_TOKEN;

if (!token) {
  throw new Error("AQ_VISUAL_QA_TOKEN is required");
}

async function api(path, expected = 200, auth = false) {
  const response = await fetch(`${apiBase}${path}`, {
    headers: {
      Accept: "application/json",
      ...(auth ? { Authorization: `Bearer ${token}` } : {}),
    },
  });
  if (response.status !== expected) {
    throw new Error(`API ${path} expected ${expected} got ${response.status}: ${(await response.text()).slice(0, 300)}`);
  }
  result.checks.push(`API ${path} ${response.status}`);
  return response.json();
}

async function page(path) {
  const response = await fetch(`${frontBase}${path}`);
  if (response.status !== 200) {
    throw new Error(`FRONT ${path} got ${response.status}`);
  }
  result.checks.push(`FRONT ${path} 200`);
}

await api("/health");
await api("/auth/me", 200, true);
await api("/public/business-units");
await api("/public/cms/menus/main");
await api("/public/cms/pages/home");
await api("/public/oils/products");
await api("/public/dates/products");
await api("/public/real-estate/real-estate/projects");
await api("/public/import-export/services");
await api("/cms/pages", 200, true);
await api("/business-units", 200, true);

for (const route of ["/", "/business-units", "/oils", "/dates", "/real-estate", "/import-export", "/login", "/dashboard"]) {
  await page(route);
}

result.finishedAt = new Date().toISOString();
result.totalChecks = result.checks.length;
console.log(JSON.stringify(result, null, 2));
