# Geolocation API

Symfony test project: web API to return geolocation data based on IP.


- According to the http methods in a REST API, the method to obtain the geolocation of an IP is GET, thus the route looks like this: /api/geolocation/:ip


TODO:
- Comments
- Beautify main entry point




php bin/console doctrine:migration:migrate
php bin/console doctrine:fixtures:load --group=UserFixtures --append
