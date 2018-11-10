/*
 * SOS Main.js
 * version: 0.2
 * @requires jQuery
 *
 * License: MIT License
 * Copyright 2016-2018 Dominik Scherm - <dominik@dnddev.com>
 *
 */

//Global init
var baseURL = '';
var apiURL = '';
var menuVisible = '';
var activeUser = '';

var isDEV = false;
if (isDEV) {
  baseURL = '';
  apiURL = '';
}

function init() {
  activeUser = store.get('active-user');

  if (activeUser != null) {
    if (!store.get(activeUser).auth) {
      window.location.href = baseURL + '/login';
    } else {
      $('body').removeClass('isHidden');

      $.modal.defaults = {
        closeExisting: true, // Close existing modals. Set this to false if you need to stack multiple modal instances.
        escapeClose: true, // Allows the user to close the modal by pressing `ESC`
        clickClose: true, // Allows the user to close the modal by clicking the overlay
        closeText: 'Abbrechen', // Text content for the close <a> tag.
        closeClass: '', // Add additional class(es) to the close <a> tag.
        showClose: false, // Shows a (X) icon/link in the top-right corner
        modalClass: 'modal', // CSS class added to the element being displayed in the modal.
        spinnerHtml: null, // HTML appended to the default spinner during AJAX requests.
        showSpinner: true, // Enable/disable the default spinner during AJAX requests.
        fadeDuration: null, // Number of milliseconds the fade transition takes (null means no transition)
        fadeDelay: 1.0 // Point during the overlay's fade-in that the modal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
      };

      var a = document.getElementsByTagName('a');
      for (var i = 0; i < a.length; i++) {
        a[i].onclick = function() {
          window.location = this.getAttribute('href');
          return false;
        };
      }

      //Deactive auto discover
      Dropzone.autoDiscover = false;

      updateData();
    }
  } else {
    window.location.href = baseURL + '/login';
  }
}

function updateData() {
  $.ajax({
    url: apiURL + '/update',
    type: 'get',
    success: function(data, status) {
      console.log(data);

      if (
        data['update']['joined_school'] != store.get(activeUser).joinedSchool
      ) {
        store.transact(activeUser, function(value) {
          value.joinedSchool = data['update']['joined_school'];
        });
      }

      if (data['update']['logged_in'] != store.get(activeUser).loggedIn) {
        store.transact(activeUser, function(value) {
          value.loggedIn = data['update']['logged_in'];
        });
      }

      if (data['update']['school_url'] != store.get(activeUser).schoolURL) {
        store.transact(activeUser, function(value) {
          value.schoolURL = data['update']['school_url'];
        });
      }

      if (!store.get(activeUser).joinedSchool) {
        initSchoolSelection(false);
      } else {
        userOnboarding();
      }
    },
    error: function(xhr, desc, err) {
      //console.log(xhr);
      //console.log("Details: " + desc + "\nError:" + err);
    },
    headers: { auth: store.get(activeUser).auth }
  });
}

$.fn.addLoadingSpinner = function() {
  var loadingSpinner =
    '<div class="dialog-center"><div class="mdl-spinner mdl-js-spinner is-active"></div></div>';
  $(this)
    .html(loadingSpinner)
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
};

$.fn.validateForm = function() {
  foundError = false;
  $(this)
    .find(':input')
    .each(function() {
      if (!foundError) {
        if ($(this).attr('data-is-required') != undefined) {
          var v = $(this).val();
          if (v == '' || v == ' ' || v == undefined) {
            $.snackbar(getSnackbarOptions('Bitte alle Felder ausfüllen!'));
            foundError = true;
          }
        }
      }
    });
  return !foundError;
};

function initSchoolSelection(cancelable) {
  index = 0;
  $('.mdl-layout__container').css({ display: 'none' });
  var cities = [];
  var cityIds = [];

  initFederalStateSelection();

  $(document).on('click', '.open-school-registration-btn', function(e) {
    registerNewSchool();
  });

  $(document).on('click', '#register-school-back-btn', function(e) {
    e.preventDefault();
    initFederalStateSelection();
  });

  $(document).on('click', '.back-btn', function(e) {
    e.preventDefault();
    if (index == 2) {
      initFederalStateSelection();
    } else if (index == 3) {
      initCitySelection(store.get('selected-state'));
    } else if (index == 4) {
      initSchoolSelection(store.get('selected-city'));
    }
  });

  $(document).on('click', '#register-school-btn', function(e) {
    console.log($('#register-school').serialize());
    e.preventDefault();
    $.ajax({
      url: apiURL + '/register/school',
      type: 'post',
      data: $('#register-school').serialize(),
      success: function(data, status) {
        console.log('School registered successfully!');
        if (data['message'] == 'School registered successfully.') {
          $.snackbar(
            getSnackbarOptions(
              'Wir haben deine Schul registriert. Wir werden uns in Kürze bei dir melden!'
            )
          );
          registerForDemoSchool();
        } else {
          $.snackbar(
            getSnackbarOptions('Oh, bitte fülle alle Textfelder aus !')
          );
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  });

  function registerForDemoSchool() {
    $.ajax({
      url: apiURL + '/register/school/demo',
      type: 'post',
      success: function(data, status) {
        console.log(data);
        if (data['message'] == 'School selection saved successfully.') {
          $('.mdl-layout__container').css({ display: 'none' });
          $.snackbar(
            getSnackbarOptions('Du wurdetst der Demoschule hinzugefügt !')
          );
          store.transact(activeUser, function(value) {
            value.joinedSchool = true;
          });
          userOnboarding();
        } else {
          $.snackbar(getSnackbarOptions('Es ist ein Fehler aufgetreten!'));
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  function registerNewSchool() {
    var content =
      '<form class="modal" role="form" id="register-school"> <div class="mdl-card__supporting-text"> <h4>Deine Schule für SOS registrieren</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="school-name" name="school_name"> <label class="mdl-textfield__label" for="school-name">Name deiner Schule</label> </div> </div><div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="school-contact" name="email"> <label class="mdl-textfield__label" for="school-contact">Email/Telefon</label> </div> </div><div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="school-contact-person" name="teacher"> <label class="mdl-textfield__label" for="school-contact-person">Ansprechpartner</label> </div> </div><div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="school-address" name="school_plz"> <label class="mdl-textfield__label" for="school-address">Ort/PLZ deiner Schule</label> </div> </div> </div> <div class="mdl-card__actions"> <button id="register-school-back-btn" class="mdl-button">Zurück</button><button type="submit" id="register-school-btn" class="mdl-button">Schule registrieren</button> </div> </form>';
    $(content)
      .appendTo('body')
      .modal({
        escapeClose: cancelable,
        clickClose: cancelable
      })
      .promise()
      .done(function() {
        componentHandler.upgradeDom();
      });
  }

  function initFederalStateSelection() {
    index = 1;
    loadContent();
    function loadContent() {
      $.ajax({
        url: apiURL + '/register/cities',
        type: 'get',
        success: function(data, status) {
          console.log(data);
          data = parseStateAndCitySelection(data['federal_states'], '');
          $(data)
            .appendTo('body')
            .modal({
              escapeClose: cancelable,
              clickClose: cancelable
            })
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        },
        error: function(xhr, desc, err) {
          //console.log(xhr);
          //console.log("Details: " + desc + "\nError:" + err);
        },
        headers: { auth: store.get(activeUser).auth }
      });
    }

    $(document).on('click', '#done-federal-state-selection', function(e) {
      e.preventDefault();
      var selectedState = $('input[name=options]:checked').val();
      store.set('selected-state', selectedState);
      initCitySelection(selectedState);
    });

    function parseStateAndCitySelection(data, userState) {
      var states =
        '<div class="modal" id="state-selection"> <div class="mdl-card__supporting-text"> <h4>Bitte wähle dein Bundesland aus:</h4> <div class="mdl-grid action-type"><ul style="list-style: none;">';
      var lastState = '';
      for (var state in data) {
        if (lastState != state) {
          cities[state] = [];
          cityIds[state] = [];
        }
        var currentState = state;
        var checked = 'checked';
        if (userState) {
          if (currentState == userState) {
            checked = 'checked';
          }
        }

        states +=
          '<li> <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-' +
          currentState +
          '"> <input type="radio" id="option-' +
          currentState +
          '" class="mdl-radio__button" name="options" ' +
          checked +
          '  value="' +
          currentState +
          '"> <span class="mdl-radio__label">' +
          currentState +
          '</span> </label> </li>';

        data[state].forEach(function(city) {
          cities[state].push(city['name']);
          cityIds[state].push(city['id']);
        });

        lastState = state;
      }

      states +=
        '</ul></div><button class="mdl-button" id="done-federal-state-selection">Weiter zur Stadtauswahl</button><button class="mdl-button open-school-registration-btn">Dein Bundesland ist nicht dabei?</button></div>';
      return states;
    }
  }

  function initCitySelection(state) {
    index = 2;
    presentCitySelection('');

    $(document).on('click', '#done-city-selection', function(e) {
      e.preventDefault();
      var cityId = $('input[name=options]:checked').val();
      store.set('selected-city', cityId);
      initSchoolSelection(cityId);
    });

    function presentCitySelection(city) {
      var citySelection =
        '<div class="modal" id="city-selection"> <div class="mdl-card__supporting-text"> <h4>Bitte wähle deine Stadt aus:</h4> <div class="mdl-grid action-type"><ul style="list-style: none;">';
      for (i = 0; i < cities[state].length; i++) {
        var currentCity = cities[state][i];
        var checked = '';
        if (city) {
          if (currentCity == city) {
            checked = 'checked';
          }
        } else if (i == 0) {
          checked = 'checked';
        }

        citySelection +=
          '<li> <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-' +
          currentCity +
          '"> <input type="radio" id="option-' +
          currentCity +
          '" class="mdl-radio__button" name="options" ' +
          checked +
          '  value="' +
          cityIds[state][i] +
          '"> <span class="mdl-radio__label">' +
          currentCity +
          '</span> </label> </li>';
      }
      citySelection +=
        '</ul></div><button class="mdl-button back-btn">Zurück</button><button class="mdl-button" id="done-city-selection">Weiter zur Schulauswahl</button><button class="mdl-button open-school-registration-btn">Deine Stadt ist nicht in der Liste?</button></div>';

      $(citySelection)
        .appendTo('body')
        .modal({
          escapeClose: cancelable,
          clickClose: cancelable
        })
        .promise()
        .done(function() {
          componentHandler.upgradeDom();
        });
    }
  }

  function initSchoolSelection(cityId) {
    index = 3;
    loadContent();

    function loadContent() {
      $.ajax({
        url: apiURL + '/register/' + cityId + '/schools',
        type: 'get',
        success: function(data, status) {
          console.log(data);
          data = parseSchoolSelection(data['schools'], '');
          $(data)
            .appendTo('body')
            .modal({
              escapeClose: cancelable,
              clickClose: cancelable
            })
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        },
        error: function(xhr, desc, err) {
          //console.log(xhr);
          //console.log("Details: " + desc + "\nError:" + err);
        },
        headers: { auth: store.get(activeUser).auth }
      });
    }

    $(document).on('click', '#done-school-selection', function(e) {
      e.preventDefault();
      var schoolId = $('input[name=options]:checked').val();
      initSchoolVerification(schoolId, cancelable);
    });

    function parseSchoolSelection(data, school) {
      var schoolSelection =
        '<div class="modal" id="school-selection"> <div class="mdl-card__supporting-text"> <h4>Bitte wähle deine Schule aus:</h4> <div class="mdl-grid action-type"><ul style="list-style: none;">';
      var i = 0;
      data.forEach(function(key) {
        var currentSchool = key['name'];
        checked = '';
        if (school) {
          if (currentSchool == school) {
            checked = 'checked';
          }
        } else {
          if (i == 0) {
            checked = 'checked';
          }
        }

        schoolSelection +=
          '<li> <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-' +
          currentSchool +
          '"> <input type="radio" id="option-' +
          currentSchool +
          '" class="mdl-radio__button" name="options" ' +
          checked +
          '  value="' +
          key['id'] +
          '"> <span class="mdl-radio__label">' +
          currentSchool +
          '</span> </label> </li>';
      });

      schoolSelection +=
        '</ul></div><button class="mdl-button back-btn">Zurück</button><button class="mdl-button" id="done-school-selection">Weiter zur Eingabe des Schulpassworts</button><button class="mdl-button open-school-registration-btn">Deine Schule ist nicht dabei?</button></div>';

      return schoolSelection;
    }
  }

  function initSchoolVerification(schoolId) {
    index = 4;
    enterSchoolPassword();

    function enterSchoolPassword() {
      var content =
        '<form class="modal" role="form" method="post" id="school-password-input"> <div class="mdl-card__supporting-text"> <h4>Schulpasswort eingeben</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="password" id="school-password" name="password"> <label class="mdl-textfield__label" for="title">Schulpasswort</label> </div></div> <div class="mdl-card__actions"> <button  class="mdl-button back-btn">Zurück</button><button type="submit" id="check-school-password" class="mdl-button">Bestätigen</button> </div> </form>';
      $(content)
        .appendTo('body')
        .modal({
          escapeClose: cancelable,
          clickClose: cancelable
        })
        .promise()
        .done(function() {
          componentHandler.upgradeDom();
        });
    }

    $(document).on('click', '#check-school-password', function(e) {
      e.preventDefault();
      var schoolPassword = $('#school-password').val();
      verifySchool(schoolPassword);
    });

    function verifySchool(schoolPassword) {
      $.ajax({
        url: apiURL + '/register/school/verify',
        type: 'post',
        data: { school_token: schoolId, school_password: schoolPassword },
        success: function(data, status) {
          if (data['message'] == 'School selection saved successfully.') {
            $('.mdl-layout__container').css({ display: 'none' });
            $.snackbar(
              getSnackbarOptions('Schulauswahl erfolgreich abgeschlossen !')
            );
            store.transact(activeUser, function(value) {
              value.joinedSchool = true;
            });
            userOnboarding();
          } else {
            $.snackbar(
              getSnackbarOptions(
                'Schulpasswort ist nicht korrekt. Bitte probiere es erneut !'
              )
            );
          }
        },
        error: function(xhr, desc, err) {
          console.log(xhr);
          console.log('Details: ' + desc + '\nError:' + err);
        },
        headers: { auth: store.get(activeUser).auth }
      });
    }
  }
}

function userOnboarding() {
  if (!store.get('hasRegisteredForNotif')) {
    registerDeviceForNotif();
  }
  if (!store.get(activeUser).loggedIn) {
    if (store.get('onboarding-index')) {
      showUserOnboarding(store.get('onboarding-index'));
    } else {
      showUserOnboarding(0);
    }
  } else {
    $('#username-label').text(store.get(activeUser).email);
    updateUserList();

    $('#homework-label').text('Aufgaben');
    getCourses();
    menuVisible = store.get(activeUser).menuIsOpen;

    if (menuVisible) {
      $('#course-list').css({ display: 'block' });
      $('#course-menu-icon').html('&#xE5C7;');
    } else {
      $('#course-list').css({ display: 'none' });
      $('#course-menu-icon').html('&#xE5C5;');
    }
  }
}

function showUserOnboarding(index) {
  var texts = [
    'Diese Plattform wird dir dabei helfen, deinen Schulalltag besser zu organisieren.',
    'Verbinde dich mit deinen Mitschülern und erhalte Aufträge und Hausaufgaben von deinen Lehrern',
    'SOS erstellt dir deinen individuellen Stundenplan, mit all deinen Ausfällen und Vertretungen.',
    'Erfahre was in deiner Schule läuft.',
    'Aufträge mit ihren Deadlines und benötigtem Material sind jetzt direkt von der App aus einsehbar.'
  ];
  var headlines = [
    'Willkommen !',
    'Einfache Kommunikation',
    'Maßgeschneidert',
    'Das Wichtigste auf einen Blick',
    'Aufträge neu erfunden'
  ];
  var colors = ['#73cdec', '#61aab6', '#9e92c0', '#f3da9d', '#536dfe'];
  var fontColors = ['#ffffff', '#ffffff', '#ffffff', '#2C2B2B', '#ffffff'];
  var images = ['start', 'pupil', 'notes', 'news', 'check', 'start'];

  var backBtn = '';
  if (index != 0) {
    backBtn =
      '<button class="mdl-button mdl-js-button mdl-button--primary" id="back-' +
      (index + 1) +
      '">Zur&uuml;ck</button>';
  }

  var onboarding =
    '<div class="modal" id="user-onboarding"><div class="mdl-grid" id="text-wrapper" style="background-color:' +
    colors[index] +
    ';"><div class="mdl-cell mdl-cell--4-col"><div class="icon-holder-tutorial"><img src="../images/tutorial/' +
    images[index] +
    '.png"></div></div><div class="mdl-cell mdl-cell--8-col"><h4 style="color:' +
    fontColors[index] +
    ';">' +
    headlines[index] +
    '</h4><br><p style="color:' +
    fontColors[index] +
    ';">' +
    texts[index] +
    '</p>' +
    backBtn +
    '<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" id="continue-' +
    (index + 1) +
    '">Weiter</button></div></div></div>';

  $(document).on('click', '#back-' + (index + 1), function() {
    showUserOnboarding(index - 1);
  });

  if (index == 4) {
    $(document).on('click', '#continue-5', function() {
      $.modal.close();
      if (store.get(activeUser).role == 'STUDENT') {
        initGradeSelection(false, '');
      } else {
        initTeacherSelection(false);
      }
    });
  } else {
    $(document).on('click', '#continue-' + (index + 1), function() {
      showUserOnboarding(index + 1);
    });
  }

  $(onboarding)
    .appendTo('body')
    .modal({
      escapeClose: false,
      clickClose: false
    })
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
      store.set('onboarding-index', index);
    });
}

function registerDeviceForNotif() {
  OneSignal.push(function() {
    OneSignal.getUserId(function(userId) {
      $.ajax({
        url: apiURL + '/notifications/register',
        type: 'post',
        data: { device_token: userId },
        success: function(data, status) {
          //console.log(data);
          //console.log("Sucessfully registered for notifications!")
        },
        error: function(xhr, desc, err) {
          //console.log(xhr);
          //console.log("Details: " + desc + "\nError:" + err);
        },
        headers: { auth: store.get(activeUser).auth }
      });
    });
  });
  store.set('hasRegisteredForNotif', true);
}

function updateUserList() {
  var users = store.get('users');
  var content = '';
  for (i = 0; i < users.length; i++) {
    var active = '';
    if (users[i] == activeUser) {
      active = 'active-user';
    }
    content +=
      '<li class="mdl-menu__item account-btn ' +
      active +
      '" data-user-name="' +
      users[i] +
      '">' +
      store.get(users[i]).email +
      '</li>';
  }

  var logoutAll = '';
  if (users.length > 1) {
    logoutAll =
      '<li class="mdl-menu__item" id="all-logout-btn">Alle Abmelden</li>';
  }

  content +=
    '<li class="mdl-menu__item" id="add-account-btn"><i class="material-icons">add</i>Account hinzuf&uuml;gen...</li><li class="mdl-menu__item" id="account-logout-btn">Abmelden</li>' +
    logoutAll;

  $('#user-list').html(content);
}

//Add Account Btn

$(document).on('click', '#add-account-btn', function(e) {
  e.preventDefault();
  addAccount();
});

$(document).on('click', '.account-btn', function(e) {
  e.preventDefault();
  var name = $(this).attr('data-user-name');
  switchAccount(name);
});

function switchAccount(name) {
  //console.log(name);
  store.set('active-user', name);
  location.reload();
  if (getURLParameter('site') == 'courses') {
    window.location.href = baseURL + '/user/main?site=plan';
  }
}

// Get user courses
$(document).on('click', '#course-btn', function(e) {
  e.preventDefault();
  if (menuVisible) {
    $('#course-list').css({ display: 'none' });
    $('#course-menu-icon').html('&#xE5C5;');
    menuVisible = false;
    store.transact(activeUser, function(value) {
      value.menuIsOpen = menuVisible;
    });
    return;
  }
  $('#course-list').css({ display: 'block' });
  $('#course-menu-icon').html('&#xE5C7;');
  menuVisible = true;
  store.transact(activeUser, function(value) {
    value.menuIsOpen = menuVisible;
  });
});

function getCourses() {
  $.ajax({
    url: apiURL + '/courses',
    type: 'get',
    success: function(data, status) {
      data = parseCourseList(data['courses']);
      $('#course-list').html(data);
      var courseName = getURLParameter('name');
      $('.course-item:contains(' + courseName + ')').addClass('active');
    },
    error: function(xhr, desc, err) {
      //console.log(xhr);
      //console.log("Details: " + desc + "\nError:" + err);
    },
    headers: { auth: store.get(activeUser).auth }
  });
}

$(document).on('click', '#account-logout-btn', function(e) {
  OneSignal.push(function() {
    var deviceToken = '-';
    OneSignal.getUserId(function(userId) {
      if (userId != undefined) {
        deviceToken = userId;
      }
    });
    $.ajax({
      url: apiURL + '/logout',
      type: 'post',
      data: { device_token: deviceToken },
      success: function(data, status) {
        //console.log(data);
        if (data['message'] == 'User successfully logged out.') {
          store.remove(activeUser);
          var users = store.get('users');

          var index = users.indexOf(activeUser);
          if (index > -1) {
            users.splice(index, 1);
          }
          activeUser = users[0];
          store.set('active-user', activeUser);
          store.set('users', users);
          store.set('hasRegisteredForNotif', false);
          location.reload();
        } else {
          //console.log("Cannot logout user");
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  });
});

$(document).on('click', '#all-logout-btn', function(e) {
  var users = store.get('users');
  var userLength = users.length;
  for (var i = 0; i < userLength; i++) {
    var currentUser = users[i];
    var index = i;
    OneSignal.push(function() {
      OneSignal.getUserId(function(userId) {
        var deviceToken = userId;
        //console.log(userId);
        if (userId == undefined) {
          deviceToken = '-';
        }
        $.ajax({
          url: apiURL + '/logout',
          type: 'post',
          data: { device_token: deviceToken },
          success: function(data, status) {
            //console.log(data);
            if (data['message'] == 'User successfully logged out.') {
              if (index == userLength - 1) {
                store.clear();
                location.reload();
              }
            } else {
              //console.log("Cannot logout user");
            }
          },
          error: function(xhr, desc, err) {
            //console.log(xhr);
            //console.log("Details: " + desc + "\nError:" + err);
          },
          headers: { auth: store.get(currentUser).auth }
        });
      });
    });
  }
});

// Course + Grade + Teacher Selection

function initGradeSelection(cancelable, grade) {
  loadContent();

  function loadContent() {
    $.ajax({
      url: apiURL + '/school/grades',
      type: 'get',
      success: function(data, status) {
        data = parseGradeSelection(data['grades'], grade);
        $(data)
          .appendTo('body')
          .modal({
            escapeClose: cancelable,
            clickClose: cancelable
          })
          .promise()
          .done(function() {
            componentHandler.upgradeDom();
          });
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '#done-grade-selection', function(e) {
    e.preventDefault();
    var grade = $('input[name=options]:checked').val();
    initCourseSelection(grade, cancelable);
  });

  function parseGradeSelection(data, grade) {
    var grades =
      '<div class="modal" id="grade-selection"> <div class="mdl-card__supporting-text"> <h4>Bitte wähle deine Klasse aus:</h4> <div class="mdl-grid action-type"><ul style="list-style: none;">';
    for (i = 0; i < data.length; i++) {
      var currentGrade = data[i];
      var checked = '';
      if (grade) {
        if (currentGrade == grade) {
          checked = 'checked';
        }
      } else if (i == 0) {
        checked = 'checked';
      }

      grades +=
        '<li> <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-' +
        currentGrade +
        '"> <input type="radio" id="option-' +
        currentGrade +
        '" class="mdl-radio__button" name="options" ' +
        checked +
        '  value="' +
        currentGrade +
        '"> <span class="mdl-radio__label">' +
        currentGrade +
        '</span> </label> </li>';
    }
    grades +=
      '</ul></div><button class="mdl-button" id="done-grade-selection">Weiter zur Kursauwahl</button></div>';

    return grades;
  }
}

function initCourseSelection(grade, cancelable) {
  var courseIDs = [];
  loadContent();

  function loadContent() {
    $.ajax({
      url: apiURL + '/school/' + grade + '/courses',
      type: 'get',
      success: function(data, status) {
        console.log(data);
        var selectCourses = false;
        if (cancelable) {
          selectCourses = true;
        }
        data = parseCourseSelection(data['courses'], grade, selectCourses);
        $(data)
          .appendTo('body')
          .modal({
            escapeClose: cancelable,
            clickClose: cancelable
          })
          .promise()
          .done(function() {
            componentHandler.upgradeDom();
          });
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document)
    .off()
    .on('click', '#done-course-selection', function(e) {
      e.preventDefault();

      $('input:checkbox:checked').each(function(i) {
        var id = $(this)
          .parent()
          .parent()
          .parent()
          .attr('data-course-id');
        console.log(id);
        if ($.inArray(id, courseIDs) == -1) {
          courseIDs[i] = id;
        }
      });
      saveStudentSelection(grade, courseIDs);
    });

  function saveStudentSelection(grade, courseIDs) {
    var course_list = courseIDs.toString();
    course_list = course_list.replace(/^,/, '');
    console.log(course_list);
    $.ajax({
      url: apiURL + '/school/' + grade + '/courses/save',
      type: 'post',
      data: { course_list: course_list },
      success: function(data, status) {
        $.modal.close();
        $('.modal').remove();
        $('input:checkbox').removeAttr('checked');
        $(document).off('click', '#back-btn');
        $(document).off('click', '#done-course-selection');
        store.transact(activeUser, function(value) {
          value.loggedIn = true;
        });
        getCourses();
        courseIDs = [];
        if (cancelable) {
          initSettingsSite();
        } else {
          location.reload();
        }
      },
      error: function(xhr, desc, err) {
        $.modal.close();
        $('.modal').remove();
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  function parseCourseSelection(data, grade, selectCourses) {
    var courseIDs;
    if (selectCourses) {
      courseIDs = [];
      $('.course-item').each(function(i) {
        courseIDs[i] = $(this).attr('data-course-id');
      });
    }
    var courses =
      '<div class="modal" id="course-selection"><table class="course-list mdl-data-table mdl-js-data-table mdl-shadow--2dp mdl-data-table--selectable"> <caption> <h3>' +
      grade +
      '</h3></caption> <thead> <tr> <th class="mdl-data-table__cell--non-numeric">Kursname</th> <th>Kurs-Abkürzung</th> <th>Lehrer/Tutor</th></tr> </thead> <tbody>';
    data.forEach(function(key) {
      var selectedClass = '';
      if (selectCourses) {
        if ($.inArray(String(key['id']), courseIDs) > -1) {
          selectedClass = 'class="is-selected"';
        }
      }
      courses +=
        '<tr data-course-id="' +
        key['id'] +
        '" ' +
        selectedClass +
        '> <td class="mdl-data-table__cell--non-numeric">' +
        key['description'] +
        '</td> <td>' +
        key['name'] +
        '</td> <td>' +
        key['teacher'] +
        '</td></tr>';
    });
    courses +=
      '</tbody></table><div class="mdl-card__actions"><button class="mdl-button" id="back-btn">Zurück</button>  <button type="submit" id="done-course-selection" class="mdl-button">Speichern</button> </div></div>';

    $(document).on('click', '#back-btn', function(e) {
      e.preventDefault();
      initGradeSelection(cancelable, grade);
    });

    return courses;
  }
}

function initTeacherSelection(cancelable) {
  loadContent();

  function loadContent() {
    $.ajax({
      url: apiURL + '/school/teachers',
      type: 'get',
      success: function(data, status) {
        data = parseTeacherSelection(data['teachers']);
        $(data)
          .appendTo('body')
          .modal({
            escapeClose: cancelable,
            clickClose: cancelable
          })
          .promise()
          .done(function() {
            componentHandler.upgradeDom();
          });
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '#done-teacher-selection', function(e) {
    e.preventDefault();
    var teacherID = $('input[name=options]:checked').val();
    //console.log(teacherID);
    saveTeacherSelection(teacherID);
  });

  function saveTeacherSelection(teacherID) {
    $.ajax({
      url: apiURL + '/school/teachers/save',
      type: 'post',
      data: { teacher_id: teacherID },
      success: function(data, status) {
        $.modal.close();
        $('.modal').remove();
        store.transact(activeUser, function(value) {
          value.loggedIn = true;
        });
        getCourses();
        if (cancelable) {
          initSettingsSite();
        } else {
          location.reload();
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  function parseTeacherSelection(data) {
    var teachers =
      '<div class="modal" id="teacher-selection"> <div class="mdl-card__supporting-text"> <h4>Lehrerkürzel auswählen</h4> <div class="mdl-grid action-type"> <p>Lehrerkürzel:</p> <ul style="list-style: none;">';
    for (i = 0; i < data.length; i++) {
      var currentTeacher = data[i];
      var checked = '';
      if (i == 0) {
        checked = 'checked';
      }
      teachers +=
        '<li> <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-' +
        currentTeacher +
        '"> <input type="radio" id="option-' +
        currentTeacher +
        '" class="mdl-radio__button" name="options" ' +
        checked +
        '  value="' +
        currentTeacher +
        '"> <span class="mdl-radio__label">' +
        currentTeacher +
        '</span> </label> </li>';
    }
    teachers +=
      '</ul></div><button class="mdl-button mdl-js-button mdl-js-ripple-effect" id="done-teacher-selection">Speichern</button></div>';

    return teachers;
  }
}

/* Timetable Functions */
function parseTimetable(data, isCurrentVP, lastUpdated) {
  var notCurrentVPWarning = '';
  if (!isCurrentVP) {
    lastUpdated = parseDate(lastUpdated);
    notCurrentVPWarning =
      '<section class="section--center mdl-grid warning-dialog"><p>Plan noch nicht aktuell. Letzte Aktualisierung am ' +
      lastUpdated +
      '</p></section>';
  }

  var timetable = notCurrentVPWarning;

  if (store.get(activeUser).role != 'TEACHER') {
    data.forEach(function(key) {
      var entry_class = '';
      if (key['lesson_type'] != 'block') {
        entry_class = 'half-entry';
      }
      var between = ' bei ';
      if (key['headline'] == 'Kein Unterricht.') {
        between = '';
      }
      timetable +=
        '<section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp ' +
        entry_class +
        '" data-timetable-id="' +
        key['oid'] +
        '" data-timetable-day="' +
        key['day'] +
        '" data-timetable-time="' +
        key['time'] +
        ', ' +
        key['period'] +
        '. Block" data-timetable-description="' +
        key['description'] +
        '" data-timetable-teacher="' +
        key['initials'] +
        '" data-timetable-headline="' +
        key['headline'] +
        '" data-timetable-subject="' +
        key['lesson'] +
        '"><div class="mdl-card mdl-cell mdl-cell--12-col timetable-entry-wrapper" style="border-left: 10px solid ' +
        key['color'] +
        ';"><div class="mdl-card__supporting-text"><h4 style="color:' +
        key['color'] +
        ';">' +
        key['headline'] +
        '</h4>' +
        key['description'] +
        between +
        key['initials'] +
        '</div><div class="mdl-card__actions mdl-card--border"><button class="mdl-button timetable-more-btn">Mehr Informationen</button></div><div class="mdl-card__menu"><h6>' +
        key['time'] +
        '</h6><p>' +
        key['duration'] +
        '   Minuten</p><p>' +
        key['period'] +
        '. Block</p></div</div></section>';
    });
  } else {
    data.forEach(function(key) {
      var grade = '';
      //console.log(key["initials"]);
      if (key['initials'] != '' && key['initials'] != ' ') {
        grade = key['initials'] + ' ';
      }
      //console.log(grade);

      timetable +=
        '<section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp" data-timetable-id="' +
        key['oid'] +
        '" data-timetable-day="' +
        key['day'] +
        '" data-timetable-time="' +
        key['time'] +
        ', ' +
        key['period'] +
        '. Block" data-timetable-description="' +
        key['description'] +
        '" data-timetable-teacher="' +
        key['initials'] +
        '" data-timetable-headline="' +
        key['headline'] +
        '" data-timetable-subject="' +
        key['lesson'] +
        '"><div class="mdl-card mdl-cell mdl-cell--12-col timetable-entry-wrapper" style="border-left: 10px solid ' +
        key['color'] +
        ';"><div class="mdl-card__supporting-text"><h4 style="color:' +
        key['color'] +
        ';">' +
        key['headline'] +
        '</h4>' +
        key['description'] +
        ' ' +
        key['initials'] +
        '</div><div class="mdl-card__actions mdl-card--border"><button class="mdl-button timetable-more-btn">Mehr Informationen</button></div><div class="mdl-card__menu"><h6>' +
        key['time'] +
        '</h6><p>' +
        key['duration'] +
        ' Minuten</p><p>' +
        key['period'] +
        '. Block</p></div</div></section>';
    });
  }
  return timetable;
}

function getDetailView(headline, subject, time, teacher, desc) {
  var content =
    '<div class="modal" id="timetable-more"><div class="mdl-card__supporting-text"><h4 id="timetable-headline">' +
    headline +
    '</h4><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Unterrichtsfach: ' +
    subject +
    '</h5></div></div><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Zeit: ' +
    time +
    '</h5></div></div><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Lehrer: ' +
    teacher +
    '</h5></div></div><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Beschreibung: ' +
    desc +
    '</h5></div></div></div><div class="mdl-card__actions"><button id="close-more-popup" class="mdl-button">Schliessen</button></div></div>';
  if (store.get(activeUser).role == 'TEACHER') {
    content =
      '<div class="modal" id="timetable-more"><div class="mdl-card__supporting-text"><h4 id="timetable-headline">' +
      headline +
      '</h4><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Unterrichtsfach: ' +
      subject +
      '</h5></div></div><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Zeit: ' +
      time +
      '</h5></div></div><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Klasse: ' +
      teacher +
      '</h5></div></div><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><h5>Beschreibung: ' +
      desc +
      '</h5></div></div></div><div class="mdl-card__actions"><button id="close-more-popup" class="mdl-button">Schliessen</button></div></div>';
  }

  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
}

/* Courses Functions */

function parseCourseList(data) {
  var courses = '';
  data.forEach(function(key) {
    courses +=
      '<a class="mdl-navigation__link course-item" href="main?site=courses&cname=' +
      key['id'] +
      '&name=' +
      key['description'] +
      '" data-course-id="' +
      key['id'] +
      '"><i class="mdl color-text--blue-grey-400 material-icons folder-icon" role="presentation">folder</i>' +
      key['description'] +
      '</a>';
  });
  return courses;
}

/* Aushang Functions */

function parseAushang(data) {
  var aushang = [];
  data.forEach(function(key) {
    var action_html = '';
    if (key['action_type'] == '1') {
      action_html =
        '<a class="mdl-button" href="mailto:' +
        key['action_url'] +
        '" target="_blank">Anbieter kontaktieren</a>';
    } else if (key['action_type'] == '2') {
      action_html =
        '<a class="mdl-button" href="tel:' +
        key['action_url'] +
        '" target="_blank">Anbieter kontaktieren</a>';
    } else if (key['action_type'] == '3') {
      action_html =
        '<a class="mdl-button" href="' +
        key['action_url'] +
        '" target="_blank">Anbieter kontaktieren</a>';
    }
    var hasImage = ['', ''];
    var style = '';
    var color = '';
    if (key['image'] != '' || key['image' != '-']) {
      hasImage = ['lazy', key['image']];
      style = 'style="height: 200px;';
      color = 'color: white;';
    }

    var verMessage = '';
    var shareBtn = '';
    var deleteBtn = '';
    if (key['verified'] == 0) {
      verMessage =
        '<span class="mdl-chip info-alert"><span class="mdl-chip__text">Aushang in Prüfung</span></span>';
      deleteBtn =
        '<button class="delete-aushang-btn mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect"><i class="material-icons">delete</i></button>';
    } else {
      shareBtn =
        '<button class="share-aushang-btn mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect"><i class="material-icons">share</i></button>';
    }

    aushang.push(
      '<section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp aushang-section" data-aushang-id="' +
        key['id'] +
        '"><div class="demo-card-wide mdl-card mdl-cell mdl-cell--12-col"><div class="bg mdl-card__title button-overlay ' +
        hasImage[0] +
        '" data-src="' +
        hasImage[1] +
        '" ' +
        style +
        '"></div><div class="mdl-card__supporting-text"><h4>' +
        key['title'] +
        '</h4>' +
        key['description'] +
        '</div><div class="mdl-card__actions mdl-card--border">' +
        action_html +
        '</div><div class="mdl-card__menu">' +
        '<span style="' +
        color +
        '">Aufgegeben am ' +
        parseDateForAushang(key['created_at']) +
        '</span>' +
        verMessage +
        shareBtn +
        deleteBtn +
        '</div></div></section>'
    );
  });
  return aushang;
}

function parseTasks(data) {
  var tasks = [];
  data.forEach(function(key) {
    var hasTasksBtn = '';
    if (key['hasFiles'] == true) {
      hasTasksBtn =
        '<button class="mdl-button task-doc-btn">Dokumente ansehen</button>';
    }

    if (store.get(activeUser).role != 'TEACHER') {
      tasks.push(
        '<section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp task-card" data-task-id="' +
          key['id'] +
          '"> <div class="mdl-card mdl-cell mdl-cell--12-col"> <div class="mdl-card__supporting-text"> <h4>' +
          key['name'] +
          '</h4>' +
          key['description'] +
          '<br> <br> </div> <div class="mdl-card__actions mdl-card--border">  ' +
          hasTasksBtn +
          '</div> <div class="mdl-card__menu"> <i class="material-icons">alarm</i>' +
          key['expire_date'] +
          '</div> </div> </section>'
      );
    } else {
      tasks.push(
        '<section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp task-card" data-task-id="' +
          key['id'] +
          '" data-task-title="' +
          key['name'] +
          '" data-task-desc="' +
          key['description'] +
          '" data-task-expire-date="' +
          key['expire_date'] +
          '"> <div class="mdl-card mdl-cell mdl-cell--12-col"> <div class="mdl-card__supporting-text"> <h4>' +
          key['name'] +
          '</h4>' +
          key['description'] +
          '<br> <br> </div> <div class="mdl-card__actions mdl-card--border">' +
          hasTasksBtn +
          ' </div> <div class="mdl-card__menu"> <i class="material-icons">alarm</i>' +
          key['expire_date'] +
          '<!--<button class="edit-task-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">edit</i> </button>--> <button class="edit-task-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">edit</i> </button><button class="delete-task-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">delete</i> </button> </div> </div> </section>'
      );
    }
  });
  return tasks;
}

function parseFiles(data) {
  var files =
    '<div class="file-preview-card"><table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp mdl-cell mdl-cell--12-col"> <thead> <tr> <th>Datei</th><th>Größe</th> <th>Hochgeladen am</th> <th>Aktion</th> ';
  files += '</tr> </thead> <tbody>';

  data.forEach(function(key) {
    var lightBoxClass = 'venobox';
    var lightBoxGallery =
      "data-gall='myGallery' data-title='" + key['name'] + "'";
    var fileFormat = key['data_type'];
    if (fileFormat == 'pdf') {
      lightBoxClass = '';
      lightBoxGallery = '';
    }
    //console.log(lightBoxClass);

    files +=
      '<tr data-file-id="' +
      key['id'] +
      '" data-file-name="' +
      key['name'] +
      '"> <td>' +
      key['name'] +
      '.' +
      key['data_type'] +
      '</td><td>' +
      key['size'] +
      '</td> <td>' +
      key['uploaded'] +
      '</td> <td><a href="' +
      key['url'] +
      '" ' +
      lightBoxGallery +
      ' target="_blank" class="' +
      lightBoxClass +
      ' mdl-button mdl-js-button mdl-button--icon"><i class="material-icons">open_in_browser</i></a>';

    if (store.get(activeUser).role == 'TEACHER') {
      files +=
        '<button class="edit-file-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">edit</i> </button> <button class="delete-file-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">delete</i> </button>';
    }

    files += '</td></tr>';
  });
  files +=
    '</tbody></table><br><button class="doc-btn-back mdl-button mdl-js-button mdl-button--raised">Zurück</button></div>';

  return files;
}

function parseCourseMembers(data) {
  var i = 0;
  var members =
    '<div class="members-card"><table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp mdl-cell mdl-cell--12-col"> <thead> <tr> <th>ID</th> <th>Name</th> </tr> </thead> <tbody>';
  if (store.get(activeUser).role == 'TEACHER') {
    members =
      '<div class="members-card"><table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp mdl-cell mdl-cell--12-col"> <thead> <tr> <th>ID</th> <th>Name</th><th>Aktion</th> </tr> </thead> <tbody>';
  }
  data.forEach(function(key) {
    i++;
    if (store.get(activeUser).role != 'TEACHER') {
      members +=
        '<tr> <td><span class="member-label">' +
        i +
        '</span></td> <td><span class="member-label">' +
        key['first_name'] +
        ' ' +
        key['username'] +
        '</span></td> </tr>';
    } else {
      members +=
        '<tr data-member-id="' +
        key['id'] +
        '"> <td><span class="member-label">' +
        i +
        '</span></td> <td><span class="member-label">' +
        key['first_name'] +
        ' ' +
        key['username'] +
        '</span></td><td><button class="remove-member-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">delete</i> </button></td></tr>';
    }
  });
  members += '</tbody></table></div>';

  return members;
}

function parseCourseEntries(data) {
  var i = 0;

  var entries =
    '<div class="members-card"><table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp mdl-cell mdl-cell--12-col"> <thead> <tr> <th>Titel</th><th>Beschreibung</th><th>Erstellt am</th> </tr> </thead> <tbody>';
  if (store.get(activeUser).role == 'TEACHER') {
    entries =
      '<div class="members-card"><table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp mdl-cell mdl-cell--12-col"> <thead> <tr><th>Titel</th><th>Beschreibung</th><th>Erstellt am</th><th>Aktion</th> </tr> </thead> <tbody>';
  }
  data.forEach(function(key) {
    i++;
    if (store.get(activeUser).role != 'TEACHER') {
      entries +=
        '<tr><td>' +
        key['title'] +
        '</td><td>' +
        key['description'] +
        '</td><td>' +
        parseDate(key['created_at']) +
        '</td> </tr>';
    } else {
      entries +=
        '<tr data-entry-id="' +
        key['id'] +
        '" data-entry-title="' +
        key['title'] +
        '" data-entry-desc="' +
        key['description'] +
        '"><td>' +
        key['title'] +
        '</td><td>' +
        key['description'] +
        '</td><td>' +
        parseDate(key['created_at']) +
        '</td><td></button><button class="edit-entry-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">edit</i> </button><button class="remove-entry-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">delete</i> </td></tr>';
    }
  });
  entries += '</tbody></table></div>';
  return entries;
}

function parseHomework(data) {
  var homework = [];
  data.forEach(function(key) {
    var homeworkBtn =
      '<a class="mdl-button done-homework-btn">Aufgabe als erledigt markieren</a>';
    var done = '';
    if (key['done'] == 1) {
      done = 'homework-done';
      homeworkBtn =
        '<p>Diese Aufgabe wurde von dir bereits erledigt.</p><a class="mdl-button undo-done-homework-btn">R&uuml;ckg&auml;ngig machen</a>';
    }
    var expireDate = parseDate(key['expire_date']);

    var course = '';
    if (key['course'] != '') {
      course = ' für Kurs ' + key['course'];
    }

    homework.push(
      '<section class="homework-section section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp ' +
        done +
        '" data-homework-id="' +
        key['id'] +
        '" data-homework-title="' +
        key['title'] +
        '" data-homework-desc="' +
        key['description'] +
        '" data-homework-expire-date="' +
        key['expire_date'] +
        '"> <div class="demo-card-wide mdl-card mdl-cell mdl-cell--12-col"> <div class="mdl-card__supporting-text"> <h4>' +
        key['title'] +
        course +
        '</h4>' +
        key['description'] +
        '</div> <div class="mdl-card__actions mdl-card--border">' +
        homeworkBtn +
        '  </div> <div class="mdl-card__menu">  bis zum ' +
        expireDate +
        '<button class="edit-homework-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">edit</i> </button> <button class="delete-homework-btn mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"> <i class="material-icons">delete</i> </button> <button class="share-homework-btn mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect"> <i class="material-icons">share</i> </button> </div> </div> </section>'
    );
  });
  return homework;
}

function parseDate(dateString) {
  //console.log("Hallo");
  var parts = dateString.split(' ');
  var date = parts[0].split('-');

  var time = parts[1].split(':');
  return (
    date[2] + '/' + date[1] + '/' + date[0] + ' ' + time[0] + ':' + time[1]
  );
}

function parseDateForAushang(dateString) {
  //console.log("Hallo");
  var parts = dateString.split(' ');
  var date = parts[0].split('-');

  var time = parts[1].split(':');
  return date[2] + '/' + date[1];
}

function parseBugReports(data) {
  var reports =
    '<section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp"> <table class="messages-list mdl-data-table mdl-js-data-table mdl-shadow--2dp" style="width:100%;"> <thead> <tr> <th>Name</th> <th>Beschreibung</th> <th>Behoben?</th> <th>Erstellt am</th> </tr> </thead> <tbody>';

  data.forEach(function(key) {
    var done = '';
    if (key['done'] == 0) {
      done = 'Noch nicht behoben';
    } else if (key['done'] == 1) {
      done = 'Behoben';
    }

    reports +=
      '<tr data-id="' +
      key['id'] +
      '"> <td>' +
      key['name'] +
      '</td> <td>' +
      key['description'] +
      '</td><td>' +
      done +
      '</td> <td>' +
      key['created_at'] +
      '</td> </tr>';
  });
  reports += '</tbody></table></section>';

  return reports;
}

function parseSettings(data) {
  var teacher = '';
  var student = '';
  var courses = '';
  if (data['role'] != 'Schüler') {
    teacher = 'Kürzel: ' + data['teacher_id'];
    teacher =
      '<li class="mdl-list__item mdl-list__item--two-line" data-grade="' +
      data['teacher_id'] +
      '" id="grade"> <span class="mdl-list__item-primary-content"> <span>K&uuml;rzel</span> <span class="mdl-list__item-sub-title">' +
      data['teacher_id'] +
      '</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" id="edit-grade-btn" href="#"><i class="material-icons">edit</i></a> </span> </li>';
  } else if (data['role'] == 'Schüler') {
    student =
      '<li class="mdl-list__item mdl-list__item--two-line" data-grade="' +
      data['grade'] +
      '" id="grade"> <span class="mdl-list__item-primary-content"> <span>Klasse</span> <span class="mdl-list__item-sub-title">' +
      data['grade'] +
      '</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" href="#" id="edit-grade-btn"><i class="material-icons">edit</i></a> </span> </li>';
    courses =
      '<li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Kurse</span> <span class="mdl-list__item-sub-title">' +
      data['count_courses'] +
      ' Kurs(e) ausgew&auml;hlt</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" href="#" id="edit-courses-btn"><i class="material-icons">edit</i></a> </span> </li>';
  }

  var settings =
    '<section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp"> <div class="demo-card-wide mdl-card mdl-cell mdl-cell--12-col"> <div class="mdl-card__supporting-text"> <h4>Pers&ouml;nliche Daten</h4><p>Um Stammdaten wie Email oder Name zu &auml;ndern, kontaktiere bitte unseren Support unter <a href="mailto:account@schoolos.de" target="_blank">account@schoolos.de</a><br>Vielen Dank!</p> <ul class="demo-list-two mdl-list"> <li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Email</span> <span class="mdl-list__item-sub-title">' +
    data['email'] +
    '</span> </span> <span class="mdl-list__item-secondary-content"> </span> </li> <li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Name</span> <span class="mdl-list__item-sub-title">' +
    data['first_name'] +
    ' ' +
    data['name'] +
    '</span> </span> <span class="mdl-list__item-secondary-content"> </span> </li> <li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Nutzerrolle</span> <span class="mdl-list__item-sub-title">' +
    data['role'] +
    '</span> </span> <span class="mdl-list__item-secondary-content"> </span> </li>' +
    student +
    teacher +
    courses +
    '</ul> </div> <div class="mdl-card__actions mdl-card--border"> </div> <div class="mdl-card__menu"> </div> </div> </section> <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp"> <div class="demo-card-wide mdl-card mdl-cell mdl-cell--12-col"> <div class="mdl-card__supporting-text"> <h4>Weiteres</h4> <ul class="demo-list-two mdl-list"> <li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Impressum</span> <span class="mdl-list__item-sub-title">Zuletzt aktualisiert am 14.09.2017</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" href="http://schoolos.de/impressum/" target="_blank"><i class="material-icons">&#xE89E;</i></a> </span> </li> <li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>AGBs</span> <span class="mdl-list__item-sub-title">Zuletzt aktualisiert am 14.09.2017</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" href="http://schoolos.de/agb-user/" target="_blank"><i class="material-icons">&#xE89E;</i></a> </span> </li> <li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Datenschutzbestimmungen</span> <span class="mdl-list__item-sub-title">Zuletzt aktualisiert am 14.09.2017</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" href="http://schoolos.de/datenschutzbestimmungen/" target="_blank"><i class="material-icons">&#xE89E;</i></a> </span> </li><li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Systemanforderungen</span> <span class="mdl-list__item-sub-title">Erfahre was f&uuml;r Anforderungen SOS ben&ouml;tigt</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" href="http://schoolos.de/system/" target="_blank"><i class="material-icons">&#xE89E;</i></a> </span> </li><li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Lizenzen</span> <span class="mdl-list__item-sub-title">Zuletzt aktualisiert am 27.10.2017</span> </span> <span class="mdl-list__item-secondary-content"> <a class="mdl-list__item-secondary-action" href="http://schoolos.de/lizenzen" target="_blank"><i class="material-icons">&#xE89E;</i></a> </span> </li> <li class="mdl-list__item mdl-list__item--two-line"> <span class="mdl-list__item-primary-content"> <span>Version</span> <span class="mdl-list__item-sub-title">' +
    data['version'] +
    '</span> </span> <span class="mdl-list__item-secondary-content"> </span> </li></ul> </div> <div class="mdl-card__actions mdl-card--border"> </div> <div class="mdl-card__menu"> </div> </div> </section>';
  return settings;
}

function addAccount() {
  $('.modal').remove();
  var content =
    '<form class="modal" role="form" method="post" id="add-user-form"> <div class="mdl-card__supporting-text"> <h4>Account hinzuf&uuml;gen</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="title" name="email" data-is-required=true> <label class="mdl-textfield__label" for="title">Email</label> </div> </div> <div class="mdl-grid"><div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="password" id="title" name="password" data-is-required=true> <label class="mdl-textfield__label" for="title">Passwort</label></div> </div></div> <div class="mdl-card__actions"> <button type="submit" class="mdl-button">Anmelden</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
      initMultiAccounts();
    });
}

function newEntry() {
  var content =
    '<form class="modal" role="form" method="post" id="save-entry"> <div class="mdl-card__supporting-text"> <h4>Neuen Eintrag erstellen</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="title" name="title" data-is-required=true> <label class="mdl-textfield__label" for="title">Titel des Eintrags</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <textarea class="mdl-textfield__input" type="text" rows= "2" id="text" name="description" data-is-required=true></textarea> <label class="mdl-textfield__label" for="text">Beschreibung</label><input type="hidden" class="form-control" id="hidden-input-course" name="course_id"> </div> </div> </div> <div class="mdl-card__actions"> <button type="submit" id="add-new-entry" class="mdl-button">Erstellen</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
}

function newAushang() {
  var content =
    '<form class="modal" role="form" method="post" id="save-aushang"> <div class="mdl-card__supporting-text"> <h4>Neuen Aushang einreichen</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"><input class="mdl-textfield__input" type="text" id="aushang-title" name="title" data-is-required=true> <label class="mdl-textfield__label" for="az-title">Name des Aushang</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <textarea class="mdl-textfield__input" type="text" rows= "2" id="aushang-text" name="description" data-is-required=true></textarea> <label class="mdl-textfield__label" for="aushang-text">Beschreibung</label> </div> </div> <div class="mdl-grid action-type"> <p>Aktionstyp auswählen:</p> <ul style="list-style: none;"> <li><label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-0"> <input type="radio" id="option-0" class="mdl-radio__button" name="action_type" value="0" checked> <span class="mdl-radio__label">Keine</span> </label></li> <li><label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-1"> <input type="radio" id="option-1" class="mdl-radio__button" name="action_type" value="1"> <span class="mdl-radio__label">Email </span> </label></li> <li><label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-2"> <input type="radio" id="option-2" class="mdl-radio__button" name="action_type" value="2"> <span class="mdl-radio__label">Telefonnummer/Handy</span> </label></li> <li><label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="option-3"> <input type="radio" id="option-3" class="mdl-radio__button" name="action_type" value="3"> <span class="mdl-radio__label">Webseiten URL</span> </label></li></ul> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col" id="action-url-input"> <input class="mdl-textfield__input" type="text" name="action_url" placeholder="Aktions URL"> <label class="mdl-textfield__label" for="aushang-link">Aktion URL</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="aushang-grade" name="aushang-grade"> <label class="mdl-textfield__label" for="aushang-grade">Klasse (mehrere bitte mit Komma seperariert)</label></div> </div> </div> <div class="mdl-card__actions"> <button type="submit" id="add-new-aushang" class="mdl-button">Erstellen</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      $('#action-url-input').hide();
      componentHandler.upgradeDom();
      $('.action-type label').click(function() {
        if ($('input[name=action_type]:checked').val() != '0') {
          $('#action-url-input').show();
        } else {
          $('#action-url-input').hide();
        }
      });

      //action-url-input
    });
}

function newHomework(grade) {
  var content =
    '<form class="modal" role="form" method="post" id="save-homework"> <div class="mdl-card__supporting-text"> <h4>Neue Aufgabe erstellen</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="title" name="title" data-is-required=true> <label class="mdl-textfield__label" for="title">Titel der Aufgabe</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <textarea class="mdl-textfield__input" type="text" rows= "2" id="text" name="description" data-is-required=true></textarea> <label class="mdl-textfield__label" for="text">Beschreibung</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" id="min-date" type="text" name="expire_date" placeholder="Fällig bis zum" data-is-required=true> </div> </div><!--<div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input course-autocomplete" type="text" id="course" name="course"> <label class="mdl-textfield__label" for="course">Für Kurs</label> </div> </div>--><div class="mdl-card__actions"> <button type="submit" id="add-new-homework" class="mdl-button">Erstellen</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
      $('#min-date').bootstrapMaterialDatePicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        minDate: new Date(),
        maxDate: new moment().add(120, 'days')
      });
      $('.course-autocomplete').autocomplete({
        //To be continued
        serviceUrl: apiURL + 'autocomplete/courses',
        onSelect: function(suggestion) {},
        params: { grade: grade }
      });
    });
}

function editFile(name) {
  var content =
    '<form class="modal" role="form" id="edit-file"> <div class="mdl-card__supporting-text"> <h4>Dokument bearbeiten</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" value="' +
    name +
    '" id="name" name="name" data-is-required=true></div> </div><div class="mdl-card__actions"> <button type="submit" id="update-file" class="mdl-button">Änderungen speichern</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
}

function editHomework(title, desc, expireDate) {
  var content =
    '<form class="modal" role="form" method="post" id="edit-homework"> <div class="mdl-card__supporting-text"> <h4>Aufgabe: "' +
    title +
    '" bearbeiten</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" value="' +
    title +
    '" id="title" name="title" data-is-required=true></div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"><textarea class="mdl-textfield__input" type="text" rows= "2" id="text" name="description" data-is-required=true>' +
    desc +
    '</textarea></div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" id="min-date" type="text" name="expire_date" placeholder="Fällig bis zum" value="' +
    expireDate +
    '" data-is-required=true> </div> </div> <div class="mdl-card__actions"> <button type="submit" id="update-homework" class="mdl-button">Änderungen speichern</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
      $('#min-date').bootstrapMaterialDatePicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        minDate: new Date(),
        maxDate: new moment().add(120, 'days')
      });
    });
  /*** Missing here ***/
}

function editEntry(title, desc) {
  var content =
    '<form class="modal" role="form" method="post" id="edit-entry"> <div class="mdl-card__supporting-text"> <h4>Eintrag: "' +
    title +
    '" bearbeiten</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" value="' +
    title +
    '" id="title" name="title" data-is-required=true></div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"><textarea class="mdl-textfield__input" type="text" rows= "2" id="text" name="description" data-is-required=true>' +
    desc +
    '</textarea></div> </div> <div class="mdl-grid">  <div class="mdl-card__actions"> <button type="submit" id="update-entry" class="mdl-button">Änderungen speichern</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
}

function newReport() {
  var content =
    '<form class="modal" role="form" method="post" id="send-report"> <div class="mdl-card__supporting-text"> <h4>Neuen Bug melden</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="report-name" name="name" data-is-required=true> <input name="report_id" type="hidden" value="sos.s.v1"/> <label class="mdl-textfield__label" for="report-name">Name des Problems</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <textarea class="mdl-textfield__input" type="text" rows= "2" id="report_link" name="link" data-is-required=true></textarea> <label class="mdl-textfield__label" for="report-link">Optional: Screenshotlink</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="report-desc" name="description" data-is-required=true> <label class="mdl-textfield__label" for="report-desc">Beschreibung</label> </div> </div> </div> <div class="mdl-card__actions"> <button type="submit" id="send-report-btn" class="mdl-button">Report senden</button> </div> </form>';
  $(content)
    .appendTo('body')
    .modal()
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
}

function shareURL(name, url, buttonText, buttonClass) {
  var button = '';
  if (buttonText != '') {
    button =
      '<button class="mdl-button ' +
      buttonClass +
      '">' +
      buttonText +
      '</button>';
  }

  return (
    '<div><div class="mdl-card__supporting-text"><h4>' +
    name +
    ':</h4><div class="mdl-grid"><div class="mdl-cell mdl-cell--12-col"><a href="http://www.facebook.com/sharer.php?u=' +
    url +
    '" target="_blank"><img src="../images/facebook-icon.png" alt="Facebook" /></a><a href="https://twitter.com/share?url=' +
    url +
    'text=Aushang%20SOS;hashtags=sos" target="_blank"><img src="../images/twitter-icon.png" alt="Twitter" /></a><!-- Google+ --><a href="https://plus.google.com/share?url=' +
    url +
    '" target="_blank"><img src="../images/google-plus-icon.png" alt="Google" /></a><a href="mailto:?Subject=SOS - Aushang teilen&amp;Body=Aushang%20kann%20unter%20folgendem%20Link%20geteilt%20werden:%20' +
    url +
    '"><img src="../images/email-icon.png" alt="Email" /></a><a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=' +
    url +
    '" target="_blank"><img src="../images/linkedin-icon.png" alt="LinkedIn" /></a></div></div><div class="mdl-grid"><div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"><label>Alternativ kann dieser Link geteilt werden:</label><input class="mdl-textfield__input" readonly type="text" id="share-aushang-url" value="' +
    url +
    '"><label class="mdl-textfield__label" for="aushang-title">Link</label></div></div>' +
    button +
    '</div><div class="mdl-card__actions"><button id="close-share" class="mdl-button">Schließen</button></div></div>'
  );
}

//* UI Methods */

function updateOptionMenu(titles, ids, icons) {
  var optMenu = '';
  if (titles.length) {
    for (var i = 0; i < titles.length; i++) {
      if (titles[i] == 'MENU') {
        var item =
          '<li class="mdl-menu__item" id="open-second-timetable">Lehrerpläne</li>';
        if (store.get(activeUser).role == 'TEACHER') {
          item =
            '<li class="mdl-menu__item" id="open-second-timetable">Klassenpläne</li>';
        }
        optMenu +=
          '<button id="demo-menu-lower-right" class="mdl-button mdl-js-button mdl-button--icon"> <i class="material-icons">more_vert</i></button><ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="demo-menu-lower-right">' +
          item +
          '<li class="mdl-menu__item" id="open-room-plans">Raumpläne</li></ul>';
      } else {
        optMenu +=
          '<div class="tooltip--left option-btn" data-tooltip="' +
          titles[i] +
          '"><button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" id="' +
          ids[i] +
          '"><i class="material-icons">' +
          icons[i] +
          '</i></button></div>';
      }
    }
  }

  $(optMenu).insertAfter('#option-menu');
}

function updateActionButtons(siteName) {
  var btnContent = '';
  var aushangBtn =
    '<li><a data-mfb-label="Neuer Aushang" class="mfb-component__button--child new-aushang-btn"><i class="mfb-component__child-icon ion-document"></i></a></li>';
  var refreshBtn =
    '<li><a data-mfb-label="Aktualisieren" class="mfb-component__button--child refresh-btn"><i class="mfb-component__child-icon ion-refresh"></i></a></li>';
  var addTaskBtn =
    '<li><a data-mfb-label="Auftrag erstellen" class="mfb-component__button--child" id="add-task-btn"><i class="mfb-component__child-icon ion-android-attach"></i></a></li>';
  var addEntryBtn =
    '<li><a data-mfb-label="Neuen Eintrag ins Kurstagebuch" class="mfb-component__button--child" id="new-entry-btn"><i class="mfb-component__child-icon ion-log-in"></i></a></li>';
  var eventBtn =
    '<li><a data-mfb-label="Neues Ereigniss" class="mfb-component__button--child new-event-btn"><i class="mfb-component__child-icon ion-ios-calendar-outline"></i></a></li>';
  var infoBtn =
    '<li><a data-mfb-label="Hilfe/Kontakt" href="" target="_blank" class="mfb-component__button--child info-btn"><i class="mfb-component__child-icon ion-help"></i></a></li>';
  var faqBtn =
    '<li><a data-mfb-label="FAQ" href="http://schoolos.de/#faq" target="_blank" class="mfb-component__button--child info-btn"><i class="mfb-component__child-icon ion-information"></i></a></li>';
  var aboutBtn =
    '<li><a data-mfb-label="Über uns" href="" target="_blank" class="mfb-component__button--child info-btn"><i class="mfb-component__child-icon ion-android-bulb"></i></a></li>';
  var reportBtn =
    '<li><a data-mfb-label="Neuen Bug melden" class="mfb-component__button--child new-report-btn"><i class="mfb-component__child-icon ion-compose"></i></a></li>';
  var homeworkBtn =
    '<li><a data-mfb-label="Neue Aufgabe eintragen" class="mfb-component__button--child new-homework-btn"><i class="mfb-component__child-icon ion-compose"></i></a></li>';
  if (siteName == 'plan') {
    btnContent = refreshBtn;
  } else if (siteName == 'timetable') {
    $('#menu').hide();
  } else if (siteName == 'aushang') {
    btnContent = aushangBtn;
  } else if (siteName == 'courses') {
    if (store.get(activeUser).role == 'TEACHER') {
      btnContent = addTaskBtn;
      btnContent += addEntryBtn;
    } else {
      $('#menu').hide();
    }
  } else if (siteName == 'homework') {
    btnContent = homeworkBtn;
  } else if (siteName == 'calendar') {
    btnContent = eventBtn;
    $('#menu').hide();
  } else if (siteName == 'bugreport') {
    btnContent = reportBtn;
  } else if (siteName == 'settings') {
    btnContent = infoBtn + faqBtn + aboutBtn;
  } else if (siteName == 'consultations') {
    $('#menu').hide();
  }

  $('#menu').html(
    '<li class="mfb-component__wrap"><a href="#" class="mfb-component__button--main" id="action-btn"><i class="mfb-component__main-icon--resting ion-plus-round"></i><i class="mfb-component__main-icon--active ion-close-round"></i></a><ul class="mfb-component__list"></ul></li>'
  );

  if (siteName == 'plan') {
    $('#menu').html(refreshBtn);
  }

  $('.mfb-component__list')
    .html(btnContent)
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
}

function switchTab(href) {
  // remove all is-active classes from tabs
  $('a.mdl-layout__tab').removeClass('is-active');
  // activate desired tab
  $('a[href="#' + href + '"]').addClass('is-active');
  // remove all is-active classes from panels
  $('.mdl-layout__tab-panel').removeClass('is-active');
  // activate desired tab panel
  $('#' + href).addClass('is-active');
}

function updateTabController(tabNames, tabRefs, tabClass) {
  var tabBarController = $('#tab-bar-controller');
  $(tabBarController).removeClass();
  $(tabBarController).addClass('mdl-layout__tab-bar');
  $(tabBarController).addClass('mdl-js-ripple-effect');
  $(tabBarController).addClass(tabClass);
  $(tabBarController).empty();

  var tabContent = '';
  for (var i = 0; i < tabNames.length; i++) {
    if (i == 0) {
      tabContent +=
        '<a href="#' +
        tabRefs[i] +
        '" class="mdl-layout__tab tab-btn is-active">' +
        tabNames[i] +
        '</a>';
    } else {
      tabContent +=
        '<a href="#' +
        tabRefs[i] +
        '" class="mdl-layout__tab tab-btn">' +
        tabNames[i] +
        '</a>';
    }
  }
  $(tabBarController)
    .html(tabContent)
    .promise()
    .done(function() {
      componentHandler.upgradeDom();
    });
}

function updateContainers(containerIds) {
  var containerContents =
    '<section class="mdl-layout__tab-panel is-active" id="main-section"><div class="page-content" id="overview"></div></section>';
  for (var i = 0; i < containerIds.length; i++) {
    containerContents +=
      '<section class="mdl-layout__tab-panel" id="' +
      containerIds[i] +
      '-section"><div class="page-content" id="' +
      containerIds[i] +
      '"></div></section>';
  }
  $('#main').html(containerContents);
}

//******///
/* Main Functions */

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

function getDayTitles() {
  var dnForToday = getDayNumber(true);
  var dnForTomorrow = getDayNumber(false);
  //console.log(dnForTomorrow);
  //console.log(dnForToday);
  var weekday = new Array(7);
  weekday[0] = 'Montag';
  weekday[1] = 'Dienstag';
  weekday[2] = 'Mittwoch';
  weekday[3] = 'Donnerstag';
  weekday[4] = 'Freitag';
  weekday[5] = 'Samstag';
  weekday[6] = 'Montag';

  var descForToday = 'Stundenplan für ' + weekday[dnForToday - 1];
  var descForTomorrow = 'Stundenplan für ' + weekday[dnForTomorrow - 1];

  return [descForToday, descForTomorrow];
}

function initMultiAccounts() {
  $(document).on('submit', '#add-user-form', function(e) {
    e.preventDefault();
    if ($(this).validateForm()) {
      $.post(apiURL + '/login', $('#add-user-form').serialize(), function(
        data
      ) {
        if (data['error'] == false) {
          if (data['verified'] == '1') {
            var users = store.get('users');
            if ($.inArray(data['token'], users) != -1) {
              store.set('active-user', data['token']);
            } else {
              store.set(data['token'], {
                email: data['email'],
                auth: data['apiKey'],
                role: data['role'],
                loggedIn: data['logged_in'],
                menuIsOpen: true,
                schoolURL: data['baseURL']
              });
              users.push(data['token']);
              store.set('users', users);
              store.set('active-user', data['token']);
            }
            store.set('hasRegisteredForNotif', false);
            $.modal.close();
            $('.modal').remove();
            location.reload();
            if (getURLParameter('site') == 'courses') {
              window.location.href = baseURL + '/user/main?site=plan';
            }
          } else {
            $.snackbar(getSnackbarOptions('Account noch nicht verifiziert!'));
          }
        } else {
          $.snackbar(getSnackbarOptions('Falsche Email oder Passwort'));
        }
      });
    }
  });
}

function getSnackbarOptions(title) {
  var options = {
    content: title,
    timeout: 10000, // time in milliseconds after the snackbar autohides, 0 is disabled
    htmlAllowed: true, // allows HTML as content value
    onClose: function() {} // callback called when the snackbar gets closed.
  };
  return options;
}

function initTimetableSite() {
  loadContent(true);

  var isForToday = true;

  //Tab btn clicked
  $(document).on('click', '.tab-btn', function(e) {
    e.preventDefault();
    var tabID = $(this).attr('href');
    if (tabID == '#main-section') {
      loadContent(true);
      isForToday = true;
    } else if (tabID == '#timetable-t-section') {
      loadContent(false);
      isForToday = false;
    }
  });

  $(document).on('click', '.refresh-btn', function(e) {
    loadContent(isForToday);
  });

  function loadContent(isForToday) {
    var container = '#overview';
    if (!isForToday) {
      container = '#timetable-t';
    }
    var daynumber = getDayNumber(isForToday);
    $(container).addLoadingSpinner();
    $.ajax({
      url: apiURL + '/timetable/' + daynumber,
      type: 'get',
      success: function(data, status) {
        data = parseTimetable(
          data['timetable'],
          data['isCurrentVP'],
          data['updated_at']
        );
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
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '.timetable-more-btn', function(e) {
    $('.modal').remove();
    e.preventDefault();
    var entry = $(this)
      .parent()
      .parent()
      .parent();
    getDetailView(
      $(entry).attr('data-timetable-headline'),
      $(entry).attr('data-timetable-subject'),
      $(entry).attr('data-timetable-time'),
      $(entry).attr('data-timetable-teacher'),
      $(entry).attr('data-timetable-description')
    );
  });

  /* Überarbeiten und API updaten! */
  $(document).on('click', '#share-vp', function() {
    var daynumber = getDayNumber(isForToday);
    var schoolURL = store.get(activeUser).schoolURL;
    var url = schoolURL + '&type=vp&day=' + daynumber;
    data = shareURL('Vertretungsplan teilen', url, '', '');
    $(data)
      .appendTo('body')
      .modal()
      .promise()
      .done(function() {
        componentHandler.upgradeDom();
      });
  });

  $(document).on('click', '#open-old-vp-view', function() {
    var daynumber = getDayNumber(isForToday);
    var schoolURL = store.get(activeUser).schoolURL;
    var url = schoolURL + '&type=vp&day=' + daynumber;
    var win = window.open(url, '_blank');
    if (win) {
      //Browser has allowed it to be opened
      win.focus();
    } else {
      //Browser has blocked it
      alert(
        'Bitte erlaube Popups für diese Seite, um den Link öffnen zu können!'
      );
    }
  });

  $(document).on('click', '#open-timetable', function() {
    if (store.get(activeUser).role == 'TEACHER') {
      openTimetable(true);
    } else {
      openTimetable(false);
    }
  });

  $(document).on('click', '#open-second-timetable', function() {
    if (store.get(activeUser).role == 'TEACHER') {
      openTimetable(false);
    } else {
      openTimetable(true);
    }
  });

  function openTimetable(forTeachers) {
    var schoolURL = store.get(activeUser).schoolURL;
    var url = schoolURL + '&type=timetable&for=student';
    if (forTeachers) {
      url = schoolURL + '&type=timetable&for=teacher';
    }
    var win = window.open(url, '_blank');
    if (win) {
      //Browser has allowed it to be opened
      win.focus();
    } else {
      //Browser has blocked it
      alert(
        'Bitte erlaube Popups für diese Seite, um den Link öffnen zu können!'
      );
    }
  }

  $(document).on('click', '#open-room-plans', function() {
    var schoolURL = store.get(activeUser).schoolURL;
    var url = schoolURL + '&type=timetable&for=room';
    var win = window.open(url, '_blank');
    if (win) {
      //Browser has allowed it to be opened
      win.focus();
    } else {
      //Browser has blocked it
      alert(
        'Bitte erlaube Popups für diese Seite, um den Link öffnen zu können!'
      );
    }
  });

  $(document).on('click', '#close-share', function(e) {
    $.modal.close();
    $('.modal').remove();
  });

  $(document).on('click', '#copy-vp-url', function(e) {
    new Clipboard('#copy-vp-url');
    $.snackbar({
      content: 'In die Zwischenablage kopiert!'
    });
  });
  $(document).on('click', '#open-vp-url', function(e) {
    var url = $('#share-vp-url').val();
    var win = window.open(url, '_blank');
    if (win) {
      //Browser has allowed it to be opened
      win.focus();
    } else {
      //Browser has blocked it
      alert(
        'Bitte erlaube Popups für diese Seite, um den Link öffnen zu können!'
      );
    }
  });

  $(document).on('click', '#close-more-popup', function(e) {
    $.modal.close();
    $('.modal').remove();
  });

  $(document).on('click', '#custom-btn-print', function() {
    generate();
  });

  function generate() {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    //        var yy = today.getYear();

    if (dd < 10) {
      dd = '0' + dd;
    }
    if (mm < 10) {
      mm = '0' + mm;
    }
    today = dd + '.' + mm;

    var doc = new jsPDF('p', 'pt');

    doc.text('Stundenplan f&uuml;r den ' + today + '.16', 40, 50);
    var elem = document.getElementById('overview');
    var res = doc.autoTableHtmlToJson(elem);
    doc.autoTable(res.columns, res.data, {
      headerStyles: {
        fillColor: [255, 89, 0],
        textColor: [255, 255, 255],
        halign: 'left'
      },
      margin: {
        top: 80
      },
      startY: 60,
      theme: 'striped'
    });

    doc.save('Stundenplan-fuer-16.pdf');
  }
}

function initAushangSite() {
  loadContent(false);

  //Tab btn clicked
  $(document).on('click', '.tab-btn', function(e) {
    e.preventDefault();
    var tabID = $(this).attr('href');
    if (tabID == '#main-section') {
      loadContent(false);
    } else if (tabID == '#user-aushang-section') {
      //console.log("Hallo");
      loadContent(true);
    }
  });

  function loadContent(forUser) {
    var container = '#overview';
    if (forUser) {
      container = '#user-aushang-section';
    }

    $(container).addLoadingSpinner();

    var url = apiURL + '/aushang';
    if (forUser) {
      url = apiURL + '/aushang/user';
    }
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (data['message'] != 'No results.') {
          data = parseAushang(data['aushang']);

          $(container)
            .html(data)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
              $('.lazy').lazy({
                bind: 'event',
                delay: 0
              });
            });
        } else {
          $(container).html(
            '<div class="dialog-center"><h4>Keine Aushänge vorhanden.</h4></div>'
          );
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '.new-aushang-btn', function(e) {
    e.preventDefault();
    $('.modal').remove();
    newAushang();
  });

  //Delete btn clicked
  $(document).on('click', '.delete-aushang-btn', function(e) {
    var aushangID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-aushang-id');
    showDialog({
      title: 'Möchtest du diesen Aushang wirklich löschen?',
      text: 'Dies kann nicht rückgängig gemacht werden.',
      negative: {
        title: 'Abbrechen'
      },
      positive: {
        title: 'Löschen',
        onClick: function(e) {
          deleteAushang(aushangID);
        }
      },
      cancelable: true
    });
  });

  //Delete Task by taskID
  function deleteAushang(aushangID) {
    var url = apiURL + '/aushang/user/' + aushangID;
    $.ajax({
      url: url,
      type: 'delete',
      success: function(data, status) {
        if (data['message'] == 'Aushang deleted successfully.') {
          $.snackbar({
            content: 'Dein Aushang wurde erfolgreich gelöscht'
          });
          loadContent(true);
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  //Share ad btn clicked
  $(document).on('click', '.share-aushang-btn', function(e) {
    $('.modal').remove();
    //console.log("hier");
    var adID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-aushang-id');

    $.ajax({
      url: apiURL + '/aushang/share/' + adID,
      type: 'get',
      success: function(data, status) {
        data = shareURL('Aushang teilen', data['url'], '', '');
        $(data)
          .appendTo('body')
          .modal()
          .promise()
          .done(function() {
            componentHandler.upgradeDom();
          });
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  });

  $(document).on('click', '#open-aushang-url', function(e) {
    var url = $('#share-aushang-url').val();
    var win = window.open(url, '_blank');
    if (win) {
      //Browser has allowed it to be opened
      win.focus();
    } else {
      //Browser has blocked it
      alert(
        'Bitte erlaube Popups für diese Seite, um den Link öffnen zu können!'
      );
    }
  });

  $(document).on('click', '#close-share', function(e) {
    $.modal.close();
    $('.modal').remove();
  });

  $(document).on('click', '#copy-aushang-url', function(e) {
    new Clipboard('#copy-aushang-url');
    $.snackbar({
      content: 'In die Zwischenablage kopiert!'
    });
  });

  $(document).on('click', '#add-new-aushang', function(e) {
    e.preventDefault(false);
    saveAushang(false);
  });

  function saveAushang(updateOld) {
    var snackbarMessage = 'Aushang erfolgreich erstellt!';
    var formName = '#save-aushang';

    $.ajax({
      url: apiURL + '/aushang/student/ad',
      type: 'post',
      data: $('#save-aushang').serialize(),
      success: function(data, status) {
        //console.log(status);
        if (data['message'] == 'Created student ad successfully.') {
          $.snackbar({
            content: snackbarMessage
          });
          $.modal.close();
          $('.modal').remove();
          loadContent(false);
          loadContent(true);
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }
}

/* Überarbeiten */

function initCourseSite() {
  var currentCourse = '';
  var filesToUpload = 0;
  loadContent();

  $(document).on('click', '.tab-btn', function(e) {
    e.preventDefault();
    var tabID = $(this).attr('href');
    if (tabID == '#main-section') {
      loadContent();
    } else if (tabID == '#course-members-section') {
      getCourseMembers();
    } else if (tabID == '#course-entry-section') {
      getCourseEntries();
    }
  });

  function loadContent() {
    $('#overview').addLoadingSpinner();
    var courseName = getURLParameter('name');
    $('.course-item:contains(' + courseName + ')').addClass('active');
    var courseID = getURLParameter('cname');
    //console.log(courseID);
    $('.mdl-layout-title').html('Kurs: ' + courseName);
    var url = apiURL + '/course/' + courseID + '/tasks';
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (
          data['message'] != 'Error. Problem while fetching tasks for course.'
        ) {
          if (!$.isArray(data['tasks']) || !data['tasks'].length) {
            $('#overview').html(
              '<div class="dialog-center"><h4>Keine Aufträge für diesen Kurs verfügbar</h4></div>'
            );
          } else {
            data = parseTasks(data['tasks']);

            $('#overview')
              .html(data)
              .promise()
              .done(function() {
                componentHandler.upgradeDom();
              });
          }
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  function getCourseMembers() {
    $('#course-members-section').addLoadingSpinner();
    var courseID = getURLParameter('cname');
    var url = apiURL + '/course/' + courseID + '/members';
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (
          data['message'] != 'Error. Problem while fetching members for course.'
        ) {
          data = parseCourseMembers(data['members']);
          $('#course-members-section')
            .html(data)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        } else {
          $('#course-members-section').html(
            '<div class="dialog-center"><h4>Keine Mitglieder für diesen Kurs verfügbar</h4></div>'
          );
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '.remove-member-btn', function(e) {
    var memberID = $(this)
      .parent()
      .parent()
      .attr('data-member-id');
    showDialog({
      title:
        'Möchtest du diesen Schüler/Schülerin wirklich aus dem Kurs entfernen?',
      text: '',
      negative: {
        title: 'Abbrechen'
      },
      positive: {
        title: 'Entfernen',
        onClick: function(e) {
          deleteMember(memberID);
        }
      },
      cancelable: true
    });
  });

  function deleteMember(memberID) {
    var courseID = getURLParameter('cname');
    var url = apiURL + '/course/' + courseID + '/members/' + memberID;
    $.ajax({
      url: url,
      type: 'delete',
      success: function(data, status) {
        if (data['message'] == 'Member removed successfully.') {
          $.snackbar({
            content: 'Schüler/Schülerin erfolgreich entfernt.'
          });
          getCourseMembers();
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  function getCourseEntries() {
    $('#course-members-section').addLoadingSpinner();
    var courseID = getURLParameter('cname');
    var url = apiURL + '/course/' + courseID + '/entries';
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (
          data['message'] != 'Error. Problem while fetching entries for course.'
        ) {
          if (!$.isArray(data['entries']) || !data['entries'].length) {
            $('#course-entry-section').html(
              '<div class="dialog-center"><h4>Noch kein Eintrag vorhanden</h4></div>'
            );
          } else {
            data = parseCourseEntries(data['entries']);
            $('#course-entry-section')
              .html(data)
              .promise()
              .done(function() {
                componentHandler.upgradeDom();
              });
          }
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '#new-entry-btn', function(e) {
    $('.modal').remove();
    e.preventDefault();
    newEntry();
  });

  $(document).on('click', '#add-new-entry', function(e) {
    e.preventDefault(false);
    if ($('#save-entry').validateForm()) {
      saveEntry(false);
    }
  });

  $(document).on('click', '.edit-entry-btn', function(e) {
    $('.modal').remove();
    e.preventDefault();

    var title = $(this)
      .parent()
      .parent()
      .attr('data-entry-title');
    var desc = $(this)
      .parent()
      .parent()
      .attr('data-entry-desc');
    var entryID = $(this)
      .parent()
      .parent()
      .attr('data-entry-id');
    store.set('entry-id', entryID);
    editEntry(title, desc);
  });

  $(document).on('click', '#update-entry', function(e) {
    e.preventDefault(false);
    if ($('#edit-entry').validateForm()) {
      saveEntry(true);
    }
  });

  function saveEntry(updateOld) {
    var courseID = getURLParameter('cname');
    $('#hidden-input-course').val(courseID);

    var snackbarMessage = 'Eintrag erfolgreich erstellt!';
    var url = apiURL + '/entries/save';
    var formName = '#save-entry';
    var type = 'post';
    if (updateOld) {
      formName = '#edit-entry';
      type = 'put';
      var entryID = store.get('entry-id');
      url = apiURL + '/entries/' + entryID;
      snackbarMessage = '&Auml;nderungen erfolgreich gespeichert!';
    }

    $.ajax({
      url: url,
      type: type,
      data: $(formName).serialize(),
      success: function(data, status) {
        if (
          data['message'] == 'Entry added successfully.' ||
          data['message'] == 'Entry updated successfully.'
        ) {
          $.modal.close();
          $('.modal').remove();
          $.snackbar({
            content: snackbarMessage
          });
          getCourseEntries();
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  //Delete btn clicked
  $(document).on('click', '.remove-entry-btn', function(e) {
    var entryID = $(this)
      .parent()
      .parent()
      .attr('data-entry-id');
    showDialog({
      title: 'Möchtest du diesen Eintrag wirklich löschen?',
      text: 'Dies kann nicht rückgängig gemacht werden.',
      negative: {
        title: 'Abbrechen'
      },
      positive: {
        title: 'Löschen',
        onClick: function(e) {
          deleteEntry(entryID);
        }
      },
      cancelable: true
    });
  });

  //Delete Task by taskID
  function deleteEntry(entryID) {
    var url = apiURL + '/entries/' + entryID;
    $.ajax({
      url: url,
      type: 'delete',
      success: function(data, status) {
        if (data['message'] == 'Entry deleted successfully.') {
          $.snackbar({
            content: 'Eintrag erfolgreich gel&ouml;scht'
          });
          getCourseEntries();
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  //Join Course
  function joinCourse(selectedCourse, password) {
    //console.log('Selected Course:' + selectedCourse);
    var url = 'join-course.php';
    url = baseScripts + url;
    $.ajax({
      url: url,
      data: {
        'selected-course': selectedCourse,
        password: password
      },
      type: 'post',
      success: function(msg) {
        //                      //console.log(msg);
        //                      if(msg[0] == 1){
        $.snackbar({
          content: 'Kurs erfolgreich beigetreten!'
        });
        $.modal.close();
        loadContent();
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      }
    });
  }

  //Select current course
  function selectCourse(course) {
    //First deselct all courses
    $('#selected-course').removeAttr('id');
    //Select specific course
    $(course).attr('id', 'selected-course');
  }

  function saveTask(updateOld, tokens) {
    var snackbarMessage = 'Auftrag erfolgreich erstellt!';
    var url = apiURL + '/tasks/save';
    var formName = '#save-task';
    if (updateOld) {
      formName = '#edit-task';
      snackbarMessage = 'Änderungen erfolgreich gespeichert!';
      //            var taskID = $('#edit-task').attr('data-task-id');
      //            $('#hidden-input-taskid').val(taskID);
    }
    var courseToken = getURLParameter('cname');
    $('#hidden-input-course').val(courseToken);

    if (tokens != '') {
      var tokenList = tokens.join(',');
      $('#hidden-input-task-token').val(tokenList);
    }

    $.ajax({
      url: url,
      type: 'post',
      data: $(formName).serialize(),
      success: function(data, status) {
        console.log(data);
        $.snackbar({
          content: snackbarMessage
        });
        $('.file-upload').hide();
        $.modal.close();
        loadContent();
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  function newTask() {
    $('.modal').remove();
    var content =
      '<form class="form" role="form" id="save-task" method="post"> <div class="mdl-card__supporting-text"> <h4>Neuer Auftrag</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="task-name-input" name="name" data-is-required=true> <label class="mdl-textfield__label" for="task-name-input">Name des Auftrags</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <textarea class="mdl-textfield__input" type="text" rows="2" id="task-desc-input" name="description"></textarea> <label class="mdl-textfield__label" for="task-desc-input">Beschreibung</label> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" id="min-date" type="text" name="expire_date" placeholder="Enddatum"> <!-- <label class="mdl-textfield__label" for="min-date" data-is-required=true>Enddatum</label>--> </div> <input type="hidden" class="form-control" id="hidden-input-course" name="course_id"><input type="hidden" class="form-control" id="hidden-input-task-token" value="" name="file_token_list"></div> <div class="mdl-grid"> <div id="upload-widget" class="mdl-cell mdl-cell--12-col dropzone"> <div class="fallback"> <input name="file" type="file"/> </div> </div> </div> <div class="mdl-grid"> <div class="mdl-grid"><label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="notification-checkbox"> <input type="checkbox" id="notification-checkbox" name="send_notification" class="mdl-checkbox__input" checked> <span class="mdl-checkbox__label">Mitteilung an alle Kursteilnehmer senden</span></label> </div> </div> <div class="mdl-card__actions"> <button type="submit" id="add-new-task" class="mdl-button">Auftrag erstellen</button> </div></form>';
    $(content)
      .appendTo('body')
      .modal()
      .promise()
      .done(function() {
        componentHandler.upgradeDom();
        initTaskSite();
        //Show date picker between now and in 60 days

        $('#min-date').bootstrapMaterialDatePicker({
          format: 'DD/MM/YYYY',
          minDate: new Date(),
          maxDate: new moment().add(120, 'days'),
          time: false
        });
      });
  }

  function editTask(title, description, expireDate, course) {
    $('.modal').remove();
    var content =
      '<form class="form" role="form" id="edit-task" method="post"> <div class="mdl-card__supporting-text"> <h4>Auftrag ' +
      title +
      ' bearbeiten</h4> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" type="text" id="task-name-input" name="name" value="' +
      title +
      '" data-is-required=true>  </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <textarea class="mdl-textfield__input" type="text" rows="2" id="task-desc-input" name="description">' +
      description +
      '</textarea> </div> </div> <div class="mdl-grid"> <div class="mdl-textfield mdl-js-textfield mdl-cell mdl-cell--12-col"> <input class="mdl-textfield__input" id="min-date" type="text" name="expire_date" placeholder="Enddatum" value="' +
      expireDate +
      '" data-is-required=true> </div> <input type="hidden" class="form-control" id="hidden-input-course" name="course_id" value="' +
      course +
      '"><input type="hidden" class="form-control" id="hidden-input-task-token" value="" name="file_token_list"></div><!-- <div class="mdl-grid"> <div id="upload-widget" class="mdl-cell mdl-cell--12-col dropzone"> <div class="fallback"> <input name="file" type="file"/> </div> </div> </div>--> </div> <div class="mdl-card__actions"> <button type="submit" id="update-task-btn" class="mdl-button">Änderungen speichern</button> </div></form>';
    $(content)
      .appendTo('body')
      .modal()
      .promise()
      .done(function() {
        componentHandler.upgradeDom();
        //Show date picker between now and in 60 days

        $('#min-date').bootstrapMaterialDatePicker({
          format: 'DD/MM/YYYY',
          minDate: new Date(),
          maxDate: new moment().add(120, 'days'),
          time: false
        });
      });
  }

  function makeid(size) {
    var text = '';
    var possible =
      'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i = 0; i < size; i++)
      text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
  }

  function initTaskSite(taskName) {
    var url = apiURL + '/files/upload';
    var tokens = [];
    var myDropzone = new Dropzone('#upload-widget', {
      url: url,
      autoProcessQueue: false,
      maxFilesize: 100,
      maxFiles: 50,
      parallelUploads: 10,
      dictDefaultMessage: 'Lade Dokumente hoch',
      acceptedFiles: 'image/*,application/pdf,word',
      addRemoveLinks: true,
      headers: {
        auth: store.get(activeUser).auth
      },
      init: function() {
        var taskname = taskName;
        myDropzone = this;

        this.on('success', function(file, response) {
          tokens.push(response['file_token']);
        });

        myDropzone.on('addedfile', function(file) {
          filesToUpload++;
        });
        myDropzone.on('complete', function(file) {
          myDropzone.removeFile(file);
          filesToUpload--;
        });

        myDropzone.on('complete', function(file) {
          filesToUpload--;
        });
        myDropzone.on('sending', function(file, xhr, formData) {
          //console.log(file);
          //console.log("Sende Dateien");
          //console.log(xhr);
        });

        myDropzone.on('queuecomplete', function() {
          //console.log("queuecomplete");
          saveTask(false, tokens);
        });
      }
    });

    $('#add-new-task').on('click', function() {
      if (filesToUpload > 0) {
        if ($('#save-task').validateForm()) {
          myDropzone.processQueue();
        }
      }
    });
  }

  $(document).on('click', '.task-doc-btn', function(e) {
    e.preventDefault();
    var taskID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-task-id');

    getFilesForTask(taskID);
  });

  function getFilesForTask(taskID) {
    var url = apiURL + '/task/' + taskID + '/files';
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (
          data['message'] != 'Error. Problem while fetching files for task.'
        ) {
          data = parseFiles(data['files']);
          $('#overview')
            .html(data)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
              $('.venobox').venobox({
                bgcolor: '#fff', // default: '#fff'
                titleattr: 'data-title', // default: 'title'
                numeratio: true, // default: false
                infinigall: false // default: false
              });
            });
          store.set('task-id', taskID);
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '.doc-btn-back', function(e) {
    e.preventDefault();
    loadContent();
  });

  //Create new task
  $(document).on('submit', '#save-task', function(e) {
    e.preventDefault();
    if (filesToUpload <= 0) {
      if ($(this).validateForm()) {
        saveTask(false, '');
      }
    }
  });
  //Delete Task by taskID
  function deleteTask(taskID) {
    var url = apiURL + '/tasks/' + taskID;
    $.ajax({
      url: url,
      type: 'delete',
      success: function(data, status) {
        if (data['message'] == 'Task deleted successfully.') {
          $.snackbar({
            content: 'Auftrag erfolgreich gel&ouml;scht'
          });
          loadContent(false);
          loadContent(true);
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  //Save changes for task
  $(document).on('submit', '#edit-task', function(e) {
    e.preventDefault();
    if (filesToUpload <= 0) {
      if ($(this).validateForm()) {
        saveTask(true, '');
      }
    }
  });

  //Edit task btn clicked
  $(document).on('click', '.edit-task-btn', function(e) {
    $('.modal').remove();
    e.preventDefault();

    var title = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-task-title');
    var desc = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-task-desc');
    var expireDate = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-task-expire-date');
    var course = getURLParameter('cname');
    var taskID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-task-id');
    store.set('task-id', taskID);
    editTask(title, desc, expireDate, course);
  });

  $(document).on('click', '#update-task-btn', function(e) {
    e.preventDefault(false);
    updateTask();
  });

  function updateTask() {
    var taskID = store.get('task-id');
    $.ajax({
      url: apiURL + '/tasks/' + taskID,
      type: 'put',
      data: $('#edit-task').serialize(),
      success: function(data, status) {
        console.log(data);
        if (data['message'] == 'Task updated successfully.') {
          $.modal.close();
          $('.modal').remove();
          $.snackbar({
            content: 'Änderungen erfolgreich gespeichert!'
          });
          loadContent();
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log('Details: ' + desc + '\nError:' + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  //Delete btn clicked
  $(document).on('click', '.delete-task-btn', function(e) {
    var taskID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-task-id');
    showDialog({
      title: 'Möchtest du diesen Auftrag wirklich löschen?',
      text: 'Dadurch werden auch alle angehängten Dateien gelöscht.',
      negative: {
        title: 'Abbrechen'
      },
      positive: {
        title: 'Löschen',
        onClick: function(e) {
          deleteTask(taskID);
        }
      },
      cancelable: true
    });
  });

  //New task btn clicked
  $(document).on('click', '#add-task-btn', function(e) {
    e.preventDefault();
    newTask();
  });

  //Files

  //Edit File by fileID
  $(document).on('click', '.edit-file-btn', function(e) {
    $('.modal').remove();
    e.preventDefault();

    var name = $(this)
      .parent()
      .parent()
      .attr('data-file-name');
    var fileID = $(this)
      .parent()
      .parent()
      .attr('data-file-id');

    store.set('file-id', fileID);
    editFile(name);
  });

  $(document).on('click', '#update-file', function(e) {
    e.preventDefault(false);
    if ($('#edit-file').validateForm()) {
      updateFile();
    }
  });

  function updateFile() {
    var fileID = store.get('file-id');
    $.ajax({
      url: apiURL + '/files/' + fileID,
      type: 'put',
      data: $('#edit-file').serialize(),
      success: function(data, status) {
        if (data['message'] == 'File updated successfully.') {
          $.modal.close();
          $('.modal').remove();
          $.snackbar({
            content: 'Änderungen erfolgreich gespeichert!'
          });
          getFilesForTask(store.get('task-id'));
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  //Delete File by fileID
  $(document).on('click', '.delete-file-btn', function(e) {
    var fileID = $(this)
      .parent()
      .parent()
      .attr('data-file-id');
    showDialog({
      title: 'Möchtest du dieses Dokuemnt wirklich löschen?',
      text: 'Dies kann nicht rückgägnig gemacht werden.',
      negative: {
        title: 'Abbrechen'
      },
      positive: {
        title: 'Löschen',
        onClick: function(e) {
          deleteFile(fileID);
        }
      },
      cancelable: true
    });
  });

  function deleteFile(fileID) {
    var url = apiURL + '/files/' + fileID;
    $.ajax({
      url: url,
      type: 'delete',
      success: function(data, status) {
        if (data['message'] == 'File deleted successfully.') {
          $.snackbar({
            content: 'Dokument erfolgreich gel&ouml;scht'
          });
          getFilesForTask(store.get('task-id'));
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }
}

function initHomeworkSite() {
  loadContent(false);

  $(document).on('click', '.tab-btn', function(e) {
    e.preventDefault();
    var tabID = $(this).attr('href');
    if (tabID == '#main-section') {
      loadContent(false);
    } else if (tabID == '#homework-done-section') {
      loadContent(true);
    }
  });

  function loadContent(isDone) {
    var url = apiURL + '/homework';
    if (isDone) {
      var url = apiURL + '/homework/done';
    }

    var container = '#overview';
    if (isDone) {
      container = '#homework-done-section';
    }
    $(container).addLoadingSpinner();
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (data['homework'].length > 0) {
          data = parseHomework(data['homework']);
          $(container)
            .html(data)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        } else {
          $(container).html(
            '<div class="dialog-center"><h4>Keine Aufgaben verfügbar</h4></div>'
          );
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  //Delete ad btn clicked
  $(document).on('click', '.delete-homework-btn', function(e) {
    var btn = $(this);
    showDialog({
      title: 'M&ouml;chtest du diese Aufgabe wirklich l&ouml;schen?',
      text: 'Dies ist nicht mehr r&uuml;ckg&auml;ngig machbar.',
      negative: {
        title: 'Abbrechen'
      },
      positive: {
        title: 'L&ouml;schen',
        onClick: function(e) {
          var homeworkID = $(btn)
            .parent()
            .parent()
            .parent()
            .attr('data-homework-id');
          deleteHomework(homeworkID);
        }
      },
      cancelable: true
    });
  });

  //Delete Homework by adID
  function deleteHomework(homeworkID) {
    var url = apiURL + '/homework/' + homeworkID;
    $.ajax({
      url: url,
      type: 'delete',
      success: function(data, status) {
        if (data['message'] == 'Homework deleted successfully.') {
          $.snackbar({
            content: 'Aufgabe erfolgreich gel&ouml;scht'
          });
          loadContent(false);
          loadContent(true);
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '.new-homework-btn', function(e) {
    $('.modal').remove();
    e.preventDefault();
    newHomework();
  });

  $(document).on('click', '.edit-homework-btn', function(e) {
    $('.modal').remove();
    e.preventDefault();

    var title = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-title');
    var desc = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-desc');
    var expireDate = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-expire-date');
    var homeworkID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-id');
    store.set('homework-id', homeworkID);
    editHomework(title, desc, expireDate);
  });

  $(document).on('click', '.share-homework-btn', function(e) {
    var homeworkID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-id');
    //console.log(homeworkID);
    $.ajax({
      url: apiURL + '/homework/share/' + homeworkID,
      type: 'get',
      success: function(data, status) {
        data = shareURL(
          'Aufgabe teilen',
          data['url'],
          '',
          'share-homework-course-btn'
        );
        $(data)
          .appendTo('body')
          .modal()
          .promise()
          .done(function() {
            componentHandler.upgradeDom();
          });
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  });

  $(document).on('click', '.share-homework-course-btn', function(e) {
    var homeworkID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-id');
    //console.log(homeworkID);
    $.ajax({
      url: apiURL + '/homework/share/' + homeworkID,
      type: 'get',
      success: function(data, status) {
        data = shareURL(
          'Aufgabe teilen',
          data['url'],
          '',
          'share-homework-course-btn'
        );
        $(data)
          .appendTo('body')
          .modal()
          .promise()
          .done(function() {
            componentHandler.upgradeDom();
          });
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  });

  $(document).on('click', '#open-homework-url', function(e) {
    var url = $('#share-homework-url').val();
    var win = window.open(url, '_blank');
    if (win) {
      //Browser has allowed it to be opened
      win.focus();
    } else {
      //Browser has blocked it
      alert(
        'Bitte erlaube Popups für diese Seite, um den Link öffnen zu können!'
      );
    }
  });

  $(document).on('click', '#close-homework-share', function(e) {
    $.modal.close();
    $('.modal').remove();
  });

  $(document).on('click', '#copy-homework-url', function(e) {
    new Clipboard('#copy-homework-url');
    $.snackbar({
      content: 'In die Zwischenablage kopiert!'
    });
  });

  $(document).on('click', '#add-new-homework', function(e) {
    e.preventDefault(false);
    if ($('#save-homework').validateForm()) {
      saveHomework(false);
    }
  });

  $(document).on('click', '#update-homework', function(e) {
    e.preventDefault(false);
    if ($('#edit-homework').validateForm()) {
      saveHomework(true);
    }
  });

  function saveHomework(updateOld) {
    var snackbarMessage = 'Aufgabe erfolgreich eingetragen!';
    var url = apiURL + '/homework/save';
    var formName = '#save-homework';
    var type = 'post';
    if (updateOld) {
      formName = '#edit-homework';
      type = 'put';
      var homeworkID = store.get('homework-id');
      url = apiURL + '/homework/' + homeworkID;
      snackbarMessage = '&Auml;nderungen erfolgreich gespeichert!';
    }

    $.ajax({
      url: url,
      type: type,
      data: $(formName).serialize(),
      success: function(data, status) {
        if (
          data['message'] == 'Homework added successfully.' ||
          data['message'] == 'Homework updated successfully.'
        ) {
          $.modal.close();
          $('.modal').remove();
          $.snackbar({
            content: snackbarMessage
          });
          loadContent(false);
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '.done-homework-btn', function(e) {
    e.preventDefault(false);
    var homeworkID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-id');
    setHomeworkDone(1, homeworkID);
  });

  $(document).on('click', '.undo-done-homework-btn', function(e) {
    e.preventDefault(false);
    var homeworkID = $(this)
      .parent()
      .parent()
      .parent()
      .attr('data-homework-id');
    setHomeworkDone(0, homeworkID);
  });

  function setHomeworkDone(done, homeworkID) {
    var url = apiURL + '/homework/' + homeworkID + '/done';
    $.ajax({
      url: url,
      type: 'put',
      data: { done: done },
      success: function(data, status) {
        if (data['message'] == 'Homework updated successfully.') {
          $.snackbar({
            content: 'Erfolgreich verschoben!'
          });
          loadContent(false);
          loadContent(true);
        } else {
          $.snackbar({
            content: 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.'
          });
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }
}

function initBugReportSite() {
  loadContent();

  function loadContent() {
    $('#overview').addLoadingSpinner();
    var url = apiURL + '/bugreport';
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (
          data['message'] != 'Error. Problem while fetching reports for user.'
        ) {
          data = parseBugReports(data['reports']);
          $('#overview')
            .html(data)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        } else {
          $('#main').html(
            '<div class="dialog-center"><h4>Keine Bug Reports verfügbar</h4></div>'
          );
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '.new-report-btn', function(e) {
    e.preventDefault();
    newReport();
  });

  $(document).on('click', '#send-report-btn', function(e) {
    e.preventDefault(false);
    if ($('#send-report').validateForm()) {
      sendReport();
    }
  });

  function sendReport() {
    var snackbarMessage = 'Report erfolgreich gesendet!';
    var url = apiURL + '/bugreport/save';
    var formName = '#send-report';
    $.ajax({
      url: url,
      type: 'post',
      data: $(formName).serialize(),
      success: function(data, status) {
        $.snackbar({
          content: snackbarMessage
        });
        $.modal.close();
        $('.modal').remove();
        loadContent();
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }
}

function initConsultationSite() {
  loadContent();

  function loadContent() {
    $('#overview').addLoadingSpinner();
    var url = apiURL + '/consultations/teacher';
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (data['message'] != 'Error. Problem while fetching consultations.') {
          data = parseConsultations(data['consultation']);
          $('#overview')
            .html(data)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        } else {
          $('#main').html(
            '<div class="dialog-center"><h4>In nächster Zeit findet kein Elternsprechtag statt.</h4></div>'
          );
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '#custom-btn-print', function() {
    generate();
  });

  function generate() {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    //        var yy = today.getYear();

    if (dd < 10) {
      dd = '0' + dd;
    }

    if (mm < 10) {
      mm = '0' + mm;
    }
    today = dd + '.' + mm;

    var doc = new jsPDF('p', 'pt');

    doc.text(
      'Anmeldungen zum Elternsprechtag am 14.11.16 (aktualisiert: ' +
        today +
        '.16)',
      40,
      50
    );
    var elem = document.getElementById('consultation-table');
    var res = doc.autoTableHtmlToJson(elem);
    doc.autoTable(res.columns, res.data, {
      headerStyles: {
        fillColor: [255, 89, 0],
        textColor: [255, 255, 255],
        halign: 'left'
      },
      margin: {
        top: 80
      },
      startY: 60,
      theme: 'striped'
    });

    doc.save('Anmeldungen-Elternsprechtag.pdf');
  }
}

function initCalendarSite() {
  var date = new Date();
  var d = date.getDate();
  var m = date.getMonth();
  var y = date.getFullYear();
  $('#overview').fullCalendar({
    displayEventTime: true, // Display event time
    // put your options and callbacks here
    events: [
      {
        title: 'event1',
        start: '2017-08-01'
      },
      {
        title: 'event2',
        start: '2017-08-01T12:30:00',
        end: '2017-08-01T14:30:00'
      },
      {
        title: 'event2',
        start: '2017-08-03T12:30:00',
        end: '2017-08-03T17:30:00'
      }
    ]
  });
}

function initSettingsSite() {
  loadContent();

  function loadContent() {
    $('#overview').addLoadingSpinner();
    var url = apiURL + '/account';
    $.ajax({
      url: url,
      type: 'get',
      success: function(data, status) {
        if (data['message'] != 'Error. Problem while fetching settings.') {
          data = parseSettings(data['settings']);
          $('#overview')
            .html(data)
            .promise()
            .done(function() {
              componentHandler.upgradeDom();
            });
        } else {
          $('#main').html(
            '<div class="dialog-center"><h4>Keine Einstellungen verfügbar.</h4></div>'
          );
        }
      },
      error: function(xhr, desc, err) {
        //console.log(xhr);
        //console.log("Details: " + desc + "\nError:" + err);
      },
      headers: { auth: store.get(activeUser).auth }
    });
  }

  $(document).on('click', '#edit-grade-btn', function() {
    if (store.get(activeUser).role == 'STUDENT') {
      var grade = $('#grade').attr('data-grade');
      initGradeSelection(true, grade);
    } else {
      initTeacherSelection(true);
    }
  });

  $(document).on('click', '#edit-courses-btn', function() {
    if (store.get(activeUser).role == 'STUDENT') {
      var grade = $('#grade').attr('data-grade');
      initCourseSelection(grade, true);
      $('.modal').remove();
    }
  });
}

/* Other Methods */

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
