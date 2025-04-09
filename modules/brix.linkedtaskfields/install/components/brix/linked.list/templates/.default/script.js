class BrixLinkedList {
    /**
     * Creating a modal
     */
    static createModal() {
        new BrixLinkedModal({
            data: {
                classModal: "brix-linked-list__modal",
                title: BX.message("BRIX_LINKED_LIST_TITLE"),
                text: 'T',
                buttons: [
                    `<button class="brix-linked-list__modal-delete ui-btn ui-btn-md ui-btn-danger-dark">${BX.message("BRIX_LINKED_LIST_OK")}</button>`,
                    `<button class="brix-linked-list__modal-close ui-btn ui-btn-md ui-btn-link">${BX.message("BRIX_LINKED_LIST_CANCEL")}</button>`
                ]
            },
            selectorClose: ".brix-linked-list__modal-close",
            maxWidth: 400,
            clickOut: true
        });

         window.parent.document.querySelector(".brix-linked-list__modal-delete").addEventListener("click", () => {
            let modal =  window.parent.document.querySelector(".brix-linked-list__modal"),
                fieldNames = JSON.parse(modal.getAttribute("data-fieldNames")),
                all = modal.getAttribute("data-all");

            BX.ajax.runAction("brix:linkedtaskfields.linked.delete", {
                data: {
                    fieldNames: fieldNames,
                    all: all
                }
            }).then((response) => {
                window.parent.document.querySelector(".brix-linked-list__modal").close();
                let topSlider = BX.SidePanel.Instance.getTopSlider();
    
                if (topSlider) {
                    topSlider.reload();
                }
            }, (response) => {
                window.parent.document.querySelector(".brix-linked-list__modal").click();
                console.error(response);
    
                if (response.error) {
                    alert(response.error);
                } else {
                    alert("An error has occurred");
                }
            });
        });
    }

    /**
     * Opens the slider for editing or creating a rule
     * @param {String} fieldName 
     */
    static openSlider(fieldName = "") {
        let url = "/bitrix/tools/brix-linkedtaskfields/element.php?FIELD_NAME=";
        BX.SidePanel.Instance.open(`${url}${fieldName}`,
            {
                cacheable: false,
                width: 1333
            }
        );
    }
    
    /**
     * Sends a request to update the item
     * @param {String} fieldName
     * @param {String} active
     */
    static update(fieldName, active = "Y") {
        BX.ajax.runAction("brix:linkedtaskfields.linked.update", {
            data: {
                fieldName: fieldName,
                fields: {ACTIVE: active},
                activity: true
            }
        }).then((response) => {
            let topSlider = BX.SidePanel.Instance.getTopSlider();

            if (topSlider) {
                topSlider.reload();
            }
        }, (response) => {
            console.error(response);

            if (response.error) {
                alert(response.error);
            } else {
                alert("An error has occurred");
            }
        });
    }

    /**
     * Sends a request to update the items
     * @param {String} action
     */
    static updateMulti(active = "Y") {
        let elements = this.checkElements();
        
        BX.ajax.runAction("brix:linkedtaskfields.linked.updateMulti", {
            data: {
                fields: {ACTIVE: active},
                fieldNames: elements.selected,
                all: elements.all
            }
        }).then((response) => {
            let topSlider = BX.SidePanel.Instance.getTopSlider();

            if (topSlider) {
                topSlider.reload();
            }
        }, (response) => {
            console.error(response);

            if (response.error) {
                alert(response.error);
            } else {
                alert("An error has occurred");
            }
        });
    }

    /**
     * Sends a request to delete an item
     * @param {array} fieldNames
     * @param {String} label
     * @param {String} all
     */
    static delete(fieldNames, label = "", all = "N") {
        let message = BX.message("BRIX_LINKED_LIST_TEXT_MULTI");

        if (label !== "") {
            message = BX.message("BRIX_LINKED_LIST_TEXT").replace("#FIELD#", label);
        }

        if (typeof fieldNames === "string") {
            fieldNames = [fieldNames];
        }

         window.parent.document.querySelector(".brix-linked-list__modal").setAttribute("data-all", all);
         window.parent.document.querySelector(".brix-linked-list__modal").setAttribute("data-fieldNames", JSON.stringify(fieldNames));
         window.parent.document.querySelector(".brix-linked-list__modal .brix-linked-modal__text").textContent = message;
         window.parent.document.querySelector(".brix-linked-list__modal").showModal();
    }
    
    /**
     * Method for calling the deletion method
     */
    static deleteMulti() {
        let elements = this.checkElements();

        this.delete(elements.selected, "", elements.all);
    }

    /**
     * Retrieves selected items
     * @returns {Array}
     */
    static checkElements() {
        let gridId = BX.message("BRIX_LINKED_LIST_GRID_ID");

        let grid = BX.Main.gridManager.getInstanceById(gridId),
            getSelectedIds = grid.getRows().getSelectedIds(),
            inputAll = document.getElementById(`actallrows_${gridId}`),
            all = "N";

        if (inputAll && inputAll.checked) {
            all = "Y";
        }

        return {selected: getSelectedIds, all: all};
    }
}
