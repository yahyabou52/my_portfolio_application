(function () {
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  if (!token) return;
  const originalFetch = window.fetch.bind(window);
  window.fetch = function(input, init) {
    const options = Object.assign({}, init || {});
    const url = new URL(input instanceof Request ? input.url : input, location.href);
    const method = String(options.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
    if (url.origin === location.origin && !['GET', 'HEAD', 'OPTIONS'].includes(method)) {
      const headers = new Headers(options.headers || (input instanceof Request ? input.headers : undefined));
      headers.set('X-CSRF-Token', token); options.headers = headers;
    }
    return originalFetch(input, options);
  };
})();
