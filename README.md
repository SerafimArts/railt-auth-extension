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

```graphql
type Example {
    field: Result! @auth 
        # This field is only available to an authenticated user
}
``` 

### Guest

```graphql
type Example {
    field: Result! @guest 
        # This field is only available to a guest (non-authenticated user)
}
``` 

### Authorization

```graphql
type Example {
    field: Result! @can(role: "some") 
        # 1. This field is only available to an authenticated user
        # 2. Authenticated user should pass the "some" authorization gate
}
```
