# AgentDesk

A Laravel-based helpdesk application with AI-powered triage, reply drafting, and knowledge base integration. Built on top of the [nunomaduro/laravel-starter-kit](https://github.com/nunomaduro/laravel-starter-kit) with ultra-strict engineering standards.

## Project overview

AgentDesk provides role-based dashboards for three user types:

- **Requester** — submits and tracks support tickets
- **Support Agent** (human) — triages, replies, and resolves tickets
- **Admin** — manages categories, macros, knowledge base, SLA targets, users, audit logs, and AI run history

AI features assist human agents by automatically triaging tickets and drafting reply suggestions using the Laravel AI SDK with Groq as the LLM provider.

## Architecture summary

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│  Livewire   │────▶│  Actions/Jobs    │───▶│  AI Agents  │
│  Components │     │  (queued)        │     │  (Groq LLM) │
└─────────────┘     └──────────────────┘     └─────────────┘
       │                    │                       │
       ▼                    ▼                       ▼
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│  Blade      │     │  Models / DTOs   │     │  ai_runs    │
│  Templates  │     │  Policies        │     │  (tracking) │
└─────────────┘     └──────────────────┘     └─────────────┘
```

All AI operations follow this flow:

**Livewire action → create `ai_runs` record → dispatch queued job → job invokes AI Agent → persist structured output → UI polls/refreshes**

AI agents are never called directly from UI rendering logic.

## Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.5+ |
| Framework | Laravel 12 |
| Frontend | Livewire 4, Alpine.js, Tailwind CSS v4 |
| Build | Vite 7, Bun |
| AI | Laravel AI SDK (`laravel/ai`), Groq provider |
| Testing | Pest 4 (100% coverage, 100% type coverage) |
| Static analysis | PHPStan max level + bleeding edge (Larastan) |
| Code style | Pint, Rector, Prettier |
| Strict defaults | `nunomaduro/essentials` — strict models, auto eager loading, immutable dates |
| Database | SQLite (development), database queue/sessions/cache |

## Installation

### Prerequisites

- PHP 8.5+ with Xdebug (for code coverage)
- Composer 2
- Bun
- A Groq API key (free at [console.groq.com](https://console.groq.com))

### Setup

```bash
# Clone the repository
git clone https://github.com/Ayush-Barai/agent-desk.git
cd agent-desk

# Install dependencies, generate key, run migrations, build frontend
composer setup
```

## Environment configuration

Copy and edit the `.env` file:

```bash
cp .env.example .env
```

Key settings:

```env
APP_NAME=AgentDesk
APP_URL=http://localhost

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

MAIL_MAILER=log
```

### Groq API key setup

1. Create a free account at [console.groq.com](https://console.groq.com)
2. Generate an API key
3. Add to `.env`:

```env
AI_PROVIDER=groq
GROQ_API_KEY=gsk_your_api_key_here
```

### Key Generation

```bash
php artisan key:generate 
```

## Database setup

### Migrations

```bash
php artisan migrate 
```

### Seeders

```bash
php artisan db:seed
```

The seeder creates:

| Type | Data |
|---|---|
| Users | Admin (`admin@agentdesk.test`), Agent (`agent@agentdesk.test`), Requester (`requester@agentdesk.test`) — password: `password` |
| Categories | Billing, Technical Support, General Inquiry |
| Tags | bug, feature-request, urgent |
| Macros | Greeting template |
| Support targets | Default first-response and resolution targets |
| KB articles | "Getting Started", "Password Reset" |

## Running the application

### Development server

```bash
composer dev
```

This starts concurrently:
- Laravel server (`php artisan serve`)
- Queue worker (`php artisan queue:listen --tries=1`)
- Vite dev server (`npm run dev`)

### Queue worker

The queue worker is included in `composer dev`. To run it independently:

```bash
php artisan queue:listen --tries=1
```

The queue processes AI triage jobs, reply draft jobs, and overdue target checks.

### Scheduler

The scheduler runs `CheckOverdueTargetsJob` every five minutes to monitor SLA compliance:

```bash
php artisan schedule:work
```

Or add to cron:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

### Full test suite

```bash
composer test
```

This runs (in order):
1. `test:type-coverage` — 100% type coverage via Pest
2. `test:unit` — Pest tests with 100% code coverage
3. `test:lint` — Pint, Rector, Prettier dry-run checks
4. `test:types` — PHPStan at maximum strictness

### Individual commands

```bash
# Type coverage (100% required)
composer test:type-coverage

# Unit tests with code coverage (100% required)
# On Windows PowerShell:
$env:XDEBUG_MODE="coverage"; php vendor/bin/pest --parallel --coverage --exactly=100.0
# On Linux/macOS:
XDEBUG_MODE=coverage php vendor/bin/pest --parallel --coverage --exactly=100.0

# Static analysis (PHPStan max level)
composer test:types

# Lint (Rector + Pint + Prettier)
composer lint
```

### Test statistics

- **462 tests**, **1072 assertions**
- **100.0%** code coverage
- **100.0%** type coverage
- **0 PHPStan errors** at max level

## Branch-by-branch local workflow

This project was built incrementally across 15 branches. Each branch builds on the previous and must pass all quality gates before merging.

> **Important:** All branches are local. Do not push automatically. Each branch must pass `composer test` before merging into `main`.

## AI architecture

### TriageAgent (`app/Ai/Agents/TriageAgent.php`)

Analyzes incoming support tickets and produces structured output:

- Suggests **category** and **priority**
- Generates a short **summary**
- Recommends **tags** from the existing tag pool
- Produces **clarifying questions** for the support agent
- Flags tickets requiring **escalation**

Invoked via `RunTicketTriageJob` (queued). Results are stored in the `ai_runs` table and displayed in the agent triage panel.

### ReplyDraftAgent (`app/Ai/Agents/ReplyDraftAgent.php`)

Drafts professional support replies using ticket context and conversation history:

- Uses `SearchKnowledgeBaseTool` to find relevant KB articles
- Drafts an empathetic reply addressing the customer's issue
- Suggests **next steps** for both the customer and the support team
- Flags **risks or concerns**
- References which KB articles were used

Invoked via `DraftTicketReplyJob` (queued). The draft is stored as an `is_ai_draft` message on the ticket.

### SearchKnowledgeBaseTool (`app/Ai/Tools/SearchKnowledgeBaseTool.php`)

A tool available to the ReplyDraftAgent that searches the knowledge base:

- Searches by keyword across article title and content
- Returns up to 5 matching published articles
- Outputs structured `KbSnippetDTO` objects with title, excerpt, and article ID

### ai_runs table

Tracks every AI operation:

| Field | Purpose |
|---|---|
| `run_type` | `Triage`, `ReplyDraft`, or `ThreadSummary` |
| `status` | `Queued` → `Running` → `Succeeded` / `Failed` |
| `initiated_by_user_id` | The human agent who triggered the AI run |
| `input_hash` | Deduplication — prevents re-running identical inputs |
| `provider` / `model` | Which AI provider and model was used |
| `output_json` | Structured result from the agent |
| `error_message` | Error details if the run failed |
| `started_at` / `completed_at` | Timing metrics |

Admins can view all AI runs at `/admin/ai-runs` and drill into individual run details.

## Demo script

### 1. Start the application

```bash
composer setup
php artisan db:seed
composer dev
```

### 2. Log in as Requester

- URL: `http://localhost:8000/login`
- Email: `requester@agentdesk.test`
- Password: `password`
- Create a new ticket with subject "Cannot reset my password" and a description

### 3. Log in as Support Agent

- Email: `agent@agentdesk.test`
- Password: `password`
- Navigate to the triage queue (`/agent/triage`)
- Open the ticket and click **Run AI Triage** — observe the triage panel populate with category, priority, summary, and tags
- Click **Draft AI Reply** — observe a professional reply draft appear, referencing KB articles
- Accept or edit the draft and send a public reply

### 4. Log in as Admin

- Email: `admin@agentdesk.test`
- Password: `password`
- Browse `/admin/users` — manage user roles
- Browse `/admin/categories` — create/edit ticket categories
- Browse `/admin/kb-articles` — manage knowledge base
- Browse `/admin/targets` — configure SLA response/resolution targets
- Browse `/admin/audit-logs` — view all system activity
- Browse `/admin/ai-runs` — inspect AI triage and reply draft runs with full input/output
- Browse `/admin/agent-reports` — view per-agent work metrics (tickets assigned, replies sent, resolutions, AI runs)

### 5. Test the scheduler

```bash
php artisan schedule:run
```

This triggers `CheckOverdueTargetsJob` to flag tickets that have breached first-response or resolution targets.

## Troubleshooting

### Groq API errors

- Verify `GROQ_API_KEY` is set correctly in `.env`
- Check the model name in `GROQ_MODEL` is valid (e.g., `llama-3.3-70b-versatile`)
- Groq has rate limits on free tier — wait and retry if you hit 429 errors
- AI runs that fail are recorded in the `ai_runs` table with `status=Failed` and `error_message` populated

### Queue not processing

- Ensure `QUEUE_CONNECTION=database` in `.env`
- Run `php artisan queue:listen --tries=1` or use `composer dev` which includes the queue worker
- Check `failed_jobs` table for errors: `php artisan queue:failed`

### Tests failing

- Ensure Xdebug is installed and enabled for coverage: `php -m | grep xdebug`
- On Windows PowerShell, set `$env:XDEBUG_MODE="coverage"` before running Pest
- Run `composer lint` first to fix formatting issues that may cause dry-run lint failures
- Run `php artisan migrate:fresh --env=testing` to reset the test database

### PHPStan errors after changes

- Run `composer lint` first — Rector may auto-fix the issue
- Closures inside `where()`, `whereHas()`, `withCount()` need typed `$q` parameter: `function (Builder $q) {}`
- `getAttribute()` returns `mixed` — use `/** @var int */` annotations when casting

### SQLite issues

- Ensure the `database/database.sqlite` file exists: `touch database/database.sqlite`
- Run `php artisan migrate` to create tables

### Mail not sending

- Default config uses `MAIL_MAILER=log` which writes emails to `storage/logs/laravel.log`
- For actual email delivery, configure SMTP settings in `.env`

## Quality standards

Every change must pass before merging:

- `composer lint` — Rector + Pint + Prettier
- `composer test:types` — PHPStan at max level with bleeding edge
- `composer test:type-coverage` — 100% type coverage
- `composer test:unit` — 100% code coverage with Pest
- All 462 tests passing with 1072 assertions

## License

AgentDesk is built on the [Laravel Starter Kit](https://github.com/nunomaduro/laravel-starter-kit) by **[Nuno Maduro](https://x.com/enunomaduro)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
