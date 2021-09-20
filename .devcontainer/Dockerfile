FROM node:14-alpine
RUN apk update && apk add \
    git \
    yarn \
    less \
    curl \
    gnupg \ 
    php-json \
    php-pdo \
    php-zip \
    php-pdo_mysql \
    php

WORKDIR /app

ENV PATH /app/node_modules/.bin:$PATH

RUN mkdir -p /app/node_modules

COPY . ./
