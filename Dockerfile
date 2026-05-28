FROM php:8.2-apache

# 1. Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libmpdec-dev \
    && rm -rf /var/list/apt/lists/*

# 2. INSTALAR Y ACTIVAR LAS EXTENSIONES DE PDO Y MYSQL (Esto es lo que te falta)
RUN docker-php-ext-install pdo pdo_mysql

# 3. Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# 4. Copiar los archivos del proyecto al contenedor
COPY . /var/www/html/

# 5. Dar permisos correctos a Apache
RUN chown -R www-data:www-data /var/www/html

# 6. Configurar el entorno virtual de Python para la IA
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip install --no-cache-dir groq pypdf

# 7. Exponer el puerto de Apache
EXPOSE 80
