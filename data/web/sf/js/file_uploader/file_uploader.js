/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * jQuery File Upload plugin
 *
 * @memberOf jQuery.fn
 * @function
 * @name fileupload
 * @param {Object} options Array of options
 * @link https://github.com/blueimp/jQuery-File-Upload
 */

(function($, window) {

  // check required plugin
  if(typeof $.fn.fileupload == 'undefined')
  {
    throw Error('File upload is not loaded. File uploader will not work.');
  }

  /**
   * Translate characters or replace substrings. strtr() for JavaScript
   * @param {type} str
   * @param {type} replacePairs
   * @see https://gist.github.com/dsheiko/2774533
   */
  function strtr(str, replacePairs)
  {
    'use strict';
    var key, re;
    for(key in replacePairs)
    {
      if(replacePairs.hasOwnProperty(key))
      {
        re = new RegExp(key, 'g');
        str = str.replace(re, replacePairs[key]);
      }
    }
    return str;
  };

  /**
   * Returns host from url
   *
   * @param {String} url
   * @returns {String} Host
   */
  function getHostFromUrl(url)
  {
    var location = document.createElement('a');
    location.href = url;
    if(location.host === '')
    {
      location.href = window.location.href;
    }
    return location.host;
  };

  /**
   * Format filesize to human readable string
   *
   * @param {Integer} bytes
   * @returns {String}
   */
  function formatFileSize(bytes)
  {
    if(typeof bytes !== 'number')
    {
      return '';
    }

    if(bytes >= 1000000000)
    {
      return (bytes / 1000000000).toFixed(2) + ' GB';
    }

    if(bytes >= 1000000)
    {
      return (bytes / 1000000).toFixed(2) + ' MB';
    }

    if(bytes >= 1000)
    {
      return (bytes / 1000).toFixed(0) + ' kB';
    }

    return bytes.toFixed(0) + ' B';
  };

  /**
   * Format percentage
   *
   * @param {Float} floatValue
   * @returns {String}
   */
  function formatPercentage(floatValue)
  {
    return floatValue.toFixed(0) + '%';
  };

  /**
   * Generates UUIDs
   *
   * @link http://stackoverflow.com/questions/105034/how-to-create-a-guid-uuid-in-javascript
   */
  function s4()
  {
    return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
  };

  /**
   * Generates UUIDs
   *
   * @link http://stackoverflow.com/questions/105034/how-to-create-a-guid-uuid-in-javascript
   */
  function generateUid()
  {
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
  }

  /**
   * Resets the input
   *
   * @param {jQuery Object} input The input to reset
   */
  function resetFileInput(input)
  {
    var inputClone = input.clone(true);

    $('<form></form>').append(inputClone)[0].reset();

    // Detaching allows to insert the fileInput on another form
    // without loosing the file input value:
    input.after(inputClone).detach();

    // Avoid memory leaks with the detached file input:
    $.cleanData(input.unbind('remove'));

    return inputClone;
  };

  /**
   * FileUpload constructor
   *
   * @param {DOM element} element DOM element
   * @param {Object} options Array of options
   * @class
   * @name FileUploader
   * @requires jQuery.fn.fileupload
   */
  var FileUploader = function(element, options)
  {
    // options
    this.options = $.extend(true, {}, $.fn.fileUploader.defaults, options);

    this.$element = $(element);

    // support features
    this.support = {
      // drag and drop support: http://stackoverflow.com/questions/2856262/detecting-html5-drag-and-drop-support-in-javascript
      dnd: ('draggable' in document.createElement('span'))
    };

    // input name
    this.name = this.$element.attr('name');
    this.$element.wrap('<div class="file-uploader-wrapper" />');
    this.$wrapper = this.$element.parent();

    // hide from the scene
    this.$element.addClass('file-uploader-hidden');

    this.$button = $('<button type="button" class="file-uploader-button btn btn-upload">'
            + (this.options.buttonText  ? this.options.buttonText : this.options.messages.browseFiles) +
            '</button>');

    this.$button.insertAfter(this.$element);

    // not multiple, and max number of files is not specified, set it to 1!
    if(!this.$element.attr('multiple') &&
        typeof this.options.maxNumberOfFiles === 'undefined')
    {
      this.options.maxNumberOfFiles = 1;
    }

    // form wrapper
    this.$form = this.$element.parents('form:first');

    // drop zone?
    if(this.options.dropZone)
    {
      if(!(this.options.dropZone instanceof jQuery))
      {
        this.options.dropZone = $(this.options.dropZone);
      }
      this.setupDropZone(this.options.dropZone);
    }

    // queue
    this.$queue = this.options.queue ? this.options.queue : $('<div />').insertAfter(this.$wrapper);
    this.$queue.addClass('file-uploader-queue');

    // initialize the input tag on the parent element!
    this.$wrapper.fileupload({
      // i18n messages
      messages: this.options.messages,
      forceIframeTransport: this.options.forceIframeTransport,
      redirect: this.options.iframeRedirect,
      redirectParamName: this.options.iframeRedirectParameterName,
      url: this.options.url,
      formData: (typeof this.options.formData === 'function' ?
                  this.options.formData : $.proxy(this.getFormData, this)),
      // only for Xhr uploads
      headers: {
        'X-File-Uploader': this.version
      },
      dataType: 'json',
      // drop zone?
      dropZone: this.options.dropZone || null,
      fileInput: this.$element,
      sequentialUploads: this.options.sequentialUploads,
      // need to work inserting of hidden inputs!
      replaceFileInput: false,
      acceptFileTypes: this.options.acceptFileTypes,
      // maximum number of files
      maxNumberOfFiles: this.options.maxNumberOfFiles,
      getNumberOfFiles: $.proxy(this.getNumberOfFiles, this),
      maxFileSize: this.options.maxFileSize,
      maxChunkSize: this.options.maxChunkSize,
      multipart: this.options.maxChunkSize > 0 ? false : true
    })
    .on('fileuploadadd', $.proxy(this.onFileAddCallback, this))
    .on('fileuploadsend', $.proxy(this.onFileSendCallback, this))
    .on('fileuploadstart', $.proxy(this.onFileStartCallback, this))
    .on('fileuploadsubmit', $.proxy(this.onFileSubmitCallback, this))
    .on('fileuploadprocess', $.proxy(this.onFileProcessCallback, this))
    .on('fileuploadprocessfail', $.proxy(this.onFileProcessFailCallback, this))
    .on('fileuploadprocessdone', $.proxy(this.onFileProcessDoneCallback, this))
    .on('fileuploadstart', $.proxy(this.onFileUploadStartCallback, this))
    .on('fileuploadprogress', $.proxy(this.onFileUploadProgressCallback, this))
    .on('fileuploadalways', $.proxy(this.onFileUploadAlwaysCallback, this));
  };

  FileUploader.prototype = {

    constructor: FileUploader,

    /**
     * Version
     */
    version: '1.0.1',

    /**
     * Setups the drop zone
     *
     */
    setupDropZone: function(dropZone)
    {
      dropZone.addClass('file-uploader-drop-zone');

      if(!this.support.dnd)
      {
        dropZone.addClass('unsupported');
      }
      else
      {
        dropZone.on('dragover', function(e)
        {
          $(this).addClass('hover');
        })
        .on('dragleave', function(e)
        {
          $(this).removeClass('hover');
        })
        .on('drop', function(e)
        {
          $(this).removeClass('hover');
        });
      }
    },

    /**
     * Returns array of data which are send along with the file
     *
     * @returns {Array}
     */
    getFormData: function()
    {
      var data = [];

      if(this.options.acceptFileTypes)
      {
        data.push({
          name: '_accept',
          value: this.options.acceptFileTypes
        });
      }

      if(this.options.maxFileSize)
      {
        data.push({
          name: '_max_size',
          value: this.options.maxFileSize
        });
      }

      // we will serialize the form
      if(this.options.formData)
      {
        data = $.extend(data, this.$form.serializeArray());
      }

      return data;
    },

    /**
     * Called when user selects a file from the input
     *
     */
    onFileAddCallback: function(e, data)
    {
      this.$element.trigger('fileupload.added', e, data);

      // reseting the file input the "right" way
      this.$element = $(resetFileInput(this.$element));
    },

    /**
     * When file is submitted. Pass uid to the server.
     *
     * @param {Object} e
     * @param {Object} data
     */
    onFileSubmitCallback: function(e, data)
    {
      var file = data.files[data.index];
      var f = data.formData();
      f.push({
        name: '_uid',
        value: file.uid
      });
      data.formData = f;
      return true;
    },

    /**
     * Callback for the start of an individual file processing queue.
     *
     * @param {Object} e
     * @param {Object} data
     */
    onFileProcessCallback: function(e, data)
    {
      var that = this;
      var file = data.files[data.index];
      var uid = file.uid = generateUid(file);

      // html template
      var $item = $(strtr(that.options.itemTemplate,
      {
        '{{type}}': file.type ? file.type.split('/')[0] : 'unknown',
        '{{file}}': file.name,
        '{{cancel}}': that.options.messages.cancel,
        '{{size}}': typeof file.size !== 'undefined' ? formatFileSize(file.size) : ''
      }));

      $item.addClass('file-uploader-file-' + uid);

      // remove button
      $item.find('a.file-uploader-cancel').data('data', data).on('click', { uid: uid }, function(e)
      {
        var $this = $(this);
        var data = $this.data('data');
        var uid = e.data.uid;

        if(data.xhr)
        {
          // abort request!
          data.xhr.abort();
        }

        var abort = data.abort();
        abort.abort();

        that.$form.find('input[type="hidden"][name="' + that.name + '"]')
            .filter(function() { return $.data(this, 'uid') == uid; })
            // remove from DOM
            .remove();

        $this.parents('.file-uploader-item:first').fadeOut('fast', function()
        {
          $(this).remove();
        });

        e.preventDefault();
      });

      $item.addClass('queued').find('.file-uploader-state').html(that.options.messages.uploadInQueue);

      file.widget = $item;
    },

    /**
     * When processing of the file fails
     *
     * @param {Object} e
     * @param {Object} data
     */
    onFileProcessFailCallback: function(e, data)
    {
      var file = data.files[data.index];
      file.widget.addClass('error')
          .find('.file-uploader-state').addClass('error').html(file.error);
      this.$queue.append(file.widget);
    },

    /**
     * When processing of the file is done
     *
     * @param {Object} e
     * @param {Object} data
     */
    onFileProcessDoneCallback: function(e, data)
    {
      // we need to assign the xhr to the data, so we can
      // successfully cancel the request we the user wants to!
      data.xhr = data.submit();
      var file = data.files[data.index];
      file.widget.data('data', data);
      this.$queue.append(file.widget);
    },

    /**
     * Returns how many files are successfully uploaded.
     *
     * @returns {Number}
     */
    getNumberOfFiles: function()
    {
      var count = this.$queue.children(':not(.error)').length;
      return count;
    },

    /**
     * Disables all submits in the form
     *
     */
    disableSubmits: function()
    {
      this.$form.find('input[type="submit"],button[type="submit"]').prop('disabled', true).trigger('change');
    },

    /**
     * Enables all submits in the form
     */
    enableSubmits: function()
    {
      this.$form.find('input[type="submit"],button[type="submit"]').prop('disabled', false).trigger('change');
    },

    /**
     * File progress callback
     *
     */
    onFileUploadProgressCallback: function(e, data)
    {
      var file = data.files[data.index];
      var percent = parseInt(data.loaded / data.total * 100, 10);
      file.widget.find('.percentage').text(formatPercentage(percent));
      file.widget.find('.bar').css('width', percent + '%');
    },

    /**
     * When uploads start
     *
     * @param {Object} e
     */
    onFileStartCallback: function(e)
    {
      this.disableSubmits();
    },

    /**
     *
     * @param {Object} e
     */
    onFileSendCallback: function(e, data)
    {
      var file = data.files[data.index];
      var element = file.widget;
      var stateElement = file.widget.find('.file-uploader-state');
      element.removeClass('queued').addClass('in-progress');
      stateElement.html(this.options.messages.uploadStarted);
    },

    /**
     * Callback - When a file upload is completed
     *
     * @param {Object} e
     * @param {Object} data
     */
    onFileUploadAlwaysCallback: function(e, data)
    {
      var that = this;
      var error = false;
      var state = this.options.messages.uploaded;
      var file = data.files[data.index];
      var element = file.widget;

      // no result, this is an error
      if(!data.result)
      {
        error = true;
        if(data.errorThrown == 'abort')
        {
          state = this.options.messages.aborted;
        }
        else
        {
          if(typeof data.jqXHR.responseJSON !== 'undefined' &&
             typeof data.jqXHR.responseJSON.files[data.index] !== 'undefined'
            && data.jqXHR.responseJSON.files[data.index].error)
          {
            state = data.jqXHR.responseJSON.files[data.index].error;
          }
          else
          {
            state = this.options.messages.error;
          }
        }
      }
      else
      {
        var name = this.options.inputName || this.name;
        if(that.$form.length)
        {
          var f = typeof data.result.files[data.index] !== 'undefined' ?
                  data.result.files[data.index] : {};
          if(f.id)
          {
            // create input
            var $input = $('<input type="hidden" name="' + name + '" value="'+ f.id +'" />')
                              .data('uid', file.uid);
            $input.insertAfter(that.$element);
          }
        }
      }

      element.removeClass('in-progress').addClass('success');

      element.find('.percentage').hide().remove();
      element.find('.bar').css('width', '100%');
      element.find('.file-uploader-state')
              .removeClass('in-progress')
              .addClass((error ? 'error' : 'success'))
              .html(state);

      // enable submits
      this.enableSubmits();
    }

  };

  /**
   * jQuery fileUploader plugin
   *
   * @memberOf jQuery.fn
   * @param {Object} option
   * @returns {Mixed}
   */
  $.fn.fileUploader = function(option)
  {
    return this.each(function()
    {
      var $this = $(this);
      var data = $this.data('fileUploader');
      var options = typeof option === 'object' && option;
      if(!data)
      {
        $this.data('fileUploader', (data = new FileUploader(this, options)));
      }
      if(typeof option === 'string')
      {
        data[option]();
      }
    });
  };

  // default options
  $.fn.fileUploader.defaults = {

    // i18n messages. Culture translations are located in i18n folder
    messages: {

      // messages for jquery upload plugin
      uploadedBytes: 'Uploaded bytes exceed file size',
      maxNumberOfFiles: 'Maximum number of files exceeded',
      acceptFileTypes: 'File type not allowed',
      maxFileSize: 'File is too large',
      minFileSize: 'File is too small',

      // custom messages
      browseFiles: 'Browse files...',
      cancel: 'cancel',
      notAvailable: 'n/a',
      error: 'Error occurred.',
      uploaded: 'Successfully uploaded.',
      aborted: 'Upload aborted.',
      uploadStarted: 'Uploading in progress, please wait...',
      uploadInQueue: 'File queued for upload.'
    },

    // button text, if not specified, browseFiles is used
    buttonText: false,

    // Set this option to true to issue all file upload
    // requests in a sequential order instead of simultaneous requests.
    sequentialUploads: true,

    // force iframe transport?
    forceIframeTransport: false,

    // redirect option for cross domain iframes
    // needed placeholder: %s, must be absolute url
    iframeRedirect : '',

    // The parameter name for the redirect url, sent as part of the
    // form data and set to 'redirect' if this option is empty.
    iframeRedirectParameterName: '_redirect',

    // submit form data?
    // Additional form data to be sent along with the file uploads can be set
    // using this option, which accepts an array of objects with name and value
    // properties, a function returning such an array, a FormData object
    // (for XHR file uploads), or a simple object.
    // The form of the first fileInput is given as parameter to the function.
    formData: false,

    // This option limits the number of files that are allowed to be uploaded using this widget.
    // By default, unlimited file uploads are allowed.
    maxNumberOfFiles: undefined,
    // The maximum allowed file size in bytes.
    // Note: This option has only an effect for browsers supporting the File API.
    maxFileSize: 200000000, // max upload size 200 MB is default
    // Only available on Firefox and Chrome
    // maxChunkSize: 10000000, // 10 MB
    maxChunkSize: 0,
    // Which files are accepted?
    // The regular expression for allowed file types, matches against either file type or
    // file name as only browsers with support for the File API report the file type.
    // acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
    acceptFileTypes: undefined,
    // name of the hidden input, which will be added to the form
    inputName: false,
    // selector of the drop zone
    dropZone: false,
    // selector for the queue
    queue: false,
    // where to send the upload? required option
    url: '',
    itemTemplate: '<div class="file-uploader-item file-uploader-type-{{type}}">\n\
    <div class="file-uploader-info"><span class="file-uploader-filename">{{file}}</span> {{size}}</div>\n<span class="percentage"></span>\n\
    <a href="#" class="file-uploader-cancel"><strong> &times;</strong> {{cancel}}</a>\n\
    <div class="file-uploader-state"></div>\n\
    <div class="file-uploader-progress"><div class="bar" style="width: 0%;"></div></div>\n\
    </div>'
  };

  $.fn.fileUploader.Constructor = FileUploader;

}(window.jQuery, window));
