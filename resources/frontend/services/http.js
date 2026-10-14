// Same-origin Laravel sessions: never store credentials or bearer tokens in the browser.
export async function apiFetch(url, options = {}) {
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');
  headers.delete('Authorization');
  if (!['GET', 'HEAD'].includes((options.method || 'GET').toUpperCase())) {
    const csrfResponse = await fetch('/api/frontend/csrf', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
    if (!csrfResponse.ok) throw new Error('Unable to start a secure session. Please reload the page.');
    const { token } = await csrfResponse.json();
    headers.set('X-CSRF-TOKEN', token);
  }
  return fetch(url, { ...options, headers, credentials: 'same-origin' });
}

export async function apiRequest(path, data, method = 'POST') {
  const response = await apiFetch(`/api/frontend/${path}`, {
    method,
    headers: { 'Content-Type': 'application/json' },
    ...(data === undefined ? {} : { body: JSON.stringify(data) }),
  });
  const body = await response.json();
  if (!response.ok) throw new Error(Object.values(body.errors || {}).flat()[0] || body.message || 'Request failed.');
  return body;
}
