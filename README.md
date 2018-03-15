# Railt Authorization

Railt framework authorization system for Laravel framework.

## Installation

Your application should use the [Laravel Provider](https://github.com/railt/laravel-provider)

- `composer require serafim/railt-authorization`
- Add into `config/railt.php`:
```php
    ...
    'extensions' => [
        \Serafim\RailtAuthorization\AuthorizationExtension::class, 
    ]
```

## Usage

### Authenticated

The field that contains the directive `@auth` is only available to an authenticated user.

```graphql
type Example {
    field: Result! @auth
}
``` 

### Guest

The field that contains the directive `@guest` is only available to a guest (non-authenticated user).

```graphql
type Example {
    field: Result! @guest
}
``` 

### Authorization

The field that contains the directive `@can(role: String!)`:
1. Is only available to an authenticated user.
2. Should pass the authorization gate defined in `role` argument.

```graphql
type Example {
    field: Result! @can(role: "some")
}
```
