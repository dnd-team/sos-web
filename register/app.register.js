/*
 * SOS Register.js
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
  $(document).on('submit', '#register-form', function(e) {
    var userType = '';
    if ($('#student-panel').hasClass('is-active')) {
      userType = 'Student';
    } else if ($('#teacher-panel').hasClass('is-active')) {
      userType = 'Teacher';
    }

    $('#user-type-input').val(userType);

    e.preventDefault(); // avoid to execute the actual submit of the form.
    $.ajax({
      url: apiURL + '/register',
      data: $('#register-form').serialize(),
      type: 'POST',
      success: function(data) {
        if (data['message'] == 'Passwords do not match.') {
          showSnackbar('Passwörter stimmen nicht überein!');
        } else if (data['message'] == 'Emails do not match.') {
          showSnackbar('Emails stimmen nicht überein!');
        } else if (data['message'] == 'User account already exists.') {
          showSnackbar('Account bereits vorhanden!');
        } else if (data['message'] == 'Wrong schoolcode.') {
          showSnackbar('Falscher Schulcode.');
        } else if (data['message'] == 'Account created successfully.') {
          var success =
            '<h3>Account erfolgreich erstellt!</h3><br><br><br><p>Wir haben dir eine Email mit einem Aktivierungslink für deinen Account geschickt.  Solltest du keine Email von uns erhalten haben, &uuml;berpr&uuml;fe bitte deinen Spam Ordner. Danke!</p>';
          $('#main-card')
            .html(success)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
        showSnackbar('Bitte alle Felder ausf&uuml;llen!');
      }
    });
  });

  function showSnackbar(title) {
    var options = {
      content: title,
      timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
      htmlAllowed: true, // allows HTML as content value
      onClose: function() {} // callback called when the snackbar gets closed.
    };
    $.snackbar(options);
  }

  $(document).on('submit', '#reset-pw', function(e) {
    e.preventDefault(); // avoid to execute the actual submit of the form.

    $.ajax({
      url: apiURL + '/reset-password',
      data: $('#reset-pw').serialize(),
      type: 'POST',
      success: function(data) {
        if (data['message'] == 'Reset email successfully sent.') {
          var options = {
            content: 'Reset Email erfolgreich versendet!',
            timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
            htmlAllowed: true, // allows HTML as content value
            onClose: function() {} // callback called when the snackbar gets closed.
          };
          $.snackbar(options);
          $('#main-card').html(
            '<h3>Danke! Reset Email erfolgreich versendet!</h3>'
          );
        } else {
          var options = {
            content: 'Falsche Email',
            timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
            htmlAllowed: true, // allows HTML as content value
            onClose: function() {} // callback called when the snackbar gets closed.
          };
          $.snackbar(options);
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      }
    });
  });

  $(document).on('click', '#schoolcode-help-btn', function(e) {
    showDialog({
      title: 'Was ist der Schulcode?',
      text:
        'Der Schulcode ist ein mehrstelliger Identifikationscode deiner Schule. Du erhältst ihn von deinem Lehrer / deiner Lehrerin.',
      positive: {
        title: 'OK'
      },
      cancelable: false
    });
  });
});
