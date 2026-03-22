(self["webpackChunkwww"] = self["webpackChunkwww"] || []).push([["app"],{

/***/ "./assets/app.js"
/*!***********************!*\
  !*** ./assets/app.js ***!
  \***********************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _styles_app_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./styles/app.css */ "./assets/styles/app.css");
/* harmony import */ var _js_scroll_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./js/scroll.js */ "./assets/js/scroll.js");
/* harmony import */ var _js_scroll_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_js_scroll_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _js_load_more_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./js/load-more.js */ "./assets/js/load-more.js");
/* harmony import */ var _js_load_more_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_js_load_more_js__WEBPACK_IMPORTED_MODULE_2__);
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */



console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

/***/ },

/***/ "./assets/js/load-more.js"
/*!********************************!*\
  !*** ./assets/js/load-more.js ***!
  \********************************/
() {

document.addEventListener('DOMContentLoaded', function () {
  var tricksSection = document.getElementById('tricks-container');
  var loadMoreBtn = document.getElementById('load-more');
  var loadText = loadMoreBtn.querySelector('.load-text');
  var spinner = loadMoreBtn.querySelector('.load-spinner');

  // LOAD MORE AJAX
  loadMoreBtn.addEventListener('click', function () {
    var page = parseInt(loadMoreBtn.dataset.page) + 1;

    // Afficher spinner, cacher texte, désactiver bouton
    loadText.classList.add('opacity-0');
    spinner.classList.remove('hidden');
    loadMoreBtn.disabled = true;
    fetch("/?page=".concat(page), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    }).then(function (response) {
      return response.text();
    }).then(function (html) {
      if (html.trim().length > 0) {
        tricksSection.insertAdjacentHTML('beforeend', html);
        loadMoreBtn.dataset.page = page;
      } else {
        loadMoreBtn.style.display = 'none';
      }
    })["catch"](function (err) {
      return console.error(err);
    })["finally"](function () {
      loadText.classList.remove('opacity-0');
      spinner.classList.add('hidden');
      loadMoreBtn.disabled = false;
    });
  });
});

/***/ },

/***/ "./assets/js/scroll.js"
/*!*****************************!*\
  !*** ./assets/js/scroll.js ***!
  \*****************************/
() {

document.addEventListener('DOMContentLoaded', function () {
  var scrollTopBtn = document.getElementById('scroll-top');
  var scrollDownBtn = document.getElementById('scroll-down');
  var tricksSection = document.getElementById('tricks-container');

  // DESCENDRE vers la grille
  scrollDownBtn.addEventListener('click', function () {
    tricksSection.scrollIntoView({
      behavior: 'smooth'
    });
  });

  // REMONTER en haut
  scrollTopBtn.addEventListener('click', function () {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  // Fonction toggle fade + slide
  function toggleButton(btn, show) {
    if (show) {
      btn.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
      btn.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
    } else {
      btn.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
      btn.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none');
    }
  }

  // Scroll event pour ↓ et ↑
  window.addEventListener('scroll', function () {
    var scrollY = window.scrollY;
    toggleButton(scrollDownBtn, scrollY < 200); // ↓ visible en haut
    toggleButton(scrollTopBtn, scrollY > 300); // ↑ visible après scroll
  });
});

/***/ },

/***/ "./assets/styles/app.css"
/*!*******************************!*\
  !*** ./assets/styles/app.css ***!
  \*******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/app.js"));
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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O0lBQ3pDZ0MsTUFBTSxDQUFDQyxRQUFRLENBQUM7TUFBRUMsR0FBRyxFQUFFLENBQUM7TUFBRUgsUUFBUSxFQUFFO0lBQVMsQ0FBQyxDQUFDO0VBQ25ELENBQUMsQ0FBQzs7RUFFRjtFQUNBLFNBQVNJLFlBQVlBLENBQUNDLEdBQUcsRUFBRUMsSUFBSSxFQUFFO0lBQzdCLElBQUlBLElBQUksRUFBRTtNQUNORCxHQUFHLENBQUMxQixTQUFTLENBQUNFLE1BQU0sQ0FBQyxXQUFXLEVBQUUsZ0JBQWdCLEVBQUUscUJBQXFCLENBQUM7TUFDMUV3QixHQUFHLENBQUMxQixTQUFTLENBQUNDLEdBQUcsQ0FBQyxhQUFhLEVBQUUsZUFBZSxFQUFFLHFCQUFxQixDQUFDO0lBQzVFLENBQUMsTUFBTTtNQUNIeUIsR0FBRyxDQUFDMUIsU0FBUyxDQUFDRSxNQUFNLENBQUMsYUFBYSxFQUFFLGVBQWUsRUFBRSxxQkFBcUIsQ0FBQztNQUMzRXdCLEdBQUcsQ0FBQzFCLFNBQVMsQ0FBQ0MsR0FBRyxDQUFDLFdBQVcsRUFBRSxnQkFBZ0IsRUFBRSxxQkFBcUIsQ0FBQztJQUMzRTtFQUNKOztFQUVBO0VBQ0FxQixNQUFNLENBQUNoQyxnQkFBZ0IsQ0FBQyxRQUFRLEVBQUUsWUFBTTtJQUNwQyxJQUFNc0MsT0FBTyxHQUFHTixNQUFNLENBQUNNLE9BQU87SUFDOUJILFlBQVksQ0FBQ04sYUFBYSxFQUFFUyxPQUFPLEdBQUcsR0FBRyxDQUFDLENBQUMsQ0FBQztJQUM1Q0gsWUFBWSxDQUFDUCxZQUFZLEVBQUVVLE9BQU8sR0FBRyxHQUFHLENBQUMsQ0FBQyxDQUFDO0VBQy9DLENBQUMsQ0FBQztBQUVOLENBQUMsQ0FBQyxDOzs7Ozs7Ozs7Ozs7QUNsQ0YiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly93d3cvLi9hc3NldHMvYXBwLmpzIiwid2VicGFjazovL3d3dy8uL2Fzc2V0cy9qcy9sb2FkLW1vcmUuanMiLCJ3ZWJwYWNrOi8vd3d3Ly4vYXNzZXRzL2pzL3Njcm9sbC5qcyIsIndlYnBhY2s6Ly93d3cvLi9hc3NldHMvc3R5bGVzL2FwcC5jc3M/NmJlNiJdLCJzb3VyY2VzQ29udGVudCI6WyIvKlxuICogV2VsY29tZSB0byB5b3VyIGFwcCdzIG1haW4gSmF2YVNjcmlwdCBmaWxlIVxuICpcbiAqIFRoaXMgZmlsZSB3aWxsIGJlIGluY2x1ZGVkIG9udG8gdGhlIHBhZ2UgdmlhIHRoZSBpbXBvcnRtYXAoKSBUd2lnIGZ1bmN0aW9uLFxuICogd2hpY2ggc2hvdWxkIGFscmVhZHkgYmUgaW4geW91ciBiYXNlLmh0bWwudHdpZy5cbiAqL1xuaW1wb3J0ICcuL3N0eWxlcy9hcHAuY3NzJztcblxuaW1wb3J0ICcuL2pzL3Njcm9sbC5qcyc7XG5pbXBvcnQgJy4vanMvbG9hZC1tb3JlLmpzJztcblxuXG5cbmNvbnNvbGUubG9nKCdUaGlzIGxvZyBjb21lcyBmcm9tIGFzc2V0cy9hcHAuanMgLSB3ZWxjb21lIHRvIEFzc2V0TWFwcGVyISDwn46JJyk7XG4iLCJkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXG4gICAgY29uc3QgdHJpY2tzU2VjdGlvbiA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd0cmlja3MtY29udGFpbmVyJyk7XG5cbiAgICBjb25zdCBsb2FkTW9yZUJ0biA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdsb2FkLW1vcmUnKTtcbiAgICBjb25zdCBsb2FkVGV4dCA9IGxvYWRNb3JlQnRuLnF1ZXJ5U2VsZWN0b3IoJy5sb2FkLXRleHQnKTtcbiAgICBjb25zdCBzcGlubmVyID0gbG9hZE1vcmVCdG4ucXVlcnlTZWxlY3RvcignLmxvYWQtc3Bpbm5lcicpO1xuXG4gICAgLy8gTE9BRCBNT1JFIEFKQVhcbiAgICBsb2FkTW9yZUJ0bi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsICgpID0+IHtcbiAgICAgICAgbGV0IHBhZ2UgPSBwYXJzZUludChsb2FkTW9yZUJ0bi5kYXRhc2V0LnBhZ2UpICsgMTtcblxuICAgICAgICAvLyBBZmZpY2hlciBzcGlubmVyLCBjYWNoZXIgdGV4dGUsIGTDqXNhY3RpdmVyIGJvdXRvblxuICAgICAgICBsb2FkVGV4dC5jbGFzc0xpc3QuYWRkKCdvcGFjaXR5LTAnKTtcbiAgICAgICAgc3Bpbm5lci5jbGFzc0xpc3QucmVtb3ZlKCdoaWRkZW4nKTtcbiAgICAgICAgbG9hZE1vcmVCdG4uZGlzYWJsZWQgPSB0cnVlO1xuXG4gICAgICAgIGZldGNoKGAvP3BhZ2U9JHtwYWdlfWAsIHtcbiAgICAgICAgICAgIGhlYWRlcnM6IHtcbiAgICAgICAgICAgICAgICAnWC1SZXF1ZXN0ZWQtV2l0aCc6ICdYTUxIdHRwUmVxdWVzdCdcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSkudGhlbihyZXNwb25zZSA9PiByZXNwb25zZS50ZXh0KCkpLnRoZW4oaHRtbCA9PiB7XG4gICAgICAgICAgICBpZiAoaHRtbC50cmltKCkubGVuZ3RoID4gMCkge1xuICAgICAgICAgICAgICAgIHRyaWNrc1NlY3Rpb24uaW5zZXJ0QWRqYWNlbnRIVE1MKCdiZWZvcmVlbmQnLCBodG1sKTtcbiAgICAgICAgICAgICAgICBsb2FkTW9yZUJ0bi5kYXRhc2V0LnBhZ2UgPSBwYWdlO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICBsb2FkTW9yZUJ0bi5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KS5jYXRjaChlcnIgPT4gY29uc29sZS5lcnJvcihlcnIpKS5maW5hbGx5KCgpID0+IHtcbiAgICAgICAgICAgIGxvYWRUZXh0LmNsYXNzTGlzdC5yZW1vdmUoJ29wYWNpdHktMCcpO1xuICAgICAgICAgICAgc3Bpbm5lci5jbGFzc0xpc3QuYWRkKCdoaWRkZW4nKTtcbiAgICAgICAgICAgIGxvYWRNb3JlQnRuLmRpc2FibGVkID0gZmFsc2U7XG4gICAgICAgIH0pO1xuICAgIH0pO1xuXG59KTsiLCJkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuXG4gICAgY29uc3Qgc2Nyb2xsVG9wQnRuID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3Njcm9sbC10b3AnKTtcbiAgICBjb25zdCBzY3JvbGxEb3duQnRuID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3Njcm9sbC1kb3duJyk7XG4gICAgY29uc3QgdHJpY2tzU2VjdGlvbiA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd0cmlja3MtY29udGFpbmVyJyk7XG5cbiAgICAvLyBERVNDRU5EUkUgdmVycyBsYSBncmlsbGVcbiAgICBzY3JvbGxEb3duQnRuLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgKCkgPT4ge1xuICAgICAgICB0cmlja3NTZWN0aW9uLnNjcm9sbEludG9WaWV3KHsgYmVoYXZpb3I6ICdzbW9vdGgnIH0pO1xuICAgIH0pO1xuXG4gICAgLy8gUkVNT05URVIgZW4gaGF1dFxuICAgIHNjcm9sbFRvcEJ0bi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsICgpID0+IHtcbiAgICAgICAgd2luZG93LnNjcm9sbFRvKHsgdG9wOiAwLCBiZWhhdmlvcjogJ3Ntb290aCcgfSk7XG4gICAgfSk7XG5cbiAgICAvLyBGb25jdGlvbiB0b2dnbGUgZmFkZSArIHNsaWRlXG4gICAgZnVuY3Rpb24gdG9nZ2xlQnV0dG9uKGJ0biwgc2hvdykge1xuICAgICAgICBpZiAoc2hvdykge1xuICAgICAgICAgICAgYnRuLmNsYXNzTGlzdC5yZW1vdmUoJ29wYWNpdHktMCcsICd0cmFuc2xhdGUteS0xMCcsICdwb2ludGVyLWV2ZW50cy1ub25lJyk7XG4gICAgICAgICAgICBidG4uY2xhc3NMaXN0LmFkZCgnb3BhY2l0eS0xMDAnLCAndHJhbnNsYXRlLXktMCcsICdwb2ludGVyLWV2ZW50cy1hdXRvJyk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBidG4uY2xhc3NMaXN0LnJlbW92ZSgnb3BhY2l0eS0xMDAnLCAndHJhbnNsYXRlLXktMCcsICdwb2ludGVyLWV2ZW50cy1hdXRvJyk7XG4gICAgICAgICAgICBidG4uY2xhc3NMaXN0LmFkZCgnb3BhY2l0eS0wJywgJ3RyYW5zbGF0ZS15LTEwJywgJ3BvaW50ZXItZXZlbnRzLW5vbmUnKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8vIFNjcm9sbCBldmVudCBwb3VyIOKGkyBldCDihpFcbiAgICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcignc2Nyb2xsJywgKCkgPT4ge1xuICAgICAgICBjb25zdCBzY3JvbGxZID0gd2luZG93LnNjcm9sbFk7XG4gICAgICAgIHRvZ2dsZUJ1dHRvbihzY3JvbGxEb3duQnRuLCBzY3JvbGxZIDwgMjAwKTsgLy8g4oaTIHZpc2libGUgZW4gaGF1dFxuICAgICAgICB0b2dnbGVCdXR0b24oc2Nyb2xsVG9wQnRuLCBzY3JvbGxZID4gMzAwKTsgLy8g4oaRIHZpc2libGUgYXByw6hzIHNjcm9sbFxuICAgIH0pO1xuXG59KTsiLCIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiXSwibmFtZXMiOlsiY29uc29sZSIsImxvZyIsImRvY3VtZW50IiwiYWRkRXZlbnRMaXN0ZW5lciIsInRyaWNrc1NlY3Rpb24iLCJnZXRFbGVtZW50QnlJZCIsImxvYWRNb3JlQnRuIiwibG9hZFRleHQiLCJxdWVyeVNlbGVjdG9yIiwic3Bpbm5lciIsInBhZ2UiLCJwYXJzZUludCIsImRhdGFzZXQiLCJjbGFzc0xpc3QiLCJhZGQiLCJyZW1vdmUiLCJkaXNhYmxlZCIsImZldGNoIiwiY29uY2F0IiwiaGVhZGVycyIsInRoZW4iLCJyZXNwb25zZSIsInRleHQiLCJodG1sIiwidHJpbSIsImxlbmd0aCIsImluc2VydEFkamFjZW50SFRNTCIsInN0eWxlIiwiZGlzcGxheSIsImVyciIsImVycm9yIiwic2Nyb2xsVG9wQnRuIiwic2Nyb2xsRG93bkJ0biIsInNjcm9sbEludG9WaWV3IiwiYmVoYXZpb3IiLCJ3aW5kb3ciLCJzY3JvbGxUbyIsInRvcCIsInRvZ2dsZUJ1dHRvbiIsImJ0biIsInNob3ciLCJzY3JvbGxZIl0sInNvdXJjZVJvb3QiOiIifQ==