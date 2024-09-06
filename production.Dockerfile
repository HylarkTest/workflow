FROM laravelphp/vapor:php80

RUN apk --update add imagemagick

RUN docker-php-ext-install imagick

COPY . /var/task
