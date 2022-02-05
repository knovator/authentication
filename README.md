
This package used for user authentication with using secure laravel passport which provides a full OAuth2 server implementation for your Laravel application in a matter of minutes. [https://laravel.com/docs/5.7/passport]


Migration Added:

- Users
- Roles
- Users Roles (Pivot Table)
- Permissions
- Permissions Roles (Pivot Table)

Features:
- Handle user role with permissions based Authentication

Require the knovator/authentication package in your composer.json and update your dependencies:

You want to need add authentication repository in your composer.json file.

```"repositories": [
          {
              "type": "vcs",
              "url": "git@github.com:knovator/authentication.git"
          }
      ],
```

You want to need add ```multiple_column```  in ```config/auth.php``` User Providers sections

This package included 
```laravel/passport``` and
```prettus/l5-repository``` packages.
```
    composer require knovator/authentication
 ```

In your ```config/app.php``` add ```Knovator\Authentication\AuthServiceProvider::class``` to the end of the providers array:

Publish Configuration:

```php artisan vendor:publish --provider "Knovator\Authentication\AuthServiceProvider"```



website : [https://github.com/knovator/authentication ]
