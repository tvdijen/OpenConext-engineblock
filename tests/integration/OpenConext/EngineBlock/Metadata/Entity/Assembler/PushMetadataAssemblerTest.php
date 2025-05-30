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

namespace OpenConext\EngineBlock\Tests;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlock\Metadata\Entity\Assembler\PushMetadataAssembler;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\MfaEntityCollection;
use OpenConext\EngineBlock\Metadata\StepupConnections;
use OpenConext\EngineBlock\Metadata\TransparentMfaEntity;
use OpenConext\EngineBlock\Validator\AllowedSchemeValidator;
use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Psr\Log\LoggerInterface;

class PushMetadataAssemblerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $assembler;

    public function setUp(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $this->assembler = new PushMetadataAssembler(
            new AllowedSchemeValidator(['http', 'https']),
            new LanguageSupportProvider(['en', 'nl'], ['en', 'nl']),
            $logger
        );
    }

    public function test_it_rejects_empty_push()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Received 0 connections, refusing to process');

        $connection = '{}';
        $input = json_decode($connection);

        $this->assembler->assemble($input);
    }

    /**
     * @dataProvider invalidAcsLocations
     */
    public function test_it_rejects_invalid_acs_location_schemes($acsLocation)
    {
        $this->expectException(\RuntimeException::class);

        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49523": {
                "allow_all_entities": true,
                "allowed_connections": [],
                "metadata": {
                    "AssertionConsumerService": [{
                        "Binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
                        "Index": "0",
                        "Location": "%s"
                    }],
                    "NameIDFormat": "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified",
                    "NameIDFormats": ["urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified", "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent", "urn:oasis:names:tc:SAML:2.0:nameid-format:transient"],
                    "contacts": [{
                        "emailAddress": "help@example.org",
                        "surName": "OpenConext",
                        "givenName": "Support",
                        "contactType": "technical"
                    }, {
                        "emailAddress": "help@example.org",
                        "surName": "OpenConext",
                        "givenName": "Support",
                        "contactType": "support"
                    }, {
                        "emailAddress": "help@example.org",
                        "surName": "OpenConext",
                        "givenName": "Support",
                        "contactType": "administrative"
                    }],
                    "description": {
                        "en": "Test SP",
                        "nl": "Test SP"
                    },
                    "displayName": {
                        "en": "Test SP",
                        "nl": "Test SP"
                    },
                    "logo": [{
                        "width": "96",
                        "url": "https:\/\/static.dev.openconext.local\/media\/conext_logo.png",
                        "height": "96"
                    }],
                    "name": {
                        "en": "Test SP",
                        "nl": "Test SP"
                    }
                },
                "name": "https:\/\/serviceregistry.dev.openconext.local\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-sp",
                "state": "prodaccepted",
                "type": "saml20-sp"
            }
	    }';

        $input = sprintf($connection, addslashes($acsLocation));
        $input = json_decode($input);

        $this->assembler->assemble($input);
    }

    /**
     * @dataProvider validCoins
     *
     * @param string $coinName The name of the coin and used to set the coin in the meta push data
     * @param string $roleType The type of the role and used to set the coin in the meta push data
     * @param string $parameter The name of the parameter used in the coin
     * @param string $type The type of coin data to run possible assertions against see the validCoinValues* helper
     *                     methods below which are used to assert the data after it went through the assembler.
     */
    public function testCoins($coinName, $roleType, $parameter, $type) {
        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49523": {
                "allow_all_entities": true,
                "allowed_connections": [],
                "metadata": {
                    "coin": {
                        "stepup": {}
                    }
                },
                "name": "https:\/\/role/sp",
                "state": "prodaccepted",
                "type": "saml20-sp"
            }
        }';

        $input = json_decode($connection);
        $input->{"2d96e27a-76cf-4ca2-ac70-ece5d4c49523"}->type = $roleType;

        switch ($type) {
            case 'bool';
                $values = $this->validCoinValuesBool();
                break;
            case 'bool-forceAuthn';
                $values = $this->validCoinValuesBoolForceAuthn();
                break;
            case 'bool-negative';
                $values = $this->validCoinValuesBoolNegative();
                break;
            case 'string';
                $values = $this->validCoinValuesString();
                break;
            case 'string-signature-method';
                $values = $this->validCoinValuesStringSignatureMethod();
                break;
            case 'string-guest-qualifier';
                $values = $this->validCoinValuesStringGuestQualifier();
                break;
            default:
                throw new RuntimeException('Unknown coin type');
        }

        foreach ($values as $assertion) {
            if ($coinName === 'forceauthn') {
                // forceauthn is a stepup coin, situated in an additional json object named: stepup
                $input->{"2d96e27a-76cf-4ca2-ac70-ece5d4c49523"}->metadata->coin->stepup->$coinName = $assertion[0];
            } else {
                $input->{"2d96e27a-76cf-4ca2-ac70-ece5d4c49523"}->metadata->coin->$coinName = $assertion[0];
            }
            $roles = $this->assembler->assemble($input);

            $this->assertSame(
                $assertion[1],
                $roles[0]->getCoins()->{$parameter}(),
                "Invalid coin conversion for {$roleType}:{$coinName}($type) expected '{$assertion[1]}' but encountered '{$roles[0]->getCoins()->{$parameter}()}'"
            );
        }
    }

    public function test_it_assembles_sfo_settings()
    {
        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49523": {
                "metadata": {
                    "coin": {
                        "stepup": {
                            "allow_no_token": "1",
                            "requireloa": "http://test.openconext.nl/assurance/loa2"
                        }
                    }
                },
                "name": "https:\/\/serviceregistry.dev.openconext.local\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-sp",
                "state": "prodaccepted",
                "type": "saml20-sp"
            },
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49524": {
                "stepup_connections": [
                    {
                      "name": "https://serviceregistry.test2.openconext.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp",
                      "level": "http://test.openconext.nl/assurance/loa2"
                    },
                    {
                      "name": "http://mock-sp",
                      "level": "http://test.openconext.nl/assurance/loa3"
                    }
                ],
                "name": "https:\/\/serviceregistry.dev.openconext.local\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-idp",
                "state": "prodaccepted",
                "type": "saml20-idp"
            }
	    }';

        $input = json_decode($connection);

        $connections = $this->assembler->assemble($input);

        /** @var ServiceProvider $sp */
        $sp = $connections[0];
        $this->assertInstanceOf(ServiceProvider::class, $sp);
        $this->assertSame(true, $sp->getCoins()->stepupAllowNoToken());
        $this->assertSame('http://test.openconext.nl/assurance/loa2', $sp->getCoins()->stepupRequireLoa());

        /** @var IdentityProvider $idp */
        $idp = $connections[1];
        $this->assertInstanceOf(IdentityProvider::class, $idp);
        $stepupConnections = $idp->getCoins()->stepupConnections();

        $this->assertInstanceOf(StepupConnections::class, $stepupConnections);
        $this->assertTrue($stepupConnections->hasConnections());
        $this->assertSame('http://test.openconext.nl/assurance/loa2', $stepupConnections->getLoa('https://serviceregistry.test2.openconext.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp'));
        $this->assertSame('http://test.openconext.nl/assurance/loa3', $stepupConnections->getLoa('http://mock-sp'));
        $this->assertNull($stepupConnections->getLoa('http://unknown-sp'));
    }

    public function test_it_does_not_assemble_invalid_sfo_settings()
    {
        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49525": {
                "stepup_connections": [
                    {},
                    {
                      "name2": "http://mock-sp",
                      "level3": "http://test.openconext.nl/assurance/loa3"
                    }
                ],
                "name": "https:\/\/serviceregistry.dev.openconext.local\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-idp",
                "state": "prodaccepted",
                "type": "saml20-idp"
            }
	    }';

        $input = json_decode($connection);

        $connections = $this->assembler->assemble($input);

        /** @var IdentityProvider $idp */
        $idp = $connections[0];
        $this->assertInstanceOf(IdentityProvider::class, $idp);
        $stepupConnections = $idp->getCoins()->stepupConnections();

        $this->assertInstanceOf(StepupConnections::class, $stepupConnections);
        $this->assertFalse($stepupConnections->hasConnections());
    }

    public function test_it_assembles_mfa_entity_settings()
    {
        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49524": {
                "mfa_entities": [{
                    "name": "https://teams.dev.openconext.local/shibboleth",
                    "level": "http://schemas.microsoft.com/claims/multipleauthn"
                }, {
                    "name": "https://aa.dev.openconext.local/shibboleth",
                    "level": "http://schemas.microsoft.com/claims/multipleauthn"
                }],
                "name": "https:\/\/serviceregistry.dev.openconext.local\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-idp",
                "state": "prodaccepted",
                "type": "saml20-idp"
            }
	    }';

        $input = json_decode($connection);

        $connections = $this->assembler->assemble($input);

        /** @var IdentityProvider $idp */
        $idp = $connections[0];
        $this->assertInstanceOf(IdentityProvider::class, $idp);
        $mfaEntities = $idp->getCoins()->mfaEntities();
        $this->assertInstanceOf(MfaEntityCollection::class, $mfaEntities);
        $this->assertCount(2, $mfaEntities);
    }

    public function test_mfa_entity_settings_are_optional()
    {
        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49524": {
                "name": "https:\/\/serviceregistry.dev.openconext.local\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-idp",
                "state": "prodaccepted",
                "type": "saml20-idp"
            }
	    }';

        $input = json_decode($connection);

        $connections = $this->assembler->assemble($input);

        /** @var IdentityProvider $idp */
        $idp = $connections[0];
        $this->assertInstanceOf(IdentityProvider::class, $idp);
        $mfaEntities = $idp->getCoins()->mfaEntities();
        $this->assertInstanceOf(MfaEntityCollection::class, $mfaEntities);
        $this->assertCount(0, $mfaEntities);
    }

    public function test_it_assembles_mfa_entity_settings_with_transparent_option()
    {
        $connection = '{
            "2d96e27a-76cf-4ca2-ac70-ece5d4c49524": {
                "mfa_entities": [{
                    "name": "https://teams.dev.openconext.local/shibboleth",
                    "level": "http://schemas.microsoft.com/claims/multipleauthn"
                }, {
                    "name": "https://aa.dev.openconext.local/shibboleth",
                    "level": "http://schemas.microsoft.com/claims/multipleauthn"
                }, {
                    "name": "https://test-sp.dev.openconext.local",
                    "level": "transparent_authn_context"
                }],
                "name": "https:\/\/serviceregistry.dev.openconext.local\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-idp",
                "state": "prodaccepted",
                "type": "saml20-idp"
            }
	    }';

        $input = json_decode($connection);

        $connections = $this->assembler->assemble($input);

        /** @var IdentityProvider $idp */
        $idp = $connections[0];
        $this->assertInstanceOf(IdentityProvider::class, $idp);
        $mfaEntities = $idp->getCoins()->mfaEntities();
        $this->assertInstanceOf(MfaEntityCollection::class, $mfaEntities);
        $this->assertCount(3, $mfaEntities);
        $transparent = $mfaEntities->findByEntityId('https://test-sp.dev.openconext.local');
        $this->assertInstanceOf(TransparentMfaEntity::class, $transparent);
    }

    public function invalidAcsLocations()
    {
        return [
            'invalid-scheme' => ['javascript:alert("hello world");'],
            'no-scheme' => ['www.sp.example.com'],
        ];
    }

    public function validCoins()
    {
        return [
            // SP
            ['no_consent_required', 'saml20-sp', 'isConsentRequired', 'bool-negative'],
            ['transparant_issuer', 'saml20-sp', 'isTransparentIssuer', 'bool'],
            ['trusted_proxy', 'saml20-sp', 'isTrustedProxy', 'bool'],
            ['display_unconnected_idps_wayf', 'saml20-sp', 'displayUnconnectedIdpsWayf', 'bool'],
            ['eula', 'saml20-sp', 'termsOfServiceUrl', 'string'],
            ['do_not_add_attribute_aliases', 'saml20-sp', 'skipDenormalization', 'bool'],
            ['policy_enforcement_decision_required', 'saml20-sp', 'policyEnforcementDecisionRequired', 'bool'],
            ['requesterid_required', 'saml20-sp', 'requesteridRequired', 'bool'],
            ['sign_response', 'saml20-sp', 'signResponse', 'bool'],
            ['forceauthn', 'saml20-sp', 'isStepupForceAuthn', 'bool-forceAuthn'],

            // IDP
            ['guest_qualifier', 'saml20-idp', 'guestQualifier', 'string-guest-qualifier'],
            ['schachomeorganization', 'saml20-idp', 'schacHomeOrganization', 'string'],
            ['hidden', 'saml20-idp', 'hidden', 'bool'],

            // Abstract
            ['disable_scoping', 'saml20-idp', 'disableScoping', 'bool'],
            ['additional_logging', 'saml20-idp', 'additionalLogging', 'bool'],
            ['signature_method', 'saml20-idp', 'signatureMethod', 'string-signature-method'],
        ];
    }

    /**
     * The first option is the manage coin value, the second is the expected entity coin value after assembling
     * @return array
     */
    private function validCoinValuesBool() {
        return [
            [null, false],
            [true, true],
            [false, false],
            ["1", true],
            ["0", false],
            ["-1", true],
        ];
    }

    /**
     * The first option is the manage coin value, the second is the expected entity coin value after assembling
     * @return array
     */
    private function validCoinValuesBoolNegative() {
        return [
            [null, true],
            [true, false],
            [false, true],
            ["1", false],
            ["0", true],
            ["-1", false],
        ];
    }

    /**
     * The first option is the manage coin value, the second is the expected entity coin value after assembling
     * @return array
     */
    private function validCoinValuesString() {
        return [
            [null, null],
            ["", ""],
            ["string", "string"],
        ];
    }

    /**
     * The first option is the manage coin value, the second is the expected entity coin value after assembling
     * @return array
     */
    private function validCoinValuesStringSignatureMethod() {
        return [
            [null, "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"],
            ["", ""],
            ["string", "string"],
        ];
    }

    /**
     * The first option is the manage coin value, the second is the expected entity coin value after assembling
     * @return array
     */
    private function validCoinValuesStringGuestQualifier() {
        return [
            [null, "All"],
            ["", ""],
            ["string", "string"],
        ];
    }

    private function validCoinValuesBoolForceAuthn()
    {
        return [
            ["1", true],
            ["0", false]
        ];
    }

    public function test_it_assembles_coin_collab_enabled()
    {
        $input = $this->readFixture('metadata_coin_collab.json');
        $connections = $this->assembler->assemble($input->connections);

        /** @var ServiceProvider $sp */
        $sp = $connections[0];
        $this->assertInstanceOf(ServiceProvider::class, $sp);

        $this->assertSame('sp coin_collab_true', $sp->nameEn);
        $this->assertSame(true, $sp->getCoins()->collabEnabled());

        /** @var ServiceProvider $sp */
        $sp = $connections[1];
        $this->assertInstanceOf(ServiceProvider::class, $sp);

        $this->assertSame('sp coin_collab_false', $sp->nameEn);
        $this->assertSame(false, $sp->getCoins()->collabEnabled());

        /** @var ServiceProvider $sp */
        $sp = $connections[2];
        $this->assertInstanceOf(ServiceProvider::class, $sp);

        $this->assertSame('sp coin_collab_unset', $sp->nameEn);
        $this->assertSame(false, $sp->getCoins()->collabEnabled());


        /** @var IdentityProvider $idp */
        $idp = $connections[3];
        $this->assertInstanceOf(IdentityProvider::class, $idp);

        $this->assertSame('Dummy IdP', $idp->nameEn);
        $this->assertSame(false, $idp->getCoins()->collabEnabled());
    }


    public function test_it_assembles_idp_details()
    {
        $input = $this->readFixture('metadata_idp_display_data.json');
        $connections = $this->assembler->assemble($input);

        /** @var IdentityProvider $idp */
        $idp = $connections[0];
        $this->assertInstanceOf(IdentityProvider::class, $idp);

        $this->assertSame('Dummy IdP', $idp->nameEn);
        $this->assertSame('Dummy IdP nl', $idp->nameNl);

        $this->assertSame('idp keywords', $idp->keywordsEn);
        $this->assertSame('idp keywords nl', $idp->keywordsNl);
        $this->assertSame('idp keywords', $idp->getMdui()->getKeywords('en'));
        $this->assertSame('idp keywords nl', $idp->getMdui()->getKeywords('nl'));

        $this->assertSame('https://engine.dev.openconext.local/images/logo.png?v=dev', $idp->logo->url);
        $this->assertSame('https://engine.dev.openconext.local/images/logo.png?v=dev', $idp->getMdui()->getLogo()->url);

        $expectedDiscoveryLogo = new Logo('https://engine.dev.openconext.local/images/discovery_logo.png?v=dev');
        $expectedDiscoveryLogo->height = 96;
        $expectedDiscoveryLogo->width = 128;

        $this->assertEquals(
            [
                Discovery::create(
                    ['en' => 'Discovery #1', 'nl' => 'Discovery #1 nl'],
                    ['en' => 'disco keywords, (test)', 'nl' => 'disco keywords, nl'],
                    $expectedDiscoveryLogo
                ),
                Discovery::create(['en' => 'Minimal discovery'], [], null),
            ],
            $idp->getDiscoveries()
        );
    }

    public function test_it_assembles_metadata_without_discoveries_details()
    {
        $input = $this->readFixture('metadata_without_discoveries.json');
        $connections = $this->assembler->assemble($input);

        /** @var IdentityProvider $idp */
        $idp = $connections[0];
        $this->assertInstanceOf(IdentityProvider::class, $idp);

        $this->assertSame('Dummy IdP', $idp->nameEn);
        $this->assertSame('idp keywords', $idp->getMdui()->getKeywords('en'));
        $this->assertSame('https://engine.dev.openconext.local/images/logo.png?v=dev', $idp->logo->url);
    }

    private function readFixture(string $file): object
    {
        return json_decode(file_get_contents(__DIR__ . '/fixtures/'. basename($file)), false);
    }
}
