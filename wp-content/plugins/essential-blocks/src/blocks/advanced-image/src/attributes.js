import {
    WRAPPER_BG,
    WRAPPER_MARGIN,
    WRAPPER_PADDING,
    WRAPPER_BORDER_SHADOW,
    IMAGE_WIDTH,
    IMAGE_HEIGHT,
    IMAGE_BORDER_SHADOW,
    CAPTION_MARGIN,
    CAPTION_PADDING,
    CAPTION_WIDTH,
    TEXT_ALIGNMENT,
    IMAGE_ALIGNMENT,
} from "./constants";
import { CAPTION_TYPOGRAPHY } from "./constants/typoConstants";

import {
    generateDimensionsAttributes,
    generateTypographyAttributes,
    generateBackgroundAttributes,
    generateBorderShadowAttributes,
    generateResponsiveRangeAttributes,
    generateResponsiveAlignAttributes,
    ImageComponent,
} from "@essential-blocks/controls";

const attributes = {
    resOption: {
        type: "string",
        default: "Desktop",
    },

    // blockId attribute for making unique className and other uniqueness
    blockId: {
        type: "string",
    },
    blockRoot: {
        type: "string",
        default: "essential_block",
    },
    blockMeta: {
        type: "object",
    },
    image: {
        type: "object",
        default: {
            id: "",
            url: "",
            alt: "",
        },
    },
    imageCaption: {
        type: "string",
    },
    selectedImgIndex: {
        type: "number",
    },
    displayCaption: {
        type: "boolean",
        default: false,
    },
    captionColor: {
        type: "string",
        default: "var(--eb-global-primary-color)",
    },
    captionBGColor: {
        type: "string",
        default: "var(--eb-global-background-color)",
    },
    horizontalAlign: {
        type: "string",
        default: "center",
    },
    verticalAlign: {
        type: "string",
        default: "bottom",
    },
    verticalAlignCap2: {
        type: "string",
        default: "bottom",
    },
    hoverEffect: {
        type: "string",
        default: "no-effect",
    },
    stylePreset: {
        type: "string",
        default: "rounded",
    },
    complexStyle: {
        type: "boolean",
        default: false,
    },
    captionStyle: {
        type: "string",
        default: "caption-style-1",
    },
    autoFit: {
        type: "boolean",
        default: true,
    },
    fitStyles: {
        type: "string",
        default: "cover",
    },
    enableLink: {
        type: "boolean",
        default: false,
    },
    imageLink: {
        type: "string",
        default: "",
    },
    openInNewTab: {
        type: "boolean",
        default: false,
    },
    autoHeight: {
        type: "boolean",
        default: true,
    },
    imgSource: {
        type: "string",
        // default: "custom",
    },
    logoUrl: {
        type: "string",
        default: "",
    },
    shouldSyncIcon: {
        type: "boolean",
        default: false,
    },
    rel: {
        type: "string",
        attribute: "rel",
        default: "",
    },
    imagePostId: {
        type: "number",
        default: 0,
    },

    // typography attributes ⬇
    ...generateTypographyAttributes(CAPTION_TYPOGRAPHY, {
        fontSize: 13,
    }),

    // margin padding attributes ⬇
    ...generateDimensionsAttributes(WRAPPER_MARGIN),
    ...generateDimensionsAttributes(WRAPPER_PADDING),
    ...generateDimensionsAttributes(CAPTION_MARGIN, {
        top: 0,
        bottom: 0,
        right: 0,
        left: 0,
        isLinked: false,
    }),
    ...generateDimensionsAttributes(CAPTION_PADDING, {
        top: 15,
        bottom: 15,
        right: 10,
        left: 10,
        isLinked: false,
    }),

    // border shadow attributes ⬇
    ...generateBorderShadowAttributes(WRAPPER_BORDER_SHADOW, {
        bdrDefaults: {
            top: 0,
            bottom: 0,
            right: 0,
            left: 0,
        },
        // noShadow: true,
        // noBorder: true,
    }),
    ...generateBorderShadowAttributes(IMAGE_BORDER_SHADOW, {
        defaultBdrColor: "#AE62D1",
        bdrDefaults: {
            top: 0,
            bottom: 0,
            right: 0,
            left: 0,
        },
        rdsDefaults: {
            top: 15,
            bottom: 15,
            right: 15,
            left: 15,
        },
        // noShadow: true,
        // noBorder: true,
    }),
    // background attributes ⬇
    ...generateBackgroundAttributes(WRAPPER_BG, {
        defaultBgGradient: "linear-gradient(45deg,#ffffff,#ffffff)",
        noOverlay: true,
    }),
    // range controller Separator Caption Width
    ...generateResponsiveRangeAttributes(CAPTION_WIDTH),
    ...generateResponsiveAlignAttributes(TEXT_ALIGNMENT, {
        defaultAlign: "center",
        defaultTabAlign: "center",
        defaultMobileAlign: "center",
    }),
    // ...generateResponsiveRangeAttributes(IMAGE_WIDTH, {
    //     // defaultRange: 300,
    // }),
    ...ImageComponent?.addAttributes({
        border: {
            key: IMAGE_BORDER_SHADOW,
            default: {
                defaultBdrColor: "#AE62D1",
                bdrDefaults: {
                    top: 0,
                    bottom: 0,
                    right: 0,
                    left: 0,
                },
            },
        },
        hasWidth: true,
        width: {
            key: IMAGE_WIDTH,
        },
        hasHeight: true,
        height: {
            key: IMAGE_HEIGHT,
        },
        useImageAlign: true,
        imageAlign: {
            key: IMAGE_ALIGNMENT,
            default: {
                defaultAlign: "0 auto",
                defaultTabAlign: "0 auto",
                defaultMobileAlign: "0 auto",
            },
        },
        hasRadius: true,
    }),
};

export default attributes;
