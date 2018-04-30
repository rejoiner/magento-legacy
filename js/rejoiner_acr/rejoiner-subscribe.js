    'use strict';

    this.RejoinerSubscribe = function (config) {
        this.config = config;
        this.init();
    };

    RejoinerSubscribe.prototype.init = function () {
        var page = this.config.subscribePage;
        if (page == 'checkoutOnePage') {
            if (!this.config.isLoggedIn) {
                if (this.config.guestCheckout) {
                    this.addRejoinerBlock(page + 'Form');
                }
                if (this.config.customerLogin) {
                    this.addRejoinerBlock('customerLoginForm');
                }
            } else {
                if (this.config.guestCheckout && !this.config.isAlreadySubscribed) {
                    this.addRejoinerBlock(page + 'Form');
                }
            }
        } else {
            if (this.config[page]) {
                if (page == 'customerLogin') {
                    this.addRejoinerBlock(page + 'Form');
                } else {
                    this.updateDefaultBlock(page + 'Block');
                }
            } else {
                if (page == 'customerRegister') {
                    this.removeDefaultBlock(page + 'Block');
                }
            }

        }
    };

    RejoinerSubscribe.prototype.addRejoinerBlock = function (formId) {
        var targetForm = document.getElementById(this.config[formId]);
        if (targetForm) {
            var anchor = targetForm.getElementsByClassName('form-list')[0];
            if (this.config.subscribePage == 'checkoutOnePage' && !this.config.isLoggedIn) {
                if (formId != 'customerLoginForm') {
                    anchor = document.getElementById(this.config.checkoutAnchor).parentNode.parentNode;
                    anchor.parentNode.insertBefore(document.getElementById(this.config.rejoinerId), anchor.nextSibling);
                } else {
                    anchor.appendChild(document.getElementById(this.config.rejoinerId).cloneNode(true));
                }
            } else {
                anchor.appendChild(document.getElementById(this.config.rejoinerId));
            }
        }

    };

    RejoinerSubscribe.prototype.updateDefaultBlock = function (blockId) {
        var subscribeInput = document.getElementById(this.config[blockId]);
        if (subscribeInput) {

            if (this.config.checkboxSelector) {
                subscribeInput.classList.add(this.config.checkboxSelector);
            }
            if (this.config.checkboxStyle) {
                subscribeInput.setAttribute("style", this.config.checkboxStyle);
            }
            if (this.config.checkboxLabel) {
                document.querySelector("[for='"+this.config[blockId]+"']").innerHTML = this.config.checkboxLabel;
            }
            if (this.config.subscribePage != 'customerAccount') {
                if (this.config.checkboxDefault) {
                    subscribeInput.checked = true;
                }
            } else {
                subscribeInput.checked = false;
            }
        }
    };

    RejoinerSubscribe.prototype.removeDefaultBlock = function (blockId) {
        var subscribeInput = document.getElementById(this.config[blockId]);
        if (subscribeInput) {
            document.getElementsByClassName('form-list')[0].removeChild(document.querySelector("[for='"+this.config[blockId]+"']").parentNode);

        }
    };
