# Talk Review API (Backend)

Laravel 12 + Sanctum + Spatie Permissions + Scout (Elasticsearch) + Reverb (WebSockets) + MailHog.

## Services (Docker)
- **nginx** → http://localhost:8080
- **mysql** → localhost:33067
- **redis** → localhost:6380
- **mailhog UI** → http://localhost:8025
- **elasticsearch** → http://localhost:9200
- **reverb** (ws) → ws://localhost:6001

## Quick start (Docker)
From the **backend repo root**:

1) Create `.env`
```bash
cp .env.example .env
```
Fill (or keep) values. At minimum, make sure these match your compose:
```dotenv
APP_URL=http://localhost:8080
FRONTEND_URL=http://localhost:5173

DB_HOST=db
DB_PORT=3306
DB_DATABASE=talkreview
DB_USERNAME=talkreview
DB_PASSWORD=talkreview

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=talkreview
REVERB_APP_KEY=localkey123
REVERB_APP_SECRET=localsecret123
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=6001
REVERB_HOST=localhost
REVERB_PORT=6001
REVERB_SCHEME=http
REVERB_ALLOWED_ORIGINS=http://localhost:5173
```

2) Build + start containers
```bash
docker compose up -d --build
```

3) Install PHP deps
```bash
docker exec -it talkreview_app sh -lc "composer install"
```

4) Generate key (if needed)
```bash
docker exec -it talkreview_app sh -lc "php artisan key:generate"
```

5) Migrate + seed
```bash
docker exec -it talkreview_app sh -lc "php artisan migrate --seed"
docker exec -it talkreview_app sh -lc "php artisan elastic:migrate"
```

6) (Optional) Storage symlink
```bash
docker exec -it talkreview_app sh -lc "php artisan storage:link"
```

7) Verify health
```bash
curl -s http://localhost:8080/api/health
```

## Realtime notifications (Reverb)
- Reverb server runs in the `reverb` service:
    - Port: **6001**
    - Pusher-protocol endpoint: **/app/{APP_KEY}**
- Auth endpoint for private channels:
    - `POST http://localhost:8080/broadcasting/auth` (Sanctum bearer token)

Quick WS smoke test:
```bash
wscat -H "Origin: http://localhost:5173" -c "ws://localhost:6001/app/localkey123?protocol=7&client=js&version=8.4.0&flash=false"
```

## Elasticsearch
Elasticsearch runs in Docker at:
```bash
curl -s http://localhost:9200
```

This project uses **Laravel Scout** with the `elastic` driver (see `.env` keys: `SCOUT_DRIVER`, `ELASTICSEARCH_HOST`).

Typical flow (if your app uses Scout indexing):
```bash
docker exec -it talkreview_app sh -lc "php artisan scout:import 'App\\Models\\Proposal'"
docker exec -it talkreview_app sh -lc "php artisan scout:import 'App\\Models\\ProposalReview'"
```

If your repo contains **Elasticsearch migrations**, run the migration command provided by the package used in your project:
```bash
docker exec -it talkreview_app sh -lc "php artisan | grep -i elastic"
```
Then run the appropriate command you see (for example, some packages use `elastic:migrate`).

## Mail (MailHog)
Outgoing emails (verification links) are captured by MailHog:
- UI: http://localhost:8025
- SMTP: `mailhog:1025`

## Swagger / OpenAPI
If L5-Swagger is installed/configured, open the API docs in your backend:
- common path: `http://localhost:8080/api/documentation`

(Exact URL depends on your `l5-swagger` config.)

## Running tests
```bash
docker exec -it talkreview_app sh -lc "php artisan test"
```

## Common troubleshooting
### 403 on /broadcasting/auth
- Ensure frontend sends `Authorization: Bearer <token>`
- Ensure backend CORS allows `http://localhost:5173` for `/broadcasting/auth`
- Ensure `BroadcastServiceProvider` has:
    - `Broadcast::routes(['middleware' => ['auth:sanctum']]);`

### “Failed to connect to localhost:6001” from backend
Inside Docker, `localhost` means inside the **api container**.  
If the backend needs to call Reverb internally, use the **service name**:
- `REVERB_HOST=reverb` (container-to-container)
  While the browser should keep using:
- `ws://localhost:6001` (host-to-container)

If you want both to work, use env separation (server bind vs client host) exactly like:
- `REVERB_SERVER_HOST=0.0.0.0`
- `REVERB_SERVER_PORT=6001`
- `REVERB_HOST=localhost` (browser)
  and in `config/broadcasting.php` for server-side events, prefer `host` as `reverb` inside Docker if needed.
