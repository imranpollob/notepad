<?php

return [
    'min_retrieval_score' => (float) env('RAG_MIN_RETRIEVAL_SCORE', 0.08),
    'semantic_weight' => (float) env('RAG_SEMANTIC_WEIGHT', 0.75),
    'keyword_weight' => (float) env('RAG_KEYWORD_WEIGHT', 0.25),
    'recent_messages_window' => (int) env('RAG_RECENT_MESSAGES_WINDOW', 6),
    'summary_char_limit' => (int) env('RAG_SUMMARY_CHAR_LIMIT', 1200),
];
