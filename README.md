# Notepad Knowledge Workspace

Notepad is a Laravel-based knowledge workspace that combines:
- shareable rich-text notes,
- notebook-based document organization,
- source ingestion (notes/files/URLs),
- and Step 2 RAG-ready chat with citations.

This project is currently in:
- **Step 1 complete**: product foundation without AI dependency.
- **Step 2 in progress**: chat, chunking, indexing, and retrieval scaffolding are implemented.

---

## Key Features

### Notes
- Rich-text note editor with autosave.
- Namespaced note routes: `/n/{slug}`.
- Legacy short links still work via redirect.
- Optional password protection (hashed at rest).
- Owner-aware edit permissions and read-only fallback for unauthorized users.

### Notebooks and Sources
- Notebook CRUD with visibility modes: `private`, `unlisted`, `public`.
- Share-token links for `unlisted` and `public`.
- Attach sources to notebooks:
  - existing notes,
  - uploaded files (`pdf`, `doc`, `docx`),
  - URLs.
- Source management:
  - remove sources,
  - status filtering (`pending`, `processing`, `ready`, `failed`),
  - retry failed file/URL ingestion.

### Ingestion Pipeline (Non-AI Foundation)
- Background ingestion jobs with status tracking.
- URL extraction with HTML cleanup.
- File extraction with format-specific strategies.
- Extracted text persisted to `source_contents`.

### RAG / Chat (Step 2 current state)
- Source chunking and embedding storage (`source_chunks`).
- Notebook-scoped semantic retrieval service.
- Notebook chat UI with conversation history management.
- Conversation lifecycle actions:
  - start new conversation,
  - continue existing conversation with context memory,
  - delete conversations.
- Assistant responses persisted with citations metadata.
- OpenAI-backed generation/embedding if configured.
- Deterministic local fallback when OpenAI is unavailable.

---

## Quick Start

```bash
cd /Users/imranpollob/Coding/notepad

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure `.env`:
- database connection
- queue connection (`database` recommended)
- optional OpenAI keys

Run migrations:
```bash
php artisan migrate
```

Build frontend assets (if needed):
```bash
npm run dev
```

Start app:
```bash
php artisan serve
```

Start queue worker (required for background processing):
```bash
php artisan queue:work
```

If using `QUEUE_CONNECTION=database` and jobs table is missing:
```bash
php artisan queue:table
php artisan migrate
```

---

## Verification

Run test suite:
```bash
vendor/bin/phpunit
```

Useful route checks:
```bash
php artisan route:list --path=n
php artisan route:list --path=notebooks
php artisan route:list --path=chat
```

Manual chat checks:
1. Open notebook chat and click `New Conversation`.
2. Send a question and confirm user/assistant messages are saved.
3. Select another conversation from the left panel and confirm history loads.
4. Delete a conversation and confirm it disappears from the list and no longer opens.

---

## Documentation

Full technical documentation:
- [`PROJECT_DOCUMENTATION.md`](PROJECT_DOCUMENTATION.md)

Includes architecture, data model, ingestion and chat internals, route map, security model, operations, and roadmap.

---

## Screenshot
![Notepad Screenshot](/screenshot.png)
