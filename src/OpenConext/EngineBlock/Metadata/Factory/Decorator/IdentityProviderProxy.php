<?php declare(strict_types=1);
/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;

use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

/**
 * This decoration is used to represent Idp's EngineBlock in it's IdP role when EngineBlock is used as authentication
 * proxy. It will make sure all required parameters to support EB are set.
 */
class IdentityProviderProxy extends AbstractIdentityProvider
{
    /**
     * @var X509KeyPair
     */
    private $keyPair;
    /**
     * @var UrlProvider
     */
    private $urlProvider;

    public function __construct(
        IdentityProviderEntityInterface $entity,
        X509KeyPair $keyPair,
        UrlProvider $urlProvider
    ) {
        parent::__construct($entity);

        $this->keyPair = $keyPair;
        $this->urlProvider = $urlProvider;
    }

    public function getCertificates(): array
    {
        return [
            $this->keyPair->getCertificate(),
        ];
    }

    public function getSupportedNameIdFormats(): array
    {
        return [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];
    }

    public function getSingleLogoutService(): ?Service
    {
        if (is_null($this->entity->getSingleLogoutService())) {
            return null;
        }

        $sloLocation = $this->urlProvider->getUrl('authentication_logout', false, null, null);
        return new Service($sloLocation, Constants::BINDING_HTTP_REDIRECT);
    }

    /**
     * When the service is requested for an entity other then EB we should replace service locations and bindings with those of EB
     * - if the entity is not EB we should add the entityId so EB could determine the IdP we are acting for.
     *
     * @return Service[]
     */
    public function getSingleSignOnServices(): array
    {
        if (!$this->isEngineBlock()) {
            $ssoLocation = $this->urlProvider->getUrl('authentication_idp_sso', false, null, $this->getEntityId());
            return [new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT)];
        }

        $ssoLocation = $this->urlProvider->getUrl('authentication_idp_sso', false, null, null);
        return [new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT)];
    }

    public function isEngineBlock(): bool
    {
        $url = $this->urlProvider->getUrl('metadata_idp', false, null, null);
        return $this->getEntityId() === $url;
    }
}
