<?php

class inheritanceActions extends autoinheritanceActions
{
  protected function addFiltersCriteria($c)
  {
    if ($this->getRequestParameter('filter'))
    {
      $c->add(ArticlePeer::ONLINE, true);
    }
  }

  protected function addSortCriteria($c)
  {
    if ($this->getRequestParameter('sort'))
    {
      $c->addAscendingOrderByColumn(ArticlePeer::TITLE);
    }
  }
}
