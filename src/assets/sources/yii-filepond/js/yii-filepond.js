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

    const FILEPOND_ENDPOINTS = [
        'process',
        'revert',
        'load',
        'restore',
        'fetch',
        'patch',
    ];

    let instances = [];

    let addServerOptions = function(filePond, connection) {
        let onLoad = (response) => {
            response = JSON.parse(response);
            $('#' + connection.fieldId).siblings('.help-block').text('');
            return response.key;
        };

        let onError = (response) => {
            response = JSON.parse(response);
            $('#' + connection.fieldId).siblings('.help-block').text(response.message);
            return response.message;
        };

        if (!('server' in filePond)) {
            filePond['server'] = {};
        }

        for (let endpoint of FILEPOND_ENDPOINTS) {
            if (endpoint in filePond['server']) {
                continue;
            }

            let options = {
                url: connection['endpoints'][endpoint],
                headers: {
                    'X-CSRF-Token': yii.getCsrfToken(),
                    'X-Session-Id': connection['sessionId'],
                },
                onerror: onError,
            };

            if ('process' == endpoint) {
                options['onload'] = onLoad;
            }

            filePond['server'][endpoint] = options;
        }

        return filePond;
    }

    let register = function(options, connection) {
        options = addServerOptions(options, connection);
        instances.push(FilePond.create(document.querySelector('#' + connection.fieldId), options));
    };

    return {
        register: register,
    };
});
