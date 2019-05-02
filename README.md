# New Developer Setup
---

## PHP Composer

1. `composer install`

## Docker setup

1. `docker build -t chwd-backend .`
   1. This builds a Docker image you can use to create containers
2. `docker run -d -p 8080:80 -v {path_to_your_project_directory}:/var/www/html --name chwd-backend-ctnr chwd-backend`
   1. `8080` is the local machine port. Change this if you want to use somethign different.
   2. `chwd-backend-ctnr` is the name of the new Docker container name. This can be changed to whatever you would like.

## Generatating API Documentation

1. https://laravel-apidoc-generator.readthedocs.io/en/latest/
2. run `php artisan apidoc:generate` in the root folder
3. NOTE: When this is run, it will reset the any changes we have manually made to the Info section