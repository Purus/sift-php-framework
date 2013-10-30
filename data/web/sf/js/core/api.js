/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * See {@link http://yepnopejs.com/} and {@link https://github.com/SlexAxton/yepnope.js/} for details
 *
 * @name yepnope
 * @class
 */

/**
 * Javascript API loader. It loads asset packages defined in asset_packages.yml
 * using yepnope loader
 *
 * @class
 * @static
 * @requires yepnope
 */
var JsAPI = JsAPI || {};

/**
 * Configuration which specifies the asset packages and its dependencies.
 * Contains dependencies and packages configuration. This is generated by
 * sfJsApi module.
 *
 * @type {Object}
 */
JsAPI.packageConfig = JsAPI.packageConfig || {
  dependencies: [],
  packages: []
};

/**
 * Holder for already loaded resources. Part of workaround yepnope's bug #91
 *
 * @link https://github.com/SlexAxton/yepnope.js/issues/91
 * @private
 */
JsAPI.loadedResources = [];

/**
 * Initialized flag
 *
 * @private
 */
JsAPI.isInitialized = false;

/**
 * Initializes the loader. Loads scripts from the document and marks them as loaded
 * so they wont be loaded twice by use_package
 *
 * @see usePackage
 */
JsAPI.initialize = function()
{
  // get all loaded scripts
  var scripts = document.getElementsByTagName('script');
  for(var i = 0; i < scripts.length; i++)
  {
    if(scripts[i].src)
    {
      this.loadedResources[scripts[i].src] = true;
    }
  }
  // stylesheets are ignored here
};

/**
 * Use package configured in asset_packages.yml with its dependencies.
 *
 * @example
 * JsAPI.usePackage('ui', function()
 * {
 *   // will be called when all files from the package have been loaded
 *   $('input.datepicker').datepicker();
 * }
 * @param {String} pckg Package name as configured in assets_packages.yml
 * @param {Function} [complete] Complete function to be invoked when all files are loaded
 * @param {Function} [callback] Callback function to be invoked on EVERY load of asset callback: function(url, result, key) {}
 * @param {Boolean} [test] Test expression
 * @link http://yepnopejs.com/
 */
JsAPI.usePackage = function(pckg, complete, callback, test)
{
  if(!this.isInitialized)
  {
    this.initialize();
    this.isInitialized = true;
  }

  if(typeof this.packageConfig.packages[pckg] !== 'undefined')
  {
    var dependencies = this.getPackageDependencies(pckg);
    var toLoad = this.packageConfig.packages[pckg];

    if(dependencies.length)
    {
      toLoad = dependencies.concat(toLoad);
    }

    // undefined? we force the load
    if(typeof test === 'undefined')
    {
      test = true;
    }

    // get only those which are not loaded
    toLoad = this.processResources(toLoad);

    // prepare options for yepnope
    var options = {
      test: test,
      yep: toLoad
    };

    if(typeof callback === 'function')
    {
      options.callback = [callback];
    }

    if(typeof complete === 'function')
    {
      options.complete = complete;
    }

    yepnope(options);
  }
  else
  {
    if("console" in window)
    {
      console.error('Invalid package "' + pckg + '".');
    }
  }

};

/**
 * Process resources, returns only those which are not loaded yet. This is a part of workaround yepnope's bug #91
 *
 * @param {Array} resourses Array of resources
 * @link https://github.com/SlexAxton/yepnope.js/issues/91
 * @private
 */
JsAPI.processResources = function(resources)
{
  var notLoaded = [];
  for(var i = 0; i < resources.length; i++)
  {
    if(!this.loadedResources[resources[i]])
    {
      notLoaded.push(resources[i]);
    }
    this.loadedResources[resources[i]] = true;
  }
  return notLoaded;
};

/**
 * Returns package dependencies for package (pckg)
 *
 * @param {String} pckg Package name
 */
JsAPI.getPackageDependencies = function(pckg)
{
  var assets = [];
  if(this.packageConfig.dependencies[pckg] !== undefined)
  {
    for(var i = 0, c = this.packageConfig.dependencies[pckg].length; i < c; i++)
    {
      var pckgName = this.packageConfig.dependencies[pckg][i];
      if(this.packageConfig.packages[pckgName] !== undefined)
      {
        // load dependencies for this pckg
        assets = assets.concat(this.getPackageDependencies(pckgName));
        assets = assets.concat(this.packageConfig.packages[pckgName]);
      }
    }
  }

  // protocol
  var scheme = document.location.protocol;

  // fix schema less urls
  for(var i = 0; i < assets.length; i++)
  {
    var parts = assets[i].split('!');
    var url = parts.pop();
    // url starts as //somethong which is scheme less URL as defined in
    // http://tools.ietf.org/html/rfc3986#section-4.2
    if(url.match(/^\/\//))
    {
      url = scheme + url;
      // rewrite assets[i]
      // glue it back
      assets[i] = parts.join('!') + url;
    }
  }
  return assets;
};

/**
 * Use package with its dependencies. This is an alias for JsAPI.usePackage()
 *
 * @example
 * JsAPI.usePackage('ui', function()
 * {
 *   // will be called when all files from the package have been loaded
 *   $('input.datepicker').datepicker();
 * }
 *
 * @param {String} pckg Package name as configured in assets_packages.yml
 * @param {Function} [complete] Complete function to be invoked when all files are loaded
 * @param {Function} [callback] Callback function to be invoked on EVERY load of asset callback: function(url, result, key) {}
 * @param {Boolean} [test] Test expression
 *
 * @link http://yepnopejs.com/
 */
var use_package = function(pckg, complete, callback, test)
{
  JsAPI.usePackage(pckg, complete, callback, test);
};
