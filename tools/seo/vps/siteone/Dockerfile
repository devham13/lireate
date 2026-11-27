FROM ubuntu:24.04
RUN apt-get update -qq \
  && DEBIAN_FRONTEND=noninteractive apt-get install -y -qq --no-install-recommends ca-certificates curl \
  && curl -1sLf 'https://dl.cloudsmith.io/public/janreges/siteone-crawler/setup.deb.sh' | bash \
  && apt-get install -y -qq siteone-crawler \
  && rm -rf /var/lib/apt/lists/*
WORKDIR /work
ENTRYPOINT ["siteone-crawler"]