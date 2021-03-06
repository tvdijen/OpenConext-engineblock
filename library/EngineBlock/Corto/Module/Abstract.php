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

/**
 * The abstract base class for all Corto internal and extension Modules.
 * @author Boy
 */
abstract class EngineBlock_Corto_Module_Abstract
{
    /**
     * A reference to the Corto_ProxyServer to which this module belongs.
     * @var EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * Construct a module, passing in a reference to the Corto_ProxyServer
     * to which this module belongs
     * @param EngineBlock_Corto_ProxyServer $server
     */
    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        $this->_server = $server;
    }
}
