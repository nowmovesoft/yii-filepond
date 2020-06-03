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

    let addServerOptions = function(filePond, connection) {
        const FILEPOND_ENDPOINTS = [
            'process',
            'revert',
            'load',
            'restore',
            'fetch',
            'patch',
        ];

        let onLoad = function(response) {
            response = JSON.parse(response);
            $('#' + connection.fieldId).siblings('.help-block').text('');
            return response.key;
        };

        let onError = function(response) {
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
    };

    /**
     * Creates validate method for `minFiles` option.
     * @param {Object} field jQuery object of validated field
     * @param {number} value minimum number of uploaded files
     * @param {string} message error message, if files are too few
     * @return {function}
     */
    let minFilesValidator = function(field, value, message) {
        let helpBlock = field.siblings('.help-block');
        let minFiles = value;
        let errorMessage = message;

        /**
         * Validate minimum uploaded files number.
         * @param {Object} pond FilePond instance
         * @return {boolean}
         */
        return function(pond) {
            let message = '';
            let status = true;

            if (pond.getFiles().length < minFiles) {
                message = errorMessage;
                status = false;
            }

            helpBlock.text(message);

            return status;
        };
    };

    let addValidators = function(field, filePond) {
        let validators = [];

        if ('minFiles' in filePond) {
            validators.push(minFilesValidator(field, filePond['minFiles'], filePond['labelTooFew']));
        }

        return validators;
    };

    let validate = function(formId) {
        for (let field of instances[formId]) {
            for (let validator of field['validators']) {
                if (!validator(field['filePond'])) {
                    return false;
                }
            }
        }

        return true;
    };

    let instances = {};

    /**
     * Registers FilePond for specified field.
     * @param {Object} options FilePond options
     * @param {Object} connection Yii-field to FilePond connection options
     */
    let register = function(options, connection) {
        options = addServerOptions(options, connection);

        let form = $('#' + connection.fieldId).closest('form');
        let formId = form.attr('id');

        if (!(formId in instances)) {
            instances[formId] = [];

            form.on('beforeValidate', function(event, messages, deferreds) {
                return validate($(this).attr('id'));
            });
        }

        instances[formId].push({
            validators: addValidators($('#' + connection.fieldId), options),
            filePond: FilePond.create(document.querySelector('#' + connection.fieldId), options),
        });
    };

    return {
        register: register,
    };
});
