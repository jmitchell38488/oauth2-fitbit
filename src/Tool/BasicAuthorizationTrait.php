<?php

namespace Healthand\OAuth2\Client\Tool;

/**
 * Enables `Basic` header authorization for providers.
 */
trait BasicAuthorizationTrait
{
    /**
     * Returns authorization headers for the 'basic' grant.
     *
     * @param  mixed|null $token Either a string or an access token instance
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return ['Authorization' => 'Basic ' . $token];
    }
}
