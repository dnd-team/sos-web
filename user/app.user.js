/*
 * SOS Main Student
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
  init();
  var site = getURLParameter('site');
  if (site == 'home') {
  } else if (site == 'plan') {
    updateContainers(['timetable-t']);
    var desc = getDayTitles();
    updateTabController(
      [desc[0], desc[1]],
      ['main-section', 'timetable-t-section'],
      'timetable-tab-menu'
    );
    updateActionButtons('plan');
    updateOptionMenu(
      [
        'Vetretungsplan &ouml;ffnen',
        'Vertretungsplan teilen',
        'Stundenplanübersicht &ouml;ffnen',
        'MENU'
      ],
      ['open-old-vp-view', 'share-vp', 'open-timetable'],
      ['assignment_turned_in', 'share', 'assignment']
    );
    initTimetableSite();
  } else if (site == 'aushang') {
    updateContainers(['user-aushang']);
    updateTabController(
      ['Aushang', 'Meine Aush&auml;nge'],
      ['main-section', 'user-aushang-section'],
      'aushang-tab-menu'
    );
    updateOptionMenu([], [], []);
    updateActionButtons('aushang');
    initAushangSite();
  } else if (site == 'courses') {
    updateContainers(['course-members', 'course-entry']);
    updateTabController(
      ['Auftr&auml;ge', 'Kursmitglieder', 'Kurstagebuch'],
      ['main-section', 'course-members-section', 'course-entry-section'],
      'courses-tab-menu'
    );
    updateActionButtons('courses');
    updateOptionMenu([], [], []);
    initCourseSite();
  } else if (site == 'homework') {
    updateContainers(['homework-done']);
    updateTabController(
      ['Offene Aufgaben', 'Erledigte Aufgaben'],
      ['main-section', 'homework-done-section'],
      'homework-tab-menu'
    );
    updateOptionMenu([], [], []);
    updateActionButtons('homework');
    initHomeworkSite();
  } else if (site == 'calendar') {
    updateOptionMenu([], [], []);
    updateActionButtons('calendar');
    initCalendarSite();
  } else if (site == 'consultations') {
    updateActionButtons('consultations');
    updateOptionMenu([], [], []);
    initConsultationSite();
  } else if (site == 'bugreport') {
    updateActionButtons('bugreport');
    updateOptionMenu([], [], []);
    initBugReportSite();
  } else if (site == 'settings') {
    updateActionButtons('settings');
    updateOptionMenu([], [], []);
    initSettingsSite();
  }
});
