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
  if (type == 'aushang') {
    getPreview(type);
  } else if (type == 'homework') {
    getPreview(type);
  } else if (type == 'timetable' || type == 'vp' || type == 'website') {
    openURL(type);
  }
});

function parsePreview(data, type) {
  if (type == 'aushang') {
    var aushang = '';

    if (data['title'] == undefined || data['description'] == undefined) {
      aushang =
        '<div class="dialog-center"><h4>Es kann kein Aushang abgerufen werden</h4></div>';
      return aushang;
    }

    var action_html = '';
    if (data['action_type'] == '1') {
      action_html =
        '<a class="mdl-button" href="mailto:' +
        data['action_url'] +
        '" target="_blank">Anbieter kontaktieren</a>';
    } else if (data['action_type'] == '2') {
      action_html =
        '<a class="mdl-button" href="tel:' +
        data['action_url'] +
        '" target="_blank">Anbieter kontaktieren</a>';
    } else if (data['action_type'] == '3') {
      action_html =
        '<a class="mdl-button" href="' +
        data['action_url'] +
        '" target="_blank">Anbieter kontaktieren</a>';
    }
    var hasImage = ['', ''];
    var style = '';
    if (data['image'] != '' || data['image' != '-']) {
      hasImage = ['lazy', data['image']];
      style = 'style="height: 200px;';
    }

    var verMessage = '';
    if (data['verified'] == '0') {
      aushang =
        '<div class="dialog-center"><h4>Anzeige in Pr&uuml;fung</h4></div>';
      return aushang;
    }

    aushang +=
      '<div class="demo-card-wide mdl-card mdl-cell mdl-cell--12-col"><div class="mdl-card__title button-overlay ' +
      hasImage[0] +
      '" data-original="' +
      hasImage[1] +
      '" ' +
      style +
      '"></div><div class="mdl-card__supporting-text"><h4 style="color:black;">' +
      data['title'] +
      '</h4><p style="color:black;">' +
      data['description'] +
      '</p></div><div class="mdl-card__actions mdl-card--border">' +
      action_html +
      '</div></div>';

    return aushang;
  } else if (type == 'homework') {
    var homework = '';
    if (data['title'] == undefined || data['description'] == undefined) {
      homework =
        '<div class="dialog-center"><h4>Es kann keine Hausaufgabe abgerufen werden</h4></div>';
    } else {
      homework =
        '<div class="demo-card-wide mdl-card mdl-cell mdl-cell--12-col"> <div class="mdl-card__supporting-text"> <h4 style="color:black;">' +
        data['title'] +
        '</h4><p style="color:black;">' +
        data['description'] +
        '</p></div> <div class="mdl-card__actions mdl-card--border"></div><div class="mdl-card__menu"> bis zum ' +
        parseDate(data['expire_date']) +
        '</div> </div>';
    }
    return homework;
  }
}

//Preview Core Features
function getPreview(type) {
  loadContent();

  function loadContent() {
    var token = getURLParameter('token');
    var url = apiURL + '/preview/';
    if (type == 'aushang') {
      url += 'aushang/' + token;
    } else if (type == 'homework') {
      console.log('hier');
      url += 'homework/' + token;
    }
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (data['error'] == false) {
          data = parsePreview(data[type], type);
        } else {
          if (type == 'homework') {
            data =
              '<div class="dialog-center"><h4>Es kann keine Hausaufgabe abgerufen werden</h4></div>';
          } else if (type == 'aushang') {
            data =
              '<div class="dialog-center"><h4>Es kann kein Aushang abgerufen werden</h4></div>';
          }
        }
        var container = '#overview';
        $(container)
          .html(data)
          .promise()
          .done(function() {
            componentHandler.upgradeDom();
          });
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      }
    });
  }
}

function openURL(type) {
  var token = getURLParameter('token');
  if (token != '' && token != ' ' && token != '  ' && token != undefined) {
    if (type == 'timetable') {
      var userType = getURLParameter('for');
      if (userType != undefined) {
        if (userType == 'teacher') {
          var url = apiURL + '/' + token + '/timetable/share/teacher';
          loadContent(token, url);
        } else if (userType == 'student') {
          var url = apiURL + '/' + token + '/timetable/share/student';
          var grade = getURLParameter('grade');
          var grades = [
            '5a',
            '6a',
            '7a',
            '7b',
            '7c',
            '7d',
            '7e',
            '8a',
            '8b',
            '8c',
            '8d',
            '8e',
            '9a',
            '9b',
            '9c',
            '9d',
            '9e',
            '10a',
            '10b',
            '10c',
            '10d',
            '10e',
            '11',
            '12'
          ];
          if (grade != undefined) {
            loadContent(token, url, grades.indexOf(grade) + 1);
          } else {
            loadContent(token, url, 0);
          }
        } else if (userType == 'room') {
          var url = apiURL + '/' + token + '/timetable/share/room';
          loadContent(token, url, 0);
        }
      }
    } else if (type == 'vp') {
      var daynumber = getURLParameter('day');
      if (daynumber != undefined && !isNaN(daynumber)) {
        var url = apiURL + '/' + token + '/vp/share/' + daynumber;
        loadContent(token, url, 0);
      }
    } else {
      var url = apiURL + '/school/' + token + '/website';
      loadContent(token, url, 0);
    }
  }

  function loadContent(token, url, page) {
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (data['error'] == false) {
          window.location.href = data['url'] + '#page=' + page;
        } else {
          console.log('Error');
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      }
    });
  }
}
