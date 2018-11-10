/*
 * SOS Consultations
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
  loadContent();
});

var base = '';
var baseScripts = '';

var isDev = 1;

if (isDev == 1) {
  base = '';
  baseScripts = '';
} else if (isDev == 0) {
  base = '';
  baseScripts = '';
} else if (isDev == 2) {
  base = '';
  baseScripts = '';
}

function loadContent() {
  var url = 'getter/get-teacher-consultations.php';
  url = baseScripts + url;
  $.ajax({
    type: 'POST',
    data: {},
    url: url,
    success: function(data) {
      console.log(data);
      var container = '#overview';
      $(container)
        .html(data)
        .promise()
        .done(function() {
          componentHandler.upgradeDom();
        });
    }
  });
}

$(document).on('click', '.open-consultation', function(e) {
  var teacherID = $(this)
    .parent()
    .parent()
    .attr('data-id');
  var teacherName = $(this)
    .parent()
    .parent()
    .attr('data-teacher-name');
  var url = 'getter/get-consultation.php';
  url = baseScripts + url;
  $.ajax({
    type: 'POST',
    url: url,
    data: { 'teacher-id': teacherID, 'teacher-name': teacherName }, // serializes the form's elements.
    success: function(data) {
      var container = '#overview';
      $(container)
        .html(data)
        .promise()
        .done(function() {
          componentHandler.upgradeDom();
        });
    }
  });
});

$(document).on('click', '.join-teacher-consultation', function(e) {
  var consultationID = $(this)
    .parent()
    .parent()
    .attr('data-ct-id');
  var url = 'register-guest.html';

  url = base + url;
  $.ajax({
    url: url,
    success: function(newHTML, textStatus, jqXHR) {
      $(newHTML)
        .appendTo('body')
        .modal()
        .promise()
        .done(function() {
          componentHandler.upgradeDom();
          $('.hidden-ctid-input').val(consultationID);
        });
    },
    error: function(jqXHR, textStatus, errorThrown) {
      // Handle AJAX errors
    }
    // More AJAX customization goes here.
  });
});

$(document).on('click', '.jump-back-btn', function(e) {
  loadContent();
});

$(document).on('submit', '#join-consultation', function(e) {
  if (validate()) {
    var url = 'setter/set-guest-ct.php'; // the script where you handle the form input.
    url = baseScripts + url;
    $.ajax({
      type: 'POST',
      url: url,
      data: $('#join-consultation').serialize(), // serializes the form's elements.
      success: function(data) {
        $.modal.close();
        $('.modal').remove();
        var options = {
          content:
            'Anmeldung erfolgreich. Wir haben dir eine Best&auml;tingungsemail gesendet. Bitte &uuml;berpr&uuml;fe dein Posteingang und <b>kontrolliere auch den Spam Ordner.</b>', // text of the snackbar
          timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
          htmlAllowed: true, // allows HTML as content value
          onClose: function() {} // callback called when the snackbar gets closed.
        };

        $.snackbar(options);
        loadContent();
      }
    });

    e.preventDefault(); // avoid to execute the actual submit of the form.
  } else {
    return false;
  }
});

function validate() {
  if (
    $('#join-consultation')
      .find('input[name="guest_name"]')
      .val() == '' ||
    $('#join-consultation')
      .find('input[name="guest-name"]')
      .val() == ' '
  ) {
    alert('Bitte Namen eintragen!');
    return false;
  }

  var emailVal = $('#join-consultation')
    .find('input[name="guest-email"]')
    .val();
  if (emailVal == '' || emailVal == ' ') {
    alert('Bitte gültige Email eintragen!');
    return false;
  }

  var emailRetryVal = $('#join-consultation')
    .find('input[name="guest-email-retry"]')
    .val();
  if (emailRetryVal == '' || emailRetryVal == ' ') {
    alert('Bitte gültige Email eintragen!');
    return false;
  }
  return true;
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
