(function($)
{
  $(document).ready(function()
  {
    // $('#exception li.debug-backtrace-item').not(':first').addClass('hidden');

    $('#exception a.exception-toggler,#exception a.debug-backtrace-toggler').click(function(e)
    {
      var $this = $(this);
      var target = $this.data('target');

      // this is not a link to id nor to class
      if(target.indexOf('#') === -1 && target.indexOf('.') === -1)
      {
        var $target = $this.parent().find('.' + target + ':first');
      }
      else
      {
        var $target = $(target + ':first');
      }

      if($target.length)
      {
        $target.toggleClass('hidden');
        $this.toggleClass('active');
      }

      e.preventDefault();
    });

  });
}(window.jQuery));
