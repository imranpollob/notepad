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

    /* Feature cards */
    .feature-card {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-card);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .feature-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-card-hover);
    }

    .feature-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        font-size: 18px;
    }

    .feature-icon--orange {
        background: #fff4e6;
        color: #e07b00;
    }

    .feature-icon--green {
        background: #e8f8f0;
        color: #1a8a4e;
    }

    .feature-icon--blue {
        background: var(--color-primary-bg);
        color: var(--color-primary);
    }

    .feature-icon--purple {
        background: #f3eeff;
        color: #7c3aed;
    }

    .feature-icon--teal {
        background: #e6faf6;
        color: #0e8a6f;
    }

    .feature-icon--red {
        background: #fff0f0;
        color: #d94040;
    }

    /* Section eyebrow label */
    .section-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--color-primary);
        background: var(--color-primary-bg);
        border: 1px solid var(--color-primary-border);
        border-radius: var(--radius-pill);
        padding: 2px 10px;
        margin-bottom: 10px;
    }

    /* CTA dark block */
    .home-cta-block {
        background: linear-gradient(135deg, #0e2644 0%, #1a4070 100%);
        border-radius: var(--radius-md);
        padding: var(--space-xl) var(--space-lg);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .home-cta-block::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(44, 162, 255, 0.15), transparent 70%);
        pointer-events: none;
    }

    .home-cta-eyebrow {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--color-primary-light);
        margin-bottom: 10px;
    }

    .home-cta-heading {
        font-family: var(--font-heading);
        font-size: 26px;
        color: #fff;
        margin-bottom: 10px;
    }

    .home-cta-sub {
        color: rgba(255, 255, 255, 0.72);
        max-width: 520px;
        margin: 0 auto 24px;
        font-size: 15px;
    }

    /* Use cases pill cloud */
    .usecases-section {
        background: var(--color-bg-subtle);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: var(--space-lg);
    }

    .usecase-pill {
        display: inline-block;
        background: var(--color-bg-page);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-pill);
        padding: 5px 14px;
        font-size: 13px;
        color: var(--color-text);
        margin: 4px 3px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .usecase-pill:hover {
        border-color: var(--color-primary-border);
        box-shadow: 0 1px 6px rgba(15, 93, 165, 0.10);
    }
</style>
@endsection

@section('content')
<div class="home-shell mb-4">
    <div class="mb-4">
        <h1 class="home-title mb-2">Write instantly. Save when you're ready.</h1>
        <p class="home-subtitle mb-0">
            Your draft lives in the browser while you type — nothing is sent to the server until you click <strong>Save to Cloud</strong>.
            No sign-up required to start writing.
        </p>
    </div>

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


</div>

<section class="mb-5">
    <div class="section-eyebrow">Why use it</div>
    <h2 class="h4 mb-4" style="font-family: var(--font-heading);">Built for quick, focused writing</h2>
    <div class="row marketing-grid">
        <div class="col-md-4 mb-4">
            <div class="feature-card card h-100">
                <div class="card-body">
                    <span class="feature-icon feature-icon--orange mb-3"><i class="fa fa-bolt"></i></span>
                    <h3 class="h6 mb-2" style="color: var(--color-heading); font-weight: 700;">Start writing instantly</h3>
                    <p class="text-muted mb-0 small">No registration walls — open the page and start typing. Perfect for quick ideas, meeting notes, or brainstorms.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card card h-100">
                <div class="card-body">
                    <span class="feature-icon feature-icon--green mb-3"><i class="fa fa-image"></i></span>
                    <h3 class="h6 mb-2" style="color: var(--color-heading); font-weight: 700;">Rich text + images</h3>
                    <p class="text-muted mb-0 small">Format with headings, lists, links, and embedded images. A full editor that stays out of your way.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card card h-100">
                <div class="card-body">
                    <span class="feature-icon feature-icon--blue mb-3"><i class="fa fa-share-alt"></i></span>
                    <h3 class="h6 mb-2" style="color: var(--color-heading); font-weight: 700;">Share and organize</h3>
                    <p class="text-muted mb-0 small">Save to cloud, share note links, group content into notebooks, and chat with your knowledge base.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card card h-100">
                <div class="card-body">
                    <span class="feature-icon feature-icon--red mb-3"><i class="fa fa-lock"></i></span>
                    <h3 class="h6 mb-2" style="color: var(--color-heading); font-weight: 700;">Password-protect notes</h3>
                    <p class="text-muted mb-0 small">Lock individual notes with a password. Share a link publicly while keeping the content private.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card card h-100">
                <div class="card-body">
                    <span class="feature-icon feature-icon--teal mb-3"><i class="fa fa-comments"></i></span>
                    <h3 class="h6 mb-2" style="color: var(--color-heading); font-weight: 700;">Chat with your notes</h3>
                    <p class="text-muted mb-0 small">Group notes into notebooks and ask questions. Get cited answers from your own content.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card card h-100">
                <div class="card-body">
                    <span class="feature-icon feature-icon--purple mb-3"><i class="fa fa-th-large"></i></span>
                    <h3 class="h6 mb-2" style="color: var(--color-heading); font-weight: 700;">Your notes dashboard</h3>
                    <p class="text-muted mb-0 small">Search, sort, and filter all your saved notes in one place. Copy links or delete with one click.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@guest
<section class="home-cta-block mb-5">
    <p class="home-cta-eyebrow">Social login &mdash; no passwords needed</p>
    <h2 class="home-cta-heading">Already saving notes?</h2>
    <p class="home-cta-sub">Login with Google or GitHub to manage your saved notes, organize notebooks, and chat with your knowledge base.</p>
    <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4">Login to your account &rarr;</a>
</section>
@endguest

<section class="usecases-section mb-5">
    <div class="section-eyebrow">Use cases</div>
    <h2 class="h5 mb-2" style="font-family: var(--font-heading); margin-top: 8px;">Who is this for?</h2>
    <p class="text-muted mb-3 small">Notebase fits wherever you need to capture ideas fast — no friction, no setup. Group notes into notebooks and chat with your knowledge base.</p>
    <div>
        <span class="usecase-pill"><i class="fa fa-pencil mr-1 text-muted"></i> Meeting notes</span>
        <span class="usecase-pill"><i class="fa fa-lightbulb-o mr-1 text-muted"></i> Quick drafts</span>
        <span class="usecase-pill"><i class="fa fa-graduation-cap mr-1 text-muted"></i> Interview prep</span>
        <span class="usecase-pill"><i class="fa fa-search mr-1 text-muted"></i> Research snippets</span>
        <span class="usecase-pill"><i class="fa fa-users mr-1 text-muted"></i> Collaborative knowledge</span>
        <span class="usecase-pill"><i class="fa fa-code mr-1 text-muted"></i> Developer notes</span>
        <span class="usecase-pill"><i class="fa fa-book mr-1 text-muted"></i> Student notes</span>
        <span class="usecase-pill"><i class="fa fa-file-text-o mr-1 text-muted"></i> Founder drafts</span>
    </div>
</section>

@endsection