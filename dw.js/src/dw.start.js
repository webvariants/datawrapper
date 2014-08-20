//
// NOTE: This file is auto-generated using grunt
// from the source files in /dw.js/src/.
//
// If you want to change anything you need to change it
// in the source files and then re-run `grunt`, or
// otherwise your changes will be lost on the make.
//

(function(){

    var root = this,
        dw = {};

    // if (typeof 'define' !== 'undefined' && define.amd) {
    //     // make define backward compatible
    //     root.dw = dw;
    //     define(dw);
    // } else
    if (typeof exports !== 'undefined') {
        if (typeof module !== 'undefined' && module.exports) {
            exports = module.exports = dw;
        }
        exports.dw = dw;
    } else {
        window.dw = dw;
    }
