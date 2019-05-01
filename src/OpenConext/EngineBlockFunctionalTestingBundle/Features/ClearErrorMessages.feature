Feature:
  In order to explain my login problem's to the helpdesk
  As a user
  I need to see useful error information when something goes wrong

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"
      And a Service Provider named "Unconnected SP"
      And an unregistered Service Provider named "Unregistered SP"
      And SP "Unconnected SP" is not connected to IdP "Dummy Idp"

  Scenario: I log in at my Identity Provider, but something goes wrong and it returns an error response.
    Given the IdP is configured to always return Responses with StatusCode Requester/InvalidNameIDPolicy
      And the IdP is configured to always return Responses with StatusMessage "NameIdPolicy is invalid"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "InvalidNameIDPolicy"
      And I should see "NameIdPolicy is invalid"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

    Scenario: I log in at my Identity Provider, but the IdP gives a message that I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
      And the IdP is configured to always return Responses with StatusMessage "Invalid IP range"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "Invalid IP range"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

    Scenario: I log in at my Identity Provider, but I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

    Scenario: I log in at my Identity Provider, that does not send assertions, but they give a message that I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
      And the IdP is configured to always return Responses with StatusMessage "Invalid IP range"
      And the IdP is configured to not send an Assertion
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

    Scenario: I log in at my Identity Provider, that does not send assertions, but I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
      And the IdP is configured to not send an Assertion
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

  Scenario: I log in at my Identity Provider, but it has changed (private/public) keys without notifying OpenConext
    Given the IdP uses the private key at "src/OpenConext/EngineBlockFunctionalTestingBundle/Resources/keys/rolled-over.key"
      And the IdP uses the certificate at "src/OpenConext/EngineBlockFunctionalTestingBundle//Resources/keys/rolled-over.crt"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Invalid Identity Provider response"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

  Scenario: I want to log on, but this Service Provider may not access any Identity Providers
    When I log in at "Unconnected SP"
    Then I should see "No Identity Providers found"
     And I should see "UR ID:"
     And I should see "IP:"
     And I should see "EC:"
     And I should see "SP:"
     And I should see "SP Name:"
     And I should not see "IdP:"

  Scenario: I want to log on but this Service Provider is not yet registered at OpenConext
    When I log in at "Unregistered SP"
    Then I should see "Unknown service"
     And I should see "UR ID:"
     And I should see "IP:"
     And I should see "EC:"
     And I should see "SP:"
     And I should not see "IdP:"

  Scenario: An Identity Provider misrepresents its entityId and is thus not recognized by EB
    Given the IdP thinks its EntityID is "https://wrong.example.edu/metadata"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Error - Unknown service"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"
      And I should see "https://wrong.example.edu/metadata"

  Scenario: An Identity Provider tries to send a response over HTTP-Redirect, violating the spec
    Given the IdP uses the HTTP Redirect Binding
     When I log in at "Dummy SP"
      And I pass through EngineBlock
     Then I should see "HTTP Method not allowed"
      And I should see "The HTTP method \"GET\" is not allowed for location \"https://engine.vm.openconext.org/authentication/sp/consume-assertion\". Supported methods are: POST."

  Scenario: An Identity Provider sends a response without a SHO
    Given the IdP does not send the attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Missing required fields"
      And I should see "UID"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

  Scenario: An Identity Provider sends a response without a uid
    Given the IdP does not send the attribute named "urn:mace:dir:attribute-def:uid"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Missing required fields"
      And I should see "schacHomeOrganization"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see "IdP:"

  Scenario: An SP sends a AuthnRequest transparently for an IdP that doesn't exist
     When I log in at SP "Dummy SP" which attempts to preselect nonexistent IdP "DoesNotExist"
     Then the url should match "/authentication/feedback/unknown-preselected-idp"
      And I should see "No connection between organisation and service"
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"

  Scenario: I log in at my Identity Provider, that has the 'block_user_on_violation' feature activated, and has an invalid schacHomeOrganization attribute.
    Given feature "eb.block_user_on_violation" is enabled
    And the IdP "Dummy Idp" sends attribute "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "out-of-scope"
    And the Idp with name "Dummy Idp" has shibd scope "invalid"
  When I log in at "Dummy SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
  Then I should see "Attribute value not allowed"
    And I should see "Your organisation used a value for attribute schacHomeOrganization (\"out-of-scope\") which is not allowed for this organisation. Therefore you cannot log in."
    And I should see "UR ID:"
    And I should see "IP:"
    And I should see "EC:"
    And I should see "SP:"
    And I should see "SP Name:"
    And I should see ART code "39211"

  Scenario: I log in at my Identity Provider, that has the 'block_user_on_violation' feature activated, and has a valid schacHomeOrganization attribute.
    Given feature "eb.block_user_on_violation" is enabled
    And the IdP "Dummy Idp" sends attribute "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "test"
    And the Idp with name "Dummy Idp" has shibd scope "test"
  When I log in at "Dummy SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
  Then I should not see "Attribute value not allowed"
    And I should not see "Your organisation used a value for attribute schacHomeOrganization"

  Scenario: I log in at my Identity Provider, that has the 'block_user_on_violation' feature activated, and has an invalid eduPersonPrincipalName attribute.
    Given feature "eb.block_user_on_violation" is enabled
      And the IdP "Dummy Idp" sends attribute "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "test"
      And the IdP "Dummy Idp" sends attribute "urn:mace:dir:attribute-def:eduPersonPrincipalName" with value "name@out-of-scope"
      And the Idp with name "Dummy Idp" has shibd scope "test"
    When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
    Then I should see "Attribute value not allowed"
      And I should see "Your organisation used a value for attribute eduPersonPrincipalName (\"out-of-scope\") which is not allowed for this organisation. Therefore you cannot log in."
      And I should see "UR ID:"
      And I should see "IP:"
      And I should see "EC:"
      And I should see "SP:"
      And I should see "SP Name:"
      And I should see ART code "25138"

  Scenario: I log in at my Identity Provider, that has the 'block_user_on_violation' feature activated, and has a valid eduPersonPrincipalName attribute.
    Given feature "eb.block_user_on_violation" is enabled
      And the IdP "Dummy Idp" sends attribute "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "test"
      And the IdP "Dummy Idp" sends attribute "urn:mace:dir:attribute-def:eduPersonPrincipalName" with value "name@test"
      And the Idp with name "Dummy Idp" has shibd scope "test"
    When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
    Then I should not see "Attribute value not allowed"
      And I should not see "Your organisation used a value for attribute eduPersonPrincipalName"

  Scenario: The session has been lost after passing through EngineBlock
    When I log in at "Dummy SP"
     And I pass through EngineBlock
     And I lose my session
     And I pass through the IdP
     Then I should see "your session was lost"

  Scenario: The session has been lost after logging in at the SP
    When I log in at "Dummy SP"
     And I lose my session
     And I pass through EngineBlock
     And I pass through the IdP
    Then I should see "your session was lost"

  Scenario: A session is not started while expected
    When I go to Engineblock URL "/authentication/sp/process-consent"
    Then I should see "your session was not found"

  Scenario: The SP uses the wrong request parameter while using HTTP Redirect binding
   Given the SP "Dummy SP" sends a malformed AuthNRequest
    When I log in at "Dummy SP"
    Then I should see "The parameter \"SAMLRequest\" is missing on the SAML SSO request"
     And I should see ART code "41946"

  Scenario: The SP uses the wrong request parameter while using HTTP Post binding
   Given the SP "Dummy SP" sends a malformed AuthNRequest
     And the SP "Dummy SP" uses the HTTP POST Binding
    When I log in at "Dummy SP"
     And I pass through the SP
    Then I should see "The parameter \"SAMLRequest\" is missing on the SAML SSO request"
     And I should see ART code "41946"

  Scenario: The acs location is missing the SamlResponse parameter
    When I post data "{}" to Engineblock URL "/authentication/sp/consume-assertion"
    Then I should see "he parameter \"SAMLResponse\" is missing on the SAML ACS request"
    And I should see ART code "16088"

  Scenario: The sso location is missing the SamlRequest parameter
    When I post data "{}" to Engineblock URL "/authentication/idp/single-sign-on"
    Then I should see "The parameter \"SAMLRequest\" is missing on the SAML SSO request"
    And I should see ART code "41946"

  Scenario: The IdP sends a SAMLResponse that triggers a NotOnOrAfter violation when behind on time
   Given The clock on the IdP "Dummy Idp" is behind
    When I log in at "Dummy SP"
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
    Then I should see "Error - The Assertion is not yet valid or has expired"
    And I should see ART code "44601"

  Scenario: The IdP sends a SAMLResponse that triggers a NotOnOrAfter violation when ahead of time
   Given The clock on the IdP "Dummy Idp" is ahead
    When I log in at "Dummy SP"
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
    Then I should see "Error - The Assertion is not yet valid or has expired"
     And I should see ART code "44601"
#  Scenario: I try an unsolicited login (at EB) but mess up by not specifying a location
#  Scenario: I try an unsolicited login (at EB) but mess up by not specifying a binding
#  Scenario: I try an unsolicited login (at EB) but mess up by not specifying an invalid index
#
#  Scenario: I don't give consent to release my attributes to a Service Provider
#
#  Scenario: An attribute manipulation determines that a user may not continue
#
#  Scenario: I want to log in to a service but am not a member of the appropriate VO
