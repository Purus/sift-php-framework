<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfFillInFormFilter fills in forms.
 *
 * @package    Sift
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfFillInFormFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    $context  = $this->getContext();
    $response = $context->getResponse();
    $request  = $context->getRequest();

    // prevents an "sfException - No form found in this page"
    // @see http://snippets.symfony-project.org/snippet/245
    if(!$request->hasErrors())
    {
      return;
    }

    $fillInForm = new sfFillInForm();

    // converters
    foreach ($this->getParameter('converters', array()) as $functionName => $fields)
    {
      $fillInForm->addConverter($functionName, $fields);
    }

    // skip fields
    $fillInForm->setSkipFields((array) $this->getParameter('skip_fields', array()));

    // types
    $excludeTypes = (array) $this->getParameter('exclude_types', array('hidden', 'password'));
    $checkTypes   = (array) $this->getParameter('check_types',   array('text', 'checkbox', 'radio', 'password', 'hidden'));
    $fillInForm->setTypes(array_diff($checkTypes, $excludeTypes));

    // fill in
    $method  = 'fillIn'.ucfirst(strtolower($this->getParameter('content_type', 'Xhtml')));

    try
    {
      $content = $fillInForm->$method($response->getContent(), $this->getParameter('name'), $this->getParameter('id'), $request->getParameterHolder()->getAll());
      $response->setContent($content);
    }
    catch (sfException $e)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->getContext()->getLogger()->err(sprintf('{sfFilter} %s', $e->getMessage()));
      }
    }
  }
}
