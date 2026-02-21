@extends('layouts.app')

@section('stylesheet')
<style>
    .home-shell {
        background:
            radial-gradient(circle at top right, rgba(15, 93, 165, 0.08), transparent 42%),
            radial-gradient(circle at bottom left, rgba(255, 173, 71, 0.10), transparent 38%),
            linear-gradient(140deg, var(--color-bg-subtle) 0%, var(--color-bg-page) 58%, var(--color-accent-bg) 100%);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: var(--space-lg);
    }

    .editor-panel {
        background: var(--color-bg-page);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card-hover);
        border-radius: var(--radius-md);
        padding: var(--space-md);
    }

    .home-title {
        font-family: var(--font-heading);
        font-size: 32px;
        line-height: 1.2;
        color: var(--color-heading);
    }

    .home-subtitle {
        color: var(--color-text-muted);
        max-width: 760px;
    }

    .home-chip {
        display: inline-flex;
        align-items: center;
        border: 1px solid var(--color-primary-border);
        background: var(--color-primary-bg);
        color: var(--color-primary);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        padding: 4px 10px;
        border-radius: var(--radius-pill);
        margin-right: 6px;
        margin-bottom: 6px;
    }

    .marketing-grid .card {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
        height: 100%;
    }

    .marketing-grid .card h3 {
        color: var(--color-heading);
    }

    .seo-block {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        background: var(--color-bg-page);
        padding: 18px;
    }

    .home-cta-block {
        background: var(--color-primary-bg);
        border: 1px solid var(--color-primary-border);
        border-radius: var(--radius-md);
        padding: var(--space-lg);
        text-align: center;
    }
</style>
@endsection

@section('content')
<div class="home-shell mb-4">
    <form action="{{ route('home.note.store') }}" method="post" id="note-form" data-editable="1" data-save-mode="local" data-draft-key="home_note_draft_v2">
        @csrf
        <x-rich-editor
            class="editor-panel"
            :initial-data="old('data')"
            :initial-title="old('title')"
            placeholder="Start writing your note..."
            status-text="Start Typing"
            :show-validation-error="true">
            <x-slot name="actions">
                <button type="submit" class="btn btn-dark btn-sm">Save to Cloud</button>
            </x-slot>
        </x-rich-editor>
    </form>

    <div class="mt-4">
        <div class="mb-2">
            <span class="home-chip">Local-first drafts</span>
            <span class="home-chip">Rich text + images</span>
            <span class="home-chip">One-click cloud save</span>
        </div>
        <h1 class="home-title mb-2">Write instantly. Save when you're ready.</h1>
        <p class="home-subtitle mb-0">
            Your draft lives in the browser while you type — nothing is sent to the server until you click <strong>Save to Cloud</strong>.
            No sign-up required to start writing.
        </p>
    </div>
</div>

<section class="mb-4">
    <h2 class="h4 mb-3" style="font-family: var(--font-heading);">Why use Note Online?</h2>
    <div class="row marketing-grid">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="h6"><i class="fa fa-bolt mr-1 text-muted"></i> Start writing instantly</h3>
                    <p class="text-muted mb-0">No registration walls — open the page and start typing. Perfect for quick ideas, meeting notes, or brainstorms.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="h6"><i class="fa fa-image mr-1 text-muted"></i> Rich text + images</h3>
                    <p class="text-muted mb-0">Format with headings, lists, links, and embedded images. A full editor that stays out of your way.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="h6"><i class="fa fa-share-alt mr-1 text-muted"></i> Share and organize</h3>
                    <p class="text-muted mb-0">Save to cloud, share note links, group content into notebooks, and chat with your knowledge base.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@guest
<section class="home-cta-block mb-4">
    <h2 class="h5 mb-2" style="font-family: var(--font-heading); color: var(--color-heading);">Already saving notes?</h2>
    <p class="text-muted mb-3">Login with Google or GitHub to manage your saved notes, organize notebooks, and share your content.</p>
    <a href="{{ route('login') }}" class="btn btn-dark">Login to your account</a>
</section>
@endguest

<section class="seo-block mb-4">
    <h2 class="h4 mb-3">Common use cases</h2>
    <p class="text-muted mb-2">Use this online notepad for meeting notes, quick drafts, interview prep, research snippets, and collaborative knowledge sharing.</p>
    <p class="text-muted mb-2">Writers, founders, students, and developers can draft locally, then save only finalized notes to the cloud.</p>
    <p class="text-muted mb-0">Create an account to manage saved notes, attach sources to notebooks, and query knowledge with notebook chat.</p>
</section>

<section class="seo-block">
    <h2 class="h4 mb-3">Features</h2>
    <div class="row">
        <div class="col-md-6">
            <ul class="text-muted mb-2">
                <li>Rich note editor with autosave and optional password protection</li>
                <li>My Notes dashboard with search, sort, filtering, and quick actions</li>
                <li>Notebook management with visibility controls (private, unlisted, public)</li>
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