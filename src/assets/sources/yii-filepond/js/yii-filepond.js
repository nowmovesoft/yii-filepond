/*!
 * Yii2 FilePond connection plugin.
 * Licensed under The 3-Clause BSD License, https://opensource.org/licenses/BSD-3-Clause
 * Please visit https://github.com/nowmovesoft/yii-filepond for details.
 */
(function(global, factory) {
    if (undefined === global.YiiFilePond) {
        global.YiiFilePond = factory();
    }
})(this, function() {
    'use strict';

    let instances = [];

    let register = function(options, connection) {
        instances.push(FilePond.create(document.querySelector('#' + connection.fieldId), options));
    };

    return {
        register: register,
    };
});
