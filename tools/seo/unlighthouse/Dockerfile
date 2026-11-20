FROM node:24-bookworm-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends chromium ca-certificates fonts-liberation fonts-noto-core \
    && rm -rf /var/lib/apt/lists/*

ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium \
    PUPPETEER_SKIP_DOWNLOAD=true \
    SEO_CHROME_NO_SANDBOX=1

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --omit=dev && npm cache clean --force
COPY unlighthouse.config.mjs unlighthouse.smoke.config.mjs unlighthouse.ci.config.mjs ./

CMD ["npm", "run", "audit"]
