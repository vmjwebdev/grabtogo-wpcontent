import {
    typoPrefix_number,
    typoPrefix_title,
    typoPrefix_subTitle,
    typoPrefix_content,
    typoPrefix_buttonText,
} from "./constants/typographyPrefixConstants";
import {
    mediaBackground,
    mediaBgRadius,
    mediaBgMargin,
    // buttonRadius,
    buttonPadding,
    contentPadding,
    titlePadding,
    subTitlePadding,
    wrapperMargin,
    wrapperPadding,
} from "./constants/dimensionsConstants";

import { infoWrapBg, infoBtnBg } from "./constants/backgroundsConstants";

import {
    wrpBdShadow,
    btnBdShd,
    mediaBdShd,
} from "./constants/borderShadowConstants";
import { BUTTON_KEYS } from "./constants";

import {
    mediaIconSize,
    mediaImageWidth,
    mediaImageHeight,
    mediaContentGap,
} from "./constants/rangeNames";

import {
    generateDimensionsAttributes,
    generateTypographyAttributes,
    generateBackgroundAttributes,
    generateBorderShadowAttributes,
    generateResponsiveRangeAttributes,
    EBButton,
    ImageComponent,
} from "@essential-blocks/controls";

const attributes = {
    // the following 4 attributes is must required for responsive options and asset generation for frontend
    // responsive control attributes ⬇
    resOption: {
        type: "string",
        default: "Desktop",
    },

    // blockId attribute for making unique className and other uniqueness ⬇
    blockId: {
        type: "string",
    },
    blockRoot: {
        type: "string",
        default: "essential_block",
    },
    version: {
        type: "string",
        default: "1",
    },

    // blockMeta is for keeping all the styles ⬇
    blockMeta: {
        type: "object",
    },

    // isOverlay is to check if a overlay on the block's background should exist ⬇
    isOverlay: {
        type: "boolean",
        default: false,
    },

    // this attribute is for selecting the desired design preset from the layout design presets options ⬇
    layoutPreset: {
        type: "string",
        default: "preset1",
    },

    // media attribute is for checking which of these (image / icon / number) is chosen for head top media ⬇
    media: {
        type: "string",
        default: "icon",
    },

    //
    numIconColor: {
        type: "string",
        default: "var(--eb-global-background-color)",
    },

    // should icon number background
    useNumIconBg: {
        type: "boolean",
        default: true,
    },

    //
    numIconBgType: {
        type: "string",
        default: "fill",
    },

    //
    numIconBgColor: {
        type: "string",
        default: "var(--eb-global-primary-color)",
    },

    //
    numIconBgGradient: {
        type: "string",
        default: "var(--eb-global-background-color)",
    },

    //
    // imageUrl: {
    //     source: "attribute",
    //     selector: ".eb-infobox-image",
    //     attribute: "src",
    //     // default: "https://source.unsplash.com/user/cristofer",
    // },

    //
    // imageId: {
    //     type: "string",
    // },

    // imageAlt: {
    //     type: "string",
    // },

    //
    mediaImgWidthUnit: {
        type: "string",
        default: "px",
    },
    mediaImgWidth: {
        type: "number",
        default: 300,
    },
    TABmediaImgWidth: {
        type: "number",
    },
    MOBmediaImgWidth: {
        type: "number",
    },

    //
    isMediaImgHeightAuto: {
        type: "boolean",
        default: true,
    },
    mediaImgHeightUnit: {
        type: "string",
        default: "px",
    },
    mediaImgHeight: {
        type: "number",
    },
    TABmediaImgHeight: {
        type: "number",
    },
    MOBmediaImgHeight: {
        type: "number",
    },

    // cant delete it as we migrate it to infoboxIcon attribute
    selectedIcon: {
        type: "string",
        source: "attribute",
        selector: ".eb-infobox-icon-data-selector",
        attribute: "data-icon",
        default: "far fa-gem",
        // default: "far fa-sun",
    },
    infoboxIcon: {
        type: "string",
        default: "far fa-gem",
    },

    //
    number: {
        type: "string",
    },

    // this attribute is for checking whether subtitle should be shown or not ⬇
    enableSubTitle: {
        type: "boolean",
        default: false,
    },

    //  this attribute is for checking whether content should be shown or not ⬇
    enableDescription: {
        type: "boolean",
        default: true,
    },

    // this attribute is for checking whether a button should be shown or not ⬇
    enableButton: {
        type: "boolean",
        default: true,
    },

    //
    isInfoClick: {
        type: "boolean",
        default: false,
    },

    //

    // //
    // buttonBgColor: {
    // 	type: "string",
    // },

    //

    //
    title: {
        type: "text",
        selector: ".title",
        default: "This is an info box",
    },

    //
    titleColor: {
        type: "string",
        default: "var(--eb-global-heading-color)",
    },

    //
    subTitle: {
        type: "text",
        selector: ".subtitle",
        default: "This is a Sub title",
    },

    //
    subTitleColor: {
        type: "string",
        default: "var(--eb-global-text-color)",
    },

    //
    description: {
        type: "text",
        selector: ".description",
        default:
            "Write a short description, that will describe the title or something informational and useful",
    },

    //
    descriptionColor: {
        type: "string",
        default: "var(--eb-global-text-color)",
    },

    //
    iconSize: {
        type: "number",
        default: 50,
    },
    TABiconSize: {
        type: "number",
    },
    MOBiconSize: {
        type: "number",
    },

    // .infobox-wrapper-inner flex-direction
    flexDirection: {
        type: "string",
    },

    // .icon-img-wrapper align-self property
    mediaAlignSelf: {
        type: "string",
    },

    // .contents-wrapper text-alignment
    contentAlignment: {
        type: "string",
    },

    //
    titleTag: {
        type: "string",
        default: "h2",
    },
    subTitleTag: {
        type: "string",
        default: "h3",
    },

    //
    mediaAlignment: {
        type: "string",
        default: "center",
    },

    //
    contentsAlignment: {
        type: "string",
        default: "center",
    },

    // btnAlignment: {
    //     type: "string",
    //     default: "center",
    // },

    enableTitle: {
        type: "boolean",
        default: true,
    },
    showMedia: {
        type: "boolean",
        default: true,
    },
    iconView: {
        type: "string",
        default: "default",
    },
    iconShape: { type: "string", default: "circle" },

    // Responsive Range Controller attributes
    ...generateResponsiveRangeAttributes(mediaIconSize, {
        defaultRange: 50,
    }),
    ...generateResponsiveRangeAttributes(mediaContentGap, {
        defaultRange: 20,
        noUnits: true,
    }),

    // typography attributes
    // ...generateTypographyAttributes(Object.values(typoPrefixs)),
    ...generateTypographyAttributes(typoPrefix_number, {
        fontSize: 28,
    }),
    ...generateTypographyAttributes(typoPrefix_title, {
        fontSize: 26,
    }),
    ...generateTypographyAttributes(typoPrefix_subTitle, {
        fontSize: 22,
    }),
    ...generateTypographyAttributes(typoPrefix_content, {
        fontSize: 20,
    }),
    ...generateTypographyAttributes(typoPrefix_buttonText, {
        fontSize: 20,
    }),

    // dimensions Control related Attributes
    ...generateDimensionsAttributes(mediaBackground, {
        top: 20,
        bottom: 20,
        right: 20,
        left: 20,
    }),
    ...generateDimensionsAttributes(mediaBgRadius, {
        top: 20,
        bottom: 20,
        isLinked: false,
    }),
    ...generateDimensionsAttributes(titlePadding, {
        top: 10,
        bottom: 10,
        isLinked: false,
    }),
    ...generateDimensionsAttributes(subTitlePadding, {
        top: 10,
        bottom: 10,
        isLinked: false,
    }),
    ...generateDimensionsAttributes(contentPadding, {
        top: 10,
        bottom: 50,
        isLinked: false,
    }),

    ...generateDimensionsAttributes(wrapperMargin),
    ...generateDimensionsAttributes(wrapperPadding, {
        top: 50,
        bottom: 70,
        right: 30,
        left: 30,
        isLinked: false,
    }),

    // ...generateDimensionsAttributes(wrp_border, {
    // 	top: 0,
    // 	bottom: 0,
    // 	right: 0,
    // 	left: 0,
    // }),
    // ...generateDimensionsAttributes(wrp_radius),

    ...generateBorderShadowAttributes(wrpBdShadow, {
        // bdrDefaults: {
        // 	top: 10,
        // 	bottom: 0,
        // 	right: 10,
        // 	left: 0,
        // 	isLinked: false,
        // },
        // rdsDefaults: {
        // 	top: 0,
        // 	bottom: 50,
        // 	right: 500,
        // 	left: 1000,
        // 	isLinked: false,
        // },
        // noShadow: true,
        // noBorder: true,
        // defaultBdrColor: "#f2f",
        // defaultBdrStyle: "solid",
    }),

    // background attributes ⬇
    ...generateBackgroundAttributes(infoWrapBg, {
        isBgDefaultGradient: true,
        defaultBgGradient:
            "linear-gradient(90deg, rgba(249,250,251,1) 0% , rgba(250,250,250,1) 100%)",
        // noOverlay: true,
        // noMainBgi: true,
        // noOverlayBgi: true, // if 'noOverlay : true' is given then there's no need to give 'noOverlayBgi : true'
    }),
    ...EBButton?.addAttributes(
        BUTTON_KEYS,
        "",
        false,
        "",
        true,
        {
            key: infoBtnBg,
            default: {
                noOverlay: true,
                noMainBgi: true,
                defaultFillColor: "var(--eb-global-button-background-color)",
                defaultBgGradient: "var(--eb-gradient-background-color)",
            },
        },
        true,
        {
            key: btnBdShd,
            default: {
                bdrDefaults: {
                    top: 2,
                    bottom: 2,
                    right: 2,
                    left: 2,
                },
                rdsDefaults: {
                    top: 10,
                    bottom: 10,
                    right: 10,
                    left: 10,
                },
                // noShadow: true,
                // noBorder: true,
            },
        },
        true,
        {
            key: buttonPadding,
            default: {
                top: 15,
                bottom: 15,
                right: 30,
                left: 30,
                isLinked: false,
            },
        },
    ),
    ...ImageComponent?.addAttributes({
        border: {
            key: mediaBdShd,
        },
        margin: {
            key: mediaBgMargin,
            default: {
                top: 15,
                isLinked: false,
            },
        },
        width: {
            key: mediaImageWidth,
            default: {
                defaultRange: 300,
            },
        },
        height: {
            key: mediaImageHeight,
        }
    }),
};

export default attributes;
