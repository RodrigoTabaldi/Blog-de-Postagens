FROM php:8.4-cli

# O mbstring depende da biblioteca Oniguruma; o PDO MySQL usa o driver do PHP.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mbstring pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/blog
COPY app/ app/
COPY public/ public/
# A imagem usa configuração própria, sem copiar credenciais locais.
COPY config/docker.php config/database.php

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
