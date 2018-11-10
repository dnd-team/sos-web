/*!
 * jQuery Sticky Alert v0.0.4
 * https://github.com/tlongren/jquery-sticky-alert
 *
 * Copyright 2013 Tyler Longren
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * Date: May 15, 2015
 */
(function($){

  $.fn.extend({

    stickyalert: function(options) {

      var defaults = {
          barColor: '#222',
          barFontColor: '#FFF',
          barFontSize: '0.9em',
          barText: 'I like to work! Hire me!',
          barTextLink: 'http://longren.io/work-with-me/',
          cookieRememberDays: '2'
      };

      var options = $.extend(defaults, options);

      return this.each(function() {

          if (document.cookie.indexOf("jqsa") >= 0) {

            $('.alert-box').remove();

          }

          else {

          $('<div class="alert-box" style="background-color:' + options.barColor + '"><a href="' + options.barTextLink + '" style="color:' + options.barFontColor + '; font-size:' + options.barFontSize + '">' + options.barText + '</a><a href="" class="close">&#10006;</a></div>').appendTo(this);

          $(".alert-box").delegate("a.close", "click", function(event) {

            event.preventDefault();

            $(this).closest(".alert-box").fadeOut(function(event){

              $(this).remove();

              // set a new cookie
              if (options.cookieRememberDays === 0) {

                // do nothing

              }

              else {

                var hidefor =  60 * 60 * 24 * options.cookieRememberDays;

                  document.cookie = "jqsa=closed;max-age=" + hidefor;

              }

            });

          });

        }

      });

    }

  });

})(jQuery);
