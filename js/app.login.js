/*
 * SOS Login
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

  var activeUser = store.get('active-user');

  if (activeUser != null && activeUser != undefined) {
    if (store.get(activeUser).auth) {
      window.location.href = baseURL + '/user/main?site=plan';
    }
  }

  $(document).on('submit', '#login-form', function(e) {
    e.preventDefault();
    $.post(apiURL + '/login', $('#login-form').serialize(), function(data) {
      if (data['error'] == false) {
        if (data['verified'] == '1') {
          store.set('users', [data['token']]);
          var role = data['role'];
          if (role == 'PRINCIPAL') {
            role = 'TEACHER';
          }
          store.set(data['token'], {
            email: data['email'],
            auth: data['apiKey'],
            role: role,
            loggedIn: data['logged_in'],
            joinedSchool: data['joined_school'],
            menuIsOpen: true,
            schoolURL: data['baseURL']
          });
          store.set('active-user', data['token']);
          window.location.href = baseURL + '/user/main?site=plan';
        } else {
          $.snackbar(getSnackbarOptions('Account noch nicht verifiziert!'));
        }
      } else {
        $.snackbar(getSnackbarOptions('Falsche Email oder Passwort'));
      }
    });
  });

  function getSnackbarOptions(title) {
    var options = {
      content: title,
      timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
      htmlAllowed: true, // allows HTML as content value
      onClose: function() {} // callback called when the snackbar gets closed.
    };
    return options;
  }
});
