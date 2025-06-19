/**
* @module secretsanta/ajax/action
*/
jn.define("secretsanta/ajax/action", (require, exports, module) => {
    const { BaseAjax } = require("secretsanta/ajax/base");
    
    const Actions = {
        config: "getConfig",
        player: "getPlayer",
        takePart: "updateTakePart",
        wishlist: "updateWishlist"
    };
    
    /**
     * @class ActionAjax
     */
    class ActionAjax extends BaseAjax {
        getEndpoint() {
            return "brix:secretsanta.secretsanta";
        }
        
        /**
         * @param {String} action
         * @param {Object} params
         * @return {Promise<Object, void>}
         */
        get(action, params = null) {
            return this.fetch(Actions[action], params);
        }

        /**
         * @param {String} action
         * @param {Object} params
         * @return {Promise<Object, void>}
         */
        post(action, params = null) {
            return this.fetch(Actions[action], params);
        }
    }
    
    module.exports = {
        ActionAjax: new ActionAjax()
    };
});
