<?php

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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProviderFactory;

final class FunctionalTestingStepupGatewayMockConfiguration
{
    /**
     * @var MockIdentityProviderFactory
     */
    private $mockIdentityProviderFactory;

    /**
     * @var MockServiceProviderFactory
     */
    private $mockServiceProviderFactory;

    /**
     * @var MockIdentityProvider
     */
    private $mockIdentityProvider;

    /**
     * @var MockServiceProvider
     */
    private $mockServiceProvider;

    public function __construct(
        MockIdentityProviderFactory $mockIdentityProviderFactory,
        MockServiceProviderFactory $mockServiceProviderFactory,
        \EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
    ) {
        $this->mockIdentityProviderFactory = $mockIdentityProviderFactory;
        $this->mockServiceProviderFactory = $mockServiceProviderFactory;

        $keysConfig = $engineBlockApplicationSingleton->getDiContainer()->getEncryptionKeysConfiguration();

        // Set gateway configured IDP
        $mockEbIdp = $this->mockIdentityProviderFactory->createNew('Stepup gateway');
        $mockEbIdp->setEntityId('https://engine.vm.openconext.org/authentication/stepup/metadata');
        $mockEbIdp->setPrivateKey($keysConfig['default']['privateFile']);
        $mockEbIdp->setCertificate($keysConfig['default']['publicFile']);

        $this->mockIdentityProvider = $mockEbIdp;

        // Set gateway configured SP
        $mockSp = $this->mockServiceProviderFactory->createNew('ebStepupSp');
        $mockSp->setEntityId('https://engine.vm.openconext.org/authentication/stepup/metadata');

        $this->mockServiceProvider = $mockSp;
    }

    /**
     * @return string
     */
    public function getIdentityProviderEntityId()
    {
        return $this->mockIdentityProvider->entityId();
    }

    /**
     * @return string
     */
    public function getIdentityProviderPublicKeyCertData()
    {
        return $this->addPublicKeyEnvelope($this->mockIdentityProvider->publicKeyCertData());
    }

    /**
     * @return string
     */
    public function getIdentityProviderGetPrivateKeyPem()
    {
        return $this->mockIdentityProvider->getPrivateKeyPem();
    }

    /**
     * @return string
     */
    public function getServiceProviderEntityId()
    {
        return $this->mockServiceProvider->entityId();
    }

    private function addPublicKeyEnvelope($key)
    {
        return "-----BEGIN CERTIFICATE-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END CERTIFICATE-----";
    }
}
