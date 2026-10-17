// POSTs a form with the page's CSRF token. If the token has gone stale (HTTP 419 —
// the session expired, or the admin logged in/out in another tab), fetch a fresh
// token by reloading the page's HTML in the background and retry once.
const tokenMeta = () => document.querySelector('meta[name="csrf-token"]');

async function refreshToken() {
    const response = await fetch(location.href, { headers: { Accept: 'text/html' }, credentials: 'same-origin' });
    const html = await response.text();
    const fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('meta[name="csrf-token"]')?.content;
    if (!fresh) return false;

    tokenMeta()?.setAttribute('content', fresh);
    // Laravel reads a form's _token field before the header, so update those too.
    document.querySelectorAll('input[name="_token"]').forEach((input) => { input.value = fresh; });

    return true;
}

export async function postForm(form) {
    const send = () => {
        const body = new FormData(form);
        if (body.has('_token')) body.set('_token', tokenMeta()?.content ?? '');

        return fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': tokenMeta()?.content ?? '',
            },
            credentials: 'same-origin',
            body,
        });
    };

    let response = await send();

    if (response.status === 419 && await refreshToken()) {
        response = await send();
    }

    return response;
}
