class BrixModal {
    /**
     * Constructor
     * 
     * @param {Object} params 
     * {
     *  data: {
     *      id: text || false,
     *      addClass: text || false,
     *      title: text || html || false,
     *      text: text || html || false,
     *      buttons: [
     *          html || false,
     *          ...
     *      ],
     *      btnCenter: false || true
     *  },
     *  showInParent: true || false,
     *  selector: ".class" || "tag" || "#id" || ... || false,
     *  selectorShow: ".class" || "tag" || "#id" || ... || false,
     *  selectorClose: ".class" || "tag" || "#id" || ... || false,
     *  maxWidth: Number || false,
     *  modalOpen: true || false,
     *  escapeEvents: true || false
     * }
     */
    constructor(params = {}) {
        this.dataCreate = params.data ?? {};
        this.showInParent = params.showInParent ?? true;
        this.modal = params.selector ? document.querySelector(params.selector) : false;
        this.selectorShow = params.selectorShow ?? false;
        this.selectorClose = params.selectorClose ?? false;
        this.maxWidth = params.maxWidth ?? false;
        this.modalOpen = params.modalOpen ?? true;
        this.escapeEvents = params.escapeEvents ?? true;

        if (Object.keys(this.dataCreate).length > 0) {
            this.modalCreate();
        } else if (this.modal) {
            this.settings();
        }
    }

    modalCreate() {
        let id = this.dataCreate.id ?? "",
            addClass = this.dataCreate.classModal ?? "",
            title = this.dataCreate.title ?? "",
            text = this.dataCreate.text ?? "",
            buttons = this.dataCreate.buttons ?? [],
            btnCenter = this.dataCreate.btnCenter ?? false;

        let dialog = document.createElement("dialog"),
            container = document.createElement("div");
        dialog.classList.add("brix-modal");
        container.classList.add("brix-modal__container");

        if (!this.modalOpen) {
            dialog.classList.add("brix-modal--nomodal");
        }

        if (id !== "") {
            dialog.setAttribute("id", id);
        }

        if (addClass !== "") {
            dialog.classList.add(addClass);
        }

        document.body.append(dialog);
        dialog.append(container);

        if (title !== "") {
            let tHtml = /<\/?[a-z][\s\S]*>/i.test(title),
                nodeTitle = tHtml ? document.createElement("div") : document.createElement("p");

            nodeTitle.classList.add("brix-modal__title");

            if (tHtml) {
                let nodeTitle = document.createRange().createContextualFragment(title);
                nodeTitle.append(nodeTitle);
            } else {
                nodeTitle.textContent = title;
            }
            
            container.append(nodeTitle);
        }

        if (text !== "") {
            let txtHtml = /<\/?[a-z][\s\S]*>/i.test(text),
                nodeText = txtHtml ? document.createElement("div") : document.createElement("p");

            nodeText.classList.add("brix-modal__text");

            if (txtHtml) {
                let nodeTxt = document.createRange().createContextualFragment(text);
                nodeText.append(nodeTxt);
            } else {
                nodeText.textContent = text;
            }
            
            container.append(nodeText);
        }

        if (buttons.length > 0) {
            let btnContainer = document.createElement("div");
            btnContainer.classList.add("brix-modal__buttons");
            btnContainer.style.cssText = `--brixModalBtnCount: ${buttons.length};`;

            if (btnCenter) {
                btnContainer.classList.add("brix-modal__buttons--center");
            }
            
            container.append(btnContainer);

            buttons.forEach(btn => {
                let nodeBtn = document.createRange().createContextualFragment(btn);
                btnContainer.append(nodeBtn);
            });
        }

        this.modal = dialog;
        this.settings();
    }

    settings() {
        if (this.modal) {
            if (this.maxWidth && Number(this.maxWidth) > 0) {
                if (this.modalOpen) {
                    this.modal.style.maxWidth = `${this.maxWidth}px`;
                } else {
                    this.modal.querySelector(".brix-modal__container").style.maxWidth = `${this.maxWidth}px`;
                }
            }

            if (this.modal.getAttribute("id")) {
                this.id = this.modal.getAttribute("id");
            } else {
                this.id = Math.random().toString(36).substring(2, 10);

                while (document.getElementById(`${this.id}`)) {
                    this.id = Math.random().toString(36).substring(2, 10);
                }

                this.modal.setAttribute("id", this.id);
            }

            if (this.showInParent) {
                window.parent.document.body.append(this.modal);
                delete this.modal;
                this.modal = window.parent.document.getElementById(this.id);
            }

            this.events();
        }
    }

    events() {
        let $this = this;

        if (this.modal) {
            if (this.selectorShow && document.querySelector(this.selectorShow)) {
                document.querySelector(this.selectorShow).addEventListener("click", () => {
                    if ($this.modalOpen) {
                        $this.modal.showModal();
                    } else {
                        $this.modal.show();
                    }
                });
            }

            if (this.selectorClose && this.modal.querySelector(this.selectorClose)) {
                this.modal.querySelector(this.selectorClose).addEventListener("click", () => {
                    $this.modal.close();
                });
            }

            BX.addCustomEvent("SidePanel.Slider:onCloseByEsc", (e) => {
                if ($this.modal.open) {
                    e.denyAction();
                    $this.modal.close();
                }
            });

            if (this.escapeEvents) {
                window.parent.document.addEventListener("keyup", function(e) {
                    if (e.key === "Escape" || e.code === "Escape" || e.keyCode === 27) {
                        if ($this.modal.open) {
                            $this.modal.close();
                        }
                    }
                });
            }
        }
    }
}