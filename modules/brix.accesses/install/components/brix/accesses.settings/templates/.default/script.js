class BrixAccessesSettings {
    /**
     * Constructor
     * 
     * @param {Object} data
     */
    constructor(data = {}) {
        this.step = data.step ?? 1;
        this.tagSelectorData = data.tagSelectorData;
        this.accessInfo = data.accessInfo;

        let isChange = (data.isChange && data.isChange === "Y") ? true : false,
            common = (data.common && data.common === "Y") ? true : false,
            iblockId = data.iblockId ?? 0,
            iblockIdMany = data.iblockIdMany ? data.iblockIdMany.split(",") : [];

        this.isChange = () => {
            return isChange;
        };
        this.getCommon = () => {
            return common;
        };
        this.getIblockId = () => {
            return Number(iblockId);
        };
        this.getIblockIdMany = () => {
            return iblockIdMany;
        };

        switch (this.step) {
            case 2:
                this.twoStep();
                break;
            case 3:
                this.threeStep();
                break;
            default:
                break;
        }
    }

    /**
     * Processing the second step
     */
    twoStep() {
        BX.Runtime.loadExtension("ui.entity-selector").then(exports => {
            const Entity = exports;
            
            this.createTagselector(Entity.TagSelector);
        });

        if (this.isChange()) {
            this.formSubmit();
        }
    }

    /**
     * Processing the third step
     */
    threeStep() {
        this.formSubmit();
        this.accessInit();

        if (this.isChange()) {
            this.createModal();
            
            BX.Runtime.loadExtension("ui.progressbar").then(exports => {
                const Progress = exports;
                
                this.createProgress(Progress.ProgressBar);
            });
        }
    }

    /**
     * Creating a TagSelector | Two step
     * 
     * @param {TagSelector} TagSelector
     */
    createTagselector(TagSelector) {
        this.tagSelectorList = {};
        this.multiRights = new Map();
        this.rightsMode = false;
        this.selectedIblock = 0;

        let $this = this,
            entity;

        if (Object.keys(this.tagSelectorData).length > 0) {
            for (let tagSelectorId in this.tagSelectorData) {
                let data = this.tagSelectorData[tagSelectorId],
                    settings = data.settings,
                    node = document.getElementById(`${data.container}`),
                    input = document.getElementById(`${tagSelectorId}`);

                if (!node) {
                    continue;
                }

                node.addEventListener("keyup", e => this.eventKeyup(e));

                settings.dialogOptions.events = {
                    "Item:onSelect": (event) => {
                        let item = event.getData().item;

                        if (item.getDialog().isMultiple()) {
                            $this.multipleValue(input, item.getId());
                        } else {
                            input.value = item.getId();
                            $this.rightsMode = item.getCustomData().get("rights");
                            $this.changeLockTagSelector(false);
                        }
                    },
                    "Item:onDeselect": (event) => {
                        let item = event.getData().item;

                        if (item.getDialog().isMultiple()) {
                            $this.multipleValue(input, item.getId(), "remove");
                        } else {
                            input.value = "";
                            $this.rightsMode = false;
                            $this.changeLockTagSelector();
                        }
                    }
                };

                if (this.getCommon() && settings.multiple) {
                    settings.dialogOptions.events.onLoad = (event) => {
                        let items = event.getTarget().getItems();

                        if (items.length > 0) {
                            items.forEach(item => {
                                let customData = item.getCustomData();

                                if (customData.size > 0) {
                                    $this.multiRights.set(item.getId(), customData.get("rights"));
                                }
                            });

                            $this.checkSelectedMultiTagSelector(items);
                        }
                    };
                }

                entity = new TagSelector(settings);
                this.tagSelectorList[tagSelectorId] = entity;
                entity.renderTo(node);

                let tabs = this.tagSelectorList[tagSelectorId].getDialog().getTabs();
                tabs.forEach(tab => {
                    tab.setVisible(false);
                });
            }
        }

        this.changeLockTagSelector();
    }

    /**
     * Locks or unlocks the Tagselector | Two step
     * 
     * @param {boolean} lock 
     */
    changeLockTagSelector(lock = true) {
        if (this.getCommon() && this.getIblockId() === 0) {
            let key = Object.keys(this.tagSelectorList).pop();

            if (lock) {
                this.tagSelectorList[key].lock();
            } else {
                this.checkSelectedMultiTagSelector();
                this.tagSelectorList[key].unlock();
            }
        }
    }

    /**
     * Checks selected items in multiple TagSekector | Two step
     * 
     * @param {Array[Item[]]} dialogItems 
     */
    checkSelectedMultiTagSelector(dialogItems = []) {
        if (this.getCommon()) {
            let keys = Object.keys(this.tagSelectorList),
                keyStart = keys.shift(),
                keyEnd = keys.pop(),
                dialogStart = this.tagSelectorList[keyStart].getDialog(),
                dialogEnd = this.tagSelectorList[keyEnd].getDialog(),
                selectedItem = dialogStart.getSelectedItems()[0].getId(),
                items = (dialogItems.length > 0) ? dialogItems : dialogEnd.getItems();

            if (items.length > 0) {
                items.forEach(item => {
                    let id = item.getId();

                    if (this.multiRights.get(id)) {
                        if (
                            this.multiRights.get(id) !== this.rightsMode || 
                            Number(id) === Number(selectedItem)
                        ) {
                            item.setHidden(true);

                            if (item.isSelected()) {
                                item.deselect();
                            }
                        } else {
                            item.setHidden(false);
                        }
                    }
                });
            }
        }
    }

    /**
     * Handling multiple field changes | Two step
     * 
     * @param {Element} node
     * @param {int} id
     * @param {string} event
     */
    multipleValue(node, id, event = "add") {
        if (node && id) {
            let value = node.value;

            if (event === "add") {
                node.value = (value === "") ? id : value + "," + id;
            } else if (event === "remove") {
                if (value !== "") {
                    let split = value.split(","),
                        index = split.indexOf(id);

                    if (index !== -1) {
                        split.splice(index, 1);

                        node.value = split.join(",");
                    }
                }
            }
        }
    }

    /**
     * Processing "enter" on the TagSelector container | Two step
     * 
     * @param {Event} e 
     */
    eventKeyup(e) {
        if (e.key === "Enter" || e.keyCode === 13) {
            e.target.querySelector(".ui-tag-selector-add-button-caption").click();
        }
    }

    /**
     * Initializes the access display | Three step
     */
    accessInit() {
        this.access = {};

        if (Object.keys(this.accessInfo).length > 0) {
            if (this.accessInfo.IBLOCKS && Object.keys(this.accessInfo.IBLOCKS).length > 0) {
                let rightsExtended = this.accessInfo.RIGHTS_LIST.EXTENDED,
                    rightsNoExtended = this.accessInfo.RIGHTS_LIST.NO_EXTENDED,
                    rightsNames = this.accessInfo.RIGHTS_NAME,
                    change = this.isChange() ? "Y" : "N";

                for (let id in this.accessInfo.IBLOCKS) {
                    let info = this.accessInfo.IBLOCKS[id],
                        options = (info.EXTENDED === "Y") ? rightsExtended : rightsNoExtended;

                    this.access[id] = new BrixAccess({
                        wrap: `ib_${id}`,
                        isAccess: `${change}`,
                        isExtended: `${info.EXTENDED}`,
                        options: options,
                        names: rightsNames,
                        items: info.ACCESS
                    });
                }
            }
        }
    }

    /**
     * Creating a modal | Three step
     */
    createModal() {
        new BrixModal({
            data: {
                classModal: "brix-settings__modal",
                text: '<div class="brix-settings__modal-progress"></div>',
                buttons: [
                    `<button class="brix-settings__modal-stop ui-btn ui-btn-primary-dark">${BX.message("BRIX_ACCESSES_SETTINGS_TEMPLATE_JS_STOP")}</button>`
                ],
                btnCenter: true,
            },
            showInParent: false,
            maxWidth: 400,
            modalOpen: false,
            escapeEvents: false
        });
    }

    /**
     * Creating a ProgressBar | Three step
     * 
     * @param {ProgressBar} ProgressBar
     */
    createProgress(ProgressBar) {
        this.stopBtn = document.querySelector(".brix-settings__modal-stop");
        this.iblockCount = this.getIblockIdMany().length;
        this.stop = false;

        if (this.getIblockId() !== 0) {
            this.iblockCount++;
        }

        this.progress = new ProgressBar({ 
            size: ProgressBar.Size.LARGE,
            maxValue: this.iblockCount,
            value: 0,
            statusType: ProgressBar.Status.PERCENT,
            textAfter: BX.message("BRIX_ACCESSES_SETTINGS_TEMPLATE_JS_PROGRESS"),
            column: true
        });

        this.progress.renderTo(document.querySelector(".brix-settings__modal-progress"));

        this.stopBtn.addEventListener("click", () => {
            this.stop = true;
            this.progress.setTextAfter(BX.message("BRIX_ACCESSES_SETTINGS_TEMPLATE_JS_STOP_PROGRESS"));
            this.stopBtn.disabled = true;
        });
    }

    /**
     * Method for processing the form submission | Two and three step
     */
    formSubmit() {
        document.getElementById("accesses-settings-form").addEventListener("submit", (e) => {
            if (e.submitter.id === "accesses-settings-back") {
                this.step--;
                document.querySelector('[name="STEP"]').value = this.step;
            } else if (Number(this.step) === 2) {
                let error = false;

                for (let id in this.tagSelectorList) {
                    if (this.tagSelectorList[id].getDialog().getSelectedItems().length === 0) {
                        error = true;
                        let node = document.getElementById(`${this.tagSelectorData[id].container}`);
                        this.error(node);
                    }
                }

                if (error) {
                    e.preventDefault();
                    let alert = document.querySelector(".accesses-settings__alert");

                    if (alert) {
                        alert.classList.remove("d-none");
                    }
                }
            } else if (Number(this.step) === 3) {
                if (!this.stop) {
                    e.preventDefault();

                    let data = {};

                    for (let id in this.access) {
                        data[id] = {};

                        data[id].ITEMS = this.access[id].items;
                        data[id].EXTENDED = document.querySelector(`[name="EXTENDED_${id}"]`).checked ? "Y" : "N";
                    }

                    if (this.getIblockId() !== 0) {
                        let basicId = this.getIblockId();

                        this.getIblockIdMany().forEach(iblock => {
                            data[Number(iblock)] = data[basicId];
                        });
                    }

                    document.querySelector("dialog").show();
                    this.saveAccess(data);
                }
            }
        });
    }

    /**
     * Sends an ajax request to save the settings and processes the response | Three step
     * 
     * @param {Object} data 
     */
    saveAccess(data) {
        let $this = this,
            keys = Object.keys(data),
            iblockId = keys[0],
            extended = data[iblockId].EXTENDED,
            arAccess = data[iblockId].ITEMS,
            val = this.iblockCount - keys.length + 1;

        delete data[iblockId];

        if (!this.stop) {
            BX.ajax.runAction("brix:accesses.iblock.saveAccess", {
                data: {
                    iblockId: iblockId,
                    extended: extended,
                    arAccess: arAccess
                }
            }).then((response) => {
                $this.progress.update(val);

                if (!$this.progress.isFinish()) {
                    $this.saveAccess(data);
                } else {
                    $this.stopProgress(BX.message("BRIX_ACCESSES_SETTINGS_TEMPLATE_JS_FINISH"));
                }		
            }, (response) => {
                console.error(response);
                alert("An error has occurred");
            });
        } else {
            this.stopProgress(BX.message("BRIX_ACCESSES_SETTINGS_TEMPLATE_JS_STOP_FINISH"));
        }
    }

    /**
     * Stops the progress bar | Three step
     * 
     * @param {string} text 
     */
    stopProgress(text = "") {
        this.stop = true;
        this.stopBtn.disabled = true;
        this.progress.setTextAfter(text);
        this.progress.finish();

        setTimeout(() => {
            document.getElementById("accesses-settings-form").submit();
        }, 3000);
    }

    /**
     * Adds an error class for node | Two step
     * 
     * @param {Element} node 
     */
    error(node) {
        if (node) {
            node.classList.add("error");
        }
    }
}