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

namespace OpenConext\EngineBlock\Xml;

use OpenConext\EngineBlock\Metadata\MetadataRepository\KetenfoMetadataRepository;

class KetenfoMetadataProvider
{
    /**
     * @var MetadataRenderer
     */
    private MetadataRenderer $renderer;

    /**
     * @var KetenfoMetadataRepository
     */
    private $metadataRepository;

    public function __construct(
        MetadataRenderer $renderer,
        KetenfoMetadataRepository $metadataRepository
    ) {
        $this->renderer = $renderer;
        $this->metadataRepository = $metadataRepository;
    }


    /**
     * Generate XML metadata for the externally used KetenFO
     *
     * @throws \EngineBlock_Exception
     */
    public function metadataForKetenfo(): string
    {
        $identityProviders = $this->metadataRepository->findIdentityProviders();
        return $this->renderer->fromIdentityProviderEntities($identityProviders, null);
    }
}
