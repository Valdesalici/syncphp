FROM php:8.2-cli

WORKDIR /var/www/html

COPY . .

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]

