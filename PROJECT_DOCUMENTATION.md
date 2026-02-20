# Project Documentation: Notepad Knowledge Workspace

## 1. Overview

Notepad is a Laravel application that started as a shareable notes tool and has been extended into a knowledge workspace with RAG-ready foundations.

Current state:
- **Step 1 (Foundation) complete**
- **Step 2 (AI/RAG) partially complete**

The system supports note authoring, notebook-based organization, ingestion of notes/files/URLs, indexing into chunks, notebook-scoped retrieval, and conversational querying with citation metadata.

## 2. Tech Stack

- **Backend**: PHP 8.2+, Laravel 12
- **Frontend**: Blade templates, Bootstrap, jQuery
- **Database**: MySQL (primary), SQLite (tests)
- **Queue**: Laravel queues (`sync` or `database`)
- **Parsing/Ingestion**:
  - URL extraction via HTTP + DOM cleanup
  - DOCX extraction via Zip/XML
  - PDF extraction via `pdftotext` preferred, parser/fallback chain
- **AI Integration**:
  - Optional OpenAI embeddings/chat
  - Deterministic local fallback embeddings and response mode

## 3. Architecture

### 3.1 Application Layers
- **Presentation**: Blade pages (`resources/views`)
- **HTTP Controllers**: request validation, authz, orchestration
- **Services**: ingestion, chunking, embeddings, retrieval, chat logic
- **Jobs**: async processing for ingestion/indexing
- **Models**: notebooks, sources, chunks, conversations, notes

### 3.2 Core Flows

1. **Ingestion Flow**
- User attaches source (`note`/`file`/`url`) to notebook.
- `source` row created/updated.
- `source_ingestions` row created for file/url.
- `ProcessSourceIngestion` job extracts text.
- On success, `source_contents` updated and source becomes `ready`.
- `IndexSourceChunks` job chunks and embeds content to `source_chunks`.

2. **Chat Flow**
- User opens notebook chat.
- User question is stored as a conversation message.
- Retrieval service scores notebook chunks by semantic similarity.
- Chat service generates response using top chunks.
- Assistant response and citations metadata stored in conversation messages.

## 4. Data Model

### 4.1 Notes Domain
- `notes`: shareable rich-text notes, optional password, owner metadata.

### 4.2 Notebook Domain
- `notebooks`: user-owned notebook container.
- `sources`: notebook source registry (`note`/`file`/`url`).
- `source_files`: uploaded file metadata.
- `source_contents`: extracted normalized content.
- `source_ingestions`: ingestion attempts and status history.

### 4.3 RAG / Chat Domain
- `source_chunks`: chunked source text with embedding payload.
- `conversations`: notebook conversation threads.
- `conversation_messages`: user/assistant messages and metadata.

## 5. Implemented Features

## 5.1 Step 1 (Complete)
- Namespaced note routing and legacy redirect compatibility.
- Note security hardening:
  - ownership-aware mutations,
  - password hashing and migration compatibility,
  - read-only fallback for unauthorized editors.
- Notebook CRUD with visibility controls.
- Share-token based notebook sharing.
- Source attachment:
  - note,
  - URL,
  - file (`pdf`, `doc`, `docx`).
- Source status filtering and retry action.
- Ingestion status tracking and failure capture.

## 5.2 Step 2 (Implemented So Far)
- RAG schema (`source_chunks`, `conversations`, `conversation_messages`).
- Chunking + embedding indexing service and background job.
- Notebook-scoped retrieval service.
- Notebook chat UI and conversation persistence.
- Conversation lifecycle support (new/select/delete).
- Citation metadata persisted for assistant responses.
- OpenAI + local fallback for embedding/chat.

## 6. Access and Permission Model

- Notes:
  - owner controls mutation,
  - password can gate edits for non-owners.
- Notebooks:
  - owner-only management and chat.
  - `public`/`unlisted` accessible by share token in read-only shared view.
  - `private` not accessible via shared link.
- Source actions:
  - owner-only attach/retry/remove.

## 7. Routes (Important Groups)

### 7.1 Notes
- `GET /n/{url}` show note
- `POST /n/{url}` autosave
- `PUT /n/{url}` update note settings
- `DELETE /n/{url}` delete note
- `POST /n/{url}/password` unlock note

### 7.2 Notebooks
- `GET /notebooks` index
- `POST /notebooks` create
- `GET /notebooks/{id}` show
- `PUT /notebooks/{id}` update
- `DELETE /notebooks/{id}` delete
- `POST /notebooks/{id}/share-token` regenerate share token
- `GET /shared/notebooks/{token}` shared view

### 7.3 Sources
- `POST /notebooks/{id}/sources/note`
- `POST /notebooks/{id}/sources/file`
- `POST /notebooks/{id}/sources/url`
- `POST /notebooks/{id}/sources/{source}/retry`
- `DELETE /notebooks/{id}/sources/{source}`

### 7.4 Chat
- `GET /notebooks/{id}/chat`
- `POST /notebooks/{id}/chat`
- `DELETE /notebooks/{id}/chat/{conversation}`

## 8. Configuration

### 8.1 Required
- `APP_KEY`
- database credentials

### 8.2 Recommended
- `QUEUE_CONNECTION=database`

### 8.3 Optional OpenAI
- `OPENAI_API_KEY`
- `OPENAI_EMBEDDING_MODEL` (default `text-embedding-3-small`)
- `OPENAI_CHAT_MODEL` (default `gpt-4o-mini`)

If OpenAI is unavailable, the app falls back to local deterministic behavior.

## 9. Setup and Runbook

## 9.1 Setup
```bash
cd /Users/imranpollob/Coding/notepad
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## 9.2 Runtime
```bash
php artisan serve
php artisan queue:work
```

## 9.3 Test
```bash
vendor/bin/phpunit
```

Targeted suites:
```bash
vendor/bin/phpunit tests/Feature/NotesSecurityTest.php
vendor/bin/phpunit tests/Feature/NotebookFeaturesTest.php
vendor/bin/phpunit tests/Feature/NotebookIngestionTest.php
vendor/bin/phpunit tests/Feature/NotebookChatTest.php
```

## 10. Manual Verification Guide

### 10.1 Notes
1. Visit `/`, confirm redirect to `/n/{slug}`.
2. Open legacy `/{slug}`, confirm redirect to `/n/{slug}`.
3. Set password, verify unlock + edit behavior.

### 10.2 Notebooks and Sources
1. Create notebook.
2. Attach note, URL, and file.
3. Confirm status transitions and source list updates.
4. Trigger failed ingestion and verify retry button flow.

### 10.3 Chat
1. Open notebook chat.
2. Ask a question.
3. Confirm conversation appears with user/assistant messages.
4. Confirm citations are shown for assistant response.
5. Create a second conversation and switch between threads from the conversation list.
6. Delete one conversation and confirm it is removed while remaining threads still work.

## 11. Operational Notes

- Queue worker must run for non-sync processing in production.
- PDF quality varies by source. Scanned image-only PDFs require OCR (not implemented yet).
- Current embeddings are stored as JSON in DB. For scale, move to vector database backend.

## 12. Known Gaps / Next Development

- Source-level chat filtering (chat selected sources only).
- Improved citation rendering in answer text.
- Streaming response support.
- Retrieval reranking/hybrid search.
- Vector DB integration (pgvector/Qdrant/Pinecone).
- Shared notebook chat policy and enforcement.
- Observability dashboards for retrieval quality, costs, and latency.
