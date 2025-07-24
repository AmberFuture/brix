class BrixBtnSettings {
    /**
     * Constructor
     */
    constructor() {
        this.container = document.querySelector(".pagetitle-align-right-container") ?? document.querySelector(".ui-toolbar-right-buttons");

        if (this.container) {
            this.btnCreate();
        }
    }

    /**
     * Creates a button to open the settings
     */
    btnCreate() {
        let button = document.createElement("button");
        button.classList.add("ui-btn", "ui-btn-light-border", "ui-btn-themes", "ui-btn-icon-copy");
        button.setAttribute("title", BX.message("BRIX_LINKEDTASKFIELDS_JS_BTN_TITLE"));
        this.container.prepend(button);
        button.addEventListener("click", () => {
            this.open();
        });
    }

    /**
     * Opens the list of settings
     */
    open() {
        let url = "/bitrix/tools/brix-linkedtaskfields/list.php";
        BX.SidePanel.Instance.open(url, {
            width: 1830,
            allowChangeHistory: false,
            cacheable: false,
            requestMethod: "get",
            events: {
                onMessage: function(event) {
                    if (event.eventId === "linked-reload") {
                        BX.SidePanel.Instance.getSlider(url).reload();
                    }
                }
            }
        });

        BX.addCustomEvent("SidePanel.Slider:onDestroyComplete", (event) => {
            if (event.getSlider().getUrl() === url) {
                if (document.querySelectorAll(".brix-linked-modal")) {
                    document.querySelectorAll(".brix-linked-modal").forEach(dialog => {
                        dialog.remove();
                    });
                }
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    "use strict";
    
    new BrixBtnSettings();
});