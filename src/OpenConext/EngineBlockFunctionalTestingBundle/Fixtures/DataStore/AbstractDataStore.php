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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RuntimeException;

abstract class AbstractDataStore
{
    protected $filePath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct($filePath)
    {
        $directory = dirname($filePath);
        $this->filePath = basename($filePath);
        $adapter = new Local($directory, LOCK_NB);
        $this->fileSystem = new Filesystem($adapter);
    }

    public function load($default = [])
    {
        if (!$this->fileSystem->has($this->filePath)) {
            return $default;
        }

        $fileContents = $this->fileSystem->read($this->filePath);

        if ($fileContents === false) {
            throw new RuntimeException(sprintf('Unable to load data from: "%s"', $this->filePath));
        }

        if (empty($fileContents)) {
            return $default;
        }

        $data = $this->decode($fileContents);
        if ($data === false) {
            throw new RuntimeException(sprintf('Unable to decode data from: "%s"', $this->filePath));
        }
        return $data;
    }

    public function save($data)
    {
        $this->fileSystem->put($this->filePath, $this->encode($data));
    }

    abstract protected function encode($data);

    abstract protected function decode($data);
}
