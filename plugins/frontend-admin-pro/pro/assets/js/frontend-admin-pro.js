!(function (e, t) {
    var a = acf.Field.extend({
        type: "product_types",
        data: { ftype: "select" },
        select2: !1,
        wait: "load",
        events: { 'click input[type="radio"]': "onClickRadio", "change select": "onChooseOption" },
        $control: function () {
            return this.$(".acf-product-types-field");
        },
        $input: function () {
            return this.getRelatedPrototype().$input.apply(this, arguments);
        },
        $forVariable: function (e) {
            return e.parents("form").find(".acf-field-product-attributes").find('div[data-name="locations"]').find("li:last");
        },
        getRelatedType: function () {
            var e = this.get("ftype");
            return "multi_select" == e && (e = "select"), e;
        },
        getRelatedPrototype: function () {
            return acf.getFieldType(this.getRelatedType()).prototype;
        },
        initialize: function () {
            this.getRelatedPrototype().initialize.apply(this, arguments);
        },
        onClickRadio: function (e, t) {
            var a = t.parent("label"),
                i = a.hasClass("selected");
            this.$(".selected").removeClass("selected"),
                a.addClass("selected"),
                this.get("allow_null") && i && (a.removeClass("selected"), t.prop("checked", !1).trigger("change")),
                "variable" == this.$input().val() ? this.$forVariable(t).removeClass("acf-hidden") : this.$forVariable(t).addClass("acf-hidden");
        },
        onChooseOption: function (e, t) {
            "variable" == this.$input().val() ? this.$forVariable(t).removeClass("acf-hidden") : this.$forVariable(t).addClass("acf-hidden");
        },
    });
    acf.registerFieldType(a), acf.registerConditionForFieldType("equalTo", "product_types"), acf.registerConditionForFieldType("notEqualTo", "product_types");
})(jQuery),
    new acf.Model({
        events: { "input .pa-custom-name input": "onInputCustomName" },
        onInputCustomName: function (e, t) {
            t.closest("div.frontend-block").find(".attr_name").text(t.val());
        },
    }),
    (function (e) {
        var t = acf.Field.extend({
            type: "product_attributes",
            wait: "",
            events: {
                'click [data-name="add-block"]': "onClickAdd",
                'click [data-name="save-changes"]': "onClickSave",
                'click [data-name="duplicate-block"]': "onClickDuplicate",
                'click [data-name="remove-block"]': "onClickRemove",
                'click [data-name="collapse-block"]': "onClickCollapse",
                showField: "onShow",
                unloadField: "onUnload",
                mouseover: "onHover",
            },
            $control: function () {
                return this.$(".acf-frontend-blocks:first");
            },
            $blocksWrap: function () {
                return this.$(".acf-frontend-blocks:first > .values");
            },
            $blocks: function () {
                return this.$(".acf-frontend-blocks:first > .values > .frontend-block");
            },
            $block: function (e) {
                return this.$(".acf-frontend-blocks:first > .values > .frontend-block:eq(" + e + ")");
            },
            $clonesWrap: function () {
                return this.$(".acf-frontend-blocks:first > .clones");
            },
            $clones: function () {
                return this.$(".acf-frontend-blocks:first > .clones  > .frontend-block");
            },
            $clone: function (e) {
                return this.$('.acf-frontend-blocks:first > .clones  > .frontend-block[data-block="' + e + '"]');
            },
            $actions: function () {
                return this.$(".acf-actions:last");
            },
            $button: function () {
                return this.$(".acf-actions:last a.add-attrs");
            },
            $saveButton: function () {
                return this.$(".acf-actions:last a.save-changes");
            },
            $forVariations: function () {
                return this.$('div[data-name="locations"]').find("li:last");
            },
            $productTypeField: function () {
                return this.$el.parents("form").find(".acf-field-product-types");
            },
            $productType: function () {
                return this.$productTypeField().find("select").val() ? this.$productTypeField().find("select").val() : this.$productTypeField().find("input:checked").val();
            },
            $popup: function () {
                return this.$(".tmpl-popup:last");
            },
            getPopupHTML: function () {
                var t = this.$popup().html();
                t = e(t);
                var a = this.$blocks();
                return (
                    t.find("[data-block]").each(function () {
                        var t = e(this),
                            i = t.data("min") || 0,
                            n = t.data("max") || 0,
                            o = t.data("block") || "",
                            s = (function (t) {
                                return a.filter(function () {
                                    return e(this).data("block") === t;
                                }).length;
                            })(o);
                        if (n && s >= n) t.addClass("disabled");
                        else if (i && s < i) {
                            (n = i - s), (s = acf.__("{required} {label} {identifier} required (min {min})"));
                            var r = acf._n("block", "blocks", n);
                            (s = (s = (s = (s = s.replace("{required}", n)).replace("{label}", o)).replace("{identifier}", r)).replace("{min}", i)), t.append('<span class="badge" title="' + s + '">' + n + "</span>");
                        }
                    }),
                    (t = t.outerHTML())
                );
            },
            getValue: function () {
                return this.$blocks().length;
            },
            allowRemove: function () {
                var e = parseInt(this.get("min"));
                return !e || e < this.val();
            },
            allowAdd: function () {
                var e = parseInt(this.get("max"));
                return !e || e > this.val();
            },
            isFull: function () {
                var e = parseInt(this.get("max"));
                return e && this.val() >= e;
            },
            addSortable: function (e) {
                1 != this.get("max") &&
                    this.$blocksWrap().sortable({
                        items: "> .frontend-block",
                        handle: "> .acf-frontend-blocks-block-handle",
                        forceHelperSize: !0,
                        forcePlaceholderSize: !0,
                        scroll: !0,
                        stop: function (t, a) {
                            e.render();
                        },
                        update: function (t, a) {
                            e.$input().trigger("change");
                        },
                    });
            },
            addCollapsed: function () {
                var t = i.load(this.get("key"));
                if (!t) return !1;
                this.$blocks().each(function (a) {
                    -1 < t.indexOf(a) && e(this).addClass("-collapsed");
                });
            },
            addUnscopedEvents: function (t) {
                this.on("invalidField", ".frontend-block", function (a) {
                    t.onInvalidField(a, e(this));
                });
            },
            initialize: function () {
                this.addUnscopedEvents(this), this.addCollapsed(), acf.disable(this.$clonesWrap(), this.cid), this.render();
            },
            render: function () {
                this.$blocks().each(function (t) {
                    e(this)
                        .find(".acf-frontend-blocks-block-order:first")
                        .html(t + 1);
                }),
                    0 == this.val() ? this.$control().addClass("-empty") : this.$control().removeClass("-empty"),
                    this.isFull() ? this.$button().addClass("disabled") : this.$button().removeClass("disabled"),
                    "variable" != this.$productType() && this.$forVariations().addClass("acf-hidden");
            },
            onShow: function (e, t, a) {
                (e = acf.getFields({ is: ":visible", parent: this.$el })), acf.doAction("show_fields", e);
            },
            validateAdd: function () {
                if (this.allowAdd()) return !0;
                var e = this.get("max"),
                    t = acf.__("This field has a limit of {max} {label} {identifier}"),
                    a = acf._n("block", "blocks", e);
                return (t = (t = (t = t.replace("{max}", e)).replace("{label}", "")).replace("{identifier}", a)), this.showNotice({ text: t, type: "warning" }), !1;
            },
            onClickAdd: function (e, t) {
                if (!this.validateAdd()) return !1;
                var i = null;
                t.hasClass("acf-icon") && (i = t.closest(".frontend-block")).addClass("-hover"),
                    new a({
                        target: t,
                        targetConfirm: !1,
                        text: this.getPopupHTML(),
                        context: this,
                        confirm: function (e, t) {
                            t.hasClass("disabled") || this.add({ block: t.data("block"), before: i }), i && i.removeClass("-hover");
                        },
                        cancel: function () {
                            i && i.removeClass("-hover");
                        },
                    }).on("click", "[data-block]", "onConfirm");
            },
            add: function (e) {
                if (((e = acf.parseArgs(e, { block: "", before: !1 })), !this.allowAdd())) return !1;
                var t = acf.duplicate({
                    target: this.$clone(e.block),
                    append: this.proxy(function (t, a) {
                        e.before ? e.before.before(a) : this.$blocksWrap().append(a), acf.enable(a, this.cid), this.render();
                    }),
                });
                return this.$input().trigger("change"), t;
            },
            onClickSave: function (t, a) {
                var i = this;
                i.$saveButton().addClass("disabled").after('<span class="fea-loader"></span>');
                var n = a.parents("form"),
                    o = n.find(".acf-field-product-variations").first(),
                    s = n.find(".acf-field-product-types").first(),
                    r = new FormData(n[0]);
                r.append("action", "frontend_admin/fields/attributes/save_attributes"),
                    r.append("attributes", this.$el.data("key")),
                    r.append("variations", o.data("key")),
                    r.append("product_types", s.data("key")),
                    e.ajax({
                        url: acf.get("ajaxurl"),
                        data: r,
                        type: "post",
                        cache: !1,
                        processData: !1,
                        contentType: !1,
                        success: function (e) {
                            e.success &&
                                e.data.variations &&
                                (i.$saveButton().removeClass("disabled").siblings(".fea-loader").remove(),
                                n.find(".acf-field-product-variations").replaceWith(e.data.variations),
                                acf.doAction("append", n),
                                n.find("input[name=_acf_objects]").val(e.data.form_objects));
                        },
                    });
            },
            onClickDuplicate: function (e, t) {
                if (!this.validateAdd()) return !1;
                var a = t.closest(".frontend-block");
                this.duplicateBlock(a);
            },
            duplicateBlock: function (e) {
                if (!this.allowAdd()) return !1;
                var t = this.get("key");
                return (
                    (e = acf.duplicate({
                        target: e,
                        rename: function (e, a, i, n) {
                            return "id" === e ? a.replace(t + "-" + i, t + "-" + n) : a.replace(t + "][" + i, t + "][" + n);
                        },
                        before: function (e) {
                            acf.doAction("unmount", e);
                        },
                        after: function (e, t) {
                            acf.doAction("remount", e);
                        },
                    })),
                    this.$input().trigger("change"),
                    this.render(),
                    acf.focusAttention(e),
                    e
                );
            },
            validateRemove: function () {
                if (this.allowRemove()) return !0;
                var e = this.get("min"),
                    t = acf.__("This field requires at least {min} {label} {identifier}"),
                    a = acf._n("block", "blocks", e);
                return (t = (t = (t = t.replace("{min}", e)).replace("{label}", "")).replace("{identifier}", a)), this.showNotice({ text: t, type: "warning" }), !1;
            },
            onClickRemove: function (e, t) {
                var a = t.closest(".frontend-block");
                if (e.shiftKey) return this.removeBlock(a);
                a.addClass("-hover"),
                    acf.newTooltip({
                        confirmRemove: !0,
                        target: t,
                        context: this,
                        confirm: function () {
                            this.removeBlock(a);
                        },
                        cancel: function () {
                            a.removeClass("-hover");
                        },
                    });
            },
            removeBlock: function (e) {
                var t = this,
                    a = 1 == this.getValue() ? 60 : 0;
                acf.remove({
                    target: e,
                    endHeight: a,
                    complete: function () {
                        t.$input().trigger("change"), t.render();
                    },
                });
            },
            onInputCustomName: function (e, t) {
                t.closest("div.frontend-block").find(".attr_name").text(t.val());
            },
            onClickCollapse: function (e, t) {
                var a = t.closest(".frontend-block");
                this.isBlockClosed(a) ? this.openBlock(a) : this.closeBlock(a);
            },
            isBlockClosed: function (e) {
                return e.hasClass("-collapsed");
            },
            openBlock: function (e) {
                e.removeClass("-collapsed"), acf.doAction("show", e, "collapse");
            },
            closeBlock: function (e) {
                e.addClass("-collapsed"), acf.doAction("hide", e, "collapse");
            },
            onUnload: function () {
                var t = [];
                this.$blocks().each(function (a) {
                    e(this).hasClass("-collapsed") && t.push(a);
                }),
                    (t = t.length ? t : null),
                    i.save(this.get("key"), t);
            },
            onInvalidField: function (e, t) {
                this.isBlockClosed(t) && this.openBlock(t);
            },
            onHover: function () {
                this.addSortable(this), this.off("mouseover");
            },
        });
        acf.registerFieldType(t);
        var a = acf.models.TooltipConfirm.extend({
            events: { "click [data-block]": "onConfirm", 'click [data-event="cancel"]': "onCancel" },
            render: function () {
                this.html(this.get("text")), this.$el.addClass("acf-frontend-blocks-popup");
            },
        });
        acf.registerConditionForFieldType("hasValue", "product_attributes"),
            acf.registerConditionForFieldType("hasNoValue", "product_attributes"),
            acf.registerConditionForFieldType("lessThan", "product_attributes"),
            acf.registerConditionForFieldType("greaterThan", "product_attributes");
        var i = new acf.Model({
                name: "this.collapsedBlocks",
                key: function (e, t) {
                    var a = this.get(e + t) || 0;
                    return a++, this.set(e + t, a, !0), 1 < a && (e += "-" + a), e;
                },
                load: function (e) {
                    e = this.key(e, "load");
                    var t = acf.getPreference(this.name);
                    return !(!t || !t[e]) && t[e];
                },
                save: function (t, a) {
                    t = this.key(t, "save");
                    var i = acf.getPreference(this.name) || {};
                    null === a ? delete i[t] : (i[t] = a), e.isEmptyObject(i) && (i = null), acf.setPreference(this.name, i);
                },
            }),
            n = new acf.Model({
                name: "this.collapsedBlocks",
                key: function (e, t) {
                    var a = this.get(e + t) || 0;
                    return a++, this.set(e + t, a, !0), a > 1 && (e += "-" + a), e;
                },
                load: function (e) {
                    e = this.key(e, "load");
                    var t = acf.getPreference(this.name);
                    return !(!t || !t[e]) && t[e];
                },
                save: function (t, a) {
                    t = this.key(t, "save");
                    var i = acf.getPreference(this.name) || {};
                    null === a ? delete i[t] : (i[t] = a), e.isEmptyObject(i) && (i = null), acf.setPreference(this.name, i);
                },
            }),
            o = acf.Field.extend({
                type: "frontend_blocks",
                wait: "",
                events: {
                    'click [data-name="add-block"]': "onClickAdd",
                    'click [data-name="duplicate-block"]': "onClickDuplicate",
                    'click [data-name="remove-block"]': "onClickRemove",
                    'click [data-name="collapse-block"]': "onClickCollapse",
                    showField: "onShow",
                    unloadField: "onUnload",
                    mouseover: "onHover",
                },
                $control: function () {
                    return this.$(".acf-frontend-blocks:first");
                },
                $blocksWrap: function () {
                    return this.$(".acf-frontend-blocks:first > .values");
                },
                $blocks: function () {
                    return this.$(".acf-frontend-blocks:first > .values > .frontend-block");
                },
                $block: function (e) {
                    return this.$(".acf-frontend-blocks:first > .values > .frontend-block:eq(" + e + ")");
                },
                $clonesWrap: function () {
                    return this.$(".acf-frontend-blocks:first > .clones");
                },
                $clones: function () {
                    return this.$(".acf-frontend-blocks:first > .clones  > .frontend-block");
                },
                $clone: function (e) {
                    return this.$('.acf-frontend-blocks:first > .clones  > .frontend-block[data-block="' + e + '"]');
                },
                $actions: function () {
                    return this.$(".acf-actions:last");
                },
                $button: function () {
                    return this.$(".acf-actions:last .button");
                },
                $popup: function () {
                    return this.$(".tmpl-popup:last");
                },
                getPopupHTML: function () {
                    var t = this.$popup().html(),
                        a = e(t),
                        i = this.$blocks();
                    return (
                        a.find("[data-block]").each(function () {
                            var t = e(this),
                                a = t.data("min") || 0,
                                n = t.data("max") || 0,
                                o = t.data("block") || "",
                                s = (function (t) {
                                    return i.filter(function () {
                                        return e(this).data("block") === t;
                                    }).length;
                                })(o);
                            if (n && s >= n) t.addClass("disabled");
                            else if (a && s < a) {
                                var r = a - s,
                                    l = acf.__("{required} {label} {identifier} required (min {min})"),
                                    c = acf._n("block", "blocks", r);
                                (l = (l = (l = (l = l.replace("{required}", r)).replace("{label}", o)).replace("{identifier}", c)).replace("{min}", a)), t.append('<span class="badge" title="' + l + '">' + r + "</span>");
                            }
                        }),
                        (t = a.outerHTML())
                    );
                },
                getValue: function () {
                    return this.$blocks().length;
                },
                allowRemove: function () {
                    var e = parseInt(this.get("min"));
                    return !e || e < this.val();
                },
                allowAdd: function () {
                    var e = parseInt(this.get("max"));
                    return !e || e > this.val();
                },
                isFull: function () {
                    var e = parseInt(this.get("max"));
                    return e && this.val() >= e;
                },
                addSortable: function (e) {
                    1 != this.get("max") &&
                        this.$blocksWrap().sortable({
                            items: "> .frontend-block",
                            handle: "> .acf-frontend-blocks-block-handle",
                            forceHelperSize: !0,
                            forcePlaceholderSize: !0,
                            scroll: !0,
                            stop: function (t, a) {
                                e.render();
                            },
                            update: function (t, a) {
                                e.$input().trigger("change");
                            },
                        });
                },
                addCollapsed: function () {
                    var t = n.load(this.get("key"));
                    if (!t) return !1;
                    this.$blocks().each(function (a) {
                        t.indexOf(a) > -1 && e(this).addClass("-collapsed");
                    });
                },
                addUnscopedEvents: function (t) {
                    this.on("invalidField", ".frontend-block", function (a) {
                        t.onInvalidField(a, e(this));
                    });
                },
                initialize: function () {
                    this.addUnscopedEvents(this), this.addCollapsed(), acf.disable(this.$clonesWrap(), this.cid), this.render();
                },
                render: function () {
                    this.$blocks().each(function (t) {
                        e(this)
                            .find(".acf-frontend-blocks-block-order:first")
                            .html(t + 1);
                    }),
                        0 == this.val() ? this.$control().addClass("-empty") : this.$control().removeClass("-empty"),
                        this.isFull() ? this.$button().addClass("disabled") : this.$button().removeClass("disabled");
                },
                onShow: function (e, t, a) {
                    var i = acf.getFields({ is: ":visible", parent: this.$el });
                    acf.doAction("show_fields", i);
                },
                validateAdd: function () {
                    if (this.allowAdd()) return !0;
                    var e = this.get("max"),
                        t = acf.__("This field has a limit of {max} {label} {identifier}"),
                        a = acf._n("block", "blocks", e);
                    return (t = (t = (t = t.replace("{max}", e)).replace("{label}", "")).replace("{identifier}", a)), this.showNotice({ text: t, type: "warning" }), !1;
                },
                onClickAdd: function (e, t) {
                    if (!this.validateAdd()) return !1;
                    var a = null;
                    t.hasClass("acf-icon") && (a = t.closest(".frontend-block")).addClass("-hover");
                    var i = new s({
                        target: t,
                        targetConfirm: !1,
                        text: this.getPopupHTML(),
                        context: this,
                        confirm: function (e, t) {
                            t.hasClass("disabled") || this.add({ block: t.data("block"), before: a });
                        },
                        cancel: function () {
                            a && a.removeClass("-hover");
                        },
                    });
                    i.on("click", "[data-block]", "onConfirm");
                },
                add: function (t) {
                    if (((t = acf.parseArgs(t, { block: "", before: !1 })), !this.allowAdd())) return !1;
                    var a = acf.duplicate({
                        target: this.$clone(t.block),
                        append: this.proxy(function (e, a) {
                            t.before ? t.before.before(a) : this.$blocksWrap().append(a), acf.enable(a, this.cid), this.render();
                        }),
                    });
                    return this.$input().trigger("change"), e("html, body").animate({ scrollTop: e(a).closest(".frontend-block").offset().top - 75 }), a;
                },
                onClickDuplicate: function (e, t) {
                    if (!this.validateAdd()) return !1;
                    var a = t.closest(".frontend-block");
                    this.duplicateBlock(a);
                },
                duplicateBlock: function (e) {
                    if (!this.allowAdd()) return !1;
                    var t = this.get("key"),
                        a = acf.duplicate({
                            target: e,
                            rename: function (e, a, i, n) {
                                return "data-id" === e || "for" === e ? a.replace(t + "-" + i, t + "-" + n) : a.replace(t + "][" + i, t + "][" + n);
                            },
                            before: function (e) {
                                acf.doAction("unmount", e);
                            },
                            after: function (e, t) {
                                acf.doAction("remount", e);
                            },
                        });
                    return this.$input().trigger("change"), this.render(), acf.focusAttention(a), a;
                },
                validateRemove: function () {
                    if (this.allowRemove()) return !0;
                    var e = this.get("min"),
                        t = acf.__("This field requires at least {min} {label} {identifier}"),
                        a = acf._n("block", "blocks", e);
                    return (t = (t = (t = t.replace("{min}", e)).replace("{label}", "")).replace("{identifier}", a)), this.showNotice({ text: t, type: "warning" }), !1;
                },
                onClickRemove: function (e, t) {
                    var a = t.closest(".frontend-block");
                    if (e.shiftKey) return this.removeBlock(a);
                    a.addClass("-hover");
                    acf.newTooltip({
                        confirmRemove: !0,
                        target: t,
                        context: this,
                        confirm: function () {
                            this.removeBlock(a);
                        },
                        cancel: function () {
                            a.removeClass("-hover");
                        },
                    });
                },
                removeBlock: function (e) {
                    var t = this,
                        a = 1 == this.getValue() ? 60 : 0;
                    acf.remove({
                        target: e,
                        endHeight: a,
                        complete: function () {
                            t.$input().trigger("change"), t.render();
                        },
                    });
                },
                onClickCollapse: function (e, t) {
                    var a = t.closest(".frontend-block");
                    this.isBlockClosed(a) ? this.openBlock(a) : this.closeBlock(a);
                },
                isBlockClosed: function (e) {
                    return e.hasClass("-collapsed");
                },
                openBlock: function (e) {
                    e.removeClass("-collapsed"), acf.doAction("show", e, "collapse");
                },
                closeBlock: function (e) {
                    e.addClass("-collapsed"), acf.doAction("hide", e, "collapse"), this.renderBlock(e);
                },
                renderBlock: function (t) {
                    var a = t.children("input").attr("name").replace("[fea_block_structure]", ""),
                        i = { action: "acf/fields/frontend_blocks/block_title", field_key: this.get("key"), i: t.index(), block: t.data("block"), value: acf.serialize(t, a) };
                    e.ajax({
                        url: acf.get("ajaxurl"),
                        data: acf.prepareForAjax(i),
                        dataType: "html",
                        type: "post",
                        success: function (e) {
                            e && t.children(".acf-frontend-blocks-block-handle").html(e);
                        },
                    });
                },
                onUnload: function () {
                    var t = [];
                    this.$blocks().each(function (a) {
                        e(this).hasClass("-collapsed") && t.push(a);
                    }),
                        (t = t.length ? t : null),
                        n.save(this.get("key"), t);
                },
                onInvalidField: function (e, t) {
                    this.isBlockClosed(t) && this.openBlock(t);
                },
                onHover: function () {
                    this.addSortable(this), this.off("mouseover");
                },
            });
        acf.registerFieldType(o);
        var s = acf.models.TooltipConfirm.extend({
            events: { "click [data-block]": "onConfirm", 'click [data-event="cancel"]': "onCancel" },
            render: function () {
                this.html(this.get("text")), this.$el.addClass("acf-frontend-blocks-popup");
            },
        });
        acf.registerConditionForFieldType("hasValue", "frontend_blocks"),
            acf.registerConditionForFieldType("hasNoValue", "frontend_blocks"),
            acf.registerConditionForFieldType("lessThan", "frontend_blocks"),
            acf.registerConditionForFieldType("greaterThan", "frontend_blocks");
        o = acf.Field.extend({
            type: "product_variations",
            wait: "",
            events: {
                'click a[data-event="add-row"]': "onClickAdd",
                'click [data-name="save-changes"]': "onClickSave",
                'click a[data-event="remove-row"]': "onClickRemove",
                "click .acf-row-handle.order": "onClickCollapse",
                showField: "onShow",
                unloadField: "onUnload",
                mouseover: "onHover",
            },
            $control: function () {
                return this.$(".acf-list-item:first");
            },
            $table: function () {
                return this.$("table:first");
            },
            $tbody: function () {
                return this.$("tbody:first");
            },
            $rows: function () {
                return this.$("tbody:first > tr").not(".acf-clone");
            },
            $row: function (e) {
                return this.$("tbody:first > tr:eq(" + e + ")");
            },
            $clone: function () {
                return this.$("tbody:first > tr.acf-clone");
            },
            $actions: function () {
                return this.$(".acf-actions:last");
            },
            $button: function () {
                return this.$(".acf-actions:last .add-variation");
            },
            $saveButton: function () {
                return this.$(".acf-actions:last .save-changes");
            },
            getValue: function () {
                return this.$rows().length;
            },
            allowRemove: function () {
                var e = parseInt(this.get("min"));
                return !e || e < this.val();
            },
            allowAdd: function () {
                var e = parseInt(this.get("max"));
                return !e || e > this.val();
            },
            addSortable: function (e) {
                1 != this.get("max") &&
                    this.$tbody().sortable({
                        items: "> tr",
                        handle: "> td.order",
                        forceHelperSize: !0,
                        forcePlaceholderSize: !0,
                        scroll: !0,
                        stop: function (t, a) {
                            e.render();
                        },
                        update: function (t, a) {
                            e.$input().trigger("change");
                        },
                    });
            },
            addCollapsed: function () {
                var t = n.load(this.get("key"));
                if (!t) return !1;
                this.$rows().each(function (a) {
                    t.indexOf(a) > -1 && e(this).addClass("-collapsed");
                });
            },
            addUnscopedEvents: function (t) {
                this.on("invalidField", ".acf-row", function (a) {
                    var i = e(this);
                    t.isCollapsed(i) && t.expand(i);
                });
            },
            initialize: function () {
                this.addUnscopedEvents(this), this.addCollapsed(), acf.disable(this.$clone(), this.cid), this.render();
            },
            render: function () {
                0 == this.val() ? this.$control().addClass("-empty") : this.$control().removeClass("-empty"), this.allowAdd() ? this.$button().removeClass("disabled") : this.$button().addClass("disabled");
            },
            validateAdd: function () {
                if (this.allowAdd()) return !0;
                var e = this.get("max"),
                    t = acf.__("Maximum rows reached ({max} rows)");
                return (t = t.replace("{max}", e)), this.showNotice({ text: t, type: "warning" }), !1;
            },
            onClickAdd: function (t, a) {
                if (!this.validateAdd()) return !1;
                this.$button().after('<span class="fea-loader"></span>');
                var i = this,
                    n = a.parents("form"),
                    o = { 
                        action: "frontend_admin/fields/variations/add_variation", 
                        field_key: a.data("key"), 
                        _acf_objects: n.find("input[name=_acf_objects]").val() 
                    };
                e.ajax({
                    url: acf.get("ajaxurl"),
                    data: acf.prepareForAjax(o),
                    type: "post",
                    dataType: "json",
                    cache: !1,
                    success: function (e) {
                        e.data.variation_id && (a.hasClass("acf-icon") ? i.add({ before: a.closest(".acf-row"), variationID: e.data.variation_id }) : i.add({ variationID: e.data.variation_id }));
                    },
                });
            },
            add: function (e) {
                if (!this.allowAdd()) return !1;
                e = acf.parseArgs(e, { before: !1 });
                var t = acf.duplicate({
                    target: this.$clone(),
                    append: this.proxy(function (t, a) {
                        e.before ? e.before.before(a) : t.before(a),
                            a.removeClass("acf-clone -collapsed"),
                            a.find(".variation-id").html("#" + e.variationID),
                            a.find(".acf-icon.-minus").attr("data-variation_id", e.variationID),
                            a.find(".row-variation-id").val(e.variationID),
                            acf.enable(a, this.cid),
                            this.render(),
                            this.$button().siblings(".fea-loader").remove();
                    }),
                });
                return this.$input().trigger("change"), t;
            },
            onClickSave: function (t, a) {
                var i = this;
                i.$saveButton().addClass("disabled").after('<span class="fea-loader"></span>');
                var n = a.parents("form"),
                    o = new FormData(n[0]);
                o.append("action", "frontend_admin/fields/variations/save_variations"),
                    o.append("field_key", i.get("key")),
                    e.ajax({
                        url: acf.get("ajaxurl"),
                        data: o,
                        type: "post",
                        cache: !1,
                        processData: !1,
                        contentType: !1,
                        success: function (e) {
                            e.data.product_id ? i.$saveButton().removeClass("disabled").siblings(".fea-loader").remove() : i.showNotice({ text: e.data, type: "warning" });
                        },
                    });
            },
            validateRemove: function () {
                if (this.allowRemove()) return !0;
                var e = this.get("min"),
                    t = acf.__("Minimum rows reached ({min} rows)");
                return (t = t.replace("{min}", e)), this.showNotice({ text: t, type: "warning" }), !1;
            },
            onClickRemove: function (e, t) {
                var a = t.closest(".acf-row");
                a.addClass("-hover");
                acf.newTooltip({
                    confirmRemove: !0,
                    target: t,
                    context: this,
                    confirm: function () {
                        this.remove(a, t);
                    },
                    cancel: function () {
                        a.removeClass("-hover");
                    },
                });
            },
            remove: function (t, a) {
                var i = this,
                    n = (a.parents("form"), { action: "frontend_admin/fields/variations/remove_variation", field_key: a.data("key"), variation_id: a.data("variation_id") });
                e.ajax({
                    url: acf.get("ajaxurl"),
                    data: acf.prepareForAjax(n),
                    type: "post",
                    dataType: "json",
                    cache: !1,
                    success: function (e) {
                        acf.remove({
                            target: t,
                            endHeight: 0,
                            complete: function () {
                                i.$input().trigger("change"), i.render();
                            },
                        });
                    },
                });
            },
            isCollapsed: function (e) {
                return e.hasClass("-collapsed");
            },
            collapse: function (e) {
                e.addClass("-collapsed"), acf.doAction("hide", e, "collapse");
            },
            expand: function (e) {
                e.removeClass("-collapsed"), acf.doAction("show", e, "collapse");
            },
            onClickCollapse: function (e, t) {
                var a = t.closest(".acf-row"),
                    i = this.isCollapsed(a);
                e.shiftKey && (a = this.$rows()), i ? this.expand(a) : this.collapse(a);
            },
            onShow: function (e, t, a) {
                var i = acf.getFields({ is: ":visible", parent: this.$el });
                acf.doAction("show_fields", i);
            },
            onUnload: function () {
                var t = [];
                this.$rows().each(function (a) {
                    e(this).hasClass("-collapsed") && t.push(a);
                }),
                    (t = t.length ? t : null),
                    n.save(this.get("key"), t);
            },
            onHover: function () {
                this.addSortable(this), this.off("mouseover");
            },
        });
        acf.registerFieldType(o),
            acf.registerConditionForFieldType("hasValue", "product_variations"),
            acf.registerConditionForFieldType("hasNoValue", "product_variations"),
            acf.registerConditionForFieldType("lessThan", "product_variations"),
            acf.registerConditionForFieldType("greaterThan", "product_variations");
        o = acf.models.ListItemsField.extend({ type: "downloadable_files" });
        acf.registerFieldType(o),
            acf.registerConditionForFieldType("hasValue", "downloadable_files"),
            acf.registerConditionForFieldType("hasNoValue", "downloadable_files"),
            acf.registerConditionForFieldType("lessThan", "downloadable_files"),
            acf.registerConditionForFieldType("greaterThan", "downloadable_files");
        o = acf.models.UploadFilesField.extend({ type: "product_images" });
        acf.registerFieldType(o),
            acf.registerConditionForFieldType("hasValue", "product_images"),
            acf.registerConditionForFieldType("hasNoValue", "product_images"),
            acf.registerConditionForFieldType("selectionLessThan", "product_images"),
            acf.registerConditionForFieldType("selectionGreaterThan", "product_images");
        e.each(["manage_stock", "sold_individually", "is_virtual", "is_downloadable", "product_enable_reviews"], function (e, t) {
            var a = acf.models.TrueFalseField.extend({ type: t });
            acf.registerFieldType(a), acf.registerConditionForFieldType("equalTo", t), acf.registerConditionForFieldType("notEqualTo", t);
        });
        o = acf.Field.extend({
            type: "form_step",
            wait: "",
            events: { "click .change-step": "onClickChangeStep" },
            $control: function () {
                return this.$(".frontend-admin-steps");
            },
            $currentStep: function () {
                var e = this.$control().data("current-step");
                return e || (e = 1), e;
            },
            $validateSteps: function () {
                return this.$control().data("validate-steps");
            },
            onClickChangeStep: function (e, t) {
                var a = t.data("step"),
                    i = t.data("button"),
                    n = this.$currentStep();
                if (a == n) return !1;
                if ("submit" == a || (this.$validateSteps() && "next" == i)) {
                    this.$("input.step-input").val(1), this.$(".fea-loader").removeClass("acf-hidden"), this.$(".button").addClass("disabled");
                    var o = this,
                        s = !1;
                    "submit" != a && (s = o.$(".acf-fields[data-step=" + n + "]")),
                        (args = {
                            form: o.$el.parents("form"),
                            reset: !1,
                            limit: s,
                            complete: function (e, t) {
                                if (t.hasErrors()) {
                                    var i = t.data.errors[0],
                                        s = e
                                            .find('input[name="' + i.input + '"]')
                                            .closest(".acf-field")
                                            .closest(".acf-fields")
                                            .data("step");
                                    return s < n && o.changeStep(s, n), o.$el.find(".fea-loader").addClass("acf-hidden"), o.$el.find(".disabled").removeClass("disabled"), void t.reset();
                                }
                                "submit" == a ? acf.submitFrontendForm(e, !1) : (o.changeStep(a, n), t.reset());
                            },
                        }),
                        acf.validateFrontendForm(args);
                } else this.changeStep(a, n);
            },
            changeStep: function (t, a) {
                this.$("input.step-input").val(t),
                    this.$(".form-tab[data-step=" + t + "]").addClass("active"),
                    this.$(".form-tab[data-step=" + a + "]").removeClass("active"),
                    this.$control().data("current-step", t),
                    this.$(".current-step").text(t),
                    this.$(".acf-fields[data-step=" + a + "]").addClass("frontend-admin-hidden"),
                    this.$(".acf-fields[data-step=" + t + "]").removeClass("frontend-admin-hidden"),
                    e("body, html").animate({ scrollTop: this.$control().offset().top - 100 }, "slow"),
                    this.$(".fea-loader").addClass("acf-hidden"),
                    this.$(".disabled").removeClass("disabled");
            },
        });
        acf.registerFieldType(o);

        var Field = acf.models.SelectField.extend(
            {
                type: 'cities'             
            }
        );
        acf.registerFieldType(Field);
        var Field = acf.models.SelectField.extend(
            {
                type: 'countries',
                events: {
                    'change select': 'onChange'
                },
                onChange: function( e, $select ) {
                    var $citiesSelect = this.$el.siblings( '.acf-field[data-type=cities]' ).find( 'select' );
                    if ( $citiesSelect.length ) {
                        $citiesSelect.empty().trigger( 'change' );
                    }

                    //if this has value enable cities select
                    if ( $select.val() ) {
                        $citiesSelect.prop( 'disabled', false );
                    } else {
                        $citiesSelect.prop( 'disabled', true );
                    }
                }                 
            }
        );
        acf.registerFieldType(Field);


        acf.addFilter(
            'select2_ajax_data/type=cities',
            function (data, args, $input, field, select2) {
    
                if ( ! field) {
                    return data;
                }
    
                var $el = field.$el;
    
                var $countriesSelect  = $el.siblings( '.acf-field[data-type=countries]' ).find( 'select' );
                var $citiesSelect = $el.find( 'select' );
    
                if ( $countriesSelect ) {
                    var $selected = $countriesSelect.select2( 'val' );
                    var countries    = [];
                    if ($selected) {
                        
                       countries.push($selected);
                    }
                    if (countries.length < 1) {
                        $citiesSelect.empty().trigger( 'change' );
                        return data;
                    } else {
                        data.countries = countries;
                    }
                }
                return data;
            }
        );

    })(jQuery);
