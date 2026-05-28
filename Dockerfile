FROM php:8.2-apache

# 1. Instalar utilidades del sistema esenciales (sin el paquete roto)
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar y activar las extensiones de PDO y MySQL para que guarde tus equipos
RUN docker-php-ext-install pdo pdo_mysql

# 3. Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# 4. Copiar los archivos del proyecto al servidor
COPY . /var/www/html/

# 5. CORRECCIÓN DEL FORBIDDEN: Asegurar permisos de lectura (755) y dueño (www-data)
RUN chmod -R 755 /var/www/html && chown -R www-data:www-data /var/www/html

# Instalar las librerías de Python indispensables para tu IA (Groq y PyPDF)
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip install --no-cache-dir groq pypdf

# 🚀 AGREGA ESTAS LÍNEAS AQUÍ PARA SUBIR EL LÍMITE DE ARCHIVOS A 100 MEGABYTES
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Exponer el puerto por defecto de Render
EXPOSE 80
