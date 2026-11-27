FROM node:22-bookworm-slim
WORKDIR /app
COPY skills/indexlift-seo-auditor/package.json ./
RUN npm install --omit=dev --no-fund --no-audit
COPY skills/indexlift-seo-auditor/scripts ./scripts
COPY skills/indexlift-seo-auditor/SKILL.md ./
ENTRYPOINT ["node", "scripts/run-audit.js"]