/**
* @module secretsanta/ajax/base
*/
jn.define("secretsanta/ajax/base", (require, exports, module) => {
    const { RunActionExecutor } = require("rest/run-action-executor");
    
    /**
    * @class BaseAjax
    * @abstract
    */
    class BaseAjax {
        /**
         * @abstract
         * @return {String}
         */
        getEndpoint() {
            throw new Error("Abstract method must be implemented in child class");
        }
        
        /**
         * @public
         * @param {String} action
         * @param {Object|null} [ajaxParams]
         * @return {Promise<Object,void>}
         */
        fetch(action, ajaxParams = null) {
            return new Promise((resolve) => {
                const endpoint = `${this.getEndpoint()}.${action}`;

                new RunActionExecutor(endpoint, ajaxParams)
                    .setHandler((result) => resolve(result))
                    .call(false);
            });
        }
    }
    
    module.exports = {
        BaseAjax
    };
});
