INSERT INTO portfoliocase (region, city, name, time, start_date, finish_date, folder, img_exp, kind, fips_link) VALUES
('Москва', 'Москва', 'Клиент А', '6 мес.', '01.01.2024', '01.07.2024', 'client_a', 'png', 'Услуги', 'https://www.fips.ru/registers-doc-view/fips_servlet?DB=RUTM&DocNumber=700001&TypeFile=html'),
('Санкт-Петербург', 'Санкт-Петербург', 'Клиент Б', '8 мес.', '15.02.2023', '15.10.2023', 'client_b', 'png', 'Ритейл', 'https://www.fips.ru/registers-doc-view/fips_servlet?DB=RUTM&DocNumber=700002&TypeFile=html'),
('Татарстан', 'Казань', 'Клиент В', '5 мес.', '10.03.2024', '10.08.2024', 'client_c', 'png', 'Услуги', 'https://www.fips.ru/registers-doc-view/fips_servlet?DB=RUTM&DocNumber=700003&TypeFile=html'),
('Свердловская область', 'Екатеринбург', 'Клиент Г', '7 мес.', '01.05.2023', '01.12.2023', 'client_d', 'png', 'Производство', 'https://www.fips.ru/registers-doc-view/fips_servlet?DB=RUTM&DocNumber=700004&TypeFile=html'),
('Новосибирская область', 'Новосибирск', 'Клиент Д', '4 мес.', '20.06.2024', '20.10.2024', 'client_e', 'png', 'IT', 'https://www.fips.ru/registers-doc-view/fips_servlet?DB=RUTM&DocNumber=700005&TypeFile=html'),
('Краснодарский край', 'Краснодар', 'Клиент Е', '9 мес.', '05.01.2022', '05.10.2022', 'client_f', 'png', 'Ритейл', 'https://www.fips.ru/registers-doc-view/fips_servlet?DB=RUTM&DocNumber=700006&TypeFile=html');

INSERT INTO reviews (videoUrl, img_link, city, company) VALUES
('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'non_image.jpg', 'Москва', 'Клиент А'),
('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'non_image.jpg', 'Казань', 'Клиент Б'),
('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'non_image.jpg', 'Новосибирск', 'Клиент В');

INSERT INTO news (name, seo_url, full_story, date, category) VALUES
('Тестовая статья блога для CI', 'ci_blog_article_fixture',
'<p>Нейтральный текст для проверки контраста Light V4.</p><h2>Раздел</h2><p>Абзац с <a href=\"/news/\">ссылкой</a>.</p><ul><li>Пункт списка</li></ul>',
'19.10.2026', 9);