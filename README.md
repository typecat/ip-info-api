# IP Information API

Symfony test project for a web API to return data based on IP.
As a showcase, this prototype retrieves a simple geolocation information
by consuming an external service.

## The projects architecture

### The base

This prototype was kick-started with the symfony skeleton and thus follows the typical 
Symfony project structure. The docker setup  is based on the [Symfony Docker installer](https://github.com/dunglas/symfony-docker)
recommended in the Symfony installation guide. Other necessary symfony packages,
like the orm bundle for database integration, were used in addition.

To retrieve the geolocation data of an ip, the package [hostbrook/sypex-geo](https://packagist.org/packages/hostbrook/sypex-geo)
was used in this prototype, simply because it offers a free version with no registration required.
Of course, in a real project a more professional package, like [IPinfo.io](https://packagist.org/packages/ipinfo/ipinfo)
should be considered.

### API architecture

The API is built according to the RESTful approach, and uses following routes:

- `/api` → index action providing the API description. (Currently just plain text. In a real project,
a more readable and descriptive method, like [Swagger UI](https://swagger.io/tools/swagger-ui/), should be used instead.)
- `/api/geolocation` → GET request expecting an IP as request body (`{"ip": "x.x.x.x"}`) to obtain its geolocation

The API can throw following responses:
- `200 / HTTP_OK`
- `400 / HTTP_NOT_FOUND` (also in case of an unknown route or an IP not matching the required structure)
- `401 / HTTP_UNAUTHORIZED`
- `429 / HTTP_TOO_MANY_REQUESTS`

The requests are handled by the [\App\Controller\IpInfoController](src%2FController%2FIpInfoController.php). The validation of the provided IP is implemented 
with the help of an [IpRequestObject](App\Entity\IpRequestObject) using property validation constraints. The
[\App\Service\IpInfoService](src%2FService%2FIpInfoService.php) takes care of the validation process, so the controller only need to throw different
exceptions for different request issues.

The controller uses the [\App\Service\IpInfoService](src%2FService%2FIpInfoService.php) to fetch the necessary
information, which is returned as a DTO. For various cases, various DTOs can be used or overridden. The most
important functions (like `toArray()`) are secured by the [\App\Model\IpDataInterface](src%2FModel%2FIpDataInterface.php)
each of the DTOs is supposed to implement.
Because this is just a prototype, no more inheritance of abstraction was introduced on this level. For a real project, or
to provide this API as a public standalone package, a deeper abstraction could be introduced, so that parts (e.g.
the geolocation package) could be easily replaced or extended. For instance, a converter with a converter interface
could be used to transform the response data of the external service to the DTO.

To ensure that error responses do not expose sensitive information
the [App\EventListener\ExceptionListener](src%2FEventListener%2FExceptionListener.php) was introduced, which only
shows the exception trace in debug mode.


### Security

To showcase a security strategy a token based authentication method was used in this prototype.
But, it already features a user management (entity and repository), which can be extended with a registration function and
a more complex authentication process (login, acquire token, use token for a specific time).
Therefore, the [\App\Entity\User](src%2FEntity%2FUser.php) already has the following fields:

- username
- password
- roles

See [Outlook/User management](#outlook-user-management) for suggestions on advanced user management.

The token based authentication is handled by storing the user-based token in the `token` field of the user record,
and providing it in the request via the `X-Auth-Token` header. The [\App\Security\TokenAuthenticator](src%2FSecurity%2FTokenAuthenticator.php)
takes care of it, so the [\App\Controller\IpInfoController](src%2FController%2FIpInfoController.php) only needs
to define the `#[IsGranted("IS_AUTHENTICATED")]` annotation at the actions to be secured.

Additionally, the [\App\EventSubscriber\RateLimiterSubscriber](src%2FEventSubscriber%2FRateLimiterSubscriber.php)
was introduced to limit the API requests within a specific time frame. With a login option, additional security measures, 
like login throttling, should be considered.

### Testing

For testing of the API the testing framework PHPUnit is used. The tests currently consider the API requests 
and the user entity object. To properly test the user authentication the [\App\Factory\UserFactory](src%2FFactory%2FUserFactory.php)
and the [\App\DataFixtures\UserFixtures](src%2FDataFixtures%2FUserFixtures.php) were used to generate a fake user 
record in the database (see setup section for usage).

## Outlook

### User management

<a id="outlook-user-management"></a>

A user management scenario with a registration and login could look like this:

1. User registers (either via a special API endpoint, like `/api/register` or via a form on a website that uses it).
2. User uses an API endpoint, e.g. `/api/login` to login.
3. A token is generated for the user. The token expires after a certain period of time or on logout.
4. The user receives the token in the response to the login action, and can now authenticate to all secured routes without providing the login information each time.

For this scenario the user entity needs to be extended and the database record could look like this:

| Column        | Type                   | Nullable  |
|---------------|------------------------|-----------|
| id            | int(10) unsigned       | not null  |
| username      | character varying(180) | not null  |
| password      | character varying(255) | not null  |
| roles         | json                   | not null  |
| token         | character varying(36)  | not null  |
| token_expires | int(10) unsigned       | not null  |
| created       | int(10) unsigned       | not null  |
| modified      | int(10) unsigned       | not null  |
| last_login    | int(10) unsigned       | not null  |

The `App\Controller\SecurityController` would be responsible for the registration and login management.
The interaction with user related repositories (user repository, user role or group repository) would be
provided by the `App\Service\UserService`, so that eventual business logic is kept out of the controller.
In addition, corresponding firewall settings would need to be added in the security package configuration.

### Speed

The average speed of a test request via PHPStorm HTTP client is < 50 ms. However, in production the speed 
depends on several aspects:
- external service(s) used
- server performance
- caching

Caching is key to a good performance. Simply said, everything that does not need to be "live" data should be cached for
an appropriate period of time, that depends on the type of data. There are different cache strategies that can
be applied to different scenarios, e.g.:

- clientside caching using cache headers
- custom serverside caching in database tables or file system (e.g. provided by frameworks or CMS)
- special caching software (e.g. Redis)

Choosing the right cache mechanism depends on the requirements to a software and the nature of its purpose. 
The requirements to this prototype suggest following cache decisions:

1. Cache on the server side to limit requests to the external service (which can has limits itself), and also to compensate a potential performance issue with the external service.
2. When caching, separate user data from data of the requested IP. (Different users might request the same information.)
3. Use a custom cache tag for the IP data, to be able to flush it separately, and to avoid its flushing with other caches (except when flushing all). To start with, Symfony's native caching system can be used for that.
4. Use load balancing to prevent overload of the application and/or the server.

Also, special caching software (e.g. Redis) could be taken into account.

## Setup

**Hint**: If running apache, stop the apache service, or change the ports in the docker compose file.

1. Build docker images: `docker compose build --no-cache` 
2. Start application: `docker compose up --pull -d`
3. Create the database: `docker exec ip-info-api-web php bin/console doctrine:database:create`
4. Apply migrations: `docker exec ip-info-api-web php bin/console doctrine:migrations:migrate`
5. Create dummy records: `docker exec ip-info-api-web php bin/console doctrine:fixtures:load --append`
6. (Optional) Run tests: `docker exec ip-info-api-web php bin/phpunit`
