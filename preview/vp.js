/*
 * SOS Preview
 * version: 0.2
 * @requires jQuery
 *
 * License: MIT License
 * Copyright 2016-2018 Dominik Scherm - <dominik@dnddev.com>
 *
 */

//Make sure jQuery has been loaded before app.js
if (typeof jQuery === 'undefined') {
  throw new Error('SOS requires jQuery');
}

$(function() {
  'use strict';
  var type = getURLParameter('type');
  var school = getURLParameter('s');
  if (school) {
    if (type == 'vp') {
      initVPView(school);
    }
  }
});

function initVPView(school) {
  loadVPForCurrentDay();

  function loadVPForCurrentDay() {
    var dayNumber = getURLParameter('daynumber');
    if (!dayNumber) {
      dayNumber = getDayNumber(true);
    }
    loadVPData(dayNumber);
  }

  function loadVPData(daynumber) {
    var school_token = school;
    var url = apiURL + '/preview/school/' + school_token + '/vp/' + daynumber;
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        data = data['vp'];
        omissions = data['omission'];
        additions = data['additions'];
        num_omission = data['num_omission'];

        $('.vpdata').remove();

        // Set Additions
        toggleWeek(additions['week']);
        $('#vp_weekday').html(vpdate(daynumber));
        $('#absent')
          .find('.break-text')
          .html(additions['absent']);
        $('#jobs')
          .find('.break-text')
          .html(additions['jobs']);
        $.each(additions['breaks'], function(id, break_text) {
          $('#break_' + id)
            .find('.break-text')
            .html(break_text);
        });

        // Load events in table
        $.each(data['omission'], function(grade, omissions) {
          omissions.forEach(function(omission, index) {
            var lesson_type_html = '';
            var lesson_type_number = 0;
            if (omission.lesson_type == 'first_half') {
              lesson_type_number = 2 * parseInt(omission.hour) - 1;
              lesson_type_html =
                '<td class="">' + String(lesson_type_number) + '.Std.</td>';
            } else if (omission.lesson_type == 'second_half') {
              lesson_type_number = 2 * parseInt(omission.hour);
              lesson_type_html =
                '<td class="">' + String(lesson_type_number) + '.Std.</td>';
            }
            var textColor = '#000';
            if (omission.color != '#ffffff') {
              textColor = '#fff';
            }

            var grade_content =
              '<td class="grade-label">' + omission.grade + '</td>';
            if (omission.grade == '11' || omission.grade == '12') {
              grade_content = '';
            }

            var o_event =
              '<li class="vpdata" id="' +
              omission.oid +
              '" style="list-style:none;"><table style="background-color:' +
              omission.color +
              ';color:' +
              textColor +
              ';"><tr>' +
              lesson_type_html +
              '' +
              grade_content +
              '<td class="lesson-label">' +
              omission.lesson +
              '</td><td class="teacher-label">' +
              omission.initials +
              '</td><td class="description-label">' +
              omission.description +
              '</td></tr></table></li>';
            $('#' + omission.ograde + '_' + omission.hour)
              .parent()
              .append(o_event);
          });
        });
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      }
    });
  }

  function toggleWeek(week) {
    if (week == 'A') {
      $('.aweek')
        .addClass('active')
        .removeClass('deactive');
      $('.bweek')
        .addClass('deactive')
        .removeClass('active');
    } else {
      $('.bweek')
        .addClass('active')
        .removeClass('deactive');
      $('.aweek')
        .addClass('deactive')
        .removeClass('active');
    }
    additions['week'] = week;
    $('#week_print').html(week + '-Woche');
  }

  function vpdate(daynumber) {
    var german = [
      'Montag',
      'Dienstag',
      'Mittwoch',
      'Donnerstag',
      'Freitag',
      'Samstag',
      'Sonntag'
    ];
    // Format and return
    return german[daynumber - 1];
  }

  function getDayNumber(forToday) {
    var d = new Date();
    var n = d.getDay();
    //console.log(n);

    if (n == 5) {
      n = forToday ? 5 : 1;
    } else if (n == 6) {
      n = forToday ? 1 : 2;
    } else if (n == 0) {
      n = forToday ? 1 : 2;
    } else {
      if (!forToday) {
        n++;
      }
    }

    return n;
  }
}

// Check for whitespace
function hasWhiteSpace(s) {
  //return s.indexOf(' ') >= 0; // Ueberarbeiten! Whitespace ist durchaus erlaubt ex. "Auftrag fuer Zuhause"
  if ((s.match(/ /g) || []).length == s.length) {
    return true;
  }
  return false;
}
// Get url parameters
function getURLParameter(name) {
  return (
    decodeURIComponent(
      (new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(
        location.search
      ) || [, ''])[1].replace(/\+/g, '%20')
    ) || null
  );
}

function setGetParameter(paramName, paramValue) {
  var url = window.location.href;
  var hash = location.hash;
  url = url.replace(hash, '');
  if (url.indexOf(paramName + '=') >= 0) {
    var prefix = url.substring(0, url.indexOf(paramName));
    var suffix = url.substring(url.indexOf(paramName));
    suffix = suffix.substring(suffix.indexOf('=') + 1);
    suffix =
      suffix.indexOf('&') >= 0 ? suffix.substring(suffix.indexOf('&')) : '';
    url = prefix + paramName + '=' + paramValue + suffix;
  } else {
    if (url.indexOf('?') < 0) url += '?' + paramName + '=' + paramValue;
    else url += '&' + paramName + '=' + paramValue;
  }
  window.location.href = url + hash;
}
