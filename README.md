## Laravel API Boilerplate

[Laravel documentation](https://laravel.com/docs/8.x)
[Laravel API](https://laravel.com/api/8.x/)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Local Development
Start Local Server
```
php artisan serve
```

Run Migrations
```
php artisan migrate
```

ReSeed Database
```
php artisan migrate:refresh --seed --env=local
```

## Included Packages
Laravel Eloquent Relationships
*This package adds some missing relationships to Eloquent in Laravel: BelongsToOne and MorphToOne*
[GitHub](https://github.com/ankurk91/laravel-eloquent-relationships)

Laravel Cashier
*Laravel Cashier provides an expressive, fluent interface to Stripe's subscription billing services*
[Docs](https://laravel.com/docs/8.x/billing)

Passport
*Laravel Passport provides a full OAuth2 server implementation for your Laravel application*
[Docs](https://laravel.com/docs/8.x/passport)

Socialite
* Laravel also provides a simple, convenient way to authenticate with OAuth providers using Laravel Socialite. Socialite currently supports authentication with Facebook, Twitter, LinkedIn, Google, GitHub, GitLab, and Bitbucket*
*Larabel Boilderplate will support FaceBook, Google, LinkedIn. Note on twitter exclusion: Stateless authentication is not available for the Twitter driver, which uses OAuth 1.0 for authentication.*
[Docs](https://laravel.com/docs/8.x/socialite)

Laravel Phone
*Adds phone number validation functionality to Laravel based on the PHP port of Google's libphonenumber API by giggsey.*
[GitHub](https://github.com/Propaganistas/Laravel-Phone)

Spatie Query Builder
*This package allows you to filter, sort and include eloquent relations based on a request. The QueryBuilder used in this package extends Laravel's default Eloquent builder. This means all your favorite methods and macros are still available. Query parameter names follow the JSON API specification as closely as possible.*
[GitHub](https://github.com/spatie/laravel-query-builder)
[Docs](https://spatie.be/docs/laravel-query-builder/v3/introduction)

Spatie Eloquent Sortable
*This package provides a trait that adds sortable behaviour to an Eloquent model.*
[GitHub](https://github.com/spatie/eloquent-sortable)

Spatie Laravel JSON API Paginate
*A paginator that plays nice with the JSON API spec*
[GitHub](https://github.com/spatie/laravel-json-api-paginate)

Laravel Vapor
*Laravel Vapor is an auto-scaling, serverless deployment platform for Laravel, powered by AWS Lambda. Manage your Laravel infrastructure on Vapor and fall in love with the scalability and simplicity of serverless.*
[Website](https://vapor.laravel.com/)
[Docs](https://docs.vapor.build/1.0/introduction.html#introduction)
