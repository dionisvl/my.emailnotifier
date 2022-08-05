FROM nginx:1.21.3-alpine

# подменим дефолтный default.conf на наш кастомный
ADD ./default.conf /etc/nginx/conf.d/default.conf

# создадим папки в дефолтовой структуре nginx, если их нет.
RUN mkdir -p /etc/nginx/sites-available
RUN mkdir -p /etc/nginx/sites-enabled

# прокидываем конфиг
ADD emailnotifier.conf /etc/nginx/sites-available/emailnotifier.conf
RUN ln -s /etc/nginx/sites-available/emailnotifier.conf /etc/nginx/sites-enabled/

WORKDIR /var/www