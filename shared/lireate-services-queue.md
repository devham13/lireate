# Очередь страниц услуг LIREATE (перенесена)

Каноническая очередь для automation **service-pages**:

**[`pages/services/queue.md`](../pages/services/queue.md)**

Следующая услуга:

```bash
python3 scripts/lireate_service_queue.py
```

Статусы в `queue.md`:

- `new` — взять в работу automation;
- `done` — страница в репозитории, PR влит в `main`;
- `published` — slug в `~/lireate-autopub/state/services-published.txt` на хостинге (CRON, не Cloud).
