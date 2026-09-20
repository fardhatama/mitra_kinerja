<?php
/**
 * Konfigurasi AI Analysis — Triple Fallback: Gemini → OpenRouter → Groq
 * 
 * URUTAN PRIORITAS:
 * 1. Gemini       (gratis, 15 RPM)
 * 2. OpenRouter   (gratis $0, model free)
 * 3. Groq         (bayar, free credit $5)
 * 
 * DAFTAR:
 * - Gemini:     https://aistudio.google.com/apikey
 * - OpenRouter: https://openrouter.ai/keys
 * - Groq:       https://console.groq.com/keys
 */

/* ── 1. Google Gemini (PRIMARY) ─────────────────────────── */
define('GEMINI_API_KEY', 'AQ.Ab8RN6LAW3zCBUWnsln_OHyNyl_-lrFJVTsG7x1Deocrr6jnzw');
define('GEMINI_MODEL', 'gemini-3.5-flash');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1/models/');

/* ── 2. OpenRouter (FALLBACK #1 — GRATIS) ───────────────── */
define('OPENROUTER_API_KEY', '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'); // Daftar: https://openrouter.ai/keys
define('OPENROUTER_MODEL', 'nex-agi/nex-n2.5-mini:free'); // $0/M token
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');

/* ── 3. Groq (FALLBACK #2 — bayar, free credit $5) ─────── */
define('GROQ_API_KEY', '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');
define('GROQ_MODEL', 'openai/gpt-oss-120b'); 
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

// Cache settings
define('AI_CACHE_TTL', 3600); // 1 jam dalam detik
define('AI_CACHE_FILE', __DIR__ . '/../cache/ai_insight.json');

// Prompt template untuk analisis
define('AI_PROMPT_TEMPLATE', <<<'PROMPT'
Anda adalah asisten analis untuk sistem monitoring kerja sama Kanwil Kemenkumham Kepri.

Berdasarkan data portofolio berikut, buatlah ringkasan insight dalam Bahasa Indonesia yang profesional dan singkat (maksimal 3 paragraf pendek).

Fokus pada:
1. Status umum portofolio (berapa sehat, bermasalah, kritis)
2. Temuan utama yang perlu perhatian (aspek terlemah, tenggat terlewat)
3. Satu rekomendasi konkret untuk perbaikan

Hindari: basa-basi, pengulangan, bullet points berlebihan.
Gaya: seperti briefing untuk pimpinan, padat dan actionable.

Data portofolio:
%s

Format output: Langsung isi insight, tanpa pembukaan "Berikut adalah..." atau sejenisnya.
PROMPT
);

