@extends('layouts.app')

@section('stylesheet')
    <style>
        .home-shell {
            background:
                radial-gradient(circle at top right, rgba(27, 145, 255, 0.12), transparent 42%),
                radial-gradient(circle at bottom left, rgba(255, 173, 71, 0.14), transparent 38%),
                linear-gradient(140deg, #f5f9ff 0%, #fff 58%, #fff8ed 100%);
            border: 1px solid #e8edf3;
            padding: 24px;
        }

        .editor-panel {
            background: #fff;
            border: 1px solid #e6ebf1;
            box-shadow: 0 12px 24px rgba(12, 42, 79, 0.08);
            padding: 16px;
        }

        .home-title {
            font-family: 'Lora', serif;
            font-size: 36px;
            line-height: 1.2;
            color: #13253b;
        }

        .home-subtitle {
            color: #5b6470;
            max-width: 760px;
        }

        .home-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d3e3f4;
            background: #f4f9ff;
            color: #204468;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            padding: 5px 10px;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .marketing-grid .card {
            border: 1px solid #e8ecef;
            box-shadow: 0 8px 20px rgba(14, 40, 69, 0.05);
            height: 100%;
        }

        .marketing-grid .card h3 {
            color: #15253b;
        }

        .seo-block {
            border: 1px solid #e8ecef;
            background: #fff;
            padding: 18px;
        }
    </style>
@endsection

@section('content')
    <div class="home-shell rounded mb-4">
        <div class="mb-4">
            <div class="mb-2">
                <span class="home-chip">LOCAL-FIRST DRAFTS</span>
                <span class="home-chip">RICH TEXT + IMAGES</span>
                <span class="home-chip">ONE-CLICK CLOUD SAVE</span>
            </div>
            <h1 class="home-title mb-2">Take notes online, then save and share in one click.</h1>
            <p class="home-subtitle mb-0">
                Start writing instantly with rich text and image support. Your homepage draft is saved locally while you type,
                and moves to cloud only when you choose to save.
            </p>
        </div>

        <form action="{{ route('home.note.store') }}" method="post" id="note-form" data-editable="1" data-save-mode="local" data-draft-key="home_note_draft_v2">
            @csrf
            <x-rich-editor
                class="editor-panel rounded"
                :initial-data="old('data')"
                :initial-title="old('title')"
                placeholder="Start writing your note..."
                status-text="Start Typing"
                :show-validation-error="true"
            >
                <x-slot name="actions">
                    <button type="submit" class="btn btn-dark btn-sm">Save to Cloud</button>
                </x-slot>
            </x-rich-editor>
        </form>
    </div>

    <section class="mb-4">
        <h2 class="h4 mb-3">Why use this notes app?</h2>
        <div class="row marketing-grid">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h3 class="h6">Fast online note taking</h3>
                        <p class="text-muted mb-0">Write instantly without registration barriers and keep momentum while brainstorming.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h3 class="h6">Rich text + image support</h3>
                        <p class="text-muted mb-0">Format notes with headings, lists, links, and embed images directly in your document.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h3 class="h6">Share and organize knowledge</h3>
                        <p class="text-muted mb-0">Save to cloud, share note links, and manage your content through notebooks and chat workflows.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="seo-block rounded">
        <h2 class="h4 mb-3">Common use cases</h2>
        <p class="text-muted mb-2">Use this online notepad for meeting notes, quick drafts, interview prep, research snippets, and collaborative knowledge sharing.</p>
        <p class="text-muted mb-2">Writers, founders, students, and developers can draft locally, then save only finalized notes to the cloud.</p>
        <p class="text-muted mb-0">Create an account to manage saved notes, attach sources to notebooks, and query knowledge with notebook chat.</p>
    </section>

    <section class="seo-block rounded mt-4">
        <h2 class="h4 mb-3">Everything Available In This Project</h2>
        <div class="row">
            <div class="col-md-6">
                <ul class="text-muted mb-2">
                    <li>Rich note editor with autosave and optional password protection</li>
                    <li>My Notes dashboard with search, sort, filtering, and quick actions</li>
                    <li>Notebook CRUD with visibility controls (`private`, `unlisted`, `public`)</li>
                    <li>Share-token links for public and unlisted notebooks</li>
                    <li>Source attachments from notes, uploaded files, and URLs</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="text-muted mb-2">
                    <li>Ingestion pipeline for file/URL extraction with status tracking and retry</li>
                    <li>Notebook chat with conversation history and deletion</li>
                    <li>Source selection controls for retrieval scope</li>
                    <li>Citation-linked assistant responses</li>
                    <li>Conversation memory summary and token usage tracking</li>
                </ul>
            </div>
        </div>
    </section>
@endsection
