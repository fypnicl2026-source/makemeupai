# Production Deployment (Vercel + Railway)

Hybrid setup: Vue SPA on **Vercel**, Laravel API on **Railway**, PostgreSQL on Railway.

**Both on Vercel?** No. The frontend is a static Vite SPA and fits Vercel. The backend is a long-running Laravel PHP process with Sanctum sessions, Postgres, and local disk uploads — deploy it on Railway (Docker already in `backend/`).

## Architecture

| Service | Platform | URL |
|---------|----------|-----|
| Frontend | Vercel | https://makemeupai.vercel.app |
| Backend API | Railway | `https://api-production-1bae0.up.railway.app` |
| Database | Railway PostgreSQL | via `DATABASE_URL` |

```
Browser → Vercel (Vue SPA)
              │  VITE_API_URL + cookies
              ▼
         Railway (Laravel API) → PostgreSQL
                              → Volume /app/storage/app/public
```

---

## API keys & env vars you must set manually

### What you generate yourself (checklist)

| Secret / key | Where to create | Where to paste | Required? |
|--------------|-----------------|----------------|-----------|
| `APP_KEY` | Local: `cd backend && php artisan key:generate --show` | Railway | **Yes** |
| Railway Postgres | Add PostgreSQL plugin on Railway | Auto as `DATABASE_URL` | **Yes** |
| SMTP (`MAIL_*`) | [Mailtrap](https://mailtrap.io), Resend, Mailgun, SES, etc. | Railway | **Yes** for email verify + password reset |
| `OPENWEATHER_KEY` | Free key at [openweathermap.org/api](https://openweathermap.org/api) | Railway | Optional (weather falls back if empty) |
| Demo seed password | Choose a strong password | Railway `SEED_CLIENT_PASSWORD` | Optional |

**Not required for this app:** OpenAI, Anthropic, Stripe, Firebase, Cloudinary, AWS. Face analysis is a local heuristic (no external CV API).

### On Vercel (frontend only — no server secrets)

Project → Settings → Environment Variables → Production.

Vite embeds these at **build time**. After any change, **redeploy**.

| Variable | Required? | Value |
|----------|-----------|-------|
| `VITE_API_URL` | **Yes** | Railway public API URL, e.g. `https://xxx.up.railway.app` (no trailing slash) |
| `VITE_SITE_URL` | **Yes** | Your Vercel URL, e.g. `https://makemeupai.vercel.app` (or custom domain) |

### On Railway (backend)

Set on the **web/API service** (not only on Postgres):

| Variable | Required? | Value / how to set |
|----------|-----------|--------------------|
| `APP_ENV` | Yes | `production` |
| `APP_DEBUG` | Yes | `false` |
| `APP_KEY` | **Yes** | Output of `php artisan key:generate --show` (`base64:...`) |
| `APP_URL` | Yes | Same as Railway public HTTPS URL |
| `FRONTEND_URL` | Yes | Exact Vercel origin, e.g. `https://makemeupai.vercel.app` (CORS) |
| `SANCTUM_STATEFUL_DOMAINS` | Yes | Host only, e.g. `makemeupai.vercel.app` (no `https://`) |
| `DB_CONNECTION` | Yes | `pgsql` |
| `DATABASE_URL` | Yes | Injected when you add Railway PostgreSQL |
| `SESSION_DRIVER` | Yes | `database` |
| `SESSION_SECURE_COOKIE` | Yes | `true` |
| `SESSION_SAME_SITE` | Yes | `none` |
| `SESSION_DOMAIN` | Yes | Leave **empty** |
| `FILESYSTEM_DISK` | Yes | `public` |
| `MAIL_MAILER` | For email features | `smtp` |
| `MAIL_HOST` | For email features | `live.smtp.mailtrap.io` (Mailtrap Sending) |
| `MAIL_PORT` | For email features | `587` |
| `MAIL_USERNAME` | For email features | `api` |
| `MAIL_PASSWORD` | For email features | Mailtrap API token (Settings → API Tokens) |
| `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` | For email features | Must use a **verified** sending domain in Mailtrap (e.g. `hello@yourdomain.com`) |
| `OPENWEATHER_KEY` | Optional | OpenWeatherMap API key |
| `SEED_CLIENT_EMAIL` / `SEED_CLIENT_PASSWORD` | Optional | Demo client seeded on boot when password is set |

**Mailtrap note:** Put `MAIL_*` on the **Railway API** service (and in `backend/.env` locally) — never in the Vite frontend `.env`. If Mailtrap returns `Sending from domain … is not allowed`, open Mailtrap → **Sending Domains**, verify that domain’s DNS, then use the same domain in `MAIL_FROM_ADDRESS`.

**Uploads:** Attach a Railway **Volume** at `/app/storage/app/public` so wardrobe/selfie files survive redeploys.

---

## 1. Railway (backend)

1. [railway.app](https://railway.app) → New Project → Deploy from GitHub → your `makemeupai` repo
2. Set **Root Directory** to `backend`
3. Builder uses [`backend/Dockerfile`](../backend/Dockerfile) + [`backend/railway.toml`](../backend/railway.toml)
4. Add **PostgreSQL** to the project
5. Set the environment variables from the table above
6. Optional: Volume at `/app/storage/app/public`
7. Generate public domain: Settings → Networking
8. Entrypoint already runs `migrate` + `db:seed` + `storage:link` on boot
9. Verify: `curl https://<api-host>/up` and `curl https://<api-host>/api/beauticians`

## 2. Vercel (frontend)

1. [vercel.com](https://vercel.com) → Import the same repo
2. **Root Directory:** repository root (default)
3. Framework: Vite (uses [`vercel.json`](../vercel.json) → `npm run build` → `dist/`)
4. Set `VITE_API_URL` and `VITE_SITE_URL` (see above)
5. Deploy

## 3. Wire both services

1. Set Railway `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` to your final Vercel URL/host
2. Set Vercel `VITE_API_URL` to your final Railway URL
3. Redeploy both if either URL changed

## 4. Production QA

Use [TESTING_CHECKLIST.md](../TESTING_CHECKLIST.md) against live URLs. Watch for:

| Symptom | Fix |
|---------|-----|
| CORS error | `FRONTEND_URL` mismatch on Railway |
| 419 CSRF | Add Vercel domain to `SANCTUM_STATEFUL_DOMAINS` |
| Auth cookie lost | `SESSION_SAME_SITE=none` + `SESSION_SECURE_COOKIE=true` |
| Upload 404 | `storage:link` + Railway volume on `storage/app/public` |
| API calls localhost | Rebuild Vercel with correct `VITE_API_URL` |

## Custom domains

Point `makemeupai.com` to Vercel and `api.makemeupai.com` to Railway, then update all env vars above.
