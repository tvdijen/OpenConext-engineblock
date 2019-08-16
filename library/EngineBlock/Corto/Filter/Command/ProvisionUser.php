<?php

/**
 * Copyright 2014 SURFnet B.V.
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

use SAML2\Constants;

class EngineBlock_Corto_Filter_Command_ProvisionUser extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseModificationInterface,
    EngineBlock_Corto_Filter_Command_CollabPersonIdModificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollabPersonId()
    {
        return $this->_collabPersonId;
    }

    public function execute()
    {
        $userDirectory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getUserDirectory();
        $user = $userDirectory->identifyUser($this->_responseAttributes);

        $collabPersonIdValue = $user->getCollabPersonId()->getCollabPersonId();
        $this->setCollabPersonId($collabPersonIdValue);

        $this->_response->setCollabPersonId($collabPersonIdValue);
        $this->_response->setOriginalNameId($this->_response->getNameId());

        // Adjust the NameID in the OLD response (for consent), set the collab:person uid
        $this->_response->getAssertion()->setNameId(array(
            'Value' => $collabPersonIdValue,
            'Format' => Constants::NAMEID_PERSISTENT,
        ));
    }
}
