
FROM php:8.3-apache

# Instalar Python y las extensiones necesarias para MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    python3-venv \
    && docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Preparar Python y sus bibliotecas
RUN python3 -m venv /opt/venv

ENV PATH="/opt/venv/bin:$PATH"

WORKDIR /var/www/html

# Copiar el proyecto
COPY . /var/www/html/

# Instalar dependencias de Python
RUN pip install --no-cache-dir --upgrade pip \
    && pip install --no-cache-dir -r requirements.txt

# Permisos para Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

# Permitir que Apache escuche el puerto asignado por el alojamiento
CMD ["bash", "-lc", "if [ -n \"$PORT\" ]; then sed -ri \"s/^Listen 80$/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -ri \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf; fi; apache2-foreground"]
