
// adding preload prefix
yepnope.addPrefix('preload', function(resource)
{
  resource.noexec = true;
  return resource;
});
