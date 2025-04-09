class BrixLinkedTaskEdit {
    constructor() {
        let taskData = (window.brixTaskParams !== undefined) ? window.brixTaskParams : (document.querySelector('[name="HIT_STATE[INITIAL_TASK_DATA][GROUP_ID]"]') ? {GROUP_ID: document.querySelector('[name="HIT_STATE[INITIAL_TASK_DATA][GROUP_ID]"]').value} : {}),
            data = {
                id: document.querySelector('[name="ACTION[0][ARGUMENTS][id]"]') ? document.querySelector('[name="ACTION[0][ARGUMENTS][id]"]').value : 0,
                taskData: taskData
            };

        BX.ajax.runAction("brix:linkedtaskfields.task.get", {
            data: data
        }).then((response) => {
            this.dataCheck(response.data);
        }, (response) => {
            console.error(response);
        });
    }

    /**
     * Checks if the script needs to be executed and, if necessary, runs the other functions.
     * @param {Object} data 
     */
    dataCheck(data = {}) {
        if (data.CONDITIONS && Object.keys(data.CONDITIONS).length > 0) {
            this.getConditions = () => {
                return data.CONDITIONS;
            };
            this.getRelationship = () => {
                return data.RELATIONSHIP ?? {};
            };
            this.getFields = () => {
                return data.FIELDS ?? {};
            };

            if (data.HIDE_FIELD && data.HIDE_FIELD.length > 0) {
                data.HIDE_FIELD.forEach(field => {
                    this.actionField(field, Number(data.CONDITIONS[field].FIELD_ID));
                });
            }

            this.proxy = new Proxy(data.CURRENT_VALUES, this.handler());

            if (data.FIELDS && Object.keys(data.FIELDS).length > 0) {
                this._inputChange = false;

                let crmFields = [],
                    dialogFields = {},
                    dialogClasses = [];

                Object.entries(data.FIELDS).forEach(([field, obj]) => {
                    if (data.DEFAULT_FIELDS.includes(field)) {
                        this.customEvent(field);
                    } else {
                        let typeField = obj.USER_TYPE_ID,
                            view = "",
                            multiple = false;

                        switch (typeField) {
                            case "boolean":
                                view = obj.SETTINGS.DISPLAY;
                                if (view === "RADIO") {
                                    this.checkRadio(field);
                                } else if (view === "DROPDOWN") {
                                    this.checkSelectSingle(field);
                                } else if (view === "CHECKBOX") {
                                    this.checkCheckbox(field, true);
                                }
                                break;
                            case "crm":
                                crmFields.push(field);
                                break;
                            case "date":
                            case "datetime":
                            case "double":
                            case "integer":
                            case "string":
                                this.checkInputText(field, obj.MULTIPLE, Number(obj.ID));
                                break;
                            case "employee":
                                this.checkEmployee(field, obj.MULTIPLE);
                                break;
                            case "enumeration":
                            case "iblock_element":
                            case "iblock_section":
                                view = obj.SETTINGS.DISPLAY;
                                multiple = (obj.MULTIPLE === "Y");

                                if (view === "LIST") {
                                    if (multiple) {
                                        this.checkSelectMultiple(field);
                                    } else {
                                        this.checkSelectSingle(field);
                                    }
                                } else if (view === "CHECKBOX") {
                                    if (multiple) {
                                        this.checkCheckbox(field);
                                    } else {
                                        this.checkRadio(field);
                                    }
                                } else if (view === "UI") {
                                    this.checkUI(field, multiple);
                                } else if (view === "DIALOG") {
                                    dialogFields[obj.ID] = field;
                                    dialogClasses.push(`js-id-item-set-item-${obj.ID}`);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                });

                if (crmFields.length > 0) {
                    this.checkCrm(crmFields);
                }

                if (Object.keys(dialogFields).length > 0) {
                    this.checkDialog(dialogFields, dialogClasses);
                }
            }
        }
    }

    /**
     * Hides the user field
     * @param {String} field
     * @param {Number|String} fieldId
     * @param {bool} hide
     */
    actionField(field, fieldId, hide = true) {
        let node = document.querySelector(`.js-id-item-set-item-${fieldId}`);

        if (field === "UF_CRM_TASK") {
            node = document.querySelector('[data-block-name="UF_CRM_TASK"]');
        }
        
        if (node) {
            let val = hide ? "none" : "block";
            node.style.display = val;
        }
    }

    /**
     * Interceptors for changing current values
     * @returns {Object}
     */
    handler() {
        let $this = this;

        return {
            get: (target, prop) => {
                return (prop in target) ? target[prop] : null;
            },
            set: (target, key, val = null) => {
                target[key] = val;

                let relationship = $this.getRelationship();

                if (relationship[key]) {
                    relationship[key].forEach(field => {
                        let conditions = $this.getConditions();

                        if (conditions[field]) {
                            BX.ajax.runAction("brix:linkedtaskfields.task.checkConditions", {
                                data: {
                                    arConditions: conditions[field].CONDITIONS,
                                    arCurrentValues: target,
                                    arFields: $this.getFields()
                                }
                            }).then((response) => {
                                $this.actionField(field, Number(conditions[field].FIELD_ID), !response.data);
                            }, (response) => {
                                console.error(response);
                            });
                        }
                    });
                }

                return true;
            }
        };
    }

    /**
     * Events for tracking field changes
     * @param {String} field
     */
    customEvent(field) {
        let $this = this,
            dialog = BX.UI.EntitySelector.Dialog;

        switch (field) {
            case "CREATED_BY":
            case "RESPONSIBLE_ID":
                let dialogId = (field === "CREATED_BY") ? "tasksMemberSelector_originator" : "tasksMemberSelector_responsible";
                if (dialog.getById(dialogId)) {
                    dialog.getById(dialogId).subscribe("Item:onSelect", (event) => {
                        let userId = event.getTarget().getSelectedItems()[0].getId();
                        $this.proxy[field] = `U${userId}`;
                    });
                }
                break;
            case "DEADLINE":
                BX.addCustomEvent(window, "change-deadline", (e) => {
                    $this.proxy[field] = (e !== "") ? e : null;
                });
                break;
            case "GROUP_ID":
                if (dialog.getById("tasksMemberSelector_project")) {
                    dialog.getById("tasksMemberSelector_project")
                        .subscribe("Item:onSelect", (event) => {
                            $this.proxy[field] = event.getTarget().getSelectedItems()[0].getId();
                        })
                        .subscribe("Item:onDeselect", (event) => {
                            $this.proxy[field] = null;
                        });
                }
                break;
            case "TAGS":
                if (dialog.getById("tasksTagSelector")) {
                    dialog.getById("tasksTagSelector")
                        .subscribe("Item:onSelect", (event) => {
                            $this.proxy[field] = $this.selectedIds(event.getTarget().getSelectedItems());
                        })
                        .subscribe("Item:onDeselect", (event) => {
                            $this.proxy[field] = $this.selectedIds(event.getTarget().getSelectedItems());
                        });
                }
                break;
            default:
                break;
        }
    }

    /**
     * Returns an array of element IDs
     * @param {Array} selected 
     * @returns {Array}
     */
    selectedIds(selected = []) {
        let ids = [];

        if (selected.length > 0) {
            selected.forEach(item => {
                let id = item.getId();

                if (typeof id === "number") {
                    ids.push(id);
                } else {
                    ids.push("0");
                }
            });
        }

        return ids;
    }

    /**
     * Monitors changes in single fields such as radio
     * @param {String} field
     */
    checkRadio(field) {
        let nodes = document.querySelectorAll(`[name="ACTION[0][ARGUMENTS][data][${field}]"]`);

        if (nodes.length > 0) {
            nodes.forEach(node => {
                node.addEventListener("change", (e) => {
                    this.proxy[field] = (e.target.value !== "") ? e.target.value : null;
                });
            });
        }
    }

    /**
     * Monitors changes in single fields such as select
     * @param {String} field
     */
    checkSelectSingle(field) {
        let node = document.querySelector(`select[name="ACTION[0][ARGUMENTS][data][${field}]"]`);

        if (node) {
            node.addEventListener("change", (e) => {
                this.proxy[field] = (e.target.value !== "") ? e.target.value : null;
            });
        }
    }

    /**
     * Monitors changes in single fields such as multiple select
     * @param {String} field
     */
    checkSelectMultiple(field) {
        let node = document.querySelector(`select[name="ACTION[0][ARGUMENTS][data][${field}][]"]`);

        if (node) {
            node.addEventListener("change", () => {
                let selected = [];

                for (let opt of node.options) {
                    if (opt.selected && opt.value !== "") {
                        selected.push(opt.value);
                    }
                }

                this.proxy[field] = (selected.length > 0) ? selected : null;
            });
        }
    }

    /**
     * Monitors changes in single fields such as checkbox
     * @param {String} field
     * @param {Boolean} bool
     */
    checkCheckbox(field, bool = false) {
        let name = bool ? `ACTION[0][ARGUMENTS][data][${field}]` : `ACTION[0][ARGUMENTS][data][${field}][]`,
            nodes = document.querySelectorAll(`[type="checkbox"][name="${name}"]`);

        if (nodes.length > 0) {
            nodes.forEach(node => {
                node.addEventListener("click", () => {
                    if (bool) {
                        this.proxy[field] = node.checked ? 1 : 0;
                    } else {
                        let selected = [];

                        nodes.forEach(n => {
                            if (n.checked && n.value !== "") {
                                selected.push(n.value);
                            }
                        });

                        this.proxy[field] = (selected.length > 0) ? selected : null;
                    }
                });
            });
        }
    }

    /**
     * Tracks fields changes with reference to CRM elements
     * @param {Array} fields
     */
    checkCrm(fields) {
        let $this = this,
            items = {},
            allFields = this.getFields();

        if (BX.UI.TileSelector) {
            let list = BX.UI.TileSelector.getList();
            list.forEach(item => {
                let inputId = item.getSearchInput().getAttribute("id");

                for (let i = 0; i < fields.length; i++) {
                    if (inputId.includes(`[${fields[i]}]`)) {
                        items[inputId] = fields[i];
                        break;
                    }
                }
            });
        }

        ["tile-add", "tile-remove"].forEach(eventName => {
            BX.Event.EventEmitter.subscribe(eventName, (event) => {
                let target = event.getTarget(),
                    inputId = target.getSearchInput().getAttribute("id");

                if (items.hasOwnProperty(inputId)) {
                    let field = items[inputId],
                        tiles = target.getTiles();

                    if (tiles.length > 0) {
                        if (allFields[field].MULTIPLE === "N") {
                            $this.proxy[field] = tiles[0].id;
                        } else {
                            let newTiles = [];
                            tiles.forEach(tile => {
                                newTiles.push(tile.id);
                            });
                            $this.proxy[field] = newTiles;
                        }
                    } else {
                        $this.proxy[field] = null;
                    }
                }
            });
        });
    }

    /**
     * Tracks changes to string fields and dates
     * @param {String} field
     * @param {String} multiple
     * @param {Number} id
     */
    checkInputText(field, multiple, id) {
        let $this = this;

        if (multiple === "N") {
            let node = document.querySelector(`[name="ACTION[0][ARGUMENTS][data][${field}]"]`);

            if (node) {
                $this.changeInput(node, multiple);
            }
        } else {
            let nodes = document.querySelectorAll(`[name="ACTION[0][ARGUMENTS][data][${field}][]"]`);

            if (nodes.length > 0) {
                nodes.forEach(node => {
                    $this.changeInput(node, field, multiple);
                });
            }

            let wrap = document.querySelector(`.js-id-item-set-item-${id} .field-wrap`);

            if (wrap) {
                let observer = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.addedNodes.length > 0) {
                            $this.changeInput(mutation.addedNodes[0], field, multiple);
                        }
                    });
                });

                observer.observe(wrap, {
                    childList: true
                });
            }
        }
    }

    /**
     * Tracking changes to date fields
     * @param {Element} node
     * @param {String} field
     * @param {String} multiple
     */
    changeInput(node, field, multiple) {
        let $this = this;

        if (node) {
            ["change", "paste", "cut", "input"].forEach(event => {
                node.addEventListener(event, (e) => {
                    clearInterval($this._inputChange);

                    $this._inputChange = setTimeout(() => {
                        if (multiple === "N") {
                            $this.proxy[field] = e.target.value;
                        } else {
                            let newValues = [],
                                nodes = document.querySelectorAll(`[name="ACTION[0][ARGUMENTS][data][${field}][]"]`);

                            if (nodes.length > 0) {
                                nodes.forEach(n => {
                                    if (n.value !== "") {
                                        newValues.push(n.value);
                                    }
                                });
                            }

                            $this.proxy[field] = (newValues.length > 0) ? newValues : null;
                        }
                    }, 800);
                });
            });
        }
    }

    /**
     * Tracks field changes with reference to employees
     * @param {String} field
     * @param {String} multiple
     */
    checkEmployee(field, multiple) {
        let node = document.querySelector(`[name="ACTION[0][ARGUMENTS][data][${field}]"]`);

        if (node && multiple === "N") {
            node.addEventListener("change", (e) => {
                this.proxy[field] = (e.target.value !== "") ? `U${e.target.value}` : null;
            });
        }
    }

    /**
     * Tracks field changes with reference to UI (list)
     * @param {String} field
     * @param {Boolean} multiple
     */
    checkUI(field, multiple) {
        let $this = this,
            name = multiple ? `ACTION[0][ARGUMENTS][data][${field}][]` : `ACTION[0][ARGUMENTS][data][${field}]`,
            node = document.querySelector(`[data-name="${name}"]`);

        if (name) {
            let observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    let values = JSON.parse(mutation.target.getAttribute("data-value")),
                        newValues = [];

                    if (multiple) {
                        if (values.length > 0) {
                            values.forEach(obj => {
                                if (obj.VALUE !== "") {
                                    newValues.push(obj.VALUE);
                                }
                            })
                        }
                        
                        $this.proxy[field] = (newValues.length > 0) ? newValues : null;
                    } else {
                        if (Object.keys(values).length > 0) {
                            if (values.VALUE !== "") {
                                $this.proxy[field] = values.VALUE;
                            }
                        }
                    }
                });
            });

            observer.observe(node, {
                attributes: true,
                attributeFilter: ["data-value"]
            });
        }
    }

    /**
     * Tracks field changes with reference to Dialog
     * @param {Object} fields
     * @param {Array} classes
     */
    checkDialog(fields, classes) {
        let $this = this,
            dialogs = {},
            allFields = this.getFields();

        if (BX.UI.EntitySelector && BX.UI.EntitySelector.Dialog) {
            let instances = BX.UI.EntitySelector.Dialog.getInstances();

            instances.forEach(instance => {
                let node = instance.getTargetNode();

                if (node) {
                    let parent = node.closest(".js-id-item-set-item");

                    if (parent) {
                        for (let className of parent.classList) {
                            if (classes.includes(className)) {
                                let id = className.replace("js-id-item-set-item-", "");
                                dialogs[fields[Number(id)]] = instance;
                                break;
                            }
                        }
                    }
                }
            });

            if (Object.keys(dialogs).length > 0) {
                Object.entries(dialogs).forEach(([field, dialog]) => {
                    ["Item:onSelect", "Item:onDeselect"].forEach(eventName => {
                        dialog.subscribe(eventName, (event) => {
                            let selected = event.getTarget().getSelectedItems();

                            if (selected.length > 0) {
                                if (allFields[field].MULTIPLE === "N") {
                                    $this.proxy[field] = selected[0].getId();
                                } else {
                                    let newValues = [];

                                    selected.forEach(item => {
                                        newValues.push(item.getId());
                                    });

                                    $this.proxy[field] = newValues;
                                }
                            } else {
                                $this.proxy[field] = null;
                            }
                        });
                    });
                });
            }
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    new BrixLinkedTaskEdit();
});