# FitBit OAuth 2.0 Provider

[![Source Code](http://img.shields.io/badge/source-jmitchell38488/oauth2--fitbit-blue.svg?style=flat-square)](https://github.com/jmitchell38488/oauth2-fitbit)
[![Latest Version](https://img.shields.io/github/release/jmitchell38488/oauth2-fitbit.svg?style=flat-square)](https://github.com/jmitchell38488/oauth2-fitbit/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/jmitchell38488/oauth2-fitbit/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-fitbit.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-fitbit)

This package makes it simple to integrate your application with the [FitBit OAuth 2.0](https://dev.fitbit.com/docs/oauth2/) service provider.

## Installation

```
composer require jmitchell38488/oauth2-fitbit
```

## Usage
The FitBit provides two different methods for authenticating with the OAuth 2.0 service, an 
[authorization grant flow](https://dev.fitbit.com/docs/oauth2/#authorization-code-grant-flow) and an 
[implicit grant flow](https://dev.fitbit.com/docs/oauth2/#implicit-grant-flow). Both require different
configuration when instantiating the provider and the implicit grant flow will require once less step.

FitBit also uses a different Authorization header than is provided by the parent library. When
a user authenticates with the FitBit 2.0 API, they need to set Authorization: Basic to generate
the access token, and provide the Authorization header with each subsequent request, however
using Bearer instead of Basic.

Included in the package are three concrete provider classes and an abstract provider
class. The abstract provider class provides shared functionality for the Authorization
and Implicit implementations. The __FitBit__ class extends the __Authorization__ 
class, so you can use that instead of the __Authorization__ class if you prefer. It is 
there for clarity when making authenticated requests. In any case, if you are supporting
either Implicit or Authorization grant flows, you will need to keep track of which
one you've used to authenticate a session, since one will timeout and you can
refresh it, whereas the other will require a user to re-authorize once it has 
timed out.

## Authorization Grant Flow
#### Authenticate session
```php
session_start();
use Healthand\OAuth2\Client\Provider\FitBitAuthorization;
require_once __DIR__ . '/vendor/autoload.php';

$provider = new FitBitAuthorization([
    'clientId'      => $my_client_id_from_fitbit,
    'clientSecret'  => $my_client_secret_from_fitbit,
    'redirectUri'   => $my_callback_url,
]);

// 1st step: Has the user authorised yet?
if (!isset($_SESSION['oauth2state'])) {
    $authorizationUrl = $provider->getAuthorizationUrl([
        'prompt' => FitBitAuthorization::PROMPT_CONSENT,
        'response_type' => FitBitAuthorization::RESPONSETYPE_CODE,
        'scope' => $provider->getAllScope(),
    ]);
    
    // Set the session state to validate in the callback
    $_SESSION['oauth2state'] = $provider->getState();
    
    header('Location: ' . $authorizationUrl);
    exit;
    
// 2nd step: User has authorised, now lets get the refresh & access tokens
} else if (isset($_GET['state']) && $_GET['state'] == $_SESSION['oauth2state'] && isset($_GET['code']) && !isset($_SESSION['fitbit']['oauth'])) {
    try {
        $token = base64_encode(sprintf('%s:%s', $my_client_id_from_fitbit, $my_client_secret_from_fitbit));
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code'  => $_GET['code'],
            'access_token' => $_GET['code'],
            'token' => $token,
        ]);
        
        unset($_SESSION['oauth2state']);
        $_SESSION['fitbit']['oauth2'] = array(
            'accessToken' => $accessToken->getToken(),
            'expires' => $accessToken->getExpires(),
            'refreshToken' => $accessToken->getRefreshToken(),
        );
    } catch (Exception $ex) {
        print $ex->getMessage();
    }

// 3rd step: Authorised, have tokens, but session needs to be refreshed
} else if (time() > $_SESSION['fitbit']['oauth2']['expires']) {
    try {
        $token = base64_encode(sprintf('%s:%s', $my_client_id_from_fitbit, $my_client_secret_from_fitbit));
        $accessToken = $provider->getAccessToken('refresh_token', [
            'grant_type'    => FitBitAuthorization::GRANTTYPE_REFRESH,
            'access_token'  => $_SESSION['fitbit']['oauth2']['accessToken'],
            'refresh_token'  => $_SESSION['fitbit']['oauth2']['refreshToken'],
            'token'         => $token,
        ]);

        unset($_SESSION['oauth2state']);
        $_SESSION['fitbit']['oauth2'] = array(
            'accessToken' => $accessToken->getToken(),
            'expires' => $accessToken->getExpires(),
            'refreshToken' => $accessToken->getRefreshToken(),
        );
    } catch (Exception $ex) {
        print $ex->getMessage();
    }
}
```
## Implicit Grant Flow
#### Authenticate session
```php
session_start();
use Healthand\OAuth2\Client\Provider\FitBitImplicit;
require_once __DIR__ . '/vendor/autoload.php';

$provider = new FitBitImplicit([
    'clientId'      => $my_client_id_from_fitbit,
    'clientSecret'  => $my_client_secret_from_fitbit,
    'redirectUri'   => $my_callback_url,
]);

// 1st step: Has the user authorised yet? Or do we need to refresh?
if (!isset($_SESSION['oauth2state'])) {
    $authorizationUrl = $provider->getAuthorizationUrl([
        'prompt' => FitBitImplicit::PROMPT_CONSENT,
        'response_type' => FitBitImplicit::RESPONSETYPE_TOKEN,
        'scope' => $provider->getAllScope(),
        'expires_in' => FitBitImplicit::EXPIRES_IN_DAY // This can be set to 1, 7 or 30 days
    ]);
    
    // Set the session state to validate in the callback
    $_SESSION['oauth2state'] = $provider->getState();
    
    header('Location: ' . $authorizationUrl);
    exit;
    
// 2nd step: User has authorised, now lets get the refresh & access tokens
// The return URL uses fragments, so you will need to implement front-end logic to redirect the 
// user back to the server with the relevant information, since the URL will look like:
// my_callback_uri#scope=nutrition+weight+location+social+heartrate+settings+sleep+activity+profile&state=abcdef1234567890&user_id=ABC123&token_type=Bearer&expires_in=86400&access_token=abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz1234567890
} else if (isset($_GET['state']) && $_GET['state'] == $_SESSION['oauth2state'] && isset($_GET['access_token']) && !isset($_SESSION['fitbit']['oauth'])) {
    unset($_SESSION['oauth2state']);
    $_SESSION['fitbit']['oauth2'] = array(
        'accessToken' => $_GET['access_token'],
        'expires' => $_GET['expires_in'],
        'refreshToken' => null,
    );
} 
```

## Making requests
The API endpoints can be found in either the [official API docs](https://dev.fitbit.com/docs)
or the [API explorer](https://apigee.com/me3/embed/console/fitbit?apig_cc=1).

### To make a request
```php

$endpoint = $provider->getBaseApiUrl() . "user/-/profile." . FitBit::FORMAT_JSON;
$provider = new FitBit([
    'clientId'      => $my_client_id_from_fitbit,
    'clientSecret'  => $my_client_secret_from_fitbit,
    'redirectUri'   => $my_callback_url,
]);

$request = $provider->getAuthenticatedRequest(
    FitBit::METHOD_GET,
    $endpoint,
    $_SESSION['fitbit']['oauth2']['accessToken']
);

$response = $provider->getResponse($request);
```