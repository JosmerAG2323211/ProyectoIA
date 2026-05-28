FROM php:8.2-apache

# Instalar extensiones de PHP necesarias para MySQL y utilidades del sistema
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libjpeg-dev \
    && docker-php-ext-install pdo pdo_mysql

# Habilitar el módulo de reescritura de Apache
RUN a2enmod rewrite

# Configurar el directorio de trabajo en el servidor
COPY . /var/www/html/

# Asegurar permisos correctos para que Apache pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html

# Instalar las librerías de Python indispensables para tu IA
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip install --no-cache-dir groq pypdf

# Exponer el puerto por defecto de Render
EXPOSE 80
