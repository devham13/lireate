<?php
/**
 * Template Name: [REDACTED] AI Home
 * Description: Главная страница Lireate / [REDACTED] в стиле AI-автоматизации бизнеса.
 *
 * Как использовать:
 * 1) Переименовать файл в front-page.php, если нужно сделать его главной темой.
 * 2) Или загрузить в активную тему и выбрать шаблон "[REDACTED] AI Home" у нужной страницы.
 */

$page_seo_title = 'AI-автоматизация бизнеса под ключ — внедрение AI-агентов, CRM и нейросетей';
$page_seo_description = 'Внедрение AI-агентов, CRM-автоматизации, голосовых менеджеров, чат-ботов, Make/n8n и аналитики для бизнеса. Автоматизация заявок, продаж, поддержки и документов.';

add_filter('document_title_parts', static function (array $parts) use ($page_seo_title): array {
    $parts['title'] = $page_seo_title;
    return $parts;
}, 20);

add_action('wp_head', static function () use ($page_seo_title, $page_seo_description): void {
    echo '<meta name="description" content="' . esc_attr($page_seo_description) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($page_seo_title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($page_seo_description) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '" />' . "\n";
    echo '<meta property="og:type" content="website" />' . "\n";
}, 1);

$brand = get_bloginfo('name') ?: '[REDACTED]';
$primary_cta_label = getenv('PRIMARY_CTA_LABEL') ?: 'Получить AI-аудит';
$primary_cta_url = getenv('PRIMARY_CTA_URL') ?: '#audit';
$secondary_cta_label = getenv('SECONDARY_CTA_LABEL') ?: 'Что можно автоматизировать';
$secondary_cta_url = getenv('SECONDARY_CTA_URL') ?: '#services';

get_header();
?>

<style>
.breadcrumbs, .breadcrumb, .breadcrumb-list, .breadcrumb-item,
nav[aria-label="Хлебные крошки"], .woocommerce-breadcrumb,
.rank-math-breadcrumb, .rank-math-breadcrumbs, .yoast-breadcrumb,
.entry-header, .page-title-section { display: none !important; }
#primary, .site-main, .site-content, #content, .content-area { padding-top: 0 !important; margin-top: 0 !important; }
/**
 * Lireate / [REDACTED] homepage design reference.
 * Назначение: новый шаблонный дизайн для главной страницы и будущих AI/B2B лендингов.
 * Использование: корневая обёртка страницы должна иметь класс .nero-ai-home-page.
 * Все пользовательские классы начинаются с .nero-ai- чтобы не конфликтовать с WordPress/Kadence.
 */

:root {
  --nero-ai-bg: #060812;
  --nero-ai-bg-2: #0b1020;
  --nero-ai-surface: rgba(255, 255, 255, 0.072);
  --nero-ai-surface-2: rgba(255, 255, 255, 0.105);
  --nero-ai-card: rgba(11, 16, 32, 0.78);
  --nero-ai-card-strong: rgba(17, 24, 39, 0.92);
  --nero-ai-text: #e6edf7;
  --nero-ai-muted: #9aa8bd;
  --nero-ai-soft: #c7d2e5;
  --nero-ai-heading: #ffffff;
  --nero-ai-border: rgba(255, 255, 255, 0.12);
  --nero-ai-border-strong: rgba(125, 249, 255, 0.26);
  --nero-ai-primary: #79f2ff;
  --nero-ai-primary-2: #8b5cf6;
  --nero-ai-accent: #22c55e;
  --nero-ai-warn: #f59e0b;
  --nero-ai-danger: #fb7185;
  --nero-ai-shadow: 0 28px 90px rgba(0, 0, 0, 0.42);
  --nero-ai-shadow-soft: 0 18px 46px rgba(0, 0, 0, 0.25);
  --nero-ai-radius-xl: 32px;
  --nero-ai-radius-lg: 24px;
  --nero-ai-radius-md: 18px;
  --nero-ai-container: 1220px;
}

#primary.nero-ai-home-page,
.nero-ai-home-page {
  min-height: 100vh;
  margin: 0;
  padding: 0;
  overflow-x: hidden;
  color: var(--nero-ai-text);
  background:
    radial-gradient(circle at 12% 7%, rgba(121, 242, 255, 0.18), transparent 28rem),
    radial-gradient(circle at 86% 12%, rgba(139, 92, 246, 0.22), transparent 34rem),
    radial-gradient(circle at 60% 90%, rgba(34, 197, 94, 0.08), transparent 35rem),
    linear-gradient(180deg, #050711 0%, #080b17 52%, #050711 100%);
  font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

.nero-ai-home-page *,
.nero-ai-home-page *::before,
.nero-ai-home-page *::after { box-sizing: border-box; }

.nero-ai-home-page a { color: inherit; }
.nero-ai-home-page p { color: var(--nero-ai-muted); }
.nero-ai-home-page h1,
.nero-ai-home-page h2,
.nero-ai-home-page h3,
.nero-ai-home-page h4 { color: var(--nero-ai-heading); letter-spacing: -0.045em; }

.nero-ai-container {
  width: min(var(--nero-ai-container), calc(100% - 40px));
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.nero-ai-section { padding: clamp(70px, 9vw, 126px) 0; position: relative; }
.nero-ai-section-tight { padding: clamp(48px, 6vw, 82px) 0; }
.nero-ai-section-alt {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0.012));
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.nero-ai-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 16px;
  padding: 8px 12px;
  border: 1px solid rgba(121, 242, 255, 0.2);
  border-radius: 999px;
  background: rgba(121, 242, 255, 0.08);
  color: var(--nero-ai-primary) !important;
  font-size: 13px;
  font-weight: 750;
  line-height: 1;
  text-transform: uppercase;
  letter-spacing: 0.11em;
}

.nero-ai-section-head {
  max-width: 820px;
  margin: 0 auto 42px;
  text-align: center;
}
.nero-ai-section-head.nero-ai-left { margin-left: 0; text-align: left; }
.nero-ai-section-head h2 {
  margin: 0;
  font-size: clamp(34px, 5vw, 64px);
  line-height: 0.98;
}
.nero-ai-section-head p {
  margin: 18px auto 0;
  max-width: 720px;
  font-size: clamp(16px, 1.7vw, 20px);
  line-height: 1.65;
}
.nero-ai-section-head.nero-ai-left p { margin-left: 0; }

.nero-ai-gradient-text {
  background: linear-gradient(92deg, #ffffff 0%, var(--nero-ai-primary) 44%, #c4b5fd 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent !important;
}

.nero-ai-btn-row { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
.nero-ai-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 14px 20px;
  border-radius: 999px;
  border: 1px solid transparent;
  font-size: 15px;
  font-weight: 800;
  line-height: 1;
  text-decoration: none !important;
  transition: transform .22s ease, border-color .22s ease, background .22s ease, box-shadow .22s ease;
}
.nero-ai-btn:hover,
.nero-ai-btn:focus-visible { transform: translateY(-2px); }
.nero-ai-btn-primary {
  color: #031018 !important;
  background: linear-gradient(135deg, var(--nero-ai-primary), #a7f3d0);
  box-shadow: 0 18px 42px rgba(121, 242, 255, 0.22);
}
.nero-ai-btn-secondary {
  color: var(--nero-ai-text) !important;
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.14);
}
.nero-ai-btn-secondary:hover { border-color: rgba(121, 242, 255, 0.36); background: rgba(121, 242, 255, 0.08); }

.nero-ai-card {
  position: relative;
  border: 1px solid var(--nero-ai-border);
  border-radius: var(--nero-ai-radius-lg);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.086), rgba(255, 255, 255, 0.044));
  box-shadow: var(--nero-ai-shadow-soft);
  backdrop-filter: blur(18px);
}
.nero-ai-card::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  padding: 1px;
  background: linear-gradient(135deg, rgba(121, 242, 255, .34), rgba(139, 92, 246, .14), rgba(34, 197, 94, .12));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  opacity: .75;
}

.nero-ai-hero {
  position: relative;
  min-height: min(980px, calc(100vh - 1px));
  min-height: min(980px, calc(100dvh - 1px));
  display: grid;
  align-items: center;
  padding: clamp(72px, 9vw, 132px) 0 clamp(44px, 7vw, 86px);
  isolation: isolate;
}
.nero-ai-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
  background-size: 64px 64px;
  mask-image: radial-gradient(circle at 45% 30%, #000 0%, transparent 72%);
  opacity: .55;
  pointer-events: none;
  z-index: -2;
}
.nero-ai-hero::after {
  content: "";
  position: absolute;
  left: 50%;
  top: 16%;
  width: 820px;
  height: 820px;
  transform: translateX(-50%);
  border-radius: 999px;
  background: radial-gradient(circle, rgba(121, 242, 255, .12), transparent 66%);
  filter: blur(6px);
  animation: neroAiGlow 8s ease-in-out infinite alternate;
  z-index: -1;
  pointer-events: none;
}
@keyframes neroAiGlow { from { opacity: .45; transform: translateX(-50%) scale(.96); } to { opacity: .86; transform: translateX(-50%) scale(1.06); } }

.nero-ai-hero-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.02fr) minmax(360px, .98fr);
  gap: clamp(28px, 4vw, 56px);
  align-items: center;
}
.nero-ai-hero-copy h1 {
  margin: 0;
  max-width: 780px;
  font-size: clamp(44px, 7.2vw, 94px);
  line-height: .89;
  letter-spacing: -0.075em;
}
.nero-ai-hero-lead {
  margin: 24px 0 0;
  max-width: 720px;
  color: var(--nero-ai-soft) !important;
  font-size: clamp(18px, 2vw, 22px);
  line-height: 1.58;
}
.nero-ai-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 26px 0 0;
  padding: 0;
  list-style: none;
}
.nero-ai-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 11px;
  border: 1px solid rgba(255,255,255,.11);
  border-radius: 999px;
  background: rgba(255,255,255,.055);
  color: #dce8f7;
  font-size: 13px;
  font-weight: 700;
  white-space: nowrap;
}
.nero-ai-hero .nero-ai-btn-row { margin-top: 34px; }

.nero-ai-dashboard {
  position: relative;
  padding: 18px;
  border-radius: 34px;
  background: rgba(2, 6, 23, 0.42);
  box-shadow: var(--nero-ai-shadow);
  transform: perspective(1100px) rotateY(-3deg) rotateX(2deg);
}
.nero-ai-dashboard-shell {
  overflow: hidden;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 26px;
  background: linear-gradient(180deg, rgba(15, 23, 42, .95), rgba(6, 10, 24, .96));
}
.nero-ai-window-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.045);
}
.nero-ai-dots { display: flex; gap: 7px; }
.nero-ai-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,.22); }
.nero-ai-dot:nth-child(1) { background: #fb7185; }
.nero-ai-dot:nth-child(2) { background: #fbbf24; }
.nero-ai-dot:nth-child(3) { background: #34d399; }
.nero-ai-window-title { color: #cfe3f9; font-size: 12px; font-weight: 750; letter-spacing: .08em; text-transform: uppercase; }
.nero-ai-window-body { padding: 18px; }
.nero-ai-dashboard-title {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}
.nero-ai-dashboard-title h3 { margin: 0; font-size: 20px; letter-spacing: -0.03em; }
.nero-ai-live-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 6px 9px;
  border-radius: 999px;
  background: rgba(34,197,94,.10);
  color: #bbf7d0;
  font-size: 12px;
  font-weight: 800;
}
.nero-ai-live-pill::before {
  content: "";
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #22c55e;
  box-shadow: 0 0 0 6px rgba(34,197,94,.14);
  animation: neroAiPulse 1.6s infinite;
}
@keyframes neroAiPulse { 0%, 100% { transform: scale(.86); opacity: .65; } 50% { transform: scale(1); opacity: 1; } }

.nero-ai-metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}
.nero-ai-metric {
  padding: 14px;
  border: 1px solid rgba(255,255,255,.09);
  border-radius: 18px;
  background: rgba(255,255,255,.055);
  transition: transform .22s ease, border-color .22s ease, background .22s ease;
}
.nero-ai-metric:hover,
.nero-ai-metric:focus-within { transform: translateY(-3px); border-color: rgba(121,242,255,.34); background: rgba(121,242,255,.07); }
.nero-ai-metric span { display: block; color: var(--nero-ai-muted); font-size: 12px; font-weight: 700; }
.nero-ai-metric strong { display: block; margin-top: 7px; color: #fff; font-size: 24px; line-height: 1; }
.nero-ai-metric small { display: block; margin-top: 6px; color: #9fb0c9; }

.nero-ai-task-stream { margin-top: 16px; display: grid; gap: 10px; }
.nero-ai-task {
  display: grid;
  grid-template-columns: 28px 1fr auto;
  align-items: center;
  gap: 10px;
  padding: 11px;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px;
  background: rgba(255,255,255,.04);
  animation: neroAiTaskFloat 5s ease-in-out infinite;
}
.nero-ai-task:nth-child(2) { animation-delay: .6s; }
.nero-ai-task:nth-child(3) { animation-delay: 1.2s; }
.nero-ai-task:nth-child(4) { animation-delay: 1.8s; }
@keyframes neroAiTaskFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
.nero-ai-task-icon {
  display: grid;
  place-items: center;
  width: 28px;
  height: 28px;
  border-radius: 12px;
  background: rgba(121,242,255,.12);
  color: var(--nero-ai-primary);
  font-size: 15px;
}
.nero-ai-task strong { display: block; color: #f8fafc; font-size: 13px; }
.nero-ai-task span { color: var(--nero-ai-muted); font-size: 12px; }
.nero-ai-status {
  padding: 5px 8px;
  border-radius: 999px;
  background: rgba(34,197,94,.11);
  color: #bbf7d0;
  font-size: 11px;
  font-weight: 800;
  white-space: nowrap;
}

.nero-ai-automation-map {
  margin-top: 22px;
  padding: 18px;
  border-radius: 22px;
  border: 1px solid rgba(255,255,255,.10);
  background:
    radial-gradient(circle at 50% 50%, rgba(121,242,255,.11), transparent 42%),
    rgba(255,255,255,.035);
}
.nero-ai-map-title { margin: 0 0 14px; color: #dce8f7; font-size: 13px; font-weight: 800; }
.nero-ai-map-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  align-items: center;
  gap: 14px;
}
.nero-ai-people, .nero-ai-processes { display: grid; gap: 10px; }
.nero-ai-person,
.nero-ai-process-node {
  position: relative;
  min-height: 54px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 16px;
  background: rgba(255,255,255,.055);
  cursor: help;
  transition: transform .22s ease, border-color .22s ease, background .22s ease;
}
.nero-ai-person:hover,
.nero-ai-person:focus-visible,
.nero-ai-process-node:hover,
.nero-ai-process-node:focus-visible { transform: translateY(-2px); border-color: rgba(121,242,255,.38); background: rgba(121,242,255,.08); outline: none; }
.nero-ai-person-figure {
  position: relative;
  flex: 0 0 28px;
  width: 28px;
  height: 34px;
}
.nero-ai-person-figure::before {
  content: "";
  position: absolute;
  left: 8px;
  top: 0;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--nero-ai-primary), #c4b5fd);
  box-shadow: 0 0 18px rgba(121,242,255,.25);
}
.nero-ai-person-figure::after {
  content: "";
  position: absolute;
  left: 4px;
  top: 15px;
  width: 20px;
  height: 18px;
  border-radius: 10px 10px 6px 6px;
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.16);
}
.nero-ai-person span,
.nero-ai-process-node span { display: block; color: #e7f0fb; font-size: 12px; font-weight: 800; line-height: 1.2; }
.nero-ai-person small,
.nero-ai-process-node small { display: block; margin-top: 3px; color: var(--nero-ai-muted); font-size: 11px; line-height: 1.25; }
.nero-ai-ai-core {
  position: relative;
  display: grid;
  place-items: center;
  min-height: 270px;
}
.nero-ai-core-ring {
  position: absolute;
  inset: 18px;
  border-radius: 50%;
  border: 1px dashed rgba(121,242,255,.28);
  animation: neroAiRotate 18s linear infinite;
}
.nero-ai-core-ring:nth-child(2) { inset: 42px; animation-duration: 12s; animation-direction: reverse; opacity: .75; }
@keyframes neroAiRotate { to { transform: rotate(360deg); } }
.nero-ai-core-chip {
  position: relative;
  z-index: 1;
  width: 120px;
  height: 120px;
  display: grid;
  place-items: center;
  border-radius: 34px;
  border: 1px solid rgba(121,242,255,.34);
  background: linear-gradient(135deg, rgba(121,242,255,.22), rgba(139,92,246,.18));
  box-shadow: 0 0 55px rgba(121,242,255,.18), inset 0 0 36px rgba(255,255,255,.06);
}
.nero-ai-core-chip strong { display: block; color: #fff; font-size: 24px; line-height: 1; }
.nero-ai-core-chip span { display: block; margin-top: 6px; color: #c7d2fe; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; }
.nero-ai-flow-line {
  position: absolute;
  height: 2px;
  width: 54%;
  background: linear-gradient(90deg, transparent, rgba(121,242,255,.65), transparent);
  animation: neroAiFlow 1.7s ease-in-out infinite;
}
.nero-ai-flow-line:nth-child(3) { top: 34%; transform: rotate(18deg); }
.nero-ai-flow-line:nth-child(4) { top: 62%; transform: rotate(-16deg); animation-delay: .55s; }
@keyframes neroAiFlow { 0% { opacity: .2; filter: blur(0); } 50% { opacity: 1; filter: blur(1px); } 100% { opacity: .2; filter: blur(0); } }

.nero-ai-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
.nero-ai-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.nero-ai-grid-4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }

.nero-ai-service-card,
.nero-ai-pain-card,
.nero-ai-trend-card,
.nero-ai-niche-card,
.nero-ai-result-card {
  padding: 22px;
  min-height: 100%;
  overflow: hidden;
}
.nero-ai-card-icon {
  display: grid;
  place-items: center;
  width: 44px;
  height: 44px;
  margin-bottom: 18px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(121,242,255,.16), rgba(139,92,246,.14));
  color: var(--nero-ai-primary);
  font-size: 21px;
}
.nero-ai-card h3 { margin: 0 0 10px; font-size: 20px; line-height: 1.08; }
.nero-ai-card p { margin: 0; font-size: 15px; line-height: 1.58; }
.nero-ai-card small { display: block; margin-top: 14px; color: #c7d2fe; font-size: 12px; font-weight: 800; }
.nero-ai-pain-card { display: flex; gap: 13px; align-items: flex-start; }
.nero-ai-pain-card::after {
  content: "";
  position: absolute;
  right: -32px;
  top: -32px;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(251,113,133,.16), transparent 66%);
}
.nero-ai-pain-mark {
  flex: 0 0 30px;
  display: grid;
  place-items: center;
  width: 30px;
  height: 30px;
  border-radius: 12px;
  background: rgba(251,113,133,.12);
  color: #fecdd3;
  font-weight: 900;
}

.nero-ai-center-flow {
  display: grid;
  gap: 12px;
}
.nero-ai-flow-step {
  display: grid;
  grid-template-columns: 74px 1fr;
  gap: 16px;
  padding: 16px;
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 20px;
  background: rgba(255,255,255,.045);
  transition: transform .22s ease, border-color .22s ease, background .22s ease;
}
.nero-ai-flow-step:hover,
.nero-ai-flow-step:focus-within { transform: translateX(4px); border-color: rgba(121,242,255,.35); background: rgba(121,242,255,.065); }
.nero-ai-step-kicker {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 34px;
  border-radius: 999px;
  background: rgba(121,242,255,.10);
  color: var(--nero-ai-primary);
  font-size: 12px;
  font-weight: 900;
}
.nero-ai-flow-step h3 { margin: 0 0 6px; font-size: 18px; }
.nero-ai-flow-step p { margin: 0; font-size: 14px; line-height: 1.55; }

.nero-ai-process-list { counter-reset: neroAiStep; display: grid; gap: 14px; }
.nero-ai-process-item {
  counter-increment: neroAiStep;
  display: grid;
  grid-template-columns: 62px 1fr;
  gap: 18px;
  padding: 20px;
}
.nero-ai-process-item::before {
  content: counter(neroAiStep, decimal-leading-zero);
  display: grid;
  place-items: center;
  width: 52px;
  height: 52px;
  border-radius: 18px;
  background: linear-gradient(135deg, rgba(121,242,255,.18), rgba(34,197,94,.12));
  color: var(--nero-ai-primary);
  font-weight: 950;
}
.nero-ai-process-item h3 { margin: 0 0 6px; font-size: 19px; }
.nero-ai-process-item p { margin: 0; line-height: 1.58; }

.nero-ai-kpi-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.nero-ai-kpi-card { padding: 24px; }
.nero-ai-kpi-value { display: block; color: #fff; font-size: clamp(36px, 5vw, 62px); font-weight: 950; letter-spacing: -0.06em; line-height: .9; }
.nero-ai-kpi-label { display: block; margin-top: 10px; color: var(--nero-ai-muted); font-weight: 750; }
.nero-ai-bars { display: grid; gap: 12px; margin-top: 20px; }
.nero-ai-bar-row { display: grid; grid-template-columns: 74px 1fr 48px; gap: 10px; align-items: center; color: var(--nero-ai-muted); font-size: 12px; font-weight: 800; }
.nero-ai-bar-track { height: 10px; border-radius: 999px; background: rgba(255,255,255,.08); overflow: hidden; }
.nero-ai-bar-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--nero-ai-primary-2), var(--nero-ai-primary)); transform-origin: left; animation: neroAiBar .9s ease-out both; }
@keyframes neroAiBar { from { transform: scaleX(0); } to { transform: scaleX(1); } }

.nero-ai-seo-box {
  padding: clamp(26px, 4vw, 46px);
  border-radius: var(--nero-ai-radius-xl);
  border: 1px solid rgba(255,255,255,.10);
  background: linear-gradient(135deg, rgba(121,242,255,.07), rgba(139,92,246,.06), rgba(255,255,255,.035));
}
.nero-ai-seo-box h2 { margin: 0 0 20px; font-size: clamp(30px, 4vw, 52px); }
.nero-ai-seo-box p { margin: 0 0 16px; font-size: 17px; line-height: 1.72; }
.nero-ai-seo-box p:last-child { margin-bottom: 0; }

.nero-ai-faq { display: grid; gap: 12px; }
.nero-ai-faq details {
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 18px;
  background: rgba(255,255,255,.045);
  overflow: hidden;
}
.nero-ai-faq summary {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  padding: 18px 20px;
  color: #fff;
  cursor: pointer;
  font-weight: 850;
  list-style: none;
}
.nero-ai-faq summary::-webkit-details-marker { display: none; }
.nero-ai-faq summary::after { content: "+"; color: var(--nero-ai-primary); font-size: 22px; line-height: 1; }
.nero-ai-faq details[open] summary::after { content: "−"; }
.nero-ai-faq details p { margin: 0; padding: 0 20px 18px; line-height: 1.62; }

.nero-ai-final-cta {
  padding: clamp(34px, 5vw, 58px);
  border-radius: 34px;
  overflow: hidden;
  background:
    radial-gradient(circle at 15% 20%, rgba(121,242,255,.18), transparent 34%),
    radial-gradient(circle at 80% 10%, rgba(139,92,246,.18), transparent 34%),
    linear-gradient(135deg, rgba(255,255,255,.09), rgba(255,255,255,.04));
}
.nero-ai-final-cta h2 { margin: 0; max-width: 850px; font-size: clamp(34px, 5.2vw, 70px); line-height: .96; }
.nero-ai-final-cta p { margin: 18px 0 0; max-width: 760px; font-size: 18px; line-height: 1.65; }
.nero-ai-final-cta .nero-ai-btn-row { margin-top: 30px; }

[data-nero-tooltip] { position: relative; }
[data-nero-tooltip]::after {
  content: attr(data-nero-tooltip);
  position: absolute;
  left: 50%;
  bottom: calc(100% + 12px);
  width: min(280px, 80vw);
  transform: translate(-50%, 8px);
  z-index: 20;
  padding: 12px 13px;
  border: 1px solid rgba(121,242,255,.22);
  border-radius: 14px;
  background: rgba(5, 8, 18, .96);
  box-shadow: var(--nero-ai-shadow-soft);
  color: #eaf3ff;
  font-size: 12px;
  line-height: 1.45;
  opacity: 0;
  pointer-events: none;
  transition: opacity .18s ease, transform .18s ease;
}
[data-nero-tooltip]:hover::after,
[data-nero-tooltip]:focus-visible::after,
[data-nero-tooltip].nero-ai-tooltip-active::after { opacity: 1; transform: translate(-50%, 0); }

.nero-ai-reveal { opacity: 0; transform: translateY(28px); transition: opacity .72s ease, transform .72s ease; }
.nero-ai-reveal.nero-ai-active { opacity: 1; transform: translateY(0); }
.nero-ai-delay-1 { transition-delay: .08s; }
.nero-ai-delay-2 { transition-delay: .16s; }
.nero-ai-delay-3 { transition-delay: .24s; }
.nero-ai-delay-4 { transition-delay: .32s; }

@media (max-width: 1100px) {
  .nero-ai-hero-grid { grid-template-columns: 1fr; }
  .nero-ai-dashboard { transform: none; }
  .nero-ai-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .nero-ai-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 820px) {
  .nero-ai-container { width: min(100% - 28px, var(--nero-ai-container)); }
  .nero-ai-hero { min-height: auto; padding-top: 56px; }
  .nero-ai-map-grid { grid-template-columns: 1fr; }
  .nero-ai-ai-core { min-height: 210px; }
  .nero-ai-grid-2,
  .nero-ai-grid-3,
  .nero-ai-grid-4,
  .nero-ai-kpi-grid { grid-template-columns: 1fr; }
  .nero-ai-metrics-grid { grid-template-columns: 1fr; }
  .nero-ai-flow-step { grid-template-columns: 1fr; }
  .nero-ai-process-item { grid-template-columns: 1fr; }
  .nero-ai-btn-row { align-items: stretch; }
  .nero-ai-btn { width: 100%; }
}
@media (max-width: 520px) {
  .nero-ai-dashboard { padding: 10px; border-radius: 24px; }
  .nero-ai-window-body { padding: 13px; }
  .nero-ai-task { grid-template-columns: 28px 1fr; }
  .nero-ai-status { grid-column: 2; width: fit-content; }
  .nero-ai-section { padding: 58px 0; }
}
@media (prefers-reduced-motion: reduce) {
  .nero-ai-home-page *,
  .nero-ai-home-page *::before,
  .nero-ai-home-page *::after { animation: none !important; transition: none !important; scroll-behavior: auto !important; }
  .nero-ai-reveal { opacity: 1; transform: none; }
}
</style>

<main id="primary" class="site-main nero-ai-home-page" role="main" tabindex="-1">
  <section class="nero-ai-hero" aria-labelledby="nero-ai-home-title">
    <div class="nero-ai-container nero-ai-hero-grid">
      <div class="nero-ai-hero-copy nero-ai-reveal">
        <p class="nero-ai-eyebrow"><?php echo esc_html($brand); ?> · внедрение AI в бизнес</p>
        <h1 id="nero-ai-home-title">AI-автоматизация бизнеса <span class="nero-ai-gradient-text">под ключ</span></h1>
        <p class="nero-ai-hero-lead">
          Внедряем AI-агентов, CRM-автоматизацию, голосовых менеджеров, чат-ботов и сценарии Make/n8n,
          чтобы заявки, продажи, документы и коммуникации работали без ручной рутины.
        </p>
        <ul class="nero-ai-badges" aria-label="Направления внедрения">
          <li class="nero-ai-badge">AI-агенты</li>
          <li class="nero-ai-badge">CRM</li>
          <li class="nero-ai-badge">Телефония</li>
          <li class="nero-ai-badge">Make/n8n</li>
          <li class="nero-ai-badge">Продажи</li>
          <li class="nero-ai-badge">Поддержка</li>
          <li class="nero-ai-badge">Документы</li>
          <li class="nero-ai-badge">Аналитика</li>
        </ul>
        <div class="nero-ai-btn-row">
          <a class="nero-ai-btn nero-ai-btn-primary" href="<?php echo esc_url($primary_cta_url); ?>"><?php echo esc_html($primary_cta_label); ?></a>
          <a class="nero-ai-btn nero-ai-btn-secondary" href="<?php echo esc_url($secondary_cta_url); ?>"><?php echo esc_html($secondary_cta_label); ?></a>
        </div>
      </div>

      <div class="nero-ai-dashboard nero-ai-reveal nero-ai-delay-2" aria-label="Демонстрация AI-операционного центра">
        <div class="nero-ai-dashboard-shell">
          <div class="nero-ai-window-top">
            <div class="nero-ai-dots"><span class="nero-ai-dot"></span><span class="nero-ai-dot"></span><span class="nero-ai-dot"></span></div>
            <span class="nero-ai-window-title">пример логики AI-системы · демо</span>
          </div>
          <div class="nero-ai-window-body">
            <div class="nero-ai-dashboard-title">
              <h3>AI-операционный центр онлайн</h3>
              <span class="nero-ai-live-pill">live</span>
            </div>
            <div class="nero-ai-metrics-grid">
              <div class="nero-ai-metric" data-nero-tooltip="AI фиксирует канал заявки, нормализует данные и отправляет их в CRM без ручного копирования.">
                <span>Входящие заявки</span><strong>24</strong><small>за сегодня</small>
              </div>
              <div class="nero-ai-metric" data-nero-tooltip="Цель — сократить ожидание клиента: автоответ, квалификация и задача менеджеру запускаются сразу.">
                <span>Средний ответ</span><strong>1:42</strong><small>минуты</small>
              </div>
              <div class="nero-ai-metric" data-nero-tooltip="Голосовой агент может принять типовой запрос, передать сложный кейс человеку и сохранить итог разговора.">
                <span>Звонки в очереди</span><strong>18</strong><small>под контролем</small>
              </div>
              <div class="nero-ai-metric" data-nero-tooltip="Демонстрационная метрика: эффект зависит от процесса, интеграций и качества исходных данных.">
                <span>Ручные операции</span><strong>−38%</strong><small>потенциал пилота</small>
              </div>
            </div>

            <div class="nero-ai-task-stream" aria-label="Живой поток задач">
              <div class="nero-ai-task"><span class="nero-ai-task-icon">↳</span><div><strong>Новая заявка с сайта</strong><span>AI-квалификация и теги</span></div><span class="nero-ai-status">готово</span></div>
              <div class="nero-ai-task"><span class="nero-ai-task-icon">CRM</span><div><strong>Лид записан в CRM</strong><span>Сделка, источник, задача</span></div><span class="nero-ai-status">готово</span></div>
              <div class="nero-ai-task"><span class="nero-ai-task-icon">TG</span><div><strong>Клиенту отправлен автоответ</strong><span>Без ожидания менеджера</span></div><span class="nero-ai-status">готово</span></div>
              <div class="nero-ai-task"><span class="nero-ai-task-icon">AI</span><div><strong>Рекомендация менеджеру</strong><span>Позвонить в течение 15 минут</span></div><span class="nero-ai-status">новое</span></div>
            </div>

            <div class="nero-ai-automation-map" aria-label="Схема как AI забирает рутину">
              <p class="nero-ai-map-title">Как нейросети забирают рутинную работу</p>
              <div class="nero-ai-map-grid">
                <div class="nero-ai-people">
                  <div class="nero-ai-person" data-nero-tooltip="Раньше менеджер вручную переносил данные из чатов и форм. Теперь AI готовит карточку и следующий шаг."><span class="nero-ai-person-figure"></span><div><span>Менеджер</span><small>меньше копирования</small></div></div>
                  <div class="nero-ai-person" data-nero-tooltip="Оператор не тратит время на одинаковые вопросы: AI отвечает на типовые запросы и эскалирует сложные."><span class="nero-ai-person-figure"></span><div><span>Оператор</span><small>типовые ответы</small></div></div>
                  <div class="nero-ai-person" data-nero-tooltip="Руководитель видит отчёт, причины отказов, SLA и узкие места без ручных таблиц."><span class="nero-ai-person-figure"></span><div><span>Руководитель</span><small>контроль воронки</small></div></div>
                </div>
                <div class="nero-ai-ai-core">
                  <span class="nero-ai-core-ring"></span><span class="nero-ai-core-ring"></span><span class="nero-ai-flow-line"></span><span class="nero-ai-flow-line"></span>
                  <div class="nero-ai-core-chip"><div><strong>AI</strong><span>ядро</span></div></div>
                </div>
                <div class="nero-ai-processes">
                  <div class="nero-ai-process-node" data-nero-tooltip="Заявки автоматически собираются из сайта, Telegram, телефонии и CRM в один поток."><div><span>Заявки</span><small>квалификация</small></div></div>
                  <div class="nero-ai-process-node" data-nero-tooltip="AI создаёт сделки, задачи, напоминания и статусы по правилам бизнеса."><div><span>CRM</span><small>сделки и задачи</small></div></div>
                  <div class="nero-ai-process-node" data-nero-tooltip="Сводки звонков, письма, КП и отчёты готовятся быстрее, но человек может утверждать результат."><div><span>Документы</span><small>черновики и отчёты</small></div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="pain" class="nero-ai-section nero-ai-section-alt">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-reveal">
        <p class="nero-ai-eyebrow">Боли бизнеса</p>
        <h2>Где теряются деньги и время</h2>
        <p>Типовые ситуации, которые закрываются внедрением AI-агентов и автоматизации процессов.</p>
      </div>
      <div class="nero-ai-grid-2">
        <?php
        $pains = [
          'Заявки теряются между сайтом, мессенджерами и CRM',
          'Менеджеры отвечают долго — клиент уходит к конкуренту',
          'После звонка или заявки нет системного follow-up',
          'Руководитель не видит реальную картину по продажам',
          'Сотрудники вручную переносят данные между системами',
          'Документы и КП собираются слишком долго',
          'База знаний есть, но командой почти не используется',
          'Звонки, чаты и сделки не анализируются системно',
        ];
        foreach ($pains as $pain) : ?>
          <article class="nero-ai-card nero-ai-pain-card nero-ai-reveal"><span class="nero-ai-pain-mark">!</span><p><?php echo esc_html($pain); ?></p></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="services" class="nero-ai-section">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-reveal">
        <p class="nero-ai-eyebrow">Услуги</p>
        <h2>Что мы внедряем</h2>
        <p>Не один чат-бот, а связанная система под ваши процессы, CRM и каналы коммуникации.</p>
      </div>
      <div class="nero-ai-grid-3">
        <?php
        $services = [
          ['🤖','AI-менеджер для входящих заявок','Принимает заявки с сайта и мессенджеров, квалифицирует и передаёт в CRM.','Интернет-магазины, услуги, B2B'],
          ['📌','AI-агент для amoCRM и Bitrix24','Работает внутри CRM: задачи, статусы, напоминания, подсказки менеджеру.','Отделы продаж'],
          ['☎️','Голосовой AI-оператор','Обрабатывает типовые звонки, фиксирует итог, передаёт сложное человеку.','Сервис, медицина, недвижимость'],
          ['💬','AI-ассистент для продаж','Готовит ответы, резюме звонков, следующий шаг по сделке.','Менеджеры по продажам'],
          ['⚙️','Автоматизация обработки лидов','Маршрутизация, дедупликация, SLA по ответу, контроль потерянных заявок.','Поток лидов'],
          ['📚','AI-база знаний компании','RAG по регламентам и продуктам — ответы для команды и клиентов.','Поддержка и онбординг'],
          ['📄','Автоматизация документов и КП','Шаблоны, сбор данных, черновики под проверку человека.','B2B, агентства, юристы'],
          ['📊','Аналитика звонков, чатов и сделок','Сводки, теги, причины отказов для руководителя.','РОП и владельцы'],
          ['🔗','Интеграции Make, n8n, API','Связываем сайт, CRM, телефонию, почту, webhooks и отчёты.','Любой стек с API'],
        ];
        foreach ($services as $service) : ?>
          <article class="nero-ai-card nero-ai-service-card nero-ai-reveal" data-nero-tooltip="<?php echo esc_attr($service[2]); ?>">
            <div class="nero-ai-card-icon" aria-hidden="true"><?php echo esc_html($service[0]); ?></div>
            <h3><?php echo esc_html($service[1]); ?></h3>
            <p><?php echo esc_html($service[2]); ?></p>
            <small>Кому: <?php echo esc_html($service[3]); ?></small>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="center" class="nero-ai-section nero-ai-section-alt">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-left nero-ai-reveal">
        <p class="nero-ai-eyebrow">Система, не бот</p>
        <h2>AI-операционный центр</h2>
        <p>Живая цепочка: что происходит с заявкой от первого касания до отчёта руководителю.</p>
      </div>
      <div class="nero-ai-center-flow">
        <?php
        $flow = [
          ['вход','Заявка поступила','Сайт, форма, Telegram или звонок — AI фиксирует канал и время.'],
          ['AI','Приём и нормализация','Имя, телефон, запрос — в единый формат без ручного копирования.'],
          ['AI','Проверка дубля','Сверка с CRM: новый лид или продолжение диалога.'],
          ['AI','AI-квалификация','Сегмент, приоритет, теги по правилам бизнеса.'],
          ['CRM','Запись в CRM','Контакт, сделка, стадия — amoCRM / Bitrix24.'],
          ['auto','Сценарий Make/n8n','Триггеры: уведомления, webhooks, смежные системы.'],
          ['итог','Задача менеджеру','SLA, дедлайн, контекст переписки в одной карточке.'],
          ['отчёт','Дашборд руководителя','Воронка, причины отказов, узкие места и контроль SLA.'],
        ];
        $i = 0;
        foreach ($flow as $item) : $i++; ?>
          <article class="nero-ai-flow-step nero-ai-reveal" data-nero-tooltip="Шаг <?php echo esc_attr((string)$i); ?>: <?php echo esc_attr($item[2]); ?>">
            <span class="nero-ai-step-kicker"><?php echo esc_html($item[0]); ?></span>
            <div><h3>Шаг <?php echo esc_html((string)$i); ?> · <?php echo esc_html($item[1]); ?></h3><p><?php echo esc_html($item[2]); ?></p></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="trends" class="nero-ai-section">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-reveal">
        <p class="nero-ai-eyebrow">Тренды 2026</p>
        <h2>Куда движется рынок AI-автоматизации</h2>
        <p>Бизнесу нужно не «поиграться с нейросетью», а встроить AI в процессы, где есть заявки, деньги и клиенты.</p>
      </div>
      <div class="nero-ai-grid-4">
        <?php
        $trends = [
          ['Agentic automation','AI-агенты выполняют цепочки задач, а не просто отвечают в чате.'],
          ['AI в CRM','Подсказки, задачи, статусы и контроль прямо в рабочей системе.'],
          ['Human-in-the-loop','AI готовит и выполняет, человек утверждает рискованные действия.'],
          ['RAG-базы знаний','Ответы по регламентам, товарам, документам и базе компании.'],
          ['Voice AI','Голосовые AI-менеджеры для входящей линии и первичной обработки.'],
          ['No-code/API','Make, n8n и webhooks ускоряют пилоты без долгой разработки.'],
          ['Логи и безопасность','Контроль ролей, действий, ошибок и качества ответов.'],
          ['ROI пилота','Оценка эффекта на одном процессе до масштабирования.'],
        ];
        foreach ($trends as $trend) : ?>
          <article class="nero-ai-card nero-ai-trend-card nero-ai-reveal"><h3><?php echo esc_html($trend[0]); ?></h3><p><?php echo esc_html($trend[1]); ?></p></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="process" class="nero-ai-section nero-ai-section-alt">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-reveal">
        <p class="nero-ai-eyebrow">Процесс</p>
        <h2>Как проходит внедрение</h2>
        <p>Сначала находим ручные операции с быстрым эффектом, затем собираем пилот и масштабируем.</p>
      </div>
      <div class="nero-ai-process-list">
        <?php
        $steps = [
          ['Аудит процессов','Изучаем заявки, продажи, CRM, звонки, чаты, документы и ручные операции.'],
          ['Выбор точек автоматизации','Определяем, где AI даст быстрый эффект: заявки, лиды, звонки, поддержка.'],
          ['Проектирование AI-сценариев','Роли агентов, правила ответов, ограничения, передача человеку.'],
          ['Интеграция с системами','CRM, сайт, телефония, Telegram, почта, Make/n8n/API.'],
          ['Настройка AI-агентов','Промты, логика, память, сценарии, обработка ошибок.'],
          ['Тестирование на реальных заявках','Проверяем типовые ситуации, дорабатываем, фиксируем метрики.'],
          ['Запуск и аналитика','Передаём в работу, обучаем команду, улучшаем по отчётам.'],
        ];
        foreach ($steps as $step) : ?>
          <article class="nero-ai-card nero-ai-process-item nero-ai-reveal"><div><h3><?php echo esc_html($step[0]); ?></h3><p><?php echo esc_html($step[1]); ?></p></div></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="niches" class="nero-ai-section">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-reveal">
        <p class="nero-ai-eyebrow">Ниши</p>
        <h2>Кому подходит</h2>
        <p>Наведите или нажмите на карточку, чтобы увидеть, что закрывает AI-автоматизация.</p>
      </div>
      <div class="nero-ai-grid-4">
        <?php
        $niches = [
          ['Онлайн-школы','AI принимает заявку, квалифицирует и ставит задачу в CRM.'],
          ['Юридические компании','Автоприём заявок, черновики документов, маршрутизация юристу.'],
          ['Медицинские центры','Голосовой AI и чат-бот: запись, FAQ, передача сложного администратору.'],
          ['Недвижимость','Мгновенный ответ, CRM, задача агенту с контекстом объекта.'],
          ['E-commerce','AI в поддержке + триггеры по заказам, возвратам и статусам.'],
          ['Маркетплейсы','Автоответы, сводки отзывов, алерты менеджеру по правилам.'],
          ['Сервисные компании','Квалификация заявки, слот мастера, напоминания клиенту.'],
          ['B2B-продажи','КП, письма, резюме созвонов и следующий шаг сделки.'],
        ];
        foreach ($niches as $niche) : ?>
          <article class="nero-ai-card nero-ai-niche-card nero-ai-reveal" data-nero-tooltip="<?php echo esc_attr($niche[1]); ?>"><h3><?php echo esc_html($niche[0]); ?></h3><p><?php echo esc_html($niche[1]); ?></p></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="result" class="nero-ai-section nero-ai-section-alt">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-reveal">
        <p class="nero-ai-eyebrow">Результат</p>
        <h2>Что получает бизнес</h2>
        <p>Без обещаний «магических» цифр: ниже — демонстрационная логика эффекта, которую можно проверять на пилоте.</p>
      </div>
      <div class="nero-ai-kpi-grid">
        <article class="nero-ai-card nero-ai-kpi-card nero-ai-reveal" data-nero-tooltip="Меньше ручного копирования, переносов, повторов и забытых follow-up.">
          <strong class="nero-ai-kpi-value" data-nero-count="38" data-nero-prefix="−" data-nero-suffix="%">−0%</strong><span class="nero-ai-kpi-label">меньше ручных операций*</span>
          <div class="nero-ai-bars"><div class="nero-ai-bar-row"><span>До</span><span class="nero-ai-bar-track"><span class="nero-ai-bar-fill" style="width:82%"></span></span><span>4 ч</span></div><div class="nero-ai-bar-row"><span>После</span><span class="nero-ai-bar-track"><span class="nero-ai-bar-fill" style="width:34%"></span></span><span>1.5 ч</span></div></div>
        </article>
        <article class="nero-ai-card nero-ai-kpi-card nero-ai-reveal nero-ai-delay-1" data-nero-tooltip="Цель пилота — быстрый первичный ответ и фиксация заявки в системе.">
          <strong class="nero-ai-kpi-value" data-nero-count="3" data-nero-suffix=" мин">0 мин</strong><span class="nero-ai-kpi-label">целевой ответ на заявку*</span>
          <div class="nero-ai-bars"><div class="nero-ai-bar-row"><span>Ожидание</span><span class="nero-ai-bar-track"><span class="nero-ai-bar-fill" style="width:86%"></span></span><span>часы</span></div><div class="nero-ai-bar-row"><span>С AI</span><span class="nero-ai-bar-track"><span class="nero-ai-bar-fill" style="width:22%"></span></span><span>мин</span></div></div>
        </article>
        <article class="nero-ai-card nero-ai-kpi-card nero-ai-reveal nero-ai-delay-2" data-nero-tooltip="Звонки, чаты, сделки и задачи видны в единой логике, а не в разрозненных заметках.">
          <strong class="nero-ai-kpi-value" data-nero-count="100" data-nero-suffix="%">0%</strong><span class="nero-ai-kpi-label">прозрачность этапов*</span>
          <div class="nero-ai-bars"><div class="nero-ai-bar-row"><span>Было</span><span class="nero-ai-bar-track"><span class="nero-ai-bar-fill" style="width:36%"></span></span><span>фрагм.</span></div><div class="nero-ai-bar-row"><span>Стало</span><span class="nero-ai-bar-track"><span class="nero-ai-bar-fill" style="width:100%"></span></span><span>центр</span></div></div>
        </article>
      </div>
    </div>
  </section>

  <section class="nero-ai-section-tight">
    <div class="nero-ai-container">
      <div class="nero-ai-seo-box nero-ai-reveal">
        <h2>Внедрение AI и автоматизация бизнес-процессов</h2>
        <p><?php echo esc_html($brand); ?> помогает компаниям перейти от разрозненных инструментов к связанной AI-автоматизации бизнеса: AI-агенты для бизнеса, интеграция с amoCRM и Bitrix24, голосовой AI-менеджер, обработка заявок и автоматизация CRM через Make и n8n.</p>
        <p>Мы проектируем нейросети для бизнеса и искусственный интеллект для бизнеса как часть операционной модели: AI для отдела продаж, AI для клиентской поддержки, автоматизация звонков и автоматизация документооборота с контролем человека.</p>
        <p>Внедрение нейросетей в компанию начинается с аудита: где есть заявки, лиды, переписки и ручные шаги. Дальше — интеграция AI с CRM, RAG-база знаний и сценарии AI-менеджера под ваши регламенты.</p>
      </div>
    </div>
  </section>

  <section id="faq" class="nero-ai-section nero-ai-section-alt">
    <div class="nero-ai-container">
      <div class="nero-ai-section-head nero-ai-reveal">
        <p class="nero-ai-eyebrow">FAQ</p>
        <h2>Частые вопросы</h2>
      </div>
      <div class="nero-ai-faq nero-ai-reveal">
        <?php
        $faq = [
          ['С чего начать внедрение AI в бизнес?','С аудита процессов: заявки, CRM, звонки, чаты. Выбираем 1–2 сценария с быстрым эффектом и измеримыми метриками.'],
          ['Можно ли подключить AI к amoCRM или Bitrix24?','Да. Настраиваем AI-агентов и автоматизацию внутри CRM через API, webhooks, Make или n8n.'],
          ['AI может отвечать клиентам сам?','Да, на типовые запросы — с ограничениями и эскалацией сложных кейсов человеку.'],
          ['Можно ли оставить подтверждение человеком?','Да. Human-in-the-loop — стандартная модель: AI готовит, человек утверждает.'],
          ['Сколько времени занимает внедрение?','Пилот одного процесса — от нескольких недель; масштабирование зависит от интеграций и числа сценариев.'],
          ['Что автоматизировать в первую очередь?','Обычно: входящие заявки, скорость ответа, запись в CRM, напоминания менеджерам.'],
          ['Безопасно ли подключать AI к данным компании?','Работаем с разграничением доступа, логами и политиками; чувствительные данные — по согласованной модели.'],
          ['Чем AI-агент отличается от обычного чат-бота?','Агент выполняет действия: пишет в CRM, ставит задачи, маршрутизирует — не только отвечает в чате.'],
        ];
        foreach ($faq as $item) : ?>
          <details><summary><?php echo esc_html($item[0]); ?></summary><p><?php echo esc_html($item[1]); ?></p></details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="audit" class="nero-ai-section">
    <div class="nero-ai-container">
      <div class="nero-ai-final-cta nero-ai-card nero-ai-reveal">
        <h2>Покажем, какие процессы можно автоматизировать уже сейчас</h2>
        <p>Разберём заявки, CRM, звонки, чаты и ручные операции. Покажем, где AI снимет рутину и усилит контроль.</p>
        <div class="nero-ai-btn-row">
          <a class="nero-ai-btn nero-ai-btn-primary" href="<?php echo esc_url($primary_cta_url); ?>"><?php echo esc_html($primary_cta_label); ?></a>
          <a class="nero-ai-btn nero-ai-btn-secondary" href="<?php echo esc_url($secondary_cta_url); ?>">Обсудить проект</a>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
(function () {
  'use strict';

  var root = document.querySelector('.nero-ai-home-page');
  if (!root) return;

  var revealItems = root.querySelectorAll('.nero-ai-reveal');
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('nero-ai-active');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

    revealItems.forEach(function (item) { observer.observe(item); });
  } else {
    revealItems.forEach(function (item) { item.classList.add('nero-ai-active'); });
  }

  var tooltipItems = root.querySelectorAll('[data-nero-tooltip]');
  tooltipItems.forEach(function (item) {
    if (!item.hasAttribute('tabindex')) item.setAttribute('tabindex', '0');

    item.addEventListener('click', function (event) {
      var isActive = item.classList.contains('nero-ai-tooltip-active');
      tooltipItems.forEach(function (other) { other.classList.remove('nero-ai-tooltip-active'); });
      if (!isActive) item.classList.add('nero-ai-tooltip-active');
      event.stopPropagation();
    });
  });

  document.addEventListener('click', function () {
    tooltipItems.forEach(function (item) { item.classList.remove('nero-ai-tooltip-active'); });
  });

  var counters = root.querySelectorAll('[data-nero-count]');
  function animateCounter(el) {
    var target = parseFloat(el.getAttribute('data-nero-count') || '0');
    var suffix = el.getAttribute('data-nero-suffix') || '';
    var prefix = el.getAttribute('data-nero-prefix') || '';
    var duration = 850;
    var start = performance.now();

    function frame(now) {
      var progress = Math.min((now - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      var value = Math.round(target * eased);
      el.textContent = prefix + value + suffix;
      if (progress < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  if ('IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !entry.target.dataset.neroDone) {
          entry.target.dataset.neroDone = '1';
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.35 });
    counters.forEach(function (counter) { counterObserver.observe(counter); });
  } else {
    counters.forEach(animateCounter);
  }
})();
</script>

<?php
get_footer();
