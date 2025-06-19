class BrixSecretSantaProfile {
    /**
     * Constructor
     */
    constructor() {
        this.player = {};
        this.recipient = {};
        this.config = {};
        this.type = false;
        this.container = document.querySelector(".intranet-user-profile-column-right");

        if (this.container) {
            this.getConfig();
        }
    }

    /**
     * Getting basic information about the game
     */
    getConfig() {
        let $this = this;

        BX.ajax.runAction("brix:secretsanta.Secretsanta.getConfig", {
            method: "get"
        }).then((response) => {
            if (response.data) {
                $this.config = response.data ?? {};
                $this.getPlayer();
            }
        }, (response) => {
            console.error(response);
        });
    }

    /**
     * Gets information about the participant
     */
    getPlayer() {
        let $this = this;

        BX.ajax.runAction("brix:secretsanta.Secretsanta.getPlayer", {
            method: "get"
        }).then((response) => {
            if (response.data) {
                $this.player = response.data.player ?? {};
                $this.recipient = response.data.recipient ?? {};
                $this.type = response.data.type ?? false;
                $this.checkPlayer();
            }
        }, (response) => {
            console.error(response);
        });
    }
    
    /**
     * Checks information about the participant and the status of the game
     */
    checkPlayer() {
        if (Object.keys(this.player).length !== 0 && !!this.type) {
            if (this.type === "registration") {
                this.registration();
            } else if (this.type === "start") {
                this.start();
            }
        }
    }

    /**
     * Creates blocks for the registration stage and performs related actions
     */
    registration() {
        let editor = false,
            title = '<div class="intranet-user-profile-container-header"><div class="intranet-user-profile-container-title">' + this.config.gamename + '</div></div>',
            start = (this.config.datestart !== "") ? this.config.textstart + '<b>' + this.config.datestart + '</b>' : '',
            end = (this.config.dateend !== "") ? this.config.textend + '<b>' + this.config.dateend + '</b>' : '',
            wish = '';
        if (start !== "" && end !== "") {
            start += "<br><br>";
        }

        if (this.player.TAKE_PART === "Y") {
            let wishText = (this.player.WISHLIST !== "") ? this.player.WISHLIST : '<span class="ui-entity-editor-block-title" style="font: inherit;">' + this.config.wishempty + '</span>';
            wish = '<details class="ui-entity-editor-content-block"><summary style="cursor: pointer;">' + this.config.mywish + '</summary><div><div class="brix-secretsanta-profile__wish"><div class="brix-secretsanta-profile__text" style="padding: 10px 0;">' + wishText + '</div>';

            if (window.BXHtmlEditor) {
                editor = true;
                wish += '<button class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-round" id="brix-secretsanta-open-edit" type="button" data-value="N"><span class="ui-btn-text">' + this.config.edit[0] +'</span></button></div><div class="brix-secretsanta-profile__edit" style="display: none; padding: 10px 0;"><div class="feed-add-post-buttons --no-wrap" style="margin-top: 10px;"><button class="ui-btn ui-btn-sm ui-btn-primary" id="brix-secretsanta-save-wish" type="button"><span class="ui-btn-text">' + this.config.edit[1] + '</span></button><button class="ui-btn ui-btn-sm ui-btn-link" id="brix-secretsanta-close-edit"><span class="ui-btn-text">' + this.config.edit[2] + '</span></button></div></div>';
            } else {
                wish += '</div>';
            }

            wish += '</div></details>';
        }

        let dates = '<div class="ui-entity-editor-content-block">' + start + end + '</div>',
            btn = (this.player.TAKE_PART === "Y") ? '<button class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-round" id="brix-secretsanta-utp" type="button" data-value="N"><span class="ui-btn-text">' + this.config.cancel +'</span></button>' : '<button class="ui-btn ui-btn-sm ui-btn-primary ui-btn-round" id="brix-secretsanta-utp" type="button" data-value="Y"><span class="ui-btn-text">' + this.config.confirm + '</span></button>',
            html = new DOMParser().parseFromString('<div class="intranet-user-profile-container brix-secretsanta-profile">' + title + '<div class="intranet-user-profile-container-body intranet-user-profile-about-wrapper"><div class="ui-entity-editor-content-block">' + dates + wish + btn + '</div></div></div>', 'text/html');

        this.container.prepend(html.body.querySelector(".brix-secretsanta-profile"));
        this.updateTakePart();

        if (editor) {
            this.edit();
        }
    }

    /**
     * Creates blocks for the start stage and performs related actions
     */
    start() {
        if (this.player.TAKE_PART === "Y") {
            let title = '<div class="intranet-user-profile-container-header"><div class="intranet-user-profile-container-title">' + this.config.gamename + '</div></div>',
                wishEmpty = '<span class="ui-entity-editor-block-title" style="font: inherit;">' + this.config.wishempty + '</span>',
                end = (this.config.dateend !== "") ? this.config.textend + '<b>' + this.config.dateend + '</b>' : '',
                recipient = (this.recipient.link !== "") ? this.config.recipient + this.recipient.link : '',
                myWishText = (this.player.WISHLIST !== "") ? this.player.WISHLIST : wishEmpty,
                recipientWishText = (this.recipient.wish !== "") ? this.recipient.wish : wishEmpty,
                myWish = '<details class="ui-entity-editor-content-block"><summary style="cursor: pointer;">' + this.config.mywish + '</summary><div class="brix-secretsanta-profile__wish" style="padding: 10px 0;">' + myWishText + '</div></details>',
                recipientWish = (this.recipient.link !== "") ? '<details class="ui-entity-editor-content-block"><summary style="cursor: pointer;">' + this.config.recipientwich + '</summary><div class="brix-secretsanta-profile__wish" style="padding: 10px 0;">' + recipientWishText + '</div></details>' : '';

            if (end !== "" && myWishText !== "") {
                end += "<br><br>";
            }

            let html = new DOMParser().parseFromString('<div class="intranet-user-profile-container brix-secretsanta-profile">' + title + '<div class="intranet-user-profile-container-body intranet-user-profile-about-wrapper"><div class="ui-entity-editor-content-block">' + '<div class="ui-entity-editor-content-block">' + end + recipient + '</div>' + recipientWish + myWish + '</div></div></div>', 'text/html');

            this.container.prepend(html.body.querySelector(".brix-secretsanta-profile"));
        }
    }

    /**
     * Action for the participation status update button
     */
    updateTakePart() {
        let btn = document.getElementById("brix-secretsanta-utp");

        if (btn) {
            btn.addEventListener("click", () => {
                BX.ajax.runAction("brix:secretsanta.Secretsanta.updateTakePart", {
                    method: "post",
                    data: {
                        takePart: btn.getAttribute("data-value")
                    }
                }).then((response) => {
                    let topSlider = BX.SidePanel.Instance.getTopSlider();
    
                    if (topSlider) {
                        topSlider.reload();
                    }
                }, (response) => {
                    console.error(response);
                });
            });
        }
    }

    /**
     * Checking and processing all data for actions to change the wishlist
     */
    edit() {
        let $this = this,
            btnOpen = document.getElementById("brix-secretsanta-open-edit"),
            btnClose = document.getElementById("brix-secretsanta-close-edit"),
            btnSave = document.getElementById("brix-secretsanta-save-wish"),
            profileWish = document.querySelector(".brix-secretsanta-profile__wish"),
            textWish = document.querySelector(".brix-secretsanta-profile__text"),
            blockEdit = document.querySelector(".brix-secretsanta-profile__edit");

        if (btnOpen && btnClose && btnSave && profileWish && textWish && blockEdit) {
            btnOpen.addEventListener("click", () => {
                $this.createEditor(blockEdit);
                profileWish.style.display = "none";
                blockEdit.style.display = "block";
            });

            btnClose.addEventListener("click", () => {
                blockEdit.style.display = "none";
                profileWish.style.display = "block";
                $this.deleteEditor();
            });

            btnSave.addEventListener("click", () => {
                if (document.getElementById("bxed_brix-secretsanta-editor")) {
                    let iframeHTML = blockEdit.querySelector("iframe").contentDocument.body.innerHTML;

                    BX.ajax.runAction("brix:secretsanta.Secretsanta.updateWishlist", {
                        method: "post",
                        data: {
                            wishlist: iframeHTML
                        }
                    }).then((response) => {
                        if (response.data) {
                            textWish.innerHTML = (response.data.text !== "") ? response.data.text : '<span class="ui-entity-editor-block-title" style="font: inherit;">' + $this.config.wishempty + '</span>';
                            $this.player.WISHLIST = response.data.text;
                            blockEdit.style.display = "none";
                            profileWish.style.display = "block";
                            $this.deleteEditor();
                        }
                    }, (response) => {
                        console.error(response);
                    });
                }
            });
        }
    }

    /**
     * Creates a visual editor
     * 
     * @param {Node} container 
     */
    createEditor(container) {
        let html = new DOMParser().parseFromString('<div class="bx-html-editor brix-secretsanta-profile__editor" id="bx-html-editor-brix-secretsanta-editor" data-name="brix-secretsanta-editor"> <div class="bxhtmled-toolbar-cnt" id="bx-html-editor-tlbr-cnt-brix-secretsanta-editor"> <div class="bxhtmled-toolbar" id="bx-html-editor-tlbr-brix-secretsanta-editor"></div> </div> <div class="bxhtmled-search-cnt" id="bx-html-editor-search-cnt-brix-secretsanta-editor" style="display: none;"></div> <div class="bxhtmled-area-cnt" id="bx-html-editor-area-cnt-brix-secretsanta-editor"> <div class="bxhtmled-iframe-cnt" id="bx-html-editor-iframe-cnt-brix-secretsanta-editor"></div> <div class="bxhtmled-textarea-cnt" id="bx-html-editor-ta-cnt-brix-secretsanta-editor"></div> <div class="bxhtmled-resizer-overlay" id="bx-html-editor-res-over-brix-secretsanta-editor"></div> <div id="bx-html-editor-split-resizer-brix-secretsanta-editor"></div> </div> <div class="bxhtmled-nav-cnt" id="bx-html-editor-nav-cnt-brix-secretsanta-editor" style="display: none;"></div> <div class="bxhtmled-taskbar-cnt bxhtmled-taskbar-hidden" id="bx-html-editor-tskbr-cnt-brix-secretsanta-editor"> <div class="bxhtmled-taskbar-top-cnt" id="bx-html-editor-tskbr-top-brix-secretsanta-editor"></div> <div class="bxhtmled-taskbar-resizer" id="bx-html-editor-tskbr-res-brix-secretsanta-editor"> <div class="bxhtmled-right-side-split-border"> <div data-bx-tsk-split-but="Y" class="bxhtmled-right-side-split-btn"></div> </div> </div> <div class="bxhtmled-taskbar-search-nothing" id="bxhed-tskbr-search-nothing-brix-secretsanta-editor"></div> <div class="bxhtmled-taskbar-search-cont" id="bxhed-tskbr-search-cnt-brix-secretsanta-editor" data-bx-type="taskbar_search"> <div class="bxhtmled-search-alignment" id="bxhed-tskbr-search-ali-brix-secretsanta-editor"> <input type="text" class="bxhtmled-search-inp" id="bxhed-tskbr-search-inp-brix-secretsanta-editor" placeholder=""/> </div> <div class="bxhtmled-search-cancel" data-bx-type="taskbar_search_cancel" title="HTMLED_SEARCH_CANCEL"></div> </div> </div> <div id="bx-html-editor-file-dialogs-brix-secretsanta-editor" style="display: none;"></div> </div>', 'text/html');

        container.prepend(html.body.querySelector(".brix-secretsanta-profile__editor"));

        let blockEditor = document.querySelector(".brix-secretsanta-profile__editor");

        this.config.bxeditor["id"] = blockEditor.getAttribute("data-name");
        this.config.bxeditor["inputName"] = blockEditor.getAttribute("data-name");
        this.config.bxeditor["content"] = this.player.WISHLIST ?? "";

        window.BXHtmlEditor.Show(this.config.bxeditor);
    }

    /**
     * Deletes the visual editor
     */
    deleteEditor() {
        delete window.BXHtmlEditor.editors["brix-secretsanta-editor"];
        document.querySelector(".brix-secretsanta-profile__editor").remove();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    new BrixSecretSantaProfile();
});