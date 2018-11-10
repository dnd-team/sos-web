/*
 * SOS Notifications
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
  var token = getURLParameter('token');
  getDeviceToken(token);
});

function getDeviceToken(token) {
  loadContent();

  function loadContent() {
    var url = apiURL + '/notification/homework/' + token;
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (data['error'] == false) {
          console.log(data['message']);
        } else {
          console.log('Fehler!!!');
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      }
    });
  }
}
