<?php declare(strict_types=1);
/**
 * Copyright 2024 SURF B.V.
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

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

/**
 * Represents an IdentityProvider that EngineBlock proxies
 *
 * IdP metadata is rendered for these IdPs in EngineBlocks IdPs metadata document.
 */
class RealProxiedIdentityProvider extends AbstractIdentityProvider
{
}
