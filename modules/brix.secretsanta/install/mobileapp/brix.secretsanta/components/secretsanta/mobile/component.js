/**
* @module secretsanta
*/
(() => {
    const require = (ext) => jn.require(ext);
    const Apptheme = require("apptheme");
    const { ActionAjax } = require("secretsanta/ajax");
    const { LoadingScreen } = require("layout/ui/loading-screen");
    const { ProfileView } = require("user/profile");
    const { PureComponent } = require("layout/pure-component");
    const { TextEditor } = require("layout/ui/text-editor");
    const STYLES = Object.freeze({
        container: {
            padding: 10
        },
        scrollContainer: {
            paddingRight: 8
        },
        text: {
            default: {
                marginBottom: 8,
                opacity: 0.9,
                fontSize: 18
            },
            title: {
                marginTop: 8,
                marginBottom: 12,
                fontSize: 18,
                fontWeight: 500,
                textDecorationLine: "underline"
            },
            wish: {
                marginBottom: 8,
                fontSize: 16
            },
            nowish: {
                marginBottom: 8,
                opacity: 0.8,
                fontSize: 15
            }
        },
        buttons: {
            default: {
                marginTop: 8,
                maxWidth: "90%"
            },
            edit: {
                marginTop: 8,
                marginBottom: 12,
                maxWidth: "90%"
            }
        }
    });

    class BrixSecretSantaMobile extends PureComponent {
        /**
         * Constructor
         */
        constructor(props) {
            super(props);
            this.layout = props.layout;
            this.recipient = {};
            this.config = {};
            this.type = false;
            this.player = {};
            this.state = {
                isLoading: true
            };
        }

        /**
         * Launching methods for the first rendering
         */
        componentDidMount() {
            this.getConfig();
        }

        /**
         * Updates the value from the state to display the loader
         */
        showLoading() {
            this.setState({
                isLoading: true
            });
        }

        /**
         * Updates the value from the state to hide the loader
         */
        hideLoading() {
            this.setState({
                isLoading: false
            });
        }

        /**
         * Getting basic information about the game
         */
        getConfig() {
            ActionAjax.get("config")
                .then((response) => {
                    if (response.data) {
                        this.config = response.data ?? {};
                        this.getPlayer();
                    }
                })
                .catch((response) => {
                    this.hideLoading();
                });
        }

        /**
         * Gets information about the participant
         */
        getPlayer() {
            ActionAjax.get("player", {isMobile: true})
                .then((response) => {
                    if (response.data) {
                        this.recipient = response.data.recipient ?? {};
                        this.type = response.data.type ?? false;
                        this.player = response.data.player ?? {};
                    }

                    this.hideLoading();
                })
                .catch((response) => {
                    this.hideLoading();
                });
        }

        /**
         * Returns the view for the registration stage
         * @returns {View}
         */
        registration() {
            return View(
                {
                    style: STYLES.container
                },
                ScrollView(
                    {
                        style: STYLES.scrollContainer,
                        showsVerticalScrollIndicator: true
                    },
                    View(
                        {},
                        (this.config.datestart !== "") ? Text({
                            style: STYLES.text.default,
                            html: this.config.textstart + "<b>" + this.config.datestart + "</b>"
                        }) : null,
                        (this.config.dateend !== "") ? Text({
                            style: STYLES.text.default,
                            html: this.config.textend + "<b>" + this.config.dateend + "</b>"
                        }) : null,
                        (this.player.TAKE_PART === "Y") ? View(
                            {},
                            Text({
                                style: STYLES.text.title,
                                text: this.config.mywish
                            }),
                            Text({
                                style: (this.player.WISHLIST !== "") ? STYLES.text.wish : STYLES.text.nowish,
                                html: (this.player.WISHLIST !== "") ? this.player.WISHLIST : this.config.wishempty
                            }),
                            View(
                                {
                                    style: STYLES.buttons.edit
                                },
                                new PrimaryButton({
                                    rounded: true,
                                    text: this.config.edit[0],
                                    onClick: () => {
                                        TextEditor.open({
                                            title: this.config.mywish,
                                            text: this.player.EDIT_WISHLIST,
                                            onSave: (text) => this.updateWishlist(text)
                                        });
                                    }
                                })
                            ),
                            View(
                                {
                                    style: STYLES.buttons.default
                                },
                                new CancelButton({
                                    rounded: true,
                                    text: this.config.cancel,
                                    onClick: () => this.updateTakePart("N")
                                })
                            )
                        ) : null,
                        (this.player.TAKE_PART !== "Y") ? View(
                            {
                                style: STYLES.buttons.default
                            },
                            new PrimaryButton({
                                rounded: true,
                                text: this.config.confirm,
                                onClick: () => this.updateTakePart()
                            })
                        ) : null
                    )
                )
            );
        }

        /**
         * Action for the participation status update button
         * @param {String} part
         */
        updateTakePart(part = "Y") {
            this.showLoading();

            ActionAjax.post("takePart", {takePart: part})
                .then((response) => {
                    this.player.TAKE_PART = part;
                    this.hideLoading();
                })
                .catch((response) => {
                    this.hideLoading();
                });
        }

        /**
         * Action for the wishlist update button
         * @param {?String} wishlist
         */
        updateWishlist(wishlist = null) {
            this.showLoading();
            wishlist = wishlist ?? "";

            ActionAjax.post("wishlist", {wishlist: wishlist, isMobile: true})
                .then((response) => {
                    if (response.data) {
                        this.player.WISHLIST = response.data.text;
                        this.player.EDIT_WISHLIST = response.data.editText;
                    }
                    this.hideLoading();
                })
                .catch((response) => {
                    this.hideLoading();
                });
        }

        /**
         * Returns the view to start with
         * @returns {View}
         */
        start() {
            return View(
                {
                    style: STYLES.container
                },
                ScrollView(
                    {
                        style: STYLES.scrollContainer,
                        showsVerticalScrollIndicator: true
                    },
                    View(
                        {},
                        (this.player.TAKE_PART === "Y") ? View(
                            {},
                            (this.config.dateend !== "") ? Text({
                                style: STYLES.text.default,
                                html: this.config.textend + "<b>" + this.config.dateend + "</b>"
                            }) : null,
                            (this.recipient.link !== "") ? View(
                                {
                                    onClick: () => {
                                        this.layout.openWidget(
                                            "list",
                                            {
                                                backdrop: {
                                                    bounceEnable: false,
                                                    swipeAllowed: true,
                                                    showOnTop: true,
                                                    hideNavigationBar: true,
                                                    horizontalSwipeAllowed: false
                                                }
                                            }
                                        )
                                            .then((list) => ProfileView.open({
                                                userId: this.recipient.id,
                                                isBackdrop: true
                                            }, list))
                                            .catch(console.error)
                                        ;
                                    }
                                },
                                Text({
                                    style: STYLES.text.default,
                                    html: this.config.recipient + '<span style="color: ' + Apptheme.colors.accentMainLinks + ';">' + this.recipient.link + '</span>'
                                }),
                                Text({
                                    style: STYLES.text.title,
                                    text: this.config.recipientwich
                                }),
                                Text({
                                    style: (this.recipient.wish !== "") ? STYLES.text.wish : STYLES.text.nowish,
                                    html: (this.recipient.wish !== "") ? this.recipient.wish : this.config.wishempty
                                })
                            ) : null
                        ) : Text({
                            style: STYLES.text.default,
                            text: this.config.notPart
                        })
                    )
                )
            );
        }

        /**
         * Basic rendering function
         * @returns {View}
         */
        render() {
            this.editor = false;

            if (this.state.isLoading) {
                return View(
                    {},
                    new LoadingScreen()
                );
            }

            if (!this.type) {
                return View({});
            }

            if (Object.keys(this.player).length === 0) {
                let text = (this.type === "registration") ? this.config.notCheckPart : this.config.notPart;
                return View(
                    {
                        style: STYLES.container
                    },
                    Text({
                        style: STYLES.text.default,
                        text: text
                    })
                );
            } else {
                if (this.type === "registration") {
                    return this.registration();
                } else if (this.type === "start") {
                    return this.start();
                }
            }
        }
    };

    BX.onViewLoaded(() => {
        layout.enableNavigationBarBorder(false);
        layout.showComponent(new BrixSecretSantaMobile({ layout }));
    });
})();