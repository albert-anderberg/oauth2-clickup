# ClickUpProvider for OAuth 2.0 Client


This package provides ClickUp OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require albert-anderberg/oauth2-clickup
```

## Usage

Usage is the same as The League's OAuth client, using `\Albertanderberg\OAuth2\Client\Provider\ClickUp` as the provider.

### Authorization Code Flow

```php
$provider = new Albertanderberg\OAuth2\Client\Provider\ClickUp([
    'clientId'          => '{clickup-client-id}',
    'clientSecret'      => '{clickup-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getId());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Refreshing a Token
ClickUp's OAuth implementation does not use refresh tokens. Access tokens are valid until a user revokes access manually.

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/albert-anderberg/oauth2-clickup/blob/master/LICENSE) for more information.
