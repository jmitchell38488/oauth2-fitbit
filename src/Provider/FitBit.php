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

use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

/**
 * <p>Represents a FitBit OAuth 2.0 service provider.</p>
 * <p>This extends from the
 * <b>FitBitAuthorization</b> class to uses the authorization grant flow to 
 * make requests with the API. You can use either this class, or the 
 * <b>FitBitAuthorization</b> class to make authorization grant flow requests.</p>
 * 
 * <p>This class is inteded to be used after authorization is made, because it
 * uses the <em>Bearer</em> token that's returned from the API authorization, rather
 * than the <em>Basic</em> token that's used as part of the authorization.</p>
 * 
 * @link https://dev.fitbit.com/docs/oauth2/ FitBit API Docs
 * @author Justin Mitchell <jmitchell38488@gmail.com>
 * @since 0.1
 * @package Jmitchell38488\OAuth2\Client\Provider
 */
class FitBit extends FitBitAuthorization
{
    use BearerAuthorizationTrait;

    /**
     * @inheritdoc
     */
    public function __construct(array $options = [], array $collaborators = [])
    {   
        parent::__construct($options, $collaborators);
    }

}