"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var KiteTemplateEngine = /** @class */ (function () {
    function KiteTemplateEngine(template, data) {
        this.formatters = {
            "date": function (v) {
                var parts = v.split("-");
                var months = ["January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"];
                if (parts.length != 3) {
                    return v;
                }
                return "".concat(months[parseInt(parts[1], 10) - 1], " ").concat(parseInt(parts[2], 10), ", ").concat(parts[0]);
            },
            "escaped": function (v) {
                return v.replace("&", "&amp;")
                    .replace("\"", "&quot;")
                    .replace("'", "&#39;")
                    .replace("<", "&lt;")
                    .replace(">", "&gt;");
            }
        };
        if (template) {
            var compiler = this.compile(template);
            return data === undefined ? compiler : compiler(data, this.formatters);
        }
    }
    KiteTemplateEngine.prototype.log = function (text) {
        if (console)
            console.log("kite:" + text);
    };
    KiteTemplateEngine.prototype.compile = function (template) {
        var _this = this;
        var out = "";
        var parts = [];
        var root = null;
        var context = null;
        var contextIndex = 0;
        var contextSet = null;
        // Implementation of all the helper functions
        var execRange = function (fromIndex, toIndex) {
            for (var i = fromIndex + 1; i < toIndex;) {
                var el = parts[i];
                if (i & 1) {
                    if (el)
                        i += el();
                    else
                        ++i;
                }
                else {
                    out += el;
                    ++i;
                }
            }
        };
        var execBlock = function (data, fromIndex, toIndex) {
            var savedContext = context;
            var savedIndex = contextIndex;
            var savedSet = contextSet;
            if (Array.isArray(data)) {
                contextSet = data;
                for (contextIndex = 0; contextIndex < data.length; contextIndex++) {
                    context = data[contextIndex];
                    execRange(fromIndex, toIndex);
                }
            }
            else {
                context = data;
                contextIndex = undefined;
                execRange(fromIndex, toIndex);
            }
            context = savedContext;
            contextIndex = savedIndex;
            contextSet = savedSet;
        };
        // Rest of implementation...
        // (Include all the other functions from the original with TypeScript types)
        return function (data, formatters) {
            root = data;
            out = "";
            _this.formatters = formatters || _this.formatters;
            execBlock(Array.isArray(data) ? { "": data } : data, -1, parts.length);
            return out;
        };
    };
    return KiteTemplateEngine;
}());
// Attach to window
window.kite = function (template, data) {
    return new KiteTemplateEngine(template, data);
};
exports.default = KiteTemplateEngine;
