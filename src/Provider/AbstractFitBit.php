<?php

namespace Healthand\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * Represents a FitBit request service provider that's based on the authorization
 * grant flow and may be used to interact with the OAuth 2.0 service provider, 
 * using Bearer token authentication
 */
class AbstractFitBit extends AbstractProvider
{
    const PROMPT_NONE        = 'none';
    const PROMPT_LOGIN       = 'login';
    const PROMPT_CONSENT     = 'consent';
    
    const RESPONSETYPE_CODE  = 'code';
    const RESPONSETYPE_TOKEN = 'token';
    
    const GRANTTYPE_REFRESH  = 'refresh_token';
    const GRANTTYPE_AUTH     = 'authorization_code';
    
    const FORMAT_JSON        = 'json';
    const FORMAT_XML         = 'xml';
    
    const EXPIRES_IN_DAY     = 60*60*24;
    const EXPIRES_IN_WEEK    = 60*60*24*7;
    const EXPIRES_IN_MONTH   = 60*60*24*30;
    
    
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
     * @var string
     */
    protected $urlAuthorize;

    /**
     * @var string
     */
    protected $urlAccessToken;

    /**
     * @var string
     */
    protected $urlResourceOwnerDetails;

    /**
     * @var string
     */
    protected $accessTokenMethod;

    /**
     * @var string
     */
    protected $accessTokenResourceOwnerId;

    /**
     * @var array|null
     */
    protected $scopes = null;

    /**
     * @var string
     */
    protected $scopeSeparator;

    /**
     * @var string
     */
    protected $responseError = 'error';

    /**
     * @var string
     */
    protected $responseCode;

    /**
     * @var string
     */
    protected $responseResourceOwnerId = 'id';

    /**
     * @param array $options
     * @param array $collaborators
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
     * @inheritdoc
     */
    protected function getAccessTokenRequest(array $params, $token = null)
    {
        $method  = $this->getAccessTokenMethod();
        $url     = $this->getAccessTokenUrl($params);
        $options = $this->getAccessTokenOptions($params);

        return $this->getAuthenticatedRequest($method, $url, $token, $options);
    }

    /**
     * @inheritdoc
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