<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CacheHelper.
 *
 * @package    Sift
 * @subpackage helper
 */


/* Usage

<?php if (!cache('name')): ?>

... HTML ...

  <?php cache_save() ?>
<?php endif; ?>

*/
function cache($name, $lifeTime = 86400)
{
  if (!sfConfig::get('sf_cache'))
  {
    return null;
  }
  
  $context = sfContext::getInstance();
  $request = $context->getRequest();
  $cache   = $context->getViewCacheManager();

  if (!is_null($request->getAttribute('started', null, 'sift/action/sfAction/cache')))
  {
    throw new sfCacheException('Cache already started');
  }

  $data = $cache->start($name, $lifeTime);

  if ($data === null)
  {
    $request->setAttribute('started', 1, 'sift/action/sfAction/cache');
    $request->setAttribute('current_name', $name, 'sift/action/sfAction/cache');

    return 0;
  }
  else
  {
    echo $data;

    return 1;
  }
}

function cache_save()
{
  if (!sfConfig::get('sf_cache'))
  {
    return null;
  }

  $context = sfContext::getInstance();
  $request = $context->getRequest();

  if (is_null($request->getAttribute('started', null, 'sift/action/sfAction/cache')))
  {
    throw new sfCacheException('Cache not started');
  }

  $name = $request->getAttribute('current_name', '', 'sift/action/sfAction/cache');

  $data = $context->getViewCacheManager()->stop($name);

  $request->setAttribute('started', null, 'sift/action/sfAction/cache');
  $request->setAttribute('current_name', null, 'sift/action/sfAction/cache');

  echo $data;
}
