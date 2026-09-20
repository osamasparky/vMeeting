/**
 * Shared same-origin API client for the session-authenticated web UI.
 *
 * Replaces the hand-copied `fetch(url, { headers: { 'X-CSRF-TOKEN': ... } })`
 * blocks scattered through the inline page scripts (~100 call sites) with
 * one place that:
 *   - attaches the CSRF token to every state-changing request,
 *   - asks for JSON (`Accept`) so the server answers errors as JSON,
 *   - handles an expired session (HTTP 419) the same way everywhere.
 *
 * `apiFetch` is a drop-in for `fetch` — it returns the raw Response, so
 * existing `res.ok` / `res.json()` handling keeps working unchanged.
 * `apiJson` is the convenience form: it parses the body and throws an
 * ApiError (with .status and .data) on a non-2xx response.
 *
 * Optional translated message, set by the page before this loads:
 *   window.ApiClientI18N = { sessionExpired: '...' }
 */
(function (global) {
    'use strict';

    var sessionExpiredHandled = false;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) {
            return meta.content;
        }
        // Pages that predate the meta tag expose a global instead.
        return typeof global.CSRF_TOKEN === 'string' ? global.CSRF_TOKEN : '';
    }

    function handleSessionExpired() {
        if (sessionExpiredHandled) {
            return;
        }
        sessionExpiredHandled = true;
        var i18n = global.ApiClientI18N || {};
        global.alert(i18n.sessionExpired || 'Session expired. The page will reload now.');
        global.location.reload();
    }

    function ApiError(message, status, data) {
        this.name = 'ApiError';
        this.message = message;
        this.status = status;
        this.data = data;
    }
    ApiError.prototype = Object.create(Error.prototype);
    ApiError.prototype.constructor = ApiError;

    function apiFetch(url, options) {
        options = options || {};
        var method = (options.method || 'GET').toUpperCase();
        var headers = Object.assign({ Accept: 'application/json' }, options.headers || {});
        var body = options.body;

        // Plain objects are sent as JSON; strings/FormData/etc. pass through.
        if (body && typeof body === 'object' && !(body instanceof FormData) && !(body instanceof Blob) && !(body instanceof URLSearchParams)) {
            body = JSON.stringify(body);
            if (!headers['Content-Type']) {
                headers['Content-Type'] = 'application/json';
            }
        }

        if (method !== 'GET' && method !== 'HEAD' && !headers['X-CSRF-TOKEN']) {
            headers['X-CSRF-TOKEN'] = csrfToken();
        }

        return fetch(url, Object.assign({ credentials: 'same-origin' }, options, { method: method, headers: headers, body: body }))
            .then(function (res) {
                if (res.status === 419) {
                    handleSessionExpired();
                }
                return res;
            });
    }

    function apiJson(url, options) {
        return apiFetch(url, options).then(function (res) {
            return res.text().then(function (text) {
                var data = null;
                if (text) {
                    try { data = JSON.parse(text); } catch (e) { data = { message: text }; }
                }
                if (!res.ok) {
                    throw new ApiError((data && data.message) || res.statusText || 'Request failed', res.status, data);
                }
                return data;
            });
        });
    }

    global.apiFetch = apiFetch;
    global.apiJson = apiJson;
    global.ApiError = ApiError;
})(window);
