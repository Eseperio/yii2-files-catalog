"use strict";

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/*
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */
var filexIndex = /*#__PURE__*/function () {
  function filexIndex(settings) {
    _classCallCheck(this, filexIndex);

    this.GRID_SELECTOR = ".filex-grid";
    this.settings = {
      //Class to be applied for those rows selected
      activeRowClass: 'info'
    };
    this.settings = $.extend(this.settings, settings);
    this.attachEvents();
  }

  _createClass(filexIndex, [{
    key: "attachEvents",
    value: function attachEvents() {
      var _this = this;

      var checkBoxSelector = '[name^="filex-bulk-action"]';
      $(document) //Handle click on row
      .on('click', '.filex-grid tbody td', function (e) {
        console.log("Clicked", this);
        if (['a', 'button', 'input'].indexOf(e.target.tagName) >= 0) return;
        var checkBox = $(this).parent().find(checkBoxSelector);
        checkBox.prop('checked', !checkBox.prop('checked'));

        if (e.shiftKey) {
          var shiftClick = jQuery.Event('click');
          shiftClick.shiftKey = true;
          checkBox.trigger(shiftClick);
        } else {
          checkBox.click();
        }

        checkBox.click();
      }) // Handle click on checkboxes
      .on('click', checkBoxSelector, function (e) {
        e.stopPropagation();
        var row = $(e.currentTarget).closest('tr');

        var rowRealObj = row.get(0),
            allRows = _toConsumableArray(rowRealObj.parentElement.children),
            currentIndex = allRows.indexOf(rowRealObj);

        if (e.shiftKey) {
          allRows.forEach(function (e, i) {
            if (_this.lastRowSelectedIdx < currentIndex) {
              if (i > _this.lastRowSelectedIdx && i < currentIndex) {
                $(e).find(checkBoxSelector).prop('checked', true).change();
              }
            } else if (_this.lastRowSelectedIdx > currentIndex) {
              if (i < _this.lastRowSelectedIdx && i > currentIndex) {
                $(e).find(checkBoxSelector).prop('checked', true).change();
              }
            }
          });
        } else {
          _this.lastRowSelectedIdx = currentIndex;
        }
      }).on('change', checkBoxSelector, function (e) {
        var row = $(e.currentTarget).closest('tr');
        row.toggleClass(_this.settings.activeRowClass, $(e.currentTarget).prop('checked'));

        var keys = _this.getSelectedRows();

        $('.filex-bulk-actions').toggleClass('collapse', !keys.length > 0);
        var params = {};
        keys.forEach(function (e, i, a) {
          params['uuids[' + i + ']'] = e;
        });
        $(".filex-bulk-delete,.filex-bulk-acl,.filex-bulk-download").data('params', params);
      });
    }
  }, {
    key: "getSelectedRows",
    value: function getSelectedRows() {
      var $grid = $(this.GRID_SELECTOR);
      var keys = [];
      $grid.find('[name^="filex-bulk-action"]:checked').each(function () {
        keys.push($(this).val());
      });
      return keys;
    }
  }]);

  return filexIndex;
}();