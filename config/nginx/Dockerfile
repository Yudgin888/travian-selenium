FROM nginx:1.21.4

RUN rm -fr /etc/nginx/conf.d/default.conf
ADD ./conf.d /etc/nginx/conf.d
ADD ./nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www
