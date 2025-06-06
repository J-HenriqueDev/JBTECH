/**
 * Config
 * -------------------------------------------------------------------------------------
 * ! IMPORTANT: Make sure you clear the browser local storage In order to see the config changes in the template.
 * ! To clear local storage: (https://www.leadshook.com/help/how-to-clear-local-storage-in-google-chrome-browser/).
 */

'use strict';

// JS global variables
window.config = {
  colors: {
    primary: '#000007',
    secondary: '#000007',
    success: '#71dd37',
    info: '#03c3ec',
    warning: '#ffab00',
    danger: '#ff3e1d',
    dark: '#233446',
    black: '#22303e',
    white: '#fff',
    cardColor: '#fff',
    bodyBg: '#f5f5f9',
    bodyColor: '#646E78',
    headingColor: '#384551',
    textMuted: '#a7acb2',
    borderColor: '#e4e6e8'
  },
  colors_label: {
    primary: '#000007',
    secondary: '#8592a329',
    success: '#71dd3729',
    info: '#03c3ec29',
    warning: '#ffab0029',
    danger: '#ff3e1d29',
    dark: '#181c211a'
  },
  colors_dark: {
    cardColor: '#2b2c40',
    bodyBg: '#232333',
    bodyColor: '#b2b2c4',
    headingColor: '#d5d5e2',
    textMuted: '#7e7f96',
    borderColor: '#4e4f6c'
  },
  enableMenuLocalStorage: true // Enable menu state with local storage support
};

window.assetsPath = document.documentElement.getAttribute('data-assets-path');
window.baseUrl = document.documentElement.getAttribute('data-base-url') + '/';
window.templateName = document.documentElement.getAttribute('data-template');
window.rtlSupport = true; // set true for rtl support (rtl + ltr), false for ltr only.
