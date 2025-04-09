function BrixAccess(data = {}) {
    let isAccess = (data.isAccess && data.isAccess === "Y") ? true : false,
        isExtended = (data.isExtended && data.isExtended === "Y") ? true : false,
        wrap = data.wrap ? document.getElementById(`${data.wrap}`) : false,
        names = data.names ?? {},
        options = [];
    this._container = null;
    this._containerClasses = data.classes ? [...["access"], ...data.classes] : ["access"];
    this._items = [];
    this._itemClasses = "";
    this._btnClasses = "";
    this._arSelected = {};
    this.counter = 0;

    if (data.options && Object.keys(data.options).length > 0) {
        let tasks = data.options;

        if (!isExtended) {
            options.push({value: "0", label: BX.message("BRIX_ACCESSES_CUSTOM_ACCESS_DEFAULT_OPT")});
        }

        for (let id in tasks) {
            options.push({value: Number(id), label: tasks[id].NAME});
        }
    }

    this.getIsAccess = () => {
        return isAccess;
    };
    this.getIsExtended = () => {
        return isExtended;
    };
    this.getWrap = () => {
        return wrap;
    };
    this.getOptions = () => {
        return options;
    };

    if (data.items) {
        Object.values(data.items).forEach(item => {
            let name = names[item.CODE] ? (!this.getIsExtended() ? names[item.CODE].name : (names[item.CODE].provider + " " + names[item.CODE].name).trim()) : "",
                newItem = {
                    id: item.ID,
                    code: item.CODE,
                    name: name,
                    option: Number(item.TASK_ID),
                    parent: (item.PARENT && item.PARENT === "Y") ? true : false
                };

            if (!newItem.parent) {
                this._arSelected[newItem.code] = true;
            }

            if (!this.getIsExtended() && Number(item.ID) === 2) {
                this._items.unshift(newItem);
            } else {
                this._items.push(newItem);
            }
        });
    }

    if (wrap) {
        this.getClasses();
        this.create();
        this.createBtnAdd();
        this.proxyHandler();
    }
}

BrixAccess.prototype = {
    get items() {
        return this._items;
    },

    set items(item) {
        if (
            typeof item === "object" && item.hasOwnProperty("id") &&
            item.hasOwnProperty("code") && item.hasOwnProperty("name")
        ) {
            this.proxy[this.proxy.length] = item;
        } else {
            console.error("Required attributes were not passed");
        }
    },

    getTemplates() {
        return {
            template: '<li class="${this._itemClasses}">${tempItem}</li>',
            templateView: '<p class="${this._textClasses}">${item.name}</p><p class="${this._textClasses}">${this.sanitizer(item.opt.label)}</p>',
            templateEdit: '<p class="${this._textClasses}">${this.sanitizer(item.name)}</p>${select}',
            templateTextDefault: '<p class="${this._titleClasses}">${BX.message(`BRIX_ACCESSES_CUSTOM_ACCESS_DEFAULT`)}</p>',
            templateTextGroup: '<p class="${this._titleClasses}">${BX.message(`BRIX_ACCESSES_CUSTOM_ACCESS_GROUPS`)}</p>',
            templateBtnDel: '<button class="${this._btnClasses} ui-btn ui-btn-link ui-btn-icon-cancel" type="button" aria-label="${BX.message(`BRIX_ACCESSES_CUSTOM_ACCESS_DEL`)}"></button>',
            templateSelect: '<div class="${this._blockClasses}"><div data-id="${item.code}"></div>${button}</div>'
        };
    },

    getClasses() {
        if (this._containerClasses.length > 0) {
            this._itemClasses = this._containerClasses.map(cl => {
                return `${cl}__item`;
            });
            this._textClasses = this._containerClasses.map(cl => {
                return `${cl}__text`;
            });
            this._titleClasses = this._containerClasses.map(cl => {
                return `${cl}__title`;
            });
            this._blockClasses = this._containerClasses.map(cl => {
                return `${cl}__block`;
            });
            this._btnClasses = this._containerClasses.map(cl => {
                return `${cl}__btn`;
            });

            this._itemClasses = this._itemClasses.join(" ");
            this._textClasses = this._textClasses.join(" ");
            this._titleClasses = this._titleClasses.join(" ");
            this._blockClasses = this._blockClasses.join(" ");
            this._btnClasses = this._btnClasses.join(" ");
        }
    },

    create(event = "create") {
        if (!this._container) {
            this._container = document.createElement("ul");

            if (this._containerClasses) {
                this._containerClasses.forEach(cl => {
                    this._container.classList.add(cl);

                    if (this.getIsExtended()) {
                        this._container.classList.add(`${cl}_extended`);
                    }
                });
            }
        }

        if (event === "del") {
            if (this._container && this._container.children.length === 0) {
                this._container.remove();
                this._container = null;
            }
        } else if (event !== "set") {
            if (this._items) {
                let parents = this._items.filter(item => item.parent);
                let items = this._items.filter(item => !item.parent);
                this._items = items;

                if (parents) {
                    parents.forEach((item) => this.add(item));
                }

                if (this._items) {
                    this._items.forEach((item) => this.add(item));
                }
            }
        }

        if (this._container) {
            this.getWrap().prepend(this._container);
        }
    },

    createBtnAdd() {
        if (this.getIsExtended() && this.getIsAccess()) {
            let btnAdd = document.createElement("button");
            btnAdd.classList.add("access-add", "ui-btn", "ui-btn-link");
            btnAdd.setAttribute("type", "button");
            btnAdd.textContent = BX.message("BRIX_ACCESSES_CUSTOM_ACCESS_ADD");
            this.getWrap().appendChild(btnAdd);
            BX.Access.Init(this._arSelected);
            BX.Access.SetSelected(this._arSelected, "RIGHTS");

            btnAdd.addEventListener("click", () => {
                BX.Access.ShowForm({
                    callback: BX.delegate(this.callRights, this),
                    bind: "RIGHTS",
                });
            });
        }
    },

    /**
     * Processing of selected access groups
     * 
     * @param arRights | object, selected access rights
     */
    callRights(arRights) {
        for (let provider in arRights) {
            if (arRights.hasOwnProperty(provider)) {
                for (let id in arRights[provider]) {
                    if (arRights[provider].hasOwnProperty(id)) {
                        this.items = {
                            id: `n${this.counter}`,
                            code: id,
                            name: (BX.Access.GetProviderPrefix(provider, id) + " " + arRights[provider][id].name).trim()
                        };

                        this.counter++;
                    }
                }
            }
        }
    },

    proxyHandler() {
        this.proxy = new Proxy(this._items, this.handler());
    },

    sanitizer(str = "") {
        let dom = new DOMParser().parseFromString(str, "text/html");
        return dom.body.textContent || "";
    },

    add(item) {
        let templates = this.getTemplates(),
            template = "",
            button = "";

        if (this.getIsExtended()) {
            button = templates.templateBtnDel.replace(/\${(.*?)}/g, (match, key) => {
                return eval(key);
            });
        }

        template = (item.parent || !this.getIsAccess()) ? templates.templateView : templates.templateEdit;

        let select = templates.templateSelect.replace(/\${(.*?)}/g, (match, key) => {
                return eval(key);
            }),
            tempItem = "",
            str = "";

        item.opt = this.getOptions().find(opt => Number(opt.value) === Number(item.option));

        if (!this.getIsExtended()) {
            if (Number(item.id) === 2) {
                tempItem = templates.templateTextDefault.replace(/\${(.*?)}/g, (match, key) => {
                    return eval(key);
                });
            } else if (Number(item.id) === Number(this._items[1].id)) {
                tempItem = templates.templateTextGroup.replace(/\${(.*?)}/g, (match, key) => {
                    return eval(key);
                });
            }

            if (tempItem !== "") {
                str = templates.template.replace(/\${(.*?)}/g, (match, key) => {
                    return eval(key);
                });
            }
        }

        tempItem = template.replace(/\${(.*?)}/g, (match, key) => {
            return eval(key);
        });
        str += templates.template.replace(/\${(.*?)}/g, (match, key) => {
            return eval(key);
        });

        let dom = new DOMParser().parseFromString(str, "text/html").body,
            btn = dom.querySelector("button");

        if (btn) {
            btn.addEventListener("click", event => this.delete(item.code));
        }

        [...dom.children].forEach(child => this._container.appendChild(child));

        if (this._container.querySelector(`[data-id="${item.code}"]`)) {
            let bxSelect = new BX.Ui.Select({ 
                value: (Number(item.option) === 0) ? '0' : item.option,
                options: this.getOptions(),
                isSearchable: false,
            });

            bxSelect.subscribe("update", (e) => {
                this.change(item.code, bxSelect.getValue());
            });
            bxSelect.renderTo(this._container.querySelector(`[data-id="${item.code}"]`));
        }
    },

    delete(i) {
        let index = this._items.findIndex(item => item.code === i);

        if (index !== -1) {
            delete this.proxy[index];
        }
    },

    change(i, v) {
        let index = this._items.findIndex(item => item.code === i);

        if (index !== -1) {
            let element = this.proxy[index],
                option = this.getOptions().find(opt => Number(opt.value) === Number(v));

            element.option = Number(v);
            element.opt = {
                id: Number(v),
                name: option.label
            };

            this.proxy[index] = element;
        }
    },

    handler() {
        return {
            deleteProperty: (target, key) => {
                let id = target[key].code,
                    res = target.splice(key, 1);

                if (this._container.querySelector(`[data-id="${id}"]`)) {
                    this._container.querySelector(`[data-id="${id}"]`).closest("li").remove();
                }

                delete this._arSelected[id];
                this.create("del");

                return res;
            },
            set: (target, key, val) => {
                if (!val.hasOwnProperty("option")) {
                    val.option = this.getOptions()[0].value;
                }

                if (!val.hasOwnProperty("parent")) {
                    val.parent = false;
                }

                let id = target.find(el => el.code === val.code);
                target[key] = val;

                this.create("set");

                if (!id) {
                    this.add(val);
                }

                return true;
            }
        };
    }
}