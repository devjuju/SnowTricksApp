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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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O0lBQ3pDZ0MsTUFBTSxDQUFDQyxRQUFRLENBQUM7TUFBRUMsR0FBRyxFQUFFLENBQUM7TUFBRUgsUUFBUSxFQUFFO0lBQVMsQ0FBQyxDQUFDO0VBQ25ELENBQUMsQ0FBQzs7RUFFRjtFQUNBLFNBQVNJLFlBQVlBLENBQUNDLEdBQUcsRUFBRUMsSUFBSSxFQUFFO0lBQzdCLElBQUlBLElBQUksRUFBRTtNQUNORCxHQUFHLENBQUMxQixTQUFTLENBQUNFLE1BQU0sQ0FBQyxXQUFXLEVBQUUsZ0JBQWdCLEVBQUUscUJBQXFCLENBQUM7TUFDMUV3QixHQUFHLENBQUMxQixTQUFTLENBQUNDLEdBQUcsQ0FBQyxhQUFhLEVBQUUsZUFBZSxFQUFFLHFCQUFxQixDQUFDO0lBQzVFLENBQUMsTUFBTTtNQUNIeUIsR0FBRyxDQUFDMUIsU0FBUyxDQUFDRSxNQUFNLENBQUMsYUFBYSxFQUFFLGVBQWUsRUFBRSxxQkFBcUIsQ0FBQztNQUMzRXdCLEdBQUcsQ0FBQzFCLFNBQVMsQ0FBQ0MsR0FBRyxDQUFDLFdBQVcsRUFBRSxnQkFBZ0IsRUFBRSxxQkFBcUIsQ0FBQztJQUMzRTtFQUNKOztFQUVBO0VBQ0FxQixNQUFNLENBQUNoQyxnQkFBZ0IsQ0FBQyxRQUFRLEVBQUUsWUFBTTtJQUNwQyxJQUFNc0MsT0FBTyxHQUFHTixNQUFNLENBQUNNLE9BQU87SUFDOUJILFlBQVksQ0FBQ04sYUFBYSxFQUFFUyxPQUFPLEdBQUcsR0FBRyxDQUFDLENBQUMsQ0FBQztJQUM1Q0gsWUFBWSxDQUFDUCxZQUFZLEVBQUVVLE9BQU8sR0FBRyxHQUFHLENBQUMsQ0FBQyxDQUFDO0VBQy9DLENBQUMsQ0FBQztBQUVOLENBQUMsQ0FBQyxDOzs7Ozs7Ozs7Ozs7QUNsQ0YiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly93d3cvLi9hc3NldHMvYXBwLmpzIiwid2VicGFjazovL3d3dy8uL2Fzc2V0cy9qcy9sb2FkLW1vcmUuanMiLCJ3ZWJwYWNrOi8vd3d3Ly4vYXNzZXRzL2pzL3Njcm9sbC5qcyIsIndlYnBhY2s6Ly93d3cvLi9hc3NldHMvc3R5bGVzL2FwcC5jc3MiXSwic291cmNlc0NvbnRlbnQiOlsiLypcbiAqIFdlbGNvbWUgdG8geW91ciBhcHAncyBtYWluIEphdmFTY3JpcHQgZmlsZSFcbiAqXG4gKiBUaGlzIGZpbGUgd2lsbCBiZSBpbmNsdWRlZCBvbnRvIHRoZSBwYWdlIHZpYSB0aGUgaW1wb3J0bWFwKCkgVHdpZyBmdW5jdGlvbixcbiAqIHdoaWNoIHNob3VsZCBhbHJlYWR5IGJlIGluIHlvdXIgYmFzZS5odG1sLnR3aWcuXG4gKi9cbmltcG9ydCAnLi9zdHlsZXMvYXBwLmNzcyc7XG5cbmltcG9ydCAnLi9qcy9zY3JvbGwuanMnO1xuaW1wb3J0ICcuL2pzL2xvYWQtbW9yZS5qcyc7XG5cblxuXG5jb25zb2xlLmxvZygnVGhpcyBsb2cgY29tZXMgZnJvbSBhc3NldHMvYXBwLmpzIC0gd2VsY29tZSB0byBBc3NldE1hcHBlciEg8J+OiScpO1xuIiwiZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcblxuICAgIGNvbnN0IHRyaWNrc1NlY3Rpb24gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgndHJpY2tzLWNvbnRhaW5lcicpO1xuXG4gICAgY29uc3QgbG9hZE1vcmVCdG4gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbG9hZC1tb3JlJyk7XG4gICAgY29uc3QgbG9hZFRleHQgPSBsb2FkTW9yZUJ0bi5xdWVyeVNlbGVjdG9yKCcubG9hZC10ZXh0Jyk7XG4gICAgY29uc3Qgc3Bpbm5lciA9IGxvYWRNb3JlQnRuLnF1ZXJ5U2VsZWN0b3IoJy5sb2FkLXNwaW5uZXInKTtcblxuICAgIC8vIExPQUQgTU9SRSBBSkFYXG4gICAgbG9hZE1vcmVCdG4uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PiB7XG4gICAgICAgIGxldCBwYWdlID0gcGFyc2VJbnQobG9hZE1vcmVCdG4uZGF0YXNldC5wYWdlKSArIDE7XG5cbiAgICAgICAgLy8gQWZmaWNoZXIgc3Bpbm5lciwgY2FjaGVyIHRleHRlLCBkw6lzYWN0aXZlciBib3V0b25cbiAgICAgICAgbG9hZFRleHQuY2xhc3NMaXN0LmFkZCgnb3BhY2l0eS0wJyk7XG4gICAgICAgIHNwaW5uZXIuY2xhc3NMaXN0LnJlbW92ZSgnaGlkZGVuJyk7XG4gICAgICAgIGxvYWRNb3JlQnRuLmRpc2FibGVkID0gdHJ1ZTtcblxuICAgICAgICBmZXRjaChgLz9wYWdlPSR7cGFnZX1gLCB7XG4gICAgICAgICAgICBoZWFkZXJzOiB7XG4gICAgICAgICAgICAgICAgJ1gtUmVxdWVzdGVkLVdpdGgnOiAnWE1MSHR0cFJlcXVlc3QnXG4gICAgICAgICAgICB9XG4gICAgICAgIH0pLnRoZW4ocmVzcG9uc2UgPT4gcmVzcG9uc2UudGV4dCgpKS50aGVuKGh0bWwgPT4ge1xuICAgICAgICAgICAgaWYgKGh0bWwudHJpbSgpLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgICAgICB0cmlja3NTZWN0aW9uLmluc2VydEFkamFjZW50SFRNTCgnYmVmb3JlZW5kJywgaHRtbCk7XG4gICAgICAgICAgICAgICAgbG9hZE1vcmVCdG4uZGF0YXNldC5wYWdlID0gcGFnZTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgbG9hZE1vcmVCdG4uc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSkuY2F0Y2goZXJyID0+IGNvbnNvbGUuZXJyb3IoZXJyKSkuZmluYWxseSgoKSA9PiB7XG4gICAgICAgICAgICBsb2FkVGV4dC5jbGFzc0xpc3QucmVtb3ZlKCdvcGFjaXR5LTAnKTtcbiAgICAgICAgICAgIHNwaW5uZXIuY2xhc3NMaXN0LmFkZCgnaGlkZGVuJyk7XG4gICAgICAgICAgICBsb2FkTW9yZUJ0bi5kaXNhYmxlZCA9IGZhbHNlO1xuICAgICAgICB9KTtcbiAgICB9KTtcblxufSk7IiwiZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcblxuICAgIGNvbnN0IHNjcm9sbFRvcEJ0biA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzY3JvbGwtdG9wJyk7XG4gICAgY29uc3Qgc2Nyb2xsRG93bkJ0biA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzY3JvbGwtZG93bicpO1xuICAgIGNvbnN0IHRyaWNrc1NlY3Rpb24gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgndHJpY2tzLWNvbnRhaW5lcicpO1xuXG4gICAgLy8gREVTQ0VORFJFIHZlcnMgbGEgZ3JpbGxlXG4gICAgc2Nyb2xsRG93bkJ0bi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsICgpID0+IHtcbiAgICAgICAgdHJpY2tzU2VjdGlvbi5zY3JvbGxJbnRvVmlldyh7IGJlaGF2aW9yOiAnc21vb3RoJyB9KTtcbiAgICB9KTtcblxuICAgIC8vIFJFTU9OVEVSIGVuIGhhdXRcbiAgICBzY3JvbGxUb3BCdG4uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PiB7XG4gICAgICAgIHdpbmRvdy5zY3JvbGxUbyh7IHRvcDogMCwgYmVoYXZpb3I6ICdzbW9vdGgnIH0pO1xuICAgIH0pO1xuXG4gICAgLy8gRm9uY3Rpb24gdG9nZ2xlIGZhZGUgKyBzbGlkZVxuICAgIGZ1bmN0aW9uIHRvZ2dsZUJ1dHRvbihidG4sIHNob3cpIHtcbiAgICAgICAgaWYgKHNob3cpIHtcbiAgICAgICAgICAgIGJ0bi5jbGFzc0xpc3QucmVtb3ZlKCdvcGFjaXR5LTAnLCAndHJhbnNsYXRlLXktMTAnLCAncG9pbnRlci1ldmVudHMtbm9uZScpO1xuICAgICAgICAgICAgYnRuLmNsYXNzTGlzdC5hZGQoJ29wYWNpdHktMTAwJywgJ3RyYW5zbGF0ZS15LTAnLCAncG9pbnRlci1ldmVudHMtYXV0bycpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgYnRuLmNsYXNzTGlzdC5yZW1vdmUoJ29wYWNpdHktMTAwJywgJ3RyYW5zbGF0ZS15LTAnLCAncG9pbnRlci1ldmVudHMtYXV0bycpO1xuICAgICAgICAgICAgYnRuLmNsYXNzTGlzdC5hZGQoJ29wYWNpdHktMCcsICd0cmFuc2xhdGUteS0xMCcsICdwb2ludGVyLWV2ZW50cy1ub25lJyk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvLyBTY3JvbGwgZXZlbnQgcG91ciDihpMgZXQg4oaRXG4gICAgd2luZG93LmFkZEV2ZW50TGlzdGVuZXIoJ3Njcm9sbCcsICgpID0+IHtcbiAgICAgICAgY29uc3Qgc2Nyb2xsWSA9IHdpbmRvdy5zY3JvbGxZO1xuICAgICAgICB0b2dnbGVCdXR0b24oc2Nyb2xsRG93bkJ0biwgc2Nyb2xsWSA8IDIwMCk7IC8vIOKGkyB2aXNpYmxlIGVuIGhhdXRcbiAgICAgICAgdG9nZ2xlQnV0dG9uKHNjcm9sbFRvcEJ0biwgc2Nyb2xsWSA+IDMwMCk7IC8vIOKGkSB2aXNpYmxlIGFwcsOocyBzY3JvbGxcbiAgICB9KTtcblxufSk7IiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sIm5hbWVzIjpbImNvbnNvbGUiLCJsb2ciLCJkb2N1bWVudCIsImFkZEV2ZW50TGlzdGVuZXIiLCJ0cmlja3NTZWN0aW9uIiwiZ2V0RWxlbWVudEJ5SWQiLCJsb2FkTW9yZUJ0biIsImxvYWRUZXh0IiwicXVlcnlTZWxlY3RvciIsInNwaW5uZXIiLCJwYWdlIiwicGFyc2VJbnQiLCJkYXRhc2V0IiwiY2xhc3NMaXN0IiwiYWRkIiwicmVtb3ZlIiwiZGlzYWJsZWQiLCJmZXRjaCIsImNvbmNhdCIsImhlYWRlcnMiLCJ0aGVuIiwicmVzcG9uc2UiLCJ0ZXh0IiwiaHRtbCIsInRyaW0iLCJsZW5ndGgiLCJpbnNlcnRBZGphY2VudEhUTUwiLCJzdHlsZSIsImRpc3BsYXkiLCJlcnIiLCJlcnJvciIsInNjcm9sbFRvcEJ0biIsInNjcm9sbERvd25CdG4iLCJzY3JvbGxJbnRvVmlldyIsImJlaGF2aW9yIiwid2luZG93Iiwic2Nyb2xsVG8iLCJ0b3AiLCJ0b2dnbGVCdXR0b24iLCJidG4iLCJzaG93Iiwic2Nyb2xsWSJdLCJzb3VyY2VSb290IjoiIn0=