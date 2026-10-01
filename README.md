# RPC - a light MVC framework

For documentation and how to use this please see https://github.com/eflorea/rpc-startup-app

## Security configuration

These `.env` settings affect security behaviour:

| Variable | Default | Purpose |
| --- | --- | --- |
| `TRUSTED_PROXIES` | unset | Comma-separated IPs/CIDR ranges of your load balancers or proxies (or `*`). `X-Forwarded-For`, `X-Real-IP`, `Client-IP` and `X-Forwarded-Proto` are **ignored** unless the request comes from one of these. Without it, `Request::getIP()` returns `REMOTE_ADDR`. |
| `SESSION_SECURE_COOKIE` | `false` | Force the `Secure` flag on the session cookie. It is set automatically when the request is HTTPS (directly or via a trusted proxy). |
| `SHOW_ERRORS` | `false` | Shows the Whoops debug page. Credentials are masked, but it still exposes source code and paths — never enable in production. |
| `DISABLE_CSRF` | `false` | Turns off CSRF validation for every POST. Prefer `$ignore_csrf` on individual controllers. |
| `LOG_QUERIES` | `false` | Writes every query, **with bound values** (including passwords), to the `query_logger` table. |

AJAX requests can send the CSRF token (`name_token`, as rendered into forms) in an `X-CSRF-Token` header instead of a `csrf_token` field.
