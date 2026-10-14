/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ([
/* 0 */,
/* 1 */
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {


var obj;
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "default", ({
    enumerable: !0,
    get: function() {
        return _default;
    }
}));
const _sidebars = (obj = __webpack_require__(2)) && obj.__esModule ? obj : {
    default: obj
}, _sidebarpro = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(40)), _sidebarpro1 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(41)), _sidebarpro2 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(42)), _sidebarpro3 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(43)), _sidebarpro4 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(44)), _sidebarpro5 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(45));
function _getRequireWildcardCache(nodeInterop) {
    if ("function" != typeof WeakMap) return null;
    var cacheBabelInterop = new WeakMap(), cacheNodeInterop = new WeakMap();
    return (_getRequireWildcardCache = function(nodeInterop) {
        return nodeInterop ? cacheNodeInterop : cacheBabelInterop;
    })(nodeInterop);
}
function _interop_require_wildcard(obj, nodeInterop) {
    if (!nodeInterop && obj && obj.__esModule) return obj;
    if (null === obj || "object" != typeof obj && "function" != typeof obj) return {
        default: obj
    };
    var cache = _getRequireWildcardCache(nodeInterop);
    if (cache && cache.has(obj)) return cache.get(obj);
    var newObj = {
        __proto__: null
    }, hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor;
    for(var key in obj)if ("default" !== key && Object.prototype.hasOwnProperty.call(obj, key)) {
        var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null;
        desc && (desc.get || desc.set) ? Object.defineProperty(newObj, key, desc) : newObj[key] = obj[key];
    }
    return newObj.default = obj, cache && cache.set(obj, newObj), newObj;
}
const _default = {
    ..._sidebars.default,
    WPCustomShortcode: _sidebarpro,
    WPSidebar: _sidebarpro1,
    WOOCategories: _sidebarpro2,
    WOOProducts: _sidebarpro3,
    WOOProductPage: _sidebarpro4,
    WOOPages: _sidebarpro5
};


/***/ }),
/* 2 */
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "default", ({
    enumerable: !0,
    get: function() {
        return _default;
    }
}));
const _sidebarpro = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(3)), _sidebarExtendParentpro = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(4)), _sidebarpro1 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(5)), _sidebarpro2 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(6)), _sidebarpro3 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(7)), _sidebarpro4 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(8)), _sidebarpro5 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(9)), _sidebarpro6 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(10)), _sidebarpro7 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(11)), _sidebarpro8 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(12)), _sidebarpro9 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(13)), _sidebarpro10 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(14)), _sidebarpro11 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(15)), _sidebarpro12 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(16)), _sidebarExtendParentpro1 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(17)), _sidebarpro13 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(18)), _sidebarExtendParentpro2 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(19)), _sidebarpro14 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(20)), _sidebarpro15 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(21)), _sidebarpro16 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(22)), _sidebarpro17 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(23)), _sidebarpro18 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(24)), _sidebarpro19 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(25)), _sidebarpro20 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(26)), _sidebarpro21 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(27)), _sidebarpro22 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(28)), _sidebarpro23 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(29)), _sidebarpro24 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(30)), _sidebarpro25 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(31)), _sidebarpro26 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(32)), _sidebarpro27 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(33)), _sidebarpro28 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(34)), _sidebarExtendParentpro3 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(35)), _sidebarpro29 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(36)), _sidebarExtendParentpro4 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(37));
function _getRequireWildcardCache(nodeInterop) {
    if ("function" != typeof WeakMap) return null;
    var cacheBabelInterop = new WeakMap(), cacheNodeInterop = new WeakMap();
    return (_getRequireWildcardCache = function(nodeInterop) {
        return nodeInterop ? cacheNodeInterop : cacheBabelInterop;
    })(nodeInterop);
}
function _interop_require_wildcard(obj, nodeInterop) {
    if (!nodeInterop && obj && obj.__esModule) return obj;
    if (null === obj || "object" != typeof obj && "function" != typeof obj) return {
        default: obj
    };
    var cache = _getRequireWildcardCache(nodeInterop);
    if (cache && cache.has(obj)) return cache.get(obj);
    var newObj = {
        __proto__: null
    }, hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor;
    for(var key in obj)if ("default" !== key && Object.prototype.hasOwnProperty.call(obj, key)) {
        var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null;
        desc && (desc.get || desc.set) ? Object.defineProperty(newObj, key, desc) : newObj[key] = obj[key];
    }
    return newObj.default = obj, cache && cache.set(obj, newObj), newObj;
}
const _default = {
    AccordionParent: _sidebarExtendParentpro,
    Button: _sidebarpro3,
    Column: _sidebarpro6,
    Countdown: _sidebarpro8,
    Countdown2: _sidebarpro7,
    Counter: _sidebarpro9,
    EmbedCode: _sidebarpro12,
    FormParent: _sidebarExtendParentpro1,
    Icon: _sidebarpro13,
    IconTextParent: _sidebarExtendParentpro2,
    Image: _sidebarpro14,
    Line: _sidebarpro15,
    Map: _sidebarpro16,
    ProgressBar: _sidebarpro18,
    RichText: _sidebarpro19,
    Row: _sidebarpro20,
    SectionFooter: _sidebarpro22,
    SectionHeaderItem: _sidebarpro23,
    SectionHeaderStickyItem: _sidebarpro24,
    SectionItem: _sidebarpro21,
    StoryItem: _sidebarpro28,
    SectionPopup: _sidebarpro26,
    SectionPopup2: _sidebarpro25,
    SoundCloud: _sidebarpro27,
    TabsParent: _sidebarExtendParentpro4,
    Video: /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(38)),
    Audio: _sidebarpro2,
    TableParent: _sidebarExtendParentpro3,
    Alert: _sidebarpro1,
    Calendly: _sidebarpro4,
    TableOfContents: _sidebarpro29,
    Price: _sidebarpro,
    Paypal: _sidebarpro17,
    EcwidPrice: _sidebarpro11,
    EcwidAddToCart: _sidebarpro10,
    Wrapper: /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(39)),
    Cloneable: _sidebarpro5
};


/***/ }),
/* 3 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 4 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 5 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 6 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 7 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 8 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 9 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ({ device })=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    id: "effects",
                    options: [
                        {
                            id: "tabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "scroll",
                                    label: t("Scroll"),
                                    options: [
                                        {
                                            id: "motion",
                                            type: "motion",
                                            position: 10,
                                            config: {
                                                disabled: "desktop" === device ? void 0 : [
                                                    "mouseTrack",
                                                    "mouseTilt"
                                                ]
                                            }
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 10 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems({ device }) {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    options: [
                        {
                            id: "tabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "settingsStyling",
                                    label: t("Styling"),
                                    icon: "nc-styling",
                                    options: []
                                },
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    id: "effects",
                    options: [
                        {
                            id: "tabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "scroll",
                                    label: t("Scroll"),
                                    options: [
                                        {
                                            id: "motion",
                                            type: "motion",
                                            position: 10,
                                            config: {
                                                disabled: "desktop" === device ? void 0 : [
                                                    "mouseTrack",
                                                    "mouseTilt"
                                                ]
                                            }
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 11 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 12 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 13 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 14 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 15 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 16 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 17 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 18 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 19 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "settingsStyling",
                                    label: t("Basic"),
                                    icon: "nc-styling"
                                },
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 20 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 21 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 22 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 23 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">${t("element")}</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">${t("element .child-element")}</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: t("element { CSS goes here }")
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 24 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 25 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 26 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems({ device }) {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    id: "effects",
                    options: [
                        {
                            id: "tabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "scroll",
                                    label: t("Scroll"),
                                    options: [
                                        {
                                            id: "motion",
                                            type: "motion",
                                            position: 10,
                                            config: {
                                                disabled: "desktop" === device ? void 0 : [
                                                    "mouseTrack",
                                                    "mouseTilt"
                                                ]
                                            }
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 27 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 28 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 29 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 30 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 31 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems({ context }) {
    let { t, utils: { getDynamicContentOption, DCTypes } } = global.Brizy, richTextDC = getDynamicContentOption({
        options: context.dynamicContent.config,
        type: DCTypes.richText
    });
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        },
                                        {
                                            id: "customAttributes",
                                            label: t("Custom Attributes"),
                                            type: "codeMirror",
                                            position: 50,
                                            placeholder: 'key1:"value1"\nkey2:"value2"',
                                            display: "block",
                                            helper: {
                                                content: t("Set your custom attribute for wrapper element. Each attribute in a separate line. Separate attribute key from the value using : character.")
                                            },
                                            population: richTextDC
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 32 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "customCSS",
                            label: t("Custom CSS"),
                            type: "codeMirror",
                            position: 45,
                            display: "block",
                            devices: "desktop",
                            helper: {
                                content: getHtml()
                            },
                            placeholder: `element { ${t("CSS goes here")} }`
                        },
                        {
                            id: "customAttributes",
                            label: t("Custom Attributes"),
                            type: "codeMirror",
                            position: 50,
                            placeholder: 'key1:"value1"\nkey2:"value2"',
                            display: "block",
                            helper: {
                                content: t("Set your custom attribute for wrapper element. Each attribute in a separate line. Separate attribute key from the value using : character.")
                            }
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 33 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 34 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "settingsTabs",
            type: "tabs",
            tabs: [
                {
                    id: "moreSettingsAdvanced",
                    label: t("Advanced"),
                    icon: "nc-cog",
                    options: [
                        {
                            id: "customCSS",
                            label: t("Custom CSS"),
                            type: "codeMirror",
                            position: 45,
                            display: "block",
                            devices: "desktop",
                            helper: {
                                content: getHtml()
                            },
                            placeholder: `element { ${t("CSS goes here")} }`
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 35 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 36 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 100,
                                            display: "block",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: "element { CSS goes here }"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 37 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 38 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
}, getItems = ()=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 39 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getItems = ({ device })=>{
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "desktop",
                            tabs: [
                                {
                                    id: "moreSettingsTransform",
                                    label: t("Transform"),
                                    icon: "nc-cog",
                                    position: 110,
                                    options: [
                                        {
                                            id: "transform",
                                            type: "transform"
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            id: "settingsTabsResponsive",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            devices: "responsive",
                            tabs: [
                                {
                                    id: "moreSettingsTransform",
                                    label: t("Transform"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "transform",
                                            type: "transform",
                                            position: 20
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    id: "effects",
                    label: t("Effects"),
                    options: [
                        {
                            id: "tabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "scroll",
                                    label: t("Scroll"),
                                    options: [
                                        {
                                            id: "motion",
                                            type: "motion",
                                            position: 10,
                                            config: {
                                                disabled: "desktop" === device ? void 0 : [
                                                    "mouseTrack",
                                                    "mouseTilt"
                                                ]
                                            }
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 40 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 41 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Basic"),
                    label: t("Basic"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            config: {
                                align: "start"
                            },
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    label: t("Advanced"),
                                    icon: "nc-cog",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 42 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            options: [
                {
                    id: "settingsTabs",
                    type: "tabs",
                    config: {
                        align: "start"
                    },
                    tabs: [
                        {
                            id: "moreSettingsAdvanced",
                            label: t("Advanced"),
                            icon: "nc-cog",
                            options: [
                                {
                                    id: "customCSS",
                                    label: t("Custom CSS"),
                                    type: "codeMirror",
                                    position: 45,
                                    display: "block",
                                    devices: "desktop",
                                    helper: {
                                        content: getHtml()
                                    },
                                    placeholder: `element { ${t("CSS goes here")} }`
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 43 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 44 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 45 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getHtml = ()=>{
    let { t } = global.Brizy;
    return `
<p class="brz-p">${t("You can use the following selectors to create targeted CSS.")}</p>
<p class="brz-p">
  <span class="brz-span brz-ed-tooltip__overlay-code">element</span> {...}
  <br class="brz-br">
  <span class="brz-span brz-ed-tooltip__overlay-code">element .child-element</span> {...}
</p>`;
};
function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "sidebarTabs",
            type: "sidebarTabs",
            tabs: [
                {
                    id: "styles",
                    title: t("Styling"),
                    label: t("Styling"),
                    options: [
                        {
                            id: "settingsTabs",
                            type: "tabs",
                            tabs: [
                                {
                                    id: "moreSettingsAdvanced",
                                    options: [
                                        {
                                            id: "customCSS",
                                            label: t("Custom CSS"),
                                            type: "codeMirror",
                                            position: 45,
                                            display: "block",
                                            devices: "desktop",
                                            helper: {
                                                content: getHtml()
                                            },
                                            placeholder: `element { ${t("CSS goes here")} }`
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}


/***/ }),
/* 46 */
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {


var obj;
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "default", ({
    enumerable: !0,
    get: function() {
        return _default;
    }
}));
const _toolbars = (obj = __webpack_require__(47)) && obj.__esModule ? obj : {
    default: obj
}, _toolbarpro = /*#__PURE__*/ function(obj, nodeInterop) {
    if (obj && obj.__esModule) return obj;
    if (null === obj || "object" != typeof obj && "function" != typeof obj) return {
        default: obj
    };
    var cache = _getRequireWildcardCache(nodeInterop);
    if (cache && cache.has(obj)) return cache.get(obj);
    var newObj = {
        __proto__: null
    }, hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor;
    for(var key in obj)if ("default" !== key && Object.prototype.hasOwnProperty.call(obj, key)) {
        var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null;
        desc && (desc.get || desc.set) ? Object.defineProperty(newObj, key, desc) : newObj[key] = obj[key];
    }
    return newObj.default = obj, cache && cache.set(obj, newObj), newObj;
}(__webpack_require__(66));
function _getRequireWildcardCache(nodeInterop) {
    if ("function" != typeof WeakMap) return null;
    var cacheBabelInterop = new WeakMap(), cacheNodeInterop = new WeakMap();
    return (_getRequireWildcardCache = function(nodeInterop) {
        return nodeInterop ? cacheNodeInterop : cacheBabelInterop;
    })(nodeInterop);
}
const _default = {
    ..._toolbars.default,
    WPSidebar: _toolbarpro
};


/***/ }),
/* 47 */
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "default", ({
    enumerable: !0,
    get: function() {
        return _default;
    }
}));
const _toolbarpro = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(48)), _toolbarpro1 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(49)), _toolbarpro2 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(50)), _toolbarpro3 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(51)), _toolbarpro4 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(52)), _toolbarpro5 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(53)), _toolbarpro6 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(54)), _toolbarpro7 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(55)), _toolbarpro8 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(56)), _toolbarpro9 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(57)), _toolbarpro10 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(58)), _toolbarpro11 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(59)), _toolbarpro12 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(60)), _toolbarpro13 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(61)), _toolbarpro14 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(62)), _toolbarpro15 = /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(63));
function _getRequireWildcardCache(nodeInterop) {
    if ("function" != typeof WeakMap) return null;
    var cacheBabelInterop = new WeakMap(), cacheNodeInterop = new WeakMap();
    return (_getRequireWildcardCache = function(nodeInterop) {
        return nodeInterop ? cacheNodeInterop : cacheBabelInterop;
    })(nodeInterop);
}
function _interop_require_wildcard(obj, nodeInterop) {
    if (!nodeInterop && obj && obj.__esModule) return obj;
    if (null === obj || "object" != typeof obj && "function" != typeof obj) return {
        default: obj
    };
    var cache = _getRequireWildcardCache(nodeInterop);
    if (cache && cache.has(obj)) return cache.get(obj);
    var newObj = {
        __proto__: null
    }, hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor;
    for(var key in obj)if ("default" !== key && Object.prototype.hasOwnProperty.call(obj, key)) {
        var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null;
        desc && (desc.get || desc.set) ? Object.defineProperty(newObj, key, desc) : newObj[key] = obj[key];
    }
    return newObj.default = obj, cache && cache.set(obj, newObj), newObj;
}
const _default = {
    Button: _toolbarpro,
    Icon: _toolbarpro1,
    Image: _toolbarpro2,
    Video: _toolbarpro3,
    Audio: _toolbarpro4,
    Map: _toolbarpro5,
    Column: _toolbarpro6,
    Row: _toolbarpro7,
    SectionItem: _toolbarpro8,
    StoryItem: _toolbarpro9,
    SectionFooter: _toolbarpro10,
    SectionHeaderItem: _toolbarpro11,
    SectionHeaderStickyItem: _toolbarpro12,
    SectionPopup: _toolbarpro13,
    SectionPopup2: _toolbarpro14,
    Lottie: _toolbarpro15,
    PostTitle: /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(64)),
    PostExcerpt: /*#__PURE__*/ _interop_require_wildcard(__webpack_require__(65))
};


/***/ }),
/* 48 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getItems = ({ component, editorMode })=>{
    let { t, utils: { context: { isPopup }, getEnabledLinkOptions } } = global.Brizy, _isPopup = isPopup(editorMode), inPopup2 = !!(component.props.meta && component.props.meta.sectionPopup2), { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig());
    return [
        {
            id: "toolbarLink",
            type: "popover",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    config: {
                        saveTab: !0
                    },
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: !linkUpload
                                }
                            ]
                        },
                        {
                            id: "action",
                            label: t("Action"),
                            options: [
                                {
                                    id: "actionClosePopup",
                                    label: t("Close Popup"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: !(inPopup2 || _isPopup)
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 49 */
/***/ ((__unused_webpack_module, exports) => {


function getItems({ component, editorMode }) {
    let { t, utils: { context: { isPopup }, getEnabledLinkOptions } } = global.Brizy, inPopup2 = !!(component.props.meta && component.props.meta.sectionPopup2), { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig());
    return [
        {
            id: "toolbarLink",
            type: "popover",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    config: {
                        saveTab: !0
                    },
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: !linkUpload
                                }
                            ]
                        },
                        {
                            id: "action",
                            label: t("Action"),
                            options: [
                                {
                                    id: "actionClosePopup",
                                    label: t("Close Popup"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: !(inPopup2 || isPopup(editorMode))
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            id: "advancedSettings",
            devices: "desktop",
            type: "advancedSettings",
            roles: [
                "admin"
            ],
            position: 110,
            title: t("Settings")
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 50 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getItems = ({ component, editorMode })=>{
    let { t, utils: { context: { isPopup, isStory }, stateMode: { NORMAL, HOVER }, getEnabledLinkOptions } } = global.Brizy, inPopup2 = !!component.props.meta.sectionPopup2, { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig()), isDisabledLinkUpload = isStory(editorMode) || !linkUpload;
    return [
        {
            id: "toolbarImage",
            type: "popover",
            options: [
                {
                    id: "media",
                    type: "tabs",
                    tabs: [
                        {
                            id: "tabFilters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "image",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            id: "toolbarLink",
            type: "popover",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: isDisabledLinkUpload,
                                    devices: "desktop"
                                }
                            ]
                        },
                        {
                            id: "action",
                            label: t("Action"),
                            options: [
                                {
                                    id: "actionClosePopup",
                                    label: t("Close Popup"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: !(inPopup2 || isPopup(editorMode))
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 51 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getItems = ()=>{
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "tabCurrentElementFilter",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ],
                                    selector: "{{WRAPPER}}:hover .brz-iframe, {{WRAPPER}}:hover .brz-video__cover:before, {{WRAPPER}}:hover.brz-custom-video video, {{WRAPPER}}:hover .brz-video__cover::before"
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 52 */
/***/ ((__unused_webpack_module, exports) => {


function getItems({ v, device, component }) {
    let { t, utils: { defaultValueValue, isBackgroundPointerEnabled } } = global.Brizy, dvv = (key)=>defaultValueValue({
            v,
            key,
            device
        }), soundCloudType = "soundcloud" === dvv("type"), isPointerEnabled = isBackgroundPointerEnabled(component.getGlobalConfig(), "audio");
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "tabCurrentElementUpload",
                            label: t("Audio"),
                            options: [
                                {
                                    id: "type",
                                    label: t("Audio"),
                                    type: "select",
                                    devices: "desktop",
                                    position: 20,
                                    choices: [
                                        {
                                            value: "soundcloud",
                                            title: t("SoundCloud")
                                        },
                                        {
                                            value: "custom",
                                            title: t("Custom")
                                        }
                                    ]
                                },
                                {
                                    id: "audio",
                                    type: "fileUpload",
                                    label: t("File"),
                                    config: {
                                        allowedExtensions: [
                                            ".mp3",
                                            ".ogg",
                                            ".wav"
                                        ]
                                    },
                                    devices: "desktop",
                                    disabled: soundCloudType
                                },
                                {
                                    id: "groupIconSize",
                                    type: "group",
                                    disabled: soundCloudType,
                                    options: [
                                        {
                                            id: "iconSize",
                                            label: t("Icons"),
                                            type: "radioGroup",
                                            devices: "desktop",
                                            choices: [
                                                {
                                                    value: "small",
                                                    icon: "nc-16"
                                                },
                                                {
                                                    value: "medium",
                                                    icon: "nc-24"
                                                },
                                                {
                                                    value: "large",
                                                    icon: "nc-32"
                                                },
                                                {
                                                    value: "custom",
                                                    icon: "nc-more"
                                                }
                                            ]
                                        },
                                        {
                                            id: "iconCustomSize",
                                            type: "slider",
                                            disabled: "custom" !== dvv("iconSize"),
                                            config: {
                                                min: 1,
                                                max: 100,
                                                units: [
                                                    {
                                                        title: "px",
                                                        value: "px"
                                                    }
                                                ]
                                            }
                                        }
                                    ]
                                },
                                {
                                    id: "loop",
                                    label: t("Loop"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: soundCloudType
                                }
                            ]
                        },
                        {
                            id: "tabCurrentElementCover",
                            label: t("Cover"),
                            devices: "desktop",
                            options: [
                                {
                                    label: t("Cover"),
                                    id: "cover",
                                    type: "imageUpload",
                                    disabled: soundCloudType,
                                    config: {
                                        pointer: isPointerEnabled,
                                        edit: "desktop" === device
                                    }
                                },
                                {
                                    id: "coverZoom",
                                    label: t("Zoom"),
                                    type: "slider",
                                    disabled: soundCloudType,
                                    config: {
                                        min: 100,
                                        max: 300,
                                        units: [
                                            {
                                                value: "%",
                                                title: "%"
                                            }
                                        ]
                                    }
                                }
                            ]
                        },
                        {
                            id: "tabCurrentElementAdvanced",
                            label: t("Advanced"),
                            options: [
                                {
                                    id: "showCurrentTime",
                                    label: t("Time"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: soundCloudType
                                },
                                {
                                    id: "showDurationTime",
                                    label: t("Duration"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: soundCloudType
                                },
                                {
                                    id: "showProgressBarTrack",
                                    label: t("Progress"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: soundCloudType
                                },
                                {
                                    id: "showProgressBarVolume",
                                    label: t("Volume"),
                                    type: "switch",
                                    devices: "desktop",
                                    disabled: soundCloudType
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            id: "popoverTypography",
            type: "popover",
            config: {
                icon: "nc-font",
                size: "desktop" === device ? "large" : "auto",
                title: t("Typography")
            },
            roles: [
                "admin"
            ],
            position: 90,
            disabled: "on" !== dvv("showCurrentTime") && "on" !== dvv("showDurationTime"),
            options: [
                {
                    id: "",
                    type: "typography",
                    disabled: soundCloudType,
                    config: {
                        fontFamily: "desktop" === device
                    }
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 53 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getItems = ()=>{
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            devices: "desktop",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "filters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ],
                                    selector: "{{WRAPPER}}:hover .brz-ui-ed-iframe"
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 54 */
/***/ ((__unused_webpack_module, exports) => {


function getItems({ component }) {
    let { t, utils: { stateMode: { NORMAL, HOVER }, getEnabledLinkOptions } } = global.Brizy, { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig());
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "filters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            id: "toolbarLink",
            type: "popover",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    config: {
                        saveTab: !0
                    },
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: !linkUpload
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            id: "toolbarSettings",
            type: "popover",
            config: {
                icon: "nc-cog",
                title: t("Settings")
            },
            position: 110,
            devices: "desktop",
            options: [
                {
                    id: "grid",
                    type: "grid",
                    config: {
                        separator: !0
                    },
                    columns: [
                        {
                            id: "grid-settings",
                            width: 50,
                            options: [
                                {
                                    id: "styles",
                                    type: "sidebarTabsButton",
                                    config: {
                                        text: t("Styling"),
                                        icon: "nc-cog",
                                        tabId: "styles"
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 55 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getItems = ({ component })=>{
    let { t, utils: { stateMode: { NORMAL, HOVER }, getEnabledLinkOptions } } = global.Brizy, { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig());
    return [
        {
            id: "toolbarMedia",
            type: "popover",
            options: [
                {
                    id: "tabsMedia",
                    type: "tabs",
                    tabs: [
                        {
                            id: "tabFilters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            id: "toolbarLink",
            type: "popover",
            devices: "responsive",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    config: {
                        saveTab: !0
                    },
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: !linkUpload
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 56 */
/***/ ((__unused_webpack_module, exports) => {


function getItems() {
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "filters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 57 */
/***/ ((__unused_webpack_module, exports) => {


function getItems() {
    let { t } = global.Brizy;
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "filters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters"
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 58 */
/***/ ((__unused_webpack_module, exports) => {


function getItems() {
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "filters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 59 */
/***/ ((__unused_webpack_module, exports) => {


function getItems() {
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "filters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 60 */
/***/ ((__unused_webpack_module, exports) => {


function getItems() {
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarCurrentElement",
            type: "popover",
            options: [
                {
                    id: "tabsCurrentElement",
                    type: "tabs",
                    tabs: [
                        {
                            id: "filters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 61 */
/***/ ((__unused_webpack_module, exports) => {


function getItems() {
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarMedia",
            type: "popover",
            options: [
                {
                    id: "tabsMedia",
                    type: "tabs",
                    tabs: [
                        {
                            id: "tabFilters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            id: "advancedSettings",
            type: "advancedSettings",
            devices: "desktop",
            roles: [
                "admin"
            ],
            position: 110,
            title: t("Settings")
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 62 */
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));
const getItems = ()=>{
    let { t, utils: { stateMode: { NORMAL, HOVER } } } = global.Brizy;
    return [
        {
            id: "toolbarMedia",
            type: "popover",
            options: [
                {
                    id: "tabsMedia",
                    type: "tabs",
                    tabs: [
                        {
                            id: "tabFilters",
                            label: t("Filters"),
                            options: [
                                {
                                    id: "",
                                    type: "filters",
                                    states: [
                                        NORMAL,
                                        HOVER
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
};


/***/ }),
/* 63 */
/***/ ((__unused_webpack_module, exports) => {


function getItems({ component }) {
    let { t, utils: { getEnabledLinkOptions } } = global.Brizy, { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig());
    return [
        {
            id: "toolbarLink",
            type: "popover",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    config: {
                        saveTab: !0
                    },
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: !linkUpload
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 64 */
/***/ ((__unused_webpack_module, exports) => {


function getItems({ component }) {
    let { t, utils: { getEnabledLinkOptions } } = global.Brizy, { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig());
    return [
        {
            id: "toolbarLink",
            type: "popover",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    config: {
                        saveTab: !0
                    },
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: !linkUpload
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 65 */
/***/ ((__unused_webpack_module, exports) => {


function getItems({ component }) {
    let { t, utils: { getEnabledLinkOptions } } = global.Brizy, { linkUpload } = getEnabledLinkOptions(component.getGlobalConfig());
    return [
        {
            id: "toolbarLink",
            type: "popover",
            options: [
                {
                    id: "linkType",
                    type: "tabs",
                    config: {
                        saveTab: !0
                    },
                    tabs: [
                        {
                            id: "upload",
                            label: t("File"),
                            options: [
                                {
                                    id: "linkUpload",
                                    label: t("File"),
                                    type: "fileUpload",
                                    disabled: !linkUpload
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ }),
/* 66 */
/***/ ((__unused_webpack_module, exports) => {


function getItems() {
    return [];
}
Object.defineProperty(exports, "__esModule", ({
    value: !0
})), Object.defineProperty(exports, "getItems", ({
    enumerable: !0,
    get: function() {
        return getItems;
    }
}));


/***/ })
/******/ 	]);
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
var exports = __webpack_exports__;

Object.defineProperty(exports, "__esModule", ({
    value: !0
}));
const _sidebars = /*#__PURE__*/ _interop_require_default(__webpack_require__(1)), _toolbars = /*#__PURE__*/ _interop_require_default(__webpack_require__(46));
function _interop_require_default(obj) {
    return obj && obj.__esModule ? obj : {
        default: obj
    };
}
const Brizy = global.Brizy;
for (let [component, toolbarExtend] of Object.entries(_toolbars.default))Brizy.addFilter(`toolbarItemsExtend_${component}`, ()=>toolbarExtend);
for (let [component, sidebarExtend] of Object.entries(_sidebars.default))Brizy.addFilter(`sidebarItemsExtend_${component}`, ()=>sidebarExtend);

})();

module.exports = __webpack_exports__;
/******/ })()
;