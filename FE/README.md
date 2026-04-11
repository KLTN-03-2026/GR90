# Urban Bus Lookup Frontend (Vue 3 + Vite)

## Setup
1. Install dependencies:
```bash
npm install
```
2. Create env file from template:
```bash
cp .env.example .env.local
```
3. Update API endpoint in `.env.local`:
```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
VITE_API_BEARER_TOKEN=
```
4. Run dev server:
```bash
npm run dev
```

## Build
```bash
npm run build
```

## VPS Deployment
1. Create `.env.production` on VPS (in FE project root):
```env
VITE_API_BASE_URL=https://your-domain.com
VITE_API_BEARER_TOKEN=
```
2. Build frontend:
```bash
npm ci
npm run build
```
3. Serve `dist/` via Nginx or Apache.

## Notes
- API base URL is now read only from Vite env (`VITE_API_BASE_URL`).
- If `VITE_API_BASE_URL` does not end with `/api`, frontend automatically appends `/api`.
- Runtime API settings page (`/admin/cau-hinh`) has been removed and redirected to dashboard.
