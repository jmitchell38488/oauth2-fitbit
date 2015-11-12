<?php
/**
 * This file is part of the jmitchell38488/oauth2-fitbit library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Justin Mitchell <jmitchell38488@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://packagist.org/packages/Jmitchell38488/oauth2-fitbit Packagist
 * @link https://github.com/jmitchell38488/oauth2-fitbit GitHub
 */

namespace Jmitchell38488\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * Represents a FitBit OAuth 2.0 service provider. FitBit provides two different
 * grant flows for creating sessions, an authorization grant flow that is
 * intended to be used by servers, and an implicit grant flow that is intended
 * to be used by browsers. Both implementations extend this class.
 */
class AbstractFitBit extends AbstractProvider
{
    /**
     * No user prompt on authorization
     */
    const PROMPT_NONE = 'none';
    
    /**
     * The user must login to authorize the request
     */
    const PROMPT_LOGIN = 'login';
    
    /**
     * If the user is already logged in, they only have to consent, otherwise
     * they login and consent
     */
    const PROMPT_CONSENT = 'consent';
    
    /**
     * Required to use the Authorization grant flow
     */
    const RESPONSETYPE_CODE = 'code';
    
    /**
     * Required to use the Implicit grant flow
     */
    const RESPONSETYPE_TOKEN = 'token';
    
    /**
     * Uses the RefreshToken Grant Provider
     */
    const GRANTTYPE_REFRESH = 'refresh_token';
    
    /**
     * Uses the AuthorizationCode Grant Provider
     */
    const GRANTTYPE_AUTH = 'authorization_code';
    
    /**
     * Return in JSON format
     */
    const FORMAT_JSON = 'json';
    
    /**
     * Return in XML format
     */
    const FORMAT_XML = 'xml';
    
    /**
     * For semantics sake in implementing applications that can use the implicit
     * grant flow
     */
    const GRANTFLOW_IMPLICIT = 'implicit';
    
    /**
     * For semantics sake in implementing applications that can use the authorization
     * grant flow
     */
    const GRANTFLOW_AUTHORIZATION = 'authorization';
    
    /**
     * For use in implicit grant flow, authorization expires in 24 hours
     */
    const EXPIRES_IN_DAY = 86400;
    
    /**
     * For use in implicit grant flow, authorization expires in 7 da7s
     */
    const EXPIRES_IN_WEEK = 604800;
    
    /**
     * For use in implicit grant flow, authorization expires in 30 days
     */
    const EXPIRES_IN_MONTH = 2592000;
    
    
    /**
     * A list of required scopes to be provided to FitBit API. This is based on
     * official FitBit developer documents, accessed 13 October 2015.
     * @var array
     */
    protected $defaultScopes = array(
        'activity', 'nutrition', 'profile', 'settings', 'sleep', 'social', 'weight'
    );
    
    /**
     * A list of all scopes available in the FitBit API. This is based on
     * official FitBit developer documents, accessed 13 October 2015.
     * @var array
     */
    protected $allScopes = array(
        'activity', 'heartrate', 'location', 'nutrition', 'profile', 'settings', 
        'sleep', 'social', 'weight'
    );
    
    /**
     * The base URI for FitBit to base all requests from
     * @var string
     */
    protected $urlApiBase;
    
    /**
     * The base URI for the FitBit API Authorization request endpoint
     * @var string
     */
    protected $urlAuthorize;

    /**
     * The base URI for the FitBit API Access Token request endpoint
     * @var string
     */
    protected $urlAccessToken;

    /**
     * The base URI for the FitBit API Resource owner request endpoint, eg. /user/-/profile
     * @var string
     */
    protected $urlResourceOwnerDetails;

    /**
     * @inheritdoc
     */
    protected $accessTokenMethod;

    /**
     * @inheritdoc
     */
    protected $accessTokenResourceOwnerId;

    /**
     * An array of strings representing the selected scopes that you will request
     * authorization to access
     * @var array[string]
     */
    protected $scopes = null;

    /**
     * The scope separator string
     * @var string
     */
    protected $scopeSeparator;

    /**
     * @inheritdoc
     */
    protected $responseError = 'error';

    /**
     * @inheritdoc
     */
    protected $responseCode;

    /**
     * @inheritdoc
     */
    protected $responseResourceOwnerId = 'id';

    /**
     * Constructor for the Abstract FitBit class. The constructor will set the 
     * request URIs that the application will interact with, so that the
     * implementor doesn't need to specify URLs on request.
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to
     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, `httpClient`, and `randomFactory`.
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {   
        $options = array_merge($options, array(
            'urlApiBase'                => 'https://api.fitbit.com/1/',
            'urlAuthorize'              => 'https://www.fitbit.com/oauth2/authorize',
            'urlAccessToken'            => 'https://api.fitbit.com/oauth2/token',
            'urlResourceOwnerDetails'   => 'https://api.fitbit.com/1/users/-/profile.json',
        ));
        
        parent::__construct($options, $collaborators);
    }

    /**
     * @inheritdoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data[$this->responseError])) {
            $error = $data[$this->responseError];
            $code  = $this->responseCode ? $data[$this->responseCode] : 0;
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    /**
     * @inheritdoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $this->responseResourceOwnerId);
    }

    /**
     * @inheritdoc
     */
    public function getBaseApiUrl()
    {
        return $this->urlApiBase;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->urlAccessToken;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->urlAuthorize;
    }

    /**
     * @inheritdoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->urlResourceOwnerDetails;
    }

    /**
     * @inheritdoc
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultScopes() 
    {
        return $this->defaultScopes;
    }
    
    public function getRequiredScope() 
    {
        return $this->defaultScopes;
    }
    
    public function getAllScope() 
    {
        return $this->allScopes;
    }

    /**
     * Returns a prepared request for requesting an access token. In the case of
     * FitBit, we want to send an authenticated request to the API because we
     * need to send an <b>Authorization</b> header. The default method used an
     * unauthenticated request to get the access token.
     *
     * @param array $params Query string parameters
     */
    protected function getAccessTokenRequest(array $params, $token = null)
    {
        $method  = $this->getAccessTokenMethod();
        $url     = $this->getAccessTokenUrl($params);
        $options = $this->getAccessTokenOptions($params);

        return $this->getAuthenticatedRequest($method, $url, $token, $options);
    }

    /**
     * Requests an access token using a specified grant and option set. For FitBit,
     * We're going to pass the response code from the initial authorization
     * request to get the access token. By default this isn't supported because
     * the library makes an unauthenticated request. To make the authenticated
     * request, we need to fetch the code from the options array and pass it
     * to the <em>getAccessTokenRequest</em> method.
     *
     * @param  mixed $grant
     * @param  array $options
     * @return AccessToken
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
        ];
        
        $token = null;
        if (!empty($options['token'])) {
            $token = $options['token'];
            unset($options['token']);
        }

        $params   = $grant->prepareRequestParameters($params, $options);
        $request  = $this->getAccessTokenRequest($params, $token);
        $response = $this->getResponse($request);
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $grant);

        return $token;
    }
    
}