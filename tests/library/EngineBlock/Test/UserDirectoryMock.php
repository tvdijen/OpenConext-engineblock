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

class EngineBlock_Test_UserDirectoryMock extends EngineBlock_UserDirectory
{
    protected $_users = array();

    public function __construct()
    {
    }

    public function setUser($id, $user)
    {
        $this->_users[$id] = $user;
        return $this;
    }

    public function findUsersByIdentifier($identifier, $ldapAttributes = array())
    {
        return array($this->_users[$identifier]);
    }
}
