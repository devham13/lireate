<?php
header('Content-Type: text/html; charset=utf-8');
$path = '/uslugi/registraciya-tovarnogo-znaka-v-kitae/';
$host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST']) : '';
$canonical = ($host !== '' ? 'https://' . $host : '') . $path;
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Регистрация товарного знака в Китае — LIREATE</title>
  <meta name="description" content="Подача и сопровождение регистрации товарного знака в КНР: классы МКТУ, перевод, CNIPA, риски для экспорта и маркетплейсов.">
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="assets/longread-page-design-reference.css">
  <style>
    .lireate-page-extra { --lireate-gold: #f59e0b; --lireate-red: #ef4444; }
    .lireate-sticky-header { position: sticky; top: 0; z-index: 40; backdrop-filter: blur(14px); background: rgba(5,7,17,.82); border-bottom: 1px solid rgba(255,255,255,.08); }
    .lireate-sticky-inner { max-width: 1120px; margin: 0 auto; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .lireate-logo { font-weight: 800; letter-spacing: .06em; color: #e8f4ff; text-decoration: none; }
    .lireate-nav { display: flex; gap: 14px; flex-wrap: wrap; }
    .lireate-nav a { color: #94a3b8; font-size: 13px; text-decoration: none; }
    .lireate-nav a:hover { color: #79f2ff; }
    .lireate-hero-split { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: center; }
    @media (max-width: 900px) { .lireate-hero-split { grid-template-columns: 1fr; } }
    #china-tm-hero-canvas { width: 100%; height: 420px; display: block; border-radius: 24px; background: #080b17; border: 1px solid rgba(121,242,255,.2); }
    .lireate-live-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.35); color: #86efac; font-size: 12px; font-weight: 700; }
    .lireate-live-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: lireatePulse 1.4s ease infinite; }
    @keyframes lireatePulse { 0%,100%{opacity:.5;transform:scale(.9)} 50%{opacity:1;transform:scale(1.1)} }
    .lireate-program-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 24px; }
    @media (max-width: 960px) { .lireate-program-grid { grid-template-columns: 1fr; } }
    .lireate-program-card { position: relative; padding: 22px 20px 20px; border-radius: 22px; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.1); transition: transform .2s, border-color .2s; }
    .lireate-program-card:hover { transform: translateY(-4px); border-color: rgba(121,242,255,.35); }
    .lireate-program-num { font-size: 11px; font-weight: 800; letter-spacing: .14em; color: var(--lireate-gold); }
    .lireate-program-card h3 { margin: 10px 0 8px; font-size: 18px; color: #f1f5f9; }
    .lireate-program-card p { margin: 0; color: #94a3b8; font-size: 14px; line-height: 1.55; }
    .lireate-checklist { color: #cbd5e1; line-height: 1.7; padding-left: 1.2em; }
    .lireate-faq { margin-bottom: 10px; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; padding: 12px 16px; background: rgba(255,255,255,.03); }
    .lireate-faq summary { cursor: pointer; font-weight: 650; color: #e2e8f0; }
    .lireate-faq p { margin: 10px 0 0; color: #94a3b8; font-size: 14px; }
    .lireate-contact-zone { display: grid; grid-template-columns: 1.1fr .9fr; gap: 24px; align-items: start; }
    @media (max-width: 900px) { .lireate-contact-zone { grid-template-columns: 1fr; } }
    .lireate-form-panel { padding: 26px; border-radius: 26px; background: linear-gradient(145deg, rgba(139,92,246,.12), rgba(121,242,255,.06)); border: 1px solid rgba(121,242,255,.25); }
    .lireate-form-panel label { display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px; }
    .lireate-form-panel input, .lireate-form-panel textarea { width: 100%; box-sizing: border-box; margin-bottom: 14px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,.14); background: rgba(5,7,17,.6); color: #f8fafc; font: inherit; }
    .lireate-form-panel textarea { min-height: 110px; resize: vertical; }
    .lireate-messenger-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 16px; }
    .lireate-msg-btn { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-radius: 16px; text-decoration: none; color: #f8fafc; font-weight: 650; font-size: 14px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.05); transition: transform .15s, border-color .15s; }
    .lireate-msg-btn:hover { transform: translateY(-2px); border-color: rgba(121,242,255,.4); }
    .lireate-msg-btn svg { width: 22px; height: 22px; flex-shrink: 0; }
    .lireate-msg-telegram { background: linear-gradient(135deg, rgba(56,189,248,.2), rgba(37,99,235,.15)); }
    .lireate-msg-whatsapp { background: linear-gradient(135deg, rgba(34,197,94,.22), rgba(21,128,61,.12)); }
    .lireate-msg-max { background: linear-gradient(135deg, rgba(168,85,247,.2), rgba(79,70,229,.12)); }
    .lireate-floating-dock { position: fixed; right: 18px; bottom: 18px; z-index: 50; display: flex; flex-direction: column; gap: 10px; }
    .lireate-floating-dock a { width: 52px; height: 52px; border-radius: 50%; display: grid; place-items: center; background: rgba(8,11,23,.92); border: 1px solid rgba(121,242,255,.35); box-shadow: 0 12px 40px rgba(0,0,0,.45); text-decoration: none; transition: transform .15s; }
    .lireate-floating-dock a:hover { transform: scale(1.06); }
    .lireate-form-success { display: none; margin-top: 12px; padding: 12px; border-radius: 12px; background: rgba(34,197,94,.15); color: #86efac; font-size: 14px; }
    .lireate-form-success.is-visible { display: block; }
    .lireate-consent { font-size: 12px; color: #64748b; line-height: 1.5; }
    .lireate-consent a { color: #79f2ff; }
  </style>
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"Service","name":"Регистрация товарного знака в Китае","provider":{"@type":"LegalService","name":"LIREATE"},"areaServed":"CN","description":"Сопровождение регистрации товарного знака в КНР"}
  </script>
</head>
<body class="lireate-page-extra">
<header class="lireate-sticky-header" role="banner">
  <div class="lireate-sticky-inner">
    <a class="lireate-logo" href="/">LIREATE</a>
    <nav class="lireate-nav" aria-label="Разделы страницы">
      <a href="#program">Маршрут</a>
      <a href="#classes">МКТУ</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Заявка</a>
    </nav>
    <a class="nero-ai-btn nero-ai-btn-primary" href="#contact">Консультация</a>
  </div>
</header>

<main id="primary" class="site-main nero-ai-home-page registraciya-tovarnogo-znaka-v-kitae-page" role="main" tabindex="-1">

<section class="nero-ai-hero nero-ai-section" id="top">
  <div class="nero-ai-wrap lireate-hero-split">
    <div>
      <span class="lireate-live-pill">Сопровождение подачи в CNIPA</span>
      <h1 class="nero-ai-hero-title">Регистрация товарного знака в&nbsp;Китае</h1>
      <p class="nero-ai-hero-sub">Выводите бренд на рынок КНР с понятным маршрутом: аудит знака, классы МКТУ, перевод, подача и ответы на уведомления ведомства. Без гарантий исхода экспертизы — с прозрачными этапами и связкой с вашим российским портфелем ИС.</p>
      <div class="nero-ai-hero-cta" style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
        <a class="nero-ai-btn nero-ai-btn-primary" href="#contact">Оставить заявку</a>
        <a class="nero-ai-btn nero-ai-btn-secondary" href="#program">Смотреть программу</a>
      </div>
      <div class="nero-ai-metrics" style="margin-top:28px;display:flex;gap:18px;flex-wrap:wrap;">
        <div class="nero-ai-metric" data-nero-tooltip="Ориентир по срокам после подачи — зависит от CNIPA"><strong data-nero-count="12" data-nero-suffix="+ мес.">0+ мес.</strong><span>типичный цикл экспертизы</span></div>
        <div class="nero-ai-metric" data-nero-tooltip="Количество классов влияет на пошлины и объем описания"><strong data-nero-count="6" data-nero-suffix=" блоков">0 блоков</strong><span>маршрут работ</span></div>
        <div class="nero-ai-metric" data-nero-tooltip="Переписка и статусы — на китайском и русском"><strong data-nero-count="2" data-nero-suffix=" языка">0 языка</strong><span>координация</span></div>
      </div>
    </div>
    <div class="nero-ai-dashboard" aria-hidden="true">
      <canvas id="china-tm-hero-canvas" role="img" aria-label="Анимация: путь заявки на товарный знак от России до регистрации в Китае"></canvas>
    </div>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="why-china">
  <div class="nero-ai-wrap">
    <h2>Зачем отдельно регистрировать знак в Китае</h2>
    <p class="nero-ai-lead">Российское или международное свидетельство (в том числе по системе Мадридского соглашения) не заменяет национальную процедуру в КНР автоматически для всех сценариев. Если вы выводите бренд на китайский рынок, продаёте на локальных маркетплейсах, открываете представительство или лицензируете производство, права на обозначение в Китае нужно выстраивать заранее — пока конкурент не подал заявку на сходное имя первым.</p>
    <p>В КНР действует принцип «первым подал — первым получил право» в рамках национальной регистрации. Это означает, что промедление с подачей в CNIPA (Национальное управление интеллектуальной собственности Китая) повышает риск блокировки вашего же бренда третьими лицами. Для бизнеса это не абстрактный юридический тезис: без регистрации сложнее защищать логотип в рекламе, на упаковке, в доменных спорах и при работе с дистрибьюторами.</p>
    <p>Мы сопровождаем подготовку материалов, перевод на китайский, выбор классов МКТУ под реальный ассортимент и коммуникацию по формальным требованиям ведомства. Ниже — структурированный разбор без обещаний «гарантированной регистрации»: итог экспертизы зависит от перечня товаров и услуг, сходства с чужими знаками и полноты доказательств использования, если они запрашиваются.</p>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal nero-ai-delay-1" id="legal-frame">
  <div class="nero-ai-wrap">
    <h2>Правовая рамка: КНР, Мадрид и российский контур</h2>
    <p>Национальная регистрация ведется по правилам Китая через CNIPA. Международная подача возможна в том числе через Мадридскую систему (ВОИС) с указанием Китая как охраняемой территории — но стратегия «только Мадрид» или «только национальная заявка» зависит от портфеля знаков, сроков и бюджета. Подробности процедур публикуются на сайтах <a href="https://www.cnipa.gov.cn/" rel="noopener noreferrer" target="_blank">CNIPA</a> и <a href="https://www.wipo.int/portal/en/index.html" rel="noopener noreferrer" target="_blank">ВОИС</a>.</p>
    <p>Для российского правообладателя важно синхронизировать китайскую заявку с действующими договорами, лицензиями и структурой группы компаний. Вопросы трансграничного использования знака и ответственности дистрибьюторов разбираются в связке с <a href="https://www.consultant.ru/document/cons_doc_LAW_64629/" rel="noopener noreferrer" target="_blank">частью четвертой ГК РФ</a> и договорной документацией — без подмены китайского права российскими нормами.</p>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="program">
  <div class="nero-ai-wrap">
    <h2>Маршрут регистрации: 6 блоков работ</h2>
    <p class="nero-ai-lead">Как на насыщенных лендингах с пошаговой программой, мы разбили путь на блоки — чтобы вы видели, где нужны ваши данные, а где подключается юрист и переводчик.</p>
    <div class="lireate-program-grid">
      <article class="lireate-program-card nero-ai-reveal nero-ai-delay-1" data-nero-tooltip="Собираем описание бренда, варианты написания латиницей и иероглифами, проверяем риски сходства по открытым реестрам.">
        <span class="lireate-program-num">01</span>
        <h3>Аудит обозначения</h3>
        <p>Поиск коллизий, оценка охраноспособности, рекомендации по классам МКТУ и формату знака (словесный, изобразительный, комбинированный).</p>
      </article>
      <article class="lireate-program-card nero-ai-reveal nero-ai-delay-2" data-nero-tooltip="Подготовка заявки, доверенности, перевода и соответствие формальным требованиям CNIPA.">
        <span class="lireate-program-num">02</span>
        <h3>Пакет документов</h3>
        <p>Учредительные документы заявителя, описание товаров и услуг, изображение знака, перевод на китайский язык, оформление представителя в КНР.</p>
      </article>
      <article class="lireate-program-card nero-ai-reveal nero-ai-delay-3" data-nero-tooltip="Подача в CNIPA и контроль номера заявки, сроков ответа на уведомления.">
        <span class="lireate-program-num">03</span>
        <h3>Подача и сопровождение</h3>
        <p>Взаимодействие с китайским поверенным, отслеживание стадий экспертизы, ответы на запросы ведомства в установленные сроки.</p>
      </article>
      <article class="lireate-program-card nero-ai-reveal nero-ai-delay-1" data-nero-tooltip="При необходимости — возражения, переговоры, корректировка перечня классов.">
        <span class="lireate-program-num">04</span>
        <h3>Экспертиза и возражения</h3>
        <p>Если поступит предварительный отказ, готовим аргументированный ответ: сравнение знаков, доводы о различии, уточнение формулировок товаров.</p>
      </article>
      <article class="lireate-program-card nero-ai-reveal nero-ai-delay-2" data-nero-tooltip="Регистрация в реестре, получение свидетельства, рекомендации по использованию знака на рынке.">
        <span class="lireate-program-num">05</span>
        <h3>Регистрация и свидетельство</h3>
        <p>После положительного решения — оплата пошлин за регистрацию и выдачу свидетельства, контроль публикации в реестре.</p>
      </article>
      <article class="lireate-program-card nero-ai-reveal nero-ai-delay-3" data-nero-tooltip="Мониторинг сходных заявок, продление, лицензии дистрибьюторам.">
        <span class="lireate-program-num">06</span>
        <h3>Пост-регистрация</h3>
        <p>Наблюдение за сходными подачами, напоминания о продлении, типовые лицензионные оговорки для контрактов в Китае.</p>
      </article>
    </div>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="classes">
  <div class="nero-ai-wrap">
    <h2>Классы МКТУ и «широкий» перечень</h2>
    <p>Ошибка на старте — заявить слишком узкий перечень товаров и услуг, а через год расширять линейку под другим юридическим покрытием. Мы помогаем сформулировать перечень под текущий экспорт и планируемый ассортимент на маркетплейсах (одежда, косметика, электроника, B2B-оборудование и т.д.), не уходя в заведомо неохраняемые формулировки.</p>
    <p>Для комбинированных знаков отдельно прорабатываем версию на китайском: транслитерация, смысловой перевод, проверка культурных и языковых коннотаций. Негативные значения иероглифов в рекламе — отдельный риск, который дешевле закрыть до подачи, а не после отказа контрагента размещать бренд.</p>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="marketplaces">
  <div class="nero-ai-wrap">
    <h2>Маркетплейсы, OEM и контрафакт</h2>
    <p>Площадки в Китае и для китайского покупателя предъявляют требования к подтверждению прав на бренд при жалобах и блокировках карточек. Наличие национальной регистрации упрощает переписку с площадкой и дистрибьютором, но не отменяет доказательную работу по каждому кейсу контрафакта.</p>
    <p>Если производство ведется по OEM/ODM, в договоре с заводом закрепляют запрет на регистрацию вашего обозначения третьими лицами и ответственность за нарушение. Это договорная дисциплина, а не замена регистрации в CNIPA.</p>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="timing">
  <div class="nero-ai-wrap">
    <h2>Сроки и факторы, которые на них влияют</h2>
    <p>Формальные сроки экспертизы меняются и зависят от очереди CNIPA, полноты ответов на уведомления и необходимости возражений. Мы даем ориентиры по этапам после аудита, без обещания фиксированной даты регистрации. Ускорение возможно только в рамках правил ведомства и оплачиваемых процедур, если они применимы к вашей заявке.</p>
    <p>Параллельная подача в нескольких юрисдикциях (РФ, ЕС, США, КНР) требует календаря приоритетов: иногда выгодно сначала зафиксировать дату подачи в Китае, иногда — выстроить международную заявку. Решение принимается по портфелю, а не по шаблону.</p>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="documents">
  <div class="nero-ai-wrap">
    <h2>Что подготовить до старта</h2>
    <ul class="lireate-checklist">
      <li>Изображение знака в требуемом качестве (вектор или растровый макет без потери читаемости).</li>
      <li>Перечень товаров и услуг в бизнес-формулировках — мы приведем к языку МКТУ.</li>
      <li>Данные заявителя: юридическое лицо или ИП, страна регистрации, цепочка владения брендом.</li>
      <li>Сведения о предыдущих заявках и регистрациях (РФ, Мадрид, другие страны).</li>
      <li>Доказательства использования — если знак уже присутствует на рынке (не всегда обязательно на стадии подачи, но полезно при спорах).</li>
    </ul>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="risks">
  <div class="nero-ai-wrap">
    <h2>Типовые риски без китайской регистрации</h2>
    <p>Регистрация сходного знака на китайского агента или партнера «для удобства» без прозрачного договора отчуждения. Потеря домена и соцсетей из-за споров о товарном знаке. Блокировка рекламных кабинетов и карточек товара. Сложности при таможенном оформлении брендированной продукции. Вынужденный ребрендинг после инвестиций в локализацию.</p>
    <p>Ни один из пунктов не является автоматическим следствием — но в практике трансграничной торговли такие сценарии встречаются регулярно, когда знак в КНР не занят правообладателем.</p>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="strategy">
  <div class="nero-ai-wrap">
    <h2>Стратегия: когда подавать и что делать параллельно в РФ</h2>
    <p>Компании, которые уже торгуют в Китае через дистрибьютора, часто узнают о проблеме поздно: партнёр регистрирует сходный знак на себя или требует эксклюзив, ссылаясь на «локальную охрану». Параллельная регистрация в РФ и КНР не дублирует друг друга, но позволяет выстраивать единую линию лицензирования: кто владеет знаком, кто платит роялти, кто отвечает за качество на маркетплейсе.</p>
    <p>Если бренд строится на латинице, имеет смысл заранее определить «китайское имя бренда» (иногда его выбирает рынок, а не кабинет маркетинга). Фиксация в заявке снижает риск, что через год придётся менять упаковку под уже занятое обозначение. Мы не навязываем иероглифы ради красоты макета — только если они нужны для охвата целевой аудитории и не создают правовых коллизий.</p>
    <p>Для группы компаний обсуждаем, кто выступает заявителем: материнская структура, дочернее общество в СЭЗ или контрактный производитель. Ошибка в заявителе на старте иногда исправляется передачей прав, но это дополнительные пошлины и время. На аудите проговариваем сценарий «что если партнёрство прекращается» — без драматизации, но с понятным планом B.</p>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="faq">
  <div class="nero-ai-wrap">
    <h2>FAQ</h2>
    <details class="lireate-faq"><summary>Достаточно ли международной регистрации по Мадриду?</summary><p>Зависит от стратегии и набора стран. Мадридская система охватывает Китай при соответствующем указании, но национальная процедура и сроки ответа CNIPA всё равно применяются. Мы сопоставим ваши текущие регистрации с целевым перечнем классов в КНР.</p></details>
    <details class="lireate-faq"><summary>Можно ли зарегистрировать знак на русском языке?</summary><p>Заявляют словесные, изобразительные и комбинированные обозначения; для китайского рынка часто готовят транслитерацию или иероглифический вариант. Охраноспособность оценивается по правилам КНР, а не только по восприятию в России.</p></details>
    <details class="lireate-faq"><summary>Кто может быть заявителем?</summary><p>Иностранное юридическое лицо или предприниматель при соблюдении требований к представителю и документам. Конкретная схема владения обсуждается на аудите.</p></details>
    <details class="lireate-faq"><summary>Гарантируете ли регистрацию?</summary><p>Нет. Юристы не могут гарантировать положительное решение ведомства. Мы снижаем формальные риски и готовим позицию при возражениях, но итог экспертизы определяет CNIPA.</p></details>
    <details class="lireate-faq"><summary>Сколько стоит сопровождение?</summary><p>Зависит от количества классов, вида знака, необходимости ответов на отказы и перевода. После краткого брифа даем смету этапов без скрытых доплат за согласованный объем.</p></details>
    <details class="lireate-faq"><summary>Как начать?</summary><p>Оставьте заявку в форме ниже или напишите в мессенджер — пришлите изображение знака и описание товаров. Вернемся с чек-листом и предложением по маршруту.</p></details>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal" id="contact">
  <div class="nero-ai-wrap lireate-contact-zone">
    <div>
      <h2>Заявка и мессенджеры</h2>
      <p class="nero-ai-lead">Как на современных лендингах с быстрым контактом: форма для брифа и прямые кнопки мессенджеров. Мы не обрабатываем персональные данные без согласия — отметьте чекбокс перед отправкой.</p>
      <p>Если удобнее — напишите в Telegram или WhatsApp: приложите логотип и кратко опишите товары. Юрист вернётся с чек-листом документов.</p>
      <div class="lireate-messenger-grid" id="messenger-grid">
        <a class="lireate-msg-btn lireate-msg-telegram" id="link-telegram" href="/contacts/" target="_blank" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.4 16.6 9 20.1c.6 0 .9-.3 1.2-.6l2.9-2.8 6-4.4c2.8-1.6 0-2.5-.4-2.8L5.7 15.2c-.5.3-.5.7 0 .9l3.7 1.2z"/></svg>
          Telegram
        </a>
        <a class="lireate-msg-btn lireate-msg-whatsapp" id="link-whatsapp" href="/contacts/" target="_blank" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.5 2 2 6.2 2 11.4c0 1.9.5 3.7 1.5 5.3L2 22l5.6-1.5c1.5.8 3.2 1.3 4.9 1.3 5.5 0 10-4.2 10-9.4S17.5 2 12 2zm5.8 13.5c-.2.6-1.2 1.1-1.7 1.1-.4 0-1.1.2-3.6-.9-3-1.3-5-4.4-5.1-4.6-.2-.3-1.2-1.6-1.2-3.1 0-1.5.8-2.2 1.1-2.5.3-.3.6-.4.8-.4.2 0 .4 0 .6.1.2 0 .4.2.5.5.2.5.6 1.7.7 1.8.1.1.1.3 0 .4 0 .1-.1.2-.2.3l-.3.3c-.1.1-.2.2-.1.4.1.2.5 1 1.1 1.6.8.7 1.5 1 1.7 1.1.2.1.3 0 .4-.1l.6-.7c.1-.2.3-.2.5-.1.8.3 2.8 1.3 3.2 1.5.3.2.5.3.6.4.1.2.1.7-.1 1.3z"/></svg>
          WhatsApp
        </a>
        <a class="lireate-msg-btn lireate-msg-max" id="link-max" href="/contacts/" target="_blank" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="10"/></svg>
          MAX / чат
        </a>
        <a class="lireate-msg-btn" id="link-email" href="/contacts/">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 6h16v12H4V6zm8 5.5L5.5 8.5v9h13v-9L12 11.5zM5.5 7.5 12 11l6.5-3.5H5.5z"/></svg>
          Email
        </a>
      </div>
    </div>
    <div class="lireate-form-panel">
      <form id="lireate-lead-form" novalidate>
        <label for="lf-name">Имя</label>
        <input id="lf-name" name="name" type="text" required autocomplete="name" placeholder="Как к вам обращаться">
        <label for="lf-phone">Телефон</label>
        <input id="lf-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+7 …">
        <label for="lf-email">Email</label>
        <input id="lf-email" name="email" type="email" autocomplete="email" placeholder="для ответа юриста">
        <label for="lf-company">Компания</label>
        <input id="lf-company" name="company" type="text" autocomplete="organization" placeholder="необязательно">
        <label for="lf-msg">Кратко о знаке и товарах</label>
        <textarea id="lf-msg" name="message" required placeholder="Логотип, категория товаров, планы по Китаю"></textarea>
        <label class="lireate-consent"><input type="checkbox" id="lf-consent" required> Согласен на обработку персональных данных для ответа на обращение (<a href="/privacy/">политика</a>).</label>
        <button type="submit" class="nero-ai-btn nero-ai-btn-primary" style="width:100%;margin-top:12px;">Отправить бриф</button>
        <p class="lireate-form-success" id="lf-success" role="status">Спасибо! Откроем мессенджер с готовым текстом — отправьте сообщение, чтобы заявка дошла до юриста.</p>
      </form>
    </div>
  </div>
</section>

<section class="nero-ai-section nero-ai-reveal">
  <div class="nero-ai-wrap">
    <p class="lireate-consent" style="max-width:720px;">Информация на странице не является публичной офертой и не заменяет консультацию юриста. Решения CNIPA и судов КНР зависят от обстоятельств дела. Ссылки на ГК РФ и ведомства приведены для ориентира; применимое право спора определяется отдельно.</p>
  </div>
</section>

</main>

<aside class="lireate-floating-dock" aria-label="Быстрый контакт">
  <a href="#contact" title="Форма заявки" aria-label="Форма заявки">✉</a>
  <a id="dock-telegram" href="/contacts/" title="Telegram" aria-label="Telegram">TG</a>
</aside>

<script src="assets/china-tm-hero.js"></script>
<script src="assets/lireate-reveal.js"></script>
<script>
(function(){
  var cfg = {
    telegram: document.documentElement.getAttribute('data-lireate-telegram') || '',
    whatsapp: document.documentElement.getAttribute('data-lireate-whatsapp') || '',
    max: document.documentElement.getAttribute('data-lireate-max') || ''
  };
  function setHref(id, url, fallback) {
    var el = document.getElementById(id);
    if (!el) return;
    el.href = url || fallback || '/contacts/';
  }
  setHref('link-telegram', cfg.telegram, 'https://t.me/');
  setHref('link-whatsapp', cfg.whatsapp);
  setHref('link-max', cfg.max);
  setHref('dock-telegram', cfg.telegram, 'https://t.me/');

  var form = document.getElementById('lireate-lead-form');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!form.checkValidity()) { form.reportValidity(); return; }
    var name = document.getElementById('lf-name').value.trim();
    var phone = document.getElementById('lf-phone').value.trim();
    var msg = document.getElementById('lf-msg').value.trim();
    var body = 'Заявка: регистрация ТЗ в Китае%0AИмя: ' + encodeURIComponent(name) + '%0AТел: ' + encodeURIComponent(phone) + '%0A' + encodeURIComponent(msg);
    var tg = document.getElementById('link-telegram').href;
    if (tg && tg.indexOf('t.me') !== -1) {
      window.open(tg + (tg.indexOf('?') === -1 ? '?' : '&') + 'text=' + body.replace(/%0A/g, '%0A'), '_blank', 'noopener,noreferrer');
    }
    document.getElementById('lf-success').classList.add('is-visible');
  });
})();
</script>
</body>
</html>
