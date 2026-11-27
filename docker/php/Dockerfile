FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configure & install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

# Get Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js (v22 LTS) & pnpm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g pnpm \
    && rm -rf /var/lib/apt/lists/*

# Create system user to match host UID/GID for local development
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} ugmuser && \
    useradd -u ${UID} -ms /bin/bash -g ugmuser ugmuser

WORKDIR /var/www/html

USER ugmuser

EXPOSE 9000
CMD ["php-fpm"]
