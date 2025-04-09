function BrixLinkedDetail(data = {}) {
    let template = document.getElementById("conditions").content,
        templateAdditional = document.getElementById("additional").content;

    this._fieldName = data.fieldName ?? "";
    this.countRules = 0;
    this._items = [];
    this._select = [];
    this._tagSelectors = [];

    this.getFields = () => {
        return data.allFields ?? {};
    };
    this.getFieldsMandatory = () => {
        return data.fieldsMandatory ?? {};
    };
    this.getConditions = () => {
        return data.conditions ?? {};
    };
    this.getTemplate = () => {
        return template;
    };
    this.getTemplateAdditional = () => {
        return templateAdditional;
    };

    if (!this._fieldName) {
        this.createSelect("FIELD_NAME", data.listField, BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_FIELD_NAME_PLACEHOLDER"));
        this.deleteLoader();
    } else {
        this.rulesField(this._fieldName);
        this.showRules();
        this.currentConditions(data.currentConditions);
    }

    this.add();
    this.events();
}

BrixLinkedDetail.prototype = {
    /**
     * Deletes loader
     */
    deleteLoader() {
        document.querySelector(".brix-loader").remove();
    },
    /**
     * Shows the block with the rules
     */
    showRules() {
        document.getElementById("all-rules").classList.remove("d-none");
    },

    /**
     * Processing existing conditions
     * @param {Array} conditions
     */
    currentConditions(conditions) {
        if (conditions.length > 0) {
            conditions.forEach(cond => {
                if (typeof cond === "object") {
                    let val = (typeof cond.value === "object") ? JSON.stringify(cond.value) : cond.value;
                    this.newConditions(this.countRules, cond.field, cond.type, val);
                    this.countRules++;
                } else if (typeof cond === "string") {
                    this.newAdditionl(this.countRules, cond);
                }
            });

            setTimeout(() => {
                Object.keys(this._tagSelectors).forEach(name => {
                    if (name.includes("VALUES")) {
                        this.selectedTagSelector(name);
                    }
                });
            }, 1000);
        }

        this.deleteLoader();
    },

    /**
     * Creates a drop-down list
     * @param {String} name
     * @param {Object} options
     * @param {String} placeholder
     * @param {bool} search
     * @param {*} value
     * @param {String} condType
     * @param {String} condValues
     */
    createSelect(name, options, placeholder, search = true, value = "", condType = "", condValues = "") {
        if (value === "" && options.length > 0) {
            if (options[0].check) {
                value = options[0].value;
            } else if (options[1] && options[1].check) {
                value = options[1].value;
            }
        }

        let bxSelect = new BX.Ui.Select({ 
            value: value,
            options: options,
            isSearchable: search,
            placeholder: placeholder,
            popupParams: {
                maxHeight: 300
            }
        });
        this._select[name] = bxSelect;

        if (document.querySelector(`[name="${name}"]`)) {
            this.updateSelect(name, value, condType, condValues);

            bxSelect.subscribe("update", (e) => {
                this.updateSelect(name, bxSelect.getValue());
            });
        }

        bxSelect.renderTo(document.querySelector(`[data-name="${name}"]`));
    },

    /**
     * Method for processing changes to the selected value
     * @param {String} name
     * @param {String} value
     * @param {String} condType
     * @param {String} condValues
     */
    updateSelect(name, value = "", condType = "", condValues = "") {
        let input = document.querySelector(`[name="${name}"]`);

        if (value !== "") {
            if (name === "FIELD_NAME") {
                if (this._fieldName === "") {
                    this.showRules();
                }

                this._fieldName = value;
                this.checkMandatory(this._fieldName);
                this.rulesField(this._fieldName);
            } else {
                if (name.includes("CONDITIONS")) {
                    if (name.includes("FIELD")) {
                        if (input.value !== value) {
                            this.rulesConditions(name, value, condType, condValues);
                        }
                    } else if (name.includes("TYPE")) {
                        if (input.value !== value) {
                            this.newValues(name, value);
                        }
                    }
                }
            }
        }

        this.changeInput(input, value);
    },

    /**
     * Updates the value in input
     * @param {Element} node
     * @param {?string, ?array} val
     * @param {bool} json
     */
    changeInput(node, val = "", json = false) {
        if (json) {
            node.value = val.length > 0 ? JSON.stringify(val) : "";
        } else {
            node.value = val;
        }
    },

    /**
     * Checks whether a custom field is required
     * @param {String} val
     */
    checkMandatory(val) {
        let fields = this.getFieldsMandatory();

        if (val === "" || fields[val] === "N") {
            document.getElementById("linked-detail-mandatory").classList.add("d-none");
        } else {
            document.getElementById("linked-detail-mandatory").classList.remove("d-none");
        }
    },

    /**
     * Generates a list of fields
     * @param {String} name 
     */
    rulesField(name) {
        let fields = this.getFields();
        this._items = [];
        
        Object.entries(fields).forEach(([key, obj]) => {
            if (key !== name) {
                this._items.push({value: key, label: obj.LABEL});
            }
        });

        Object.entries(this._select).forEach(([key, select]) => {
            if (
                key.includes("CONDITIONS") && key.includes("FIELD")
                && select.getValue() === name
            ) {
                select.hideMenu();
                delete this._select[key];
                this.createSelect(key, this._items, BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_FIELD_NAME_PLACEHOLDER"));

                let type = key.replace("FIELD", "TYPE");

                if (this._select[type]) {
                    delete this._select[type];
                    document.querySelector(`[data-name="${type}"] .ui-select`).remove();
                }
            }
        });
    },

    /**
     * Generates a list of conditions
     * @param {String} name
     * @param {String} value
     * @param {String} condType
     * @param {String} condValues
     */
    rulesConditions(name, value, condType = "", condValues = "") {
        let conditions = this.getConditions(),
            allFields = this.getFields(),
            list = [],
            type = allFields[value].USER_TYPE_ID,
            multiple = allFields[value].MULTIPLE,
            types = [],
            placeholder = BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_TYPE_PLACEHOLDER");

        name = name.replace("FIELD", "TYPE");
        
        Object.entries(conditions).forEach(([key, obj]) => {
            let push = false;
            if (
                (obj.FIELDS.includes(type) && obj.MULTIPLE.includes(multiple)) ||
                (obj.FIELDS.includes(type) && type === "boolean")
            ) {
                push = true;
                if ((value === "CREATED_BY" || value === "RESPONSIBLE_ID") && key === "FILL") {
                    push = false;
                }
            }
            if (push) {
                list.push({value: key, label: obj.NAME});
                types.push(key);
            }
        });

        if (this._select[name]) {
            let getVal = this._select[name].getValue();

            if (getVal !== "") {
                condType = !types.includes(getVal) ? "" : getVal;
            }

            delete this._select[name];
        }

        this.createSelect(name, list, placeholder, true, condType);

        if (condType !== "") {
            this.newValues(name, condType, condValues);
        } else {
            this.newValues(name);
        }
    },


    /**
     * The action of adding a new rule
     */
    add() {
        let btn = document.getElementById("linked-detail-add");

        if (btn) {
            btn.addEventListener("click", () => {
                this.newConditions(this.countRules);
                this.countRules++;
            });
        }
    },

    /**
     * Adds a block with a new condition
     * @param {Number} id
     * @param {String} val
     * @param {String} condType
     * @param {String} condValues
     */
    newConditions(id = 0, val = "", condType = "", condValues = "") {
        let rules = document.querySelector(".linked-detail__rules"),
            template = this.getTemplate().cloneNode(true),
            inputs = template.querySelectorAll("input[name]"),
            inputName = inputs[0].getAttribute("name").replace("#ID#", id);

        inputs.forEach(input => {
            let name = input.getAttribute("name").replace("#ID#", id);
            input.setAttribute("name", name);

            if (input.nextElementSibling) {
                input.nextElementSibling.setAttribute("data-name", name);
            }
        });

        template.querySelector(".linked-detail__cond-del").addEventListener("click", (e) => {
            let block = e.target.closest(".linked-detail__cond");

            if (block.nextElementSibling) {
                block.nextElementSibling.remove();
            } else if (block.previousElementSibling) {
                block.previousElementSibling.remove();
            }

            block.remove();
        });

        if (rules.querySelectorAll(".linked-detail__cond").length > 0 && val === "") {
            this.newAdditionl(id);
        }

        rules.append(template);
        this.createSelect(inputName, this._items, BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_FIELD_NAME_PLACEHOLDER"), true, val, condType, condValues);
    },

    /**
     * Adds an intermediary condition
     * @param {Number} id
     * @param {String} value
     */
    newAdditionl(id = 0, value = "") {
        id--;
        let rules = document.querySelector(".linked-detail__rules"),
            template = this.getTemplateAdditional().cloneNode(true),
            inputName = template.querySelector("input[name]").getAttribute("name").replace("#ID#", id),
            list = BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_ADDITIONAL");

        if (value === "") {
            value = list[0].value;
        }
        template.querySelector("input").setAttribute("name", inputName);
        template.querySelector("[data-name]").setAttribute("data-name", inputName);
        rules.append(template);
        this.createSelect(inputName, list, BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_FIELD_NAME_PLACEHOLDER"), false, value);
    },

    /**
     * Generates a block with options
     * @param {String} name
     * @param {String} type
     * @param {String} condValues
     */
    newValues(name, type = "FILL", condValues = "") {
        let valuesName = name.replace("TYPE", "VALUES"),
            selectName = name.replace("TYPE", "FIELD"),
            select = this._select[selectName].getValue(),
            fields = this.getFields();

        document.querySelector(`[name="${valuesName}"]`).value = "";

        if (this._tagSelectors[valuesName]) {
            delete this._tagSelectors[valuesName];
        }
        if (document.querySelector(`[data-name="${valuesName}"] .linked-detail__cond-block`)) {
            document.querySelector(`[data-name="${valuesName}"] .linked-detail__cond-block`).remove();
        }
        if (document.querySelector(`[data-name="${valuesName}"] .ui-select`)) {
            document.querySelector(`[data-name="${valuesName}"] .ui-select`).remove();
        }

        switch (type) {
            case "FILL":
                this.changeInput(document.querySelector(`[name="${valuesName}"]`));
                if (this._select[type]) {
                    delete this._select[type];
                }
                break;
            case "DATE":
            case "BIG":
            case "BIG_OR":
            case "LESS":
            case "LESS_OR":
            case "RANGE":
                let precision =(type === "DATE") ? 0 : fields[select]["SETTINGS"]["PRECISION"],
                    double = (type === "RANGE") ? true : false,
                    attributes = {
                        min: (type === "DATE") ? 1 : Number(fields[select]["SETTINGS"]["MIN_VALUE"]),
                        max: (type === "DATE") ? 0 : Number(fields[select]["SETTINGS"]["MAX_VALUE"]),
                        step: (type === "DATE" || fields[select]["SETTINGS"]["PRECISION"] === 0) ? 1 : Number("0." + "0".repeat(fields[select]["SETTINGS"]["PRECISION"] - 1) + "1")
                    };

                this.createNumber(valuesName, attributes, Number(precision), double, condValues);
                break;
            case "IN":
            case "IN_NO":
                if (fields[select]["USER_TYPE_ID"] !== "string") {
                    condValues = (condValues !== "") ? JSON.parse(condValues) : [];
                }

                if (fields[select]["USER_TYPE_ID"] === "string") {
                    this.createText(valuesName, condValues);
                } else if (fields[select]["USER_TYPE_ID"] === "employee") {
                    this.createTagSelector(valuesName, this.userDialogOptions(condValues));
                } else if (fields[select]["USER_TYPE_ID"] === "group") {
                    this.createTagSelector(valuesName, this.groupDialogOptions(condValues));
                } else if (fields[select]["USER_TYPE_ID"] === "enumeration") {
                    let context = `brix_userfield_${fields[select]["ID"]}`;
                    this.createTagSelector(valuesName, this.enumsDialogOptions(Number(fields[select]["ID"]), condValues), context);
                } else if (
                    fields[select]["USER_TYPE_ID"] === "iblock_section" ||
                    fields[select]["USER_TYPE_ID"] === "iblock_element"
                ) {
                    let iblockId = Number(fields[select]["SETTINGS"]["IBLOCK_ID"]),
                        type = fields[select]["USER_TYPE_ID"].replace("iblock_", "");
                        context = `brix_userfield_${iblockId}`;
                    this.createTagSelector(valuesName, this.iblockDialogOptions(iblockId, type, condValues), context);
                } else if (fields[select]["USER_TYPE_ID"] === "crm") {
                    let context = `brix_userfield_${fields[select]["USER_TYPE_ID"]}`;
                    this.createTagSelector(valuesName, this.crmDialogOptions(fields[select]["SETTINGS"], condValues), context);
                }
                break;
            case "KEEP":
            case "KEEP_NO":
            case "SAME":
            case "SAME_NO":
                let precondition = (type === "SAME_NO") ? "AND" : "OR";

                if (fields[select]["USER_TYPE_ID"] !== "boolean" && fields[select]["USER_TYPE_ID"] !== "string") {
                    condValues = (condValues !== "") ? JSON.parse(condValues) : [];
                }

                if (fields[select]["USER_TYPE_ID"] === "boolean") {
                    let options = [
                        {
                            value: "0",
                            label: (fields[select]["SETTINGS"]["LABEL"][0] !== "") ? fields[select]["SETTINGS"]["LABEL"][0] : BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_NO"),
                            check: (condValues !== "" && Number(condValues) === 0) ? true : false
                        },
                        {
                            value: "1",
                            label: (fields[select]["SETTINGS"]["LABEL"][1] !== "") ? fields[select]["SETTINGS"]["LABEL"][1] : BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_YES"),
                            check: (condValues !== "" && Number(condValues) === 1) ? true : false
                        }
                    ];
                    this.createSelect(valuesName, options, BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_VALUES", false, condValues));
                } else if (fields[select]["USER_TYPE_ID"] === "string") {
                    precondition = (type === "KEEP_NO") ? "AND" : precondition;
                    this.createText(valuesName, condValues, true, precondition);
                } else if (fields[select]["USER_TYPE_ID"] === "employee") {
                    this.createTagSelector(valuesName, this.userDialogOptions(condValues, type), "", true, precondition);
                } else if (fields[select]["USER_TYPE_ID"] === "tags") {
                    this.createTagSelector(valuesName, this.tagsDialogOptions(condValues), "", true, precondition);
                } else if (fields[select]["USER_TYPE_ID"] === "enumeration") {
                    let context = `brix_userfield_${fields[select]["ID"]}`;
                    this.createTagSelector(valuesName, this.enumsDialogOptions(Number(fields[select]["ID"]), condValues), context, true, precondition);
                } else if (
                    fields[select]["USER_TYPE_ID"] === "iblock_section" ||
                    fields[select]["USER_TYPE_ID"] === "iblock_element"
                ) {
                    let iblockId = Number(fields[select]["SETTINGS"]["IBLOCK_ID"]),
                        type = fields[select]["USER_TYPE_ID"].replace("iblock_", "");
                        context = `brix_userfield_${iblockId}`;
                    this.createTagSelector(valuesName, this.iblockDialogOptions(iblockId, type, condValues), context, true, precondition);
                } else if (fields[select]["USER_TYPE_ID"] === "crm") {
                    let context = `brix_userfield_${fields[select]["USER_TYPE_ID"]}`;
                    this.createTagSelector(valuesName, this.crmDialogOptions(fields[select]["SETTINGS"], condValues), context, true, precondition);
                }
                break;
            default:
                break;
        }
    },

    /**
     * Creates numeric fields and tracks their changes
     * 
     * @param {String} name
     * @param {Object} attributes = {min: 0, max: 0, step: 0}
     * @param {Number} precision
     * @param {bool} double
     * @param {String} condValues
     */
    createNumber(name, attributes = {min: 0, max: 0, step: 0}, precision = 0, double = false, condValues = "") {
        let container = this.createContainer(name),
            input = document.createElement("input"),
            dInput = double ? document.createElement("input") : "",
            values = [],
            nodes = [];

        input.classList.add("ui-ctl-element");
        input.setAttribute("type", "number");

        if (dInput) {
            dInput.classList.add("ui-ctl-element");
            dInput.setAttribute("type", "number");
        }

        Object.entries(attributes).forEach(([key, val]) => {
            if (Number(val) !== 0) {
                input.setAttribute(key, val);

                if (dInput) {
                    dInput.setAttribute(key, val);
                }
            }
        });

        if (double) {
            let dopContainer = document.createElement("div"),
                btn = !container.querySelector("button") ? document.createElement("button") : "";
            dopContainer.classList.add("linked-detail__cond-double");

            if (btn !== "") {
                btn.setAttribute("type", "button");
                btn.classList.add("ui-btn", "ui-btn-link");
                btn.textContent = BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_COND_ADD");
                btn.addEventListener("click", () => {
                    this.createNumber(name, attributes, Number(precision), double);
                });
                container.append(btn);
            }

            if (condValues !== "") {
                condValues = JSON.parse(condValues);
                values = condValues.shift();

                if (values.min) {
                    input.value = values.min;
                    input.setAttribute("data-value", condValues);
                }
                if (values.max) {
                    dInput.value = values.max;
                    dInput.setAttribute("data-value", condValues);
                }
            }

            nodes.push(input);
            nodes.push(dInput);
            dopContainer.append(input);
            dopContainer.append(dInput);
            container.querySelector("button").before(dopContainer);
        } else {
            input.value = condValues;
            input.setAttribute("data-value", condValues);
            nodes.push(input);
            container.append(input);
        }

        nodes.forEach(node => {
            ["input", "paste"].forEach(ev => {
                node.addEventListener(ev, () => {
                    this.validationNumber(name, node, Number(attributes.min), Number(attributes.max), Number(precision), double);
                });
            });

            node.addEventListener("keyup", function(e) {
                if (e.key === "+" || e.code === "Equal") {
                    node.value = node.getAttribute("data-value") ?? "";
                }
            });
        });

        if (typeof condValues === "object" && condValues.length > 0) {
            this.createNumber(name, attributes, Number(precision), double, JSON.stringify(condValues));
        } else {
            this.validationNumber(name, input, Number(attributes.min), Number(attributes.max), Number(precision), double);
        }
    },

    /**
     * The method of validating numeric fields
     * 
     * @param {String} name
     * @param {Element} node
     * @param {Number} min
     * @param {Number} max
     * @param {Number} precision
     * @param {bool} double
     */
    validationNumber(name, node, min = 0, max = 0, precision = 0, double = false) {
        let v = node.value;

        if (v !== "") {
            if (!Number.isInteger(v)) {
                let string = v.toString(),
                    i = string.includes(".") ? string.indexOf(".") : 0,
                    length = (string.length - i - 1);

                if (Number(precision) < Number(length)) {
                    let del = Number(precision) - Number(length);
                    string = string.slice(0, del);
                    node.value = Number(string);
                }
            }

            if (Number(min) !== 0 && Number(v) < Number(min)) {
                node.value = min;
            } else if (Number(max) !== 0 && Number(v) > Number(max)) {
                node.value = max;
            }

            node.setAttribute("data-value", v);
        }

        if (!double) {
            this.changeInput(document.querySelector(`[name="${name}"]`), node.value);
        } else {
            let inputs = document.querySelectorAll(`[data-name="${name}"] input`),
                obj = {},
                values = [];

            inputs.forEach((input, k) => {
                if (k % 2 === 0) {
                    obj["min"] = input.value;
                } else {
                    obj["max"] = input.value;
                    values.push(obj);
                    obj = {};
                }
            });

            this.changeInput(document.querySelector(`[name="${name}"]`), values, true);
        }
    },

    /**
     * Creates numeric fields and tracks their changes
     * 
     * @param {String} name
     * @param {String} condValues
     * @param {bool} multi
     * @param {String} precondition
     */
    createText(name, condValues = "", multi = false, precondition = "") {
        let container = this.createContainer(name),
            input = document.createElement("input"),
            value = "",
            nodes = [];

        input.classList.add("ui-ctl-element");
        input.setAttribute("type", "text");


        if (multi) {
            let dopContainer = document.createElement("div"),
                btn = !container.querySelector("button") ? document.createElement("button") : "",
                labels = BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_ADDITIONAL"),
                beforeText = (precondition === "OR") ? labels[1]["label"] : labels[0]["label"];

            dopContainer.classList.add("linked-detail__cond-text");
            dopContainer.setAttribute("data-before", beforeText);

            if (btn !== "") {
                btn.setAttribute("type", "button");
                btn.classList.add("ui-btn", "ui-btn-link");
                btn.textContent = BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_COND_ADD");
                btn.addEventListener("click", () => {
                    this.createText(name, "", multi, precondition);
                });
                container.append(btn);
            }

            if (condValues !== "") {
                condValues = JSON.parse(condValues);
                value = condValues.shift();
                input.value = value;
            }

            nodes.push(input);
            dopContainer.append(input);
            container.querySelector("button").before(dopContainer);
        } else {
            input.value = condValues;
            nodes.push(input);
            container.append(input);
        }

        nodes.forEach(node => {
            ["input", "paste"].forEach(ev => {
                node.addEventListener(ev, () => {
                    this.validationText(name, input, multi);
                });
            });
        });

        if (typeof condValues === "object" && condValues.length > 0) {
            this.createText(name, JSON.stringify(condValues), multi, precondition);
        } else {
            this.validationText(name, input, multi);
        }
    },

    /**
     * The method of validating numeric fields
     * 
     * @param {String} name
     * @param {Element} node
     * @param {bool} multi
     */
    validationText(name, node, multi = false) {
        let v = node.value;

        if (v !== "" && v !== v.replace(/(<([^>]+)>)/ig, "")) {
            node.value = v.replace(/(<([^>]+)>)/ig, "");
        }

        if (!multi) {
            this.changeInput(document.querySelector(`[name="${name}"]`), node.value);
        } else {
            let inputs = document.querySelectorAll(`[data-name="${name}"] input`),
                values = [];

            inputs.forEach(input => {
                if (input.value !== "") {
                    values.push(input.value);
                }
            });

            this.changeInput(document.querySelector(`[name="${name}"]`), values, true);
        }
    },

    /**
     * Creates Tagselector objects
     * @param {String} name
     * @param {Object} dialogOptions
     * @param {String} context
     * @param {bool} multi
     * @param {String} precondition
     */
    createTagSelector(name, dialogOptions = {}, context = "", multi = false, precondition = "") {
        if (context === "") {
            context = "brix.linked.detail";
        }
        dialogOptions.context = context;
        dialogOptions.enableSearch = true;

        let $this = this,
            container = this.createContainer(name),
            preselectedItems = [],
            options = {
                multiple: true,
                dialogOptions: dialogOptions,
                events: {
                    onAfterTagAdd: (event) => {
                        $this.selectedTagSelector(name);
                    },
                    onAfterTagRemove: (event) => {
                        $this.selectedTagSelector(name);
                    }
                }
            };

        if (dialogOptions.preselectedItems) {
            preselectedItems = dialogOptions.preselectedItems;
            options.dialogOptions.preselectedItems = preselectedItems.shift();
        }

        let tagSelector = new BX.UI.EntitySelector.TagSelector(options);

        if (!this._tagSelectors[name]) {
            this._tagSelectors[name] = [];
        }

        this._tagSelectors[name].push(tagSelector);

        if (multi) {
            let dopContainer = document.createElement("div"),
                btn = !container.querySelector("button") ? document.createElement("button") : "",
                labels = BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_ADDITIONAL"),
                beforeText = (precondition === "OR") ? labels[1]["label"] : labels[0]["label"];

            dopContainer.classList.add("linked-detail__cond-tagselector");
            dopContainer.setAttribute("data-before", beforeText);

            if (btn !== "") {
                btn.setAttribute("type", "button");
                btn.classList.add("ui-btn", "ui-btn-link");
                btn.textContent = BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_COND_ADD");
                if (dialogOptions.preselectedItems) {
                    delete dialogOptions.preselectedItems;
                }
                btn.addEventListener("click", () => {
                    this.createTagSelector(name, dialogOptions, context, multi, precondition);
                });
                container.append(btn);
            }

            container.querySelector("button").before(dopContainer);
            tagSelector.renderTo(dopContainer);
        } else {
            tagSelector.renderTo(container);
        }

        if (preselectedItems.length > 0) {
            dialogOptions.preselectedItems = preselectedItems;
            this.createTagSelector(name, dialogOptions, context, multi, precondition);
        }
    },

    /**
     * Returns an array for a field with users
     * @param {Array} condValues
     * @param {String} type
     * @returns {Object}
     */
    userDialogOptions(condValues = [], type = "") {
        let dialogOptions = {
            entities: [
                {
                    id: "user",
                    options: {
                        inviteEmployeeLink: false
                    }
                },
                {
                    id: "department",
                    options: {
                        selectMode: "usersOnly"
                    }
                }
            ]
        };

        if (type === "" || (type !== "SAME" && type !== "SAME_NO")) {
            dialogOptions.entities[1]["options"] = {
                selectMode: "usersAndDepartments",
                allowFlatDepartments: true
            };
        }

        if (condValues.length > 0) {
            dialogOptions.preselectedItems = [];
            condValues.forEach(cond => {
                let preselectedItems = [];
                cond.forEach(c => {
                    let chart0 = (c.charAt(0) === "U"),
                        chart1 = (!chart0 && c.charAt(1) === "C"),
                        id = chart0 ? Number(c.slice(1)) : (chart1 ? Number(c.slice(2)) : c.slice(1) + ":F");

                    preselectedItems.push([
                        chart0 ? "user" : "department",
                        id
                    ]);
                });
                dialogOptions.preselectedItems.push(preselectedItems);
            });
        }

        return dialogOptions;
    },

    /**
     * Returns an array for a field with groups/projects
     * @param {Array} condValues
     * @returns {Object}
     */
    groupDialogOptions(condValues = []) {
        let dialogOptions = {
            entities: [
                {
                    id: "project",
                    options: {
                        createProjectLink: false
                    }
                }
            ]
        };

        if (condValues.length > 0) {
            dialogOptions.preselectedItems = [];
            condValues.forEach(cond => {
                let preselectedItems = [];
                cond.forEach(c => {
                    preselectedItems.push(["project", c]);
                });
                dialogOptions.preselectedItems.push(preselectedItems);
            });
        }

        return dialogOptions;
    },

    /**
     * Returns an array for a field with task-tag
     * @param {Array} condValues
     * @returns {Object}
     */
    tagsDialogOptions(condValues = []) {
        let dialogOptions = {
            entities: [
                {
                    id: "brix_task_tag",
                    dynamicLoad: true,
                    dynamicSearch: true
                }
            ]
        };

        if (condValues.length > 0) {
            dialogOptions.preselectedItems = [];
            condValues.forEach(cond => {
                let preselectedItems = [];
                cond.forEach(c => {
                    preselectedItems.push(["brix_task_tag", c]);
                });
                dialogOptions.preselectedItems.push(preselectedItems);
            });
        }

        return dialogOptions;
    },

    /**
     * Returns an array for a field with enumerations
     * @param {Number} id
     * @param {Array} condValues
     * @returns {Object}
     */
    enumsDialogOptions(id, condValues = []) {
        let dialogOptions = {
            entities: [
                {
                    id: "brix_enumeration",
                    options: {
                        id: id
                    }
                }
            ]
        };

        if (condValues.length > 0) {
            dialogOptions.preselectedItems = [];
            condValues.forEach(cond => {
                let preselectedItems = [];
                cond.forEach(c => {
                    preselectedItems.push(["brix_enumeration", c]);
                });
                dialogOptions.preselectedItems.push(preselectedItems);
            });
        }

        return dialogOptions;
    },

    /**
     * Returns an array for a field with iblocks
     * @param {Number} iblockId
     * @param {String} type
     * @param {Array} condValues
     * @returns {Object}
     */
    iblockDialogOptions(iblockId, type = "element", condValues = []) {
        type = `iblock-property-${type}`;
        let dialogOptions = {
            entities: [
                {
                    id: type,
                    dynamicLoad: true,
                    dynamicSearch: true,
                    options: {
                        iblockId: iblockId
                    }
                }
            ]
        };

        if (condValues.length > 0) {
            dialogOptions.preselectedItems = [];
            condValues.forEach(cond => {
                let preselectedItems = [];
                cond.forEach(c => {
                    preselectedItems.push([type, c]);
                });
                dialogOptions.preselectedItems.push(preselectedItems);
            });
        }

        return dialogOptions;
    },

    /**
     * Returns an array for a field with crm
     * @param {Object} settings
     * @param {Array} condValues
     * @returns {Object}
     */
    crmDialogOptions(settings, condValues = []) {
        let entities = [],
            dialogOptions = {
                entities: []
            },
            crm = {
                CO: "company",
                C: "contact",
                D: "deal",
                L: "lead",
                Q: "quote",
                SI: "smart_invoice"
            },
            dynamic = [];

        Object.entries(settings).forEach(([type, val]) => {
            if (val === "Y") {
                if (type.includes("DYNAMIC_")) {
                    let typeName = type.split("_", 2);
                    crm[`DYN${typeName[1]}`] = "dynamic_multiple";
                    dynamic.push(Number(typeName[1]));
                } else {
                    entities.push({
                        id: type.toLowerCase(),
                        dynamicLoad: true,
                        dynamicSearch: true
                    });
                }
            }
        });

        if (dynamic.length > 0) {
            entities.push({
                id: "dynamic_multiple",
                dynamicLoad: true,
                dynamicSearch: true,
                options: {
                    dynamicTypeIds: dynamic
                }
            });
        }

        dialogOptions.entities = entities;

        if (condValues.length > 0) {
            dialogOptions.preselectedItems = [];
            condValues.forEach(cond => {
                let preselectedItems = [];
                cond.forEach(c => {
                    let ar = c.split("_", 2);
                    if (ar[0] === "DYN") {
                        preselectedItems.push(["dynamic_multiple", ar[1]]);
                    } else {
                        preselectedItems.push([crm[ar[0]], Number(ar[1])]);
                    }
                });
                dialogOptions.preselectedItems.push(preselectedItems);
            });
        }

        return dialogOptions;
    },

    /**
     * Processes selected TagSelector elements
     * @param {String} name
     */
    selectedTagSelector(name) {
        let items = [],
            count = 0,
            crm = {
                company: "CO_",
                contact: "C_",
                deal: "D_",
                lead: "L_",
                quote: "Q_",
                smart_invoice: "SI_"
            };
        this._tagSelectors[name].forEach((ts) => {
            let selected = ts.getDialog().getSelectedItems();

            if (selected.length > 0) {
                items[count] = [];
                selected.forEach(it => {
                    let ent = it.getEntityId(),
                        id = it.getId();

                    if (ent === "user") {
                        items[count].push(`U${id}`);
                    } else if (ent === "department") {
                        if (typeof id === "number") {
                            items[count].push(`DC${id}`);
                        } else {
                            let ar = id.split(":");
                            items[count].push(`D${Number(ar[0])}`);
                        }
                    } else if (crm[ent]) {
                        items[count].push(`${crm[ent]}${id}`);
                    } else if (ent === "dynamic_multiple") {
                        items[count].push(`DYN_${id}`);
                    } else {
                        items[count].push(id);
                    }
                });
                count++;
            }
        });
        this.changeInput(document.querySelector(`[name="${name}"]`), items, true);
    },

    /**
     * Creates a container for fields
     * @param {String} name 
     * @returns {Element}
     */
    createContainer(name) {
        let node = document.querySelector(`[data-name="${name}"] .linked-detail__cond-block`);

        if (!node) {
            node = document.createElement("div");
            node.classList.add("linked-detail__cond-block");
            document.querySelector(`[data-name="${name}"]`).append(node);
        }

        return node;
    },

    /**
     * Actions on form buttons
     */
    events() {
        let $this = this,
            fields = this.getFields();

        document.getElementById("linked-detail-close").addEventListener("click", () => {
            let topSlider = BX.SidePanel.Instance.getTopSlider();
    
            if (topSlider) {
                topSlider.close();
            }
        });

        document.getElementById("linked-detail-form").addEventListener("submit", (e) => {
            e.preventDefault();
            let formData = new FormData(e.target),
                data = {},
                conditions = [],
                additional = [],
                error = [],
                type, typeNode, values, valuesNode;

            for (let [name, value] of formData) {
                if (name === "FIELD_NAME") {
                    if (value === "") {
                        error.push(name);
                        break;
                    } else {
                        data[name] = value;
                    }
                } else if (name === "ACTIVE" || name === "REQUIRED") {
                    data[name] = value;
                } else if (name.includes("CONDITIONS") && name.includes("FIELD")) {
                    if (value === "") {
                        error.push(name);
                        break;
                    } else {
                        type = name.replace("FIELD", "TYPE");
                        values = name.replace("FIELD", "VALUES");
                        typeNode = document.querySelector(`[name="${type}"]`);
                        valuesNode = document.querySelector(`[name="${values}"]`);

                        if (typeNode.value === "") {
                            error.push(type);
                            break;
                        } else if (typeNode.value !== "FILL" && valuesNode.value === "") {
                            error.push(values);
                            break;
                        } else {
                            let valuePush = valuesNode.value;
                            
                            if (
                                typeNode.value === "KEEP" || typeNode.value === "KEEP_NO" || typeNode.value === "RANGE" ||
                                (typeNode.value === "IN" && fields[value]["USER_TYPE_ID"] !== "string") ||
                                (typeNode.value === "IN_NO" && fields[value]["USER_TYPE_ID"] !== "string") ||
                                (typeNode.value === "SAME" && fields[value]["USER_TYPE_ID"] !== "boolean") ||
                                (typeNode.value === "SAME_NO" && fields[value]["USER_TYPE_ID"] !== "boolean")
                            ) {
                                valuePush = JSON.parse(valuesNode.value);
                            }

                            conditions.push({field: value, type: typeNode.value, value: valuePush});
                        }
                    }
                } else if (name.includes("ADDITIONAL")) {
                    additional.push(value);
                }
            }

            if (!data["ACTIVE"]) {
                data["ACTIVE"] = "N";
            }
            if (!data["REQUIRED"]) {
                data["REQUIRED"] = "N";
            }

            if (conditions.length === 0) {
                $this.showError();
            } else if (error.length > 0) {
                $this.showError(false);
            } else {
                data["CONDITIONS"] = [];
                conditions.forEach((cond, key) => {
                    data["CONDITIONS"].push(cond);
                    if (additional[key]) {
                        data["CONDITIONS"].push(additional[key]);
                    }
                });

                BX.ajax.runAction("brix:linkedtaskfields.linked.update", {
                    data: {
                        fields: data,
                        fieldName: $this._fieldName
                    }
                }).then((response) => {
                    BX.SidePanel.Instance.postMessage(window, "linked-reload");
                    let topSlider = BX.SidePanel.Instance.getTopSlider();
                    if (topSlider) {
                        topSlider.close();
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
        });
    },

    /**
     * Shows an error
     * @param {boolean} type
     */
    showError(type = true) {
        let errorNode = document.getElementById("linked-detail-error"),
            text = type ? BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_ERROR_1") : BX.message("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_ERROR_2");
        errorNode.querySelector(".linked-detail__text").textContent = text;
        errorNode.classList.remove("d-none");

        let targetPosition = {
                top: window.pageYOffset + errorNode.getBoundingClientRect().top,
                bottom: window.pageYOffset + errorNode.getBoundingClientRect().bottom
            },
            windowPosition = {
                top: window.pageYOffset,
                bottom: window.pageYOffset + document.documentElement.clientHeight
            };
      
        if (targetPosition.bottom <= windowPosition.top || targetPosition.top >= windowPosition.bottom) {
            errorNode.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }
    }
};