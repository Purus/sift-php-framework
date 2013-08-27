<?php

class securityActions extends myActions
{
  public function executeIndex()
  {
    $this->secured = sfSecurity::isActionSecure('security', 'secured');
    $this->index = sfSecurity::isActionSecure('security', 'index');
    $this->securedCredentials = sfSecurity::getActionCredentials('security', 'secured');
    $this->securedIsAllowed = sfSecurity::isUserAllowedToExecuteAction($this->getUser(), 'security', 'secured');

    $this->userCredentials = $this->getUser()->getCredentials();
  }

  public function executeCredential()
  {
    $this->getUser()->setAuthenticated(true)->addCredentials('foo_credential');

    $this->setFlash('success', 'redirected');

    $this->forward('security', 'index');
  }

  public function executeSecured()
  {
    return $this->renderText('Secured');
  }

}
