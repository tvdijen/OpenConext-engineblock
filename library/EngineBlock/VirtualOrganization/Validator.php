<?php

class EngineBlock_VirtualOrganization_Validator
{
    private $message;

    public function isMember($voId, $subjectId, $idp, $sp)
    {
        $virtualOrganization = new EngineBlock_VirtualOrganization($voId);
        $voType = $virtualOrganization->getType();

        switch ($voType) {
            case 'MIXED':
                if ($this->_isMemberOfIdps($virtualOrganization, $idp)) {
                    return true;
                }
                else if ($this->_isMemberOfGroups($virtualOrganization, $subjectId, $idp, $sp)) {
                    return true;
                }
                else {
                    return false;
                }

            case 'GROUP':
                if ($this->_isMemberOfGroups($virtualOrganization, $subjectId, $idp, $sp)) {
                    return true;
                }
                else {
                    return false;
                }

            case 'IDP':
                if ($this->_isMemberOfIdps($virtualOrganization, $idp)) {
                    return true;
                }
                else {
                    return false;
                }

            default:
                throw new EngineBlock_Exception("Unknown Virtual Organization type '$voType'");
        }
    }

    protected function _isMemberOfGroups(EngineBlock_VirtualOrganization $virtualOrganization, $subjectId, $idp, $sp)
    {
        $groups = $virtualOrganization->getGroupsIdentifiers();
        $groupValidator = new EngineBlock_VirtualOrganization_GroupValidator();
        $isMember = $groupValidator->isMember($subjectId, $groups, $idp, $sp);

        // No access? Get the message.
        if (!$isMember)
        {
            $this->message = $groupValidator
              ->setLang('en')
              ->getMessage();
        }
        return $isMember;
    }

    protected function _isMemberOfIdps(EngineBlock_VirtualOrganization $virtualOrganization, $idp)
    {
        $idpIdentifiers = $virtualOrganization->getIdpIdentifiers();
        foreach ($idpIdentifiers as $idpId) {
            if ($idpId === $idp) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the reason/message when user has no access.
     */
    public function getMessage()
    {
        return $this->message;
    }
}