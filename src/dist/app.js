/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/ts/app.ts");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/ts/app.ts":
/*!***********************!*\
  !*** ./src/ts/app.ts ***!
  \***********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\r\n(function () {\r\n    const dir = document.querySelector('.dir');\r\n    dir.addEventListener('click', function () {\r\n        const copyFrom = document.createElement('textarea');\r\n        copyFrom.textContent = dir.dataset.dir;\r\n        const bodyElement = document.getElementsByTagName('body')[0];\r\n        bodyElement.appendChild(copyFrom);\r\n        copyFrom.select();\r\n        document.execCommand('copy');\r\n        bodyElement.removeChild(copyFrom);\r\n        window.open();\r\n    }, false);\r\n    const toggle = document.querySelector('span.toggle');\r\n    function toggleFileList() {\r\n        const files = document.querySelector('div.phpfiles');\r\n        if (files.style.display === 'none') {\r\n            toggle.innerHTML = '<span class=\"icon-chevron-down\"></span>';\r\n            files.style.display = 'block';\r\n        }\r\n        else {\r\n            toggle.innerHTML = '<span class=\"icon-chevron-right\"></span>';\r\n            files.style.display = 'none';\r\n        }\r\n    }\r\n    if (toggle) {\r\n        toggle.addEventListener('click', toggleFileList);\r\n    }\r\n    const targetedElements = [];\r\n    function targetNameChange() {\r\n        if (location.hash) {\r\n            const className = location.hash.replace('#', '');\r\n            const targetElement = document.querySelector('span.' + className);\r\n            targetedElements.forEach(function (element) {\r\n                element.style.backgroundColor = '';\r\n                element.style.color = '';\r\n            });\r\n            if (!targetedElements.includes(targetElement)) {\r\n                targetedElements.push(targetElement);\r\n            }\r\n            targetElement.style.backgroundColor = '\"#e2e6ff';\r\n            targetElement.style.color = '#191919';\r\n        }\r\n    }\r\n    window.onhashchange = targetNameChange;\r\n    targetNameChange();\r\n    const links = document.querySelectorAll('[role=\"link\"]');\r\n    const pressedLinks = [];\r\n    links.forEach(function (link) {\r\n        link.addEventListener('mouseup', function (event) {\r\n            if (event.which === 1 || event.which === 2) {\r\n                link.style.backgroundColor = '';\r\n                link.style.color = '';\r\n            }\r\n            if (!pressedLinks.includes(link)) {\r\n                pressedLinks.push(link);\r\n            }\r\n            link.style.backgroundColor = '#dcf7d4';\r\n            link.style.color = '#191919';\r\n        });\r\n    });\r\n    const toggleButton = document.querySelector('button[role=\"toggle_input_text\"]');\r\n    const inputTextAndButton = document.querySelector('div[role=\"input_text_and_button\"]');\r\n    function toggleInputTextAndButton(event) {\r\n        if (inputTextAndButton.classList.contains('none')) {\r\n            inputTextAndButton.classList.remove('none');\r\n            event.target.classList.replace('icon-chevron-right', 'icon-chevron-left');\r\n        }\r\n        else {\r\n            inputTextAndButton.classList.add('none');\r\n            event.target.classList.replace('icon-chevron-left', 'icon-chevron-right');\r\n        }\r\n    }\r\n    toggleButton.addEventListener('click', toggleInputTextAndButton);\r\n})();\r\n\n\n//# sourceURL=webpack:///./src/ts/app.ts?");

/***/ })

/******/ });