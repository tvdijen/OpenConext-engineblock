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

namespace OpenConext\EngineBlockBundle\Controller;

use OpenConext\EngineBlock\Xml\KetenfoMetadataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KetenfoMetadataController
{
    /**
     * @var KetenfoMetadataProvider
     */
    private KetenfoMetadataProvider $metadataService;

    public function __construct(
        KetenfoMetadataProvider $metadataService
    ) {
        $this->metadataService = $metadataService;
    }

    public function ketenfoMetadataAction(Request $request): Response
    {
        $metadataXml = $this->metadataService->metadataForKetenfo();

        $response = new Response($metadataXml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
